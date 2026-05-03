#!/usr/bin/env bash
# Audit domain form pages for drift from the project's standard pattern.
#
# Standard pattern (see CLAUDE.md "Form pages: 12-col layout"):
#   1. Form.php uses App\Livewire\Concerns\WithNotes (directly or via parent class)
#      and defines getNotableModel(), so $this->activitiesAndNotes works.
#   2. form.blade.php renders <x-ui.chatter-buttons> AND <x-ui.chatter-forms>
#      together (the buttons toggle the forms).
#   3. form.blade.php uses the canonical 12-col layout: lg:grid-cols-12 with
#      lg:col-span-9 form + lg:col-span-3 right panel.
#   4. Alpine x-data declares showSendMessage / showLogNote / showScheduleActivity
#      so the chatter buttons actually toggle anything.
#
# A "domain form" is detected by the presence of <x-ui.chatter-forms> in the
# view; settings forms (Company, Email, Localization) and other single-form
# config pages don't have a chatter panel and are skipped.
#
# Exits with the number of findings.
#
# Usage: bin/audit-form-pages.sh [--quiet]

set -uo pipefail

cd "$(dirname "$0")/.." || exit 1

QUIET=0
[[ "${1:-}" == "--quiet" ]] && QUIET=1

LIVEWIRE_DIR="app/Livewire"
VIEWS_DIR="resources/views/livewire"

findings=()

# -------------------------------------------------------- helpers

# component_uses_with_notes "<absolute path to Form.php>"
# Returns 0 if the file uses WithNotes directly OR extends a class whose file
# uses WithNotes. Resolves PHP `use ... as Alias` imports so subclasses like
# `class Form extends RfqForm` (aliased from Purchase\Rfq\Form) resolve correctly.
component_uses_with_notes() {
    local f="$1"
    [[ ! -f "$f" ]] && return 1
    grep -q "use .*WithNotes\|use WithNotes\b" "$f" && return 0

    local parent
    parent=$(grep -oE "class\s+\w+\s+extends\s+\w+" "$f" | awk '{print $NF}')
    [[ -z "$parent" || "$parent" == "Component" ]] && return 1

    # If the parent is an alias, resolve via `use ... as Parent;` to the real FQN.
    local fqn
    fqn=$(grep -oE "^\s*use\s+[A-Za-z\\\\]+\s+as\s+$parent\s*;" "$f" | awk '{print $2}')
    if [[ -n "$fqn" ]]; then
        local rel
        rel=$(echo "$fqn" | tr '\\' '/')
        rel="${rel#App/}"
        local parent_file="app/$rel.php"
        [[ -f "$parent_file" ]] && grep -q "use .*WithNotes\|use WithNotes\b" "$parent_file" && return 0
    fi

    # Fallback: locate by class basename (only works when not aliased).
    local parent_file
    parent_file=$(find "$LIVEWIRE_DIR" -name "$parent.php" 2>/dev/null | head -1)
    [[ -z "$parent_file" ]] && return 1
    grep -q "use .*WithNotes\|use WithNotes\b" "$parent_file"
}

# Map a view path → component path. Kebab-cased segments (`journal-entries`,
# `payment-terms`) capitalize each hyphen-separated word: `JournalEntries`.
view_to_component() {
    local view_rel="$1"          # e.g. accounting/journal-entries/form.blade.php
    local dir="${view_rel%/form.blade.php}"
    local ns
    ns=$(echo "$dir" | awk -F'/' '{
        for (i = 1; i <= NF; i++) {
            n = split($i, parts, "-")
            out = ""
            for (j = 1; j <= n; j++) {
                out = out toupper(substr(parts[j], 1, 1)) substr(parts[j], 2)
            }
            $i = out
        }
        print
    }' OFS='/')
    echo "$LIVEWIRE_DIR/$ns/Form.php"
}

# -------------------------------------------------------- finding #1
# Form.php using WithNotes must define getNotableModel().
while IFS= read -r f; do
    grep -q "use .*WithNotes\|use WithNotes\b" "$f" || continue
    if ! grep -q "getNotableModel" "$f"; then
        findings+=("missing-getNotableModel: ${f#"$LIVEWIRE_DIR"/} uses WithNotes but does not define getNotableModel()")
    fi
done < <(find "$LIVEWIRE_DIR" -name "Form.php")

# -------------------------------------------------------- finding #2
# Domain form views must use BOTH chatter-buttons AND chatter-forms.
while IFS= read -r v; do
    rel="${v#"$VIEWS_DIR"/}"
    has_forms=$(grep -c "x-ui.chatter-forms" "$v")
    has_buttons=$(grep -c "x-ui.chatter-buttons" "$v")
    [[ "$has_forms" -eq 0 && "$has_buttons" -eq 0 ]] && continue
    if [[ "$has_forms" -gt 0 && "$has_buttons" -eq 0 ]]; then
        findings+=("decoupled-chatter: $rel uses x-ui.chatter-forms without x-ui.chatter-buttons (likely hand-rolled buttons)")
    fi
    if [[ "$has_buttons" -gt 0 && "$has_forms" -eq 0 ]]; then
        findings+=("decoupled-chatter: $rel uses x-ui.chatter-buttons without x-ui.chatter-forms")
    fi
done < <(find "$VIEWS_DIR" -name "form.blade.php")

# -------------------------------------------------------- finding #3
# Domain form views (those using chatter-forms) must use the 12-col layout.
while IFS= read -r v; do
    rel="${v#"$VIEWS_DIR"/}"
    grep -q "x-ui.chatter-forms" "$v" || continue
    if ! grep -q "lg:grid-cols-12" "$v"; then
        findings+=("missing-12-col-layout: $rel does not use lg:grid-cols-12 (canonical 9/3 split)")
    fi
done < <(find "$VIEWS_DIR" -name "form.blade.php")

# -------------------------------------------------------- finding #4
# Views using chatter-buttons must declare the Alpine flags those buttons toggle.
# Each chatter-button has a prop to disable it (showMessage / showNote / showActivity).
# Only flag a missing flag when the corresponding button is actually rendered.
while IFS= read -r v; do
    rel="${v#"$VIEWS_DIR"/}"
    grep -q "x-ui.chatter-buttons" "$v" || continue

    # Send Message button → requires showSendMessage flag (unless :showMessage="false").
    if ! grep -q ':showMessage="false"' "$v" && ! grep -q "showSendMessage" "$v"; then
        findings+=("missing-alpine-flag: $rel uses chatter-buttons (Send Message enabled) but x-data does not declare showSendMessage")
    fi
    # Log Note button → requires showLogNote flag (unless :showNote="false").
    if ! grep -q ':showNote="false"' "$v" && ! grep -q "showLogNote" "$v"; then
        findings+=("missing-alpine-flag: $rel uses chatter-buttons (Log Note enabled) but x-data does not declare showLogNote")
    fi
    # Schedule Activity button → requires showScheduleActivity flag (unless :showActivity="false").
    if ! grep -q ':showActivity="false"' "$v" && ! grep -q "showScheduleActivity" "$v"; then
        findings+=("missing-alpine-flag: $rel uses chatter-buttons (Schedule Activity enabled) but x-data does not declare showScheduleActivity")
    fi
done < <(find "$VIEWS_DIR" -name "form.blade.php")

# -------------------------------------------------------- finding #5
# Views with chatter components must back a Form.php that uses WithNotes
# (directly or via parent class).
while IFS= read -r v; do
    rel="${v#"$VIEWS_DIR"/}"
    grep -q "x-ui.chatter-buttons\|x-ui.chatter-forms" "$v" || continue
    comp=$(view_to_component "$rel")
    if [[ ! -f "$comp" ]]; then
        findings+=("missing-component: $rel has chatter UI but cannot find $comp")
        continue
    fi
    if ! component_uses_with_notes "$comp"; then
        findings+=("missing-with-notes: $rel has chatter UI but ${comp#"$LIVEWIRE_DIR"/} does not use WithNotes")
    fi
done < <(find "$VIEWS_DIR" -name "form.blade.php")

# -------------------------------------------------------- report
if [[ "$QUIET" -eq 0 ]]; then
    if [[ "${#findings[@]}" -eq 0 ]]; then
        echo "Form-page audit: clean. No drift detected."
    else
        echo "Form-page audit: ${#findings[@]} finding(s)."
        echo ""
        for line in "${findings[@]}"; do
            echo "  - $line"
        done
        echo ""
        echo "See CLAUDE.md (\"Form pages: 12-col layout with right panel\") for the standard pattern."
    fi
fi

exit "${#findings[@]}"
