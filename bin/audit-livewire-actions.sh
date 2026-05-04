#!/usr/bin/env bash
# Audit Livewire actions for missing authorization on destructive operations.
#
# Route-level permission middleware gates page ACCESS — but a user who can
# view payroll might not be the user who can approve it. Action-level authz
# (\$this->authorizePermission('module.action')) is the right check for
# privileged sub-actions inside a page.
#
# This script flags public methods that look destructive (state transitions,
# bulk ops, email side effects) and don't contain any authorization marker.
# It does NOT auto-fix — exit code is the number of findings.
#
# Method-name patterns flagged:
#   approve, reject, post, confirm, cancel, archive, restore, finalize,
#   markAs{Won,Lost,Completed,Paid,Sent,Read,Viewed,Active,...},
#   send{Email,InvoiceEmail,TestEmail,Notification},
#   bulk{Delete,Archive,Activate,Deactivate,Cancel,Confirm},
#   forceDelete, validateAndPost, validateReceipt, recordPayment,
#   addPayment, payAll, void, revert, reopen
#
# Body-level markers also flag a method:
#   - calls \$model->transitionTo(...)
#   - sends mail/notification (Mail::to, Mail::send, Notification::send, ->notify)
#
# Authz markers — any of these clears a method:
#   - \$this->authorizePermission('...')
#   - \$this->authorize('...')
#   - Gate::allows/denies/check/authorize
#   - abort(403, ...)
#   - // authz: <reason>   (explicit opt-out for self-scoped actions)
#
# Usage:
#   bin/audit-livewire-actions.sh
#   bin/audit-livewire-actions.sh --quiet
#
# Run from repo root.

set -uo pipefail
cd "$(dirname "$0")/.." || exit 1

QUIET=0
[[ "${1:-}" == "--quiet" ]] && QUIET=1

LIVEWIRE_DIR="app/Livewire"

# Skip:
#   - Concerns/  (shared traits, not direct entrypoints — concrete components
#                 either inherit and override, or call the trait method behind
#                 their own check)
#   - Public/    (signed-URL routes; the signature is the trust boundary)
findings=$(find "$LIVEWIRE_DIR" -name "*.php" -type f \
    -not -path "*/Concerns/*" \
    -not -path "*/Public/*" | while read -r f; do
    rel="${f#"$LIVEWIRE_DIR"/}"
    awk -v file="$rel" '
        # New method declaration at 4-space indent (PSR-12).
        /^    public function [a-zA-Z_]+\s*\(/ {
            # Finalize any open method.
            if (in_method) check_method()

            # Extract method name.
            if (match($0, /public function [a-zA-Z_]+/)) {
                method = substr($0, RSTART + 16, RLENGTH - 16)
            }
            body = $0 "\n"
            lineno = NR
            in_method = 1
            next
        }

        # Method end: closing brace at 4-space indent.
        /^    }[[:space:]]*$/ && in_method {
            body = body $0 "\n"
            check_method()
            in_method = 0
            method = ""
            body = ""
            next
        }

        in_method { body = body $0 "\n" }

        END { if (in_method) check_method() }

        function check_method(   reason, has_authz, name_pattern) {
            reason = ""

            # Method-name patterns indicating destructive intent.
            name_pattern = "^(approve|reject|post|confirm|cancel|archive|restore|finalize|reverse|reopen|void|revert|forceDelete|validateAndPost|validateReceipt|recordPayment|addPayment|payAll|confirmOrder|sendEmail|sendInvoiceEmail|sendTestEmail|sendNotification|bulkDelete|bulkArchive|bulkActivate|bulkDeactivate|bulkCancel|bulkConfirm|markAs[A-Z][a-zA-Z]+)$"

            if (method ~ name_pattern) {
                reason = method "()"
            }

            # Body-level marker: state machine transition.
            if (body ~ /->transitionTo\(/) {
                reason = (reason ? reason " + " : "") "calls transitionTo()"
            }

            # Body-level marker: outbound email/notification side effect.
            if (body ~ /Mail::to\(|Mail::send\(|Notification::send\(|->notify\(/) {
                reason = (reason ? reason " + " : "") "sends mail/notification"
            }

            if (length(reason) == 0) return

            # Authz markers — any of these counts as "checked".
            has_authz = 0
            if (body ~ /authorizePermission\(/) has_authz = 1
            if (body ~ /Gate::(allows|denies|check|authorize)\(/) has_authz = 1
            if (body ~ /\$this->authorize\(/) has_authz = 1
            if (body ~ /abort\(403/) has_authz = 1
            # Explicit opt-out marker — for actions that are intentionally
            # public or have their own scoping (e.g. user mutating own row).
            # Add a brief reason after the colon.
            if (body ~ /\/\/ authz:/) has_authz = 1

            if (has_authz) return

            print file ":" lineno " " method "() — " reason
        }
    ' "$f"
done)

# Strip empty lines and count.
findings=$(printf "%s\n" "$findings" | sed '/^[[:space:]]*$/d')
count=$([[ -z "$findings" ]] && echo 0 || echo "$findings" | wc -l | tr -d ' ')

if [[ "$QUIET" -eq 0 ]]; then
    if [[ "$count" -eq 0 ]]; then
        echo "Livewire-action authz audit: clean. No drift detected."
    else
        echo "Livewire-action authz audit: $count finding(s)."
        echo ""
        echo "$findings" | sed 's/^/  - /'
        echo ""
        echo "Add \$this->authorizePermission('module.action') at the start of each."
        echo "If the action is intentionally public (e.g., user marking own"
        echo "notification read), add a brief inline comment explaining why."
    fi
fi

exit "$count"
