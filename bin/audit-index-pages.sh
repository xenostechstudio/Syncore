#!/usr/bin/env bash
# Audit list-style index pages for drift from the project's standard pattern.
#
# Standard pattern (see CLAUDE.md / README.md):
#   1. Component uses App\Livewire\Concerns\WithIndexComponent
#   2. View renders <x-ui.index-header> inside <x-slot:header>
#   3. Filters live inside <x-ui.searchbox-dropdown> with activeFilterCount + clearAction props
#   4. Components with extra #[Url] filter state override getCustomActiveFilterCount() AND
#      reset that state in clearFilters()
#
# This script does NOT auto-fix anything — it only reports. Exit code is the
# number of findings, so CI / a scheduled agent can fail loud or open a PR.
#
# Usage:
#   bin/audit-index-pages.sh                 # full report
#   bin/audit-index-pages.sh --quiet         # exit code only
#
# Run from repo root.

set -uo pipefail

cd "$(dirname "$0")/.." || exit 1

QUIET=0
[[ "${1:-}" == "--quiet" ]] && QUIET=1

LIVEWIRE_DIR="app/Livewire"
VIEWS_DIR="resources/views/livewire"

# Modules whose top-level Index.php is a dashboard (not a list page) — skip.
DASHBOARD_DIRS=(
    "Dashboard"
    "Reports"            # reports/ subfolders are dashboards
    "Sales/Reports"
    "Invoicing/Reports"
)

findings=()

is_dashboard() {
    local rel="$1"
    for d in "${DASHBOARD_DIRS[@]}"; do
        [[ "$rel" == "$d/"* ]] && return 0
    done
    return 1
}

# ---------------------------------------------------------------- finding #1
# Index.php files that look like list pages but don't use WithIndexComponent
# OR WithManualPagination (the only two acceptable bases for a list page).

while IFS= read -r f; do
    rel="${f#"$LIVEWIRE_DIR"/}"
    # Module-root Index.php (one path component) is the module landing page.
    [[ "$(echo "$rel" | tr '/' '\n' | wc -l | tr -d ' ')" -lt 3 ]] && continue
    is_dashboard "$rel" && continue
    # Promotions, single-form Settings/{Email,Localization,Company}, PaymentGateway etc
    # — none of these have a paginator. Filter out by checking for paginate() in the file.
    grep -q "paginate(" "$f" || continue
    if ! grep -q "WithIndexComponent\|WithManualPagination" "$f"; then
        findings+=("missing-trait: $rel does not use WithIndexComponent (or WithManualPagination)")
    fi
done < <(find "$LIVEWIRE_DIR" -name "Index.php")

# ---------------------------------------------------------------- finding #2
# Index views that don't render <x-ui.index-header>.

while IFS= read -r v; do
    rel="${v#"$VIEWS_DIR"/}"
    # Skip views in dashboard-only modules
    case "$rel" in
        reports/*|sales/reports/*|invoicing/reports/*|*/reports/*) continue ;;
    esac
    # Some pages legitimately don't paginate (e.g. settings/email is a single form).
    grep -q "paginate\|->links()\|->total()" "$v" || continue
    if ! grep -q "x-ui.index-header" "$v"; then
        findings+=("missing-chrome: $rel does not use <x-ui.index-header>")
    fi
done < <(find "$VIEWS_DIR" -name "index.blade.php")

# ---------------------------------------------------------------- finding #3
# searchbox-dropdown consumers missing activeFilterCount or clearAction.

while IFS= read -r v; do
    rel="${v#"$VIEWS_DIR"/}"
    has_count=$(grep -c "activeFilterCount" "$v")
    has_clear=$(grep -c "clearAction" "$v")
    if [[ "$has_count" -eq 0 || "$has_clear" -eq 0 ]]; then
        findings+=("partial-searchbox: $rel uses x-ui.searchbox-dropdown without activeFilterCount/clearAction")
    fi
done < <(grep -l "x-ui.searchbox-dropdown" -r "$VIEWS_DIR" --include="*.blade.php" 2>/dev/null || true)

# ---------------------------------------------------------------- finding #3b
# Views calling $this->getActiveFilterCount() must back a component that
# either uses WithIndexComponent (which provides it) or defines its own
# getActiveFilterCount() method. Otherwise hitting the page throws
# "Method ... ::getActiveFilterCount does not exist".

while IFS= read -r v; do
    rel="${v#"$VIEWS_DIR"/}"
    grep -q '\$this->getActiveFilterCount' "$v" || continue
    # Map the view path back to a Livewire component path. Index views can be
    # used by multiple components (e.g. Sales/Orders/Index AND Sales/Invoices/
    # OrdersToInvoice both render livewire.sales.orders.index), so check ALL
    # components whose render() returns this view.
    view_dotted=$(echo "${rel%.blade.php}" | tr '/' '.')
    while IFS= read -r f; do
        grep -q "view('livewire\.${view_dotted}'" "$f" || continue
        if ! grep -qE "WithIndexComponent|function getActiveFilterCount" "$f"; then
            findings+=("missing-getActiveFilterCount: ${f#"$LIVEWIRE_DIR"/} renders $rel which calls \$this->getActiveFilterCount() but the component lacks WithIndexComponent or its own getActiveFilterCount()")
        fi
    done < <(find "$LIVEWIRE_DIR" -name "*.php")
done < <(find "$VIEWS_DIR" -name "*.blade.php")

# ---------------------------------------------------------------- finding #4
# Index components with extra #[Url] filter props but no getCustomActiveFilterCount
# override (they undercount the filter pill) or whose clearFilters doesn't list them
# (the dropdown's "Clear all filters" leaves them set).

# Standard URL props provided by the trait — these never need a custom override.
STANDARD_URL_PROPS="search status sort view groupBy page perPage"
SAFE_PROPS_REGEX=$(echo "$STANDARD_URL_PROPS" | tr ' ' '|')
SAFE_PROPS_REGEX="^(${SAFE_PROPS_REGEX})\$"

while IFS= read -r f; do
    rel="${f#"$LIVEWIRE_DIR"/}"
    is_dashboard "$rel" && continue
    grep -q "WithIndexComponent\|WithManualPagination" "$f" || continue

    # Pull props declared right under #[Url] attributes.
    custom_props=$(awk '
        /^\s*#\[Url(\(|])/ { url=1; next }
        url && /^\s*public\s/ {
            match($0, /\$[a-zA-Z_]+/)
            if (RSTART) print substr($0, RSTART+1, RLENGTH-1)
            url=0; next
        }
        !/^\s*$/ && url { url=0 }
    ' "$f" | grep -vxE "$SAFE_PROPS_REGEX" || true)

    [[ -z "$custom_props" ]] && continue

    has_count_override=$(grep -c "getCustomActiveFilterCount\|getActiveFilterCount" "$f")
    clear_block=$(awk '/function clearFilters/,/^\s*}\s*$/' "$f")

    while IFS= read -r prop; do
        [[ -z "$prop" ]] && continue
        if [[ "$has_count_override" -eq 0 ]]; then
            findings+=("uncounted-filter: $rel has \$$prop but no getCustomActiveFilterCount() override")
        fi
        # clearFilters must mention the prop name to reset it.
        if [[ -n "$clear_block" ]] && ! echo "$clear_block" | grep -q "['\"]$prop['\"]\|->$prop\b"; then
            findings+=("unreset-filter: $rel clearFilters() does not reset \$$prop")
        fi
    done <<< "$custom_props"
done < <(find "$LIVEWIRE_DIR" -name "Index.php")

# ---------------------------------------------------------------- report

if [[ "$QUIET" -eq 0 ]]; then
    if [[ "${#findings[@]}" -eq 0 ]]; then
        echo "Index-page audit: clean. No drift detected."
    else
        echo "Index-page audit: ${#findings[@]} finding(s)."
        echo ""
        for line in "${findings[@]}"; do
            echo "  - $line"
        done
        echo ""
        echo "See CLAUDE.md / README.md for the standard pattern."
    fi
fi

exit "${#findings[@]}"
