# Syncore

A Laravel ERP covering Inventory, Sales, Purchase, Delivery, Invoicing, Accounting, CRM, and HR — built on Livewire 3 + Flux UI. SQLite for local/test, Postgres in production.

## Stack

- **Backend:** PHP 8.5 (runtime), Laravel 12, Livewire 3 (Volt), Spatie Permission, Maatwebsite Excel, dompdf
- **Frontend:** Tailwind 4, Flux UI, Chart.js, Vite 7
- **Tests:** Pest 4 on in-memory SQLite (`RefreshDatabase`)
- **Lint:** Laravel Pint

## Modules

The app is organized as a single Laravel install with one Livewire namespace per module. Each module follows the same layout: a `module.blade.php` shell + `Index` (list) and `Form` (create/edit) Livewire components per resource.

| Module | Resources |
|---|---|
| Inventory | Items, Categories, Warehouses, Adjustments (Inbound / Outbound), Internal Transfers |
| Sales | Quotations & Orders, Customers, Products, Teams, Pricelists, Taxes, Payment Terms, Promotions |
| Purchase | RFQ / POs, Goods Receipts (GRN), Vendor Bills, Suppliers |
| Delivery | Delivery Orders |
| Invoicing | Invoices, Payments |
| Accounting | Chart of Accounts, Journal Entries |
| CRM | Leads, Opportunities, Activities |
| HR | Employees, Departments, Positions, Attendance & Schedules, Payroll & Salary Components, Leave Requests & Types |
| Settings | Users, Roles, Company Profile, Email, Localization, Audit Trail, Modules |

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # local dev runs on SQLite by default
php artisan migrate --seed          # seeds an admin user + demo data
npm install
composer dev                        # runs serve, queue:listen, pail, vite concurrently
```

The seeder creates an initial admin: **`rifqi@mail.com` / `password`** (change on first login).

## Daily workflow

```bash
./vendor/bin/pest                     # full suite, sequential (~95s, 496 tests)
./vendor/bin/pest --parallel          # 8-process, ~22s (sqlite — pgsql parallel not yet wired)
./vendor/bin/pest --filter="Sales"    # subset
./vendor/bin/pint                     # format
php artisan migrate                   # always after pulling
bin/audit-index-pages.sh              # flag drift from the standard list-page pattern
bin/audit-form-pages.sh               # flag drift from the standard form-page pattern
bin/audit-livewire-actions.sh         # flag write actions missing $this->authorizePermission()
```

CI runs Pint but the auto-commit step is disabled — fix style issues only on files you're already editing (don't try a project-wide sweep on a feature branch).

## Project conventions

Day-to-day rules live in [CLAUDE.md](./CLAUDE.md); the cross-cutting
architecture (notification pipeline, ResourceType, audit scripts, query
budgets, driver caveats) is in [docs/ARCHITECTURE.md](./docs/ARCHITECTURE.md);
every document state machine (SO, Invoice, DO, PO, Payment, Transfer,
Leave, Payroll, Lead, Opportunity) with its transitions and triggers
is diagrammed in [docs/STATE_MACHINES.md](./docs/STATE_MACHINES.md);
the operator-facing reference for every Excel/CSV importer (required
columns, lookup keys, default values) is in [docs/IMPORTS.md](./docs/IMPORTS.md).
Headlines:

- **Validation lives in Livewire components**, not FormRequests. There are no FormRequest classes — put rules in `rules()` on the component.
- **Index list pages** render `<x-ui.index-header :bare="true">` inside `<x-slot:header>` so they don't double-up the layout's chrome bar. Search + filters go through `<x-ui.searchbox-dropdown>` (chevron-down menu with active-filter pill + "Clear all filters" footer).
- **Form pages** follow the 12-col canonical shape from `sales/configuration/taxes/form.blade.php`: 9-col form + 3-col chatter/timeline panel, action bar above with status badge + chatter buttons.
- **Status enums** implement `App\Enums\Contracts\HasDisplayMetadata` (`label()`, `color()`, `icon()`). Render via `<x-ui.status-badge :status="$model->state" />`. For custom variants (kanban headers, dot indicators) use a `match` block — Tailwind JIT can't see interpolated class strings.
- **State changes** go through `HasStateMachine::transitionTo()` so the activity log captures the transition.
- **Driver-aware migrations** for any state-enum value change — SQLite ignores `enum()` CHECK constraints, but Postgres can reject. See `2026_04_24_082748_realign_inventory_transfer_status_enum` for the pattern.
- **SoftDeletes** requires a `deleted_at` column on every table that uses `HasSoftDeletes`.

## Shared building blocks

### Livewire traits (`app/Livewire/Concerns/`)

| Trait | What it gives you |
|---|---|
| `WithIndexComponent` | `$search/$status/$sort/$view/$groupBy` URL state, `getActiveFilterCount()` (override `getCustomActiveFilterCount()` for page-specific filters), pagination + bulk-selection plumbing |
| `WithBulkActions` | `$selected[]`, `selectAll()`, `clearSelection()` |
| `WithManualPagination` | `goToPreviousPage` / `goToNextPage` wired by `<x-ui.index-header>`'s pagination chevrons |
| `WithNotes` | `$this->activitiesAndNotes`, `addNote()` (requires `getNotableModel()` on the component) |
| `WithImport` | XLSX/CSV import modal wiring |
| `WithExport` | export-selected helper |
| `WithPermissions` | `can()` / `authorize()` against Spatie permissions |

### Model traits (`app/Traits/`)

`LogsActivity`, `HasNotes`, `HasSoftDeletes`, `HasStateMachine`, `HasYearlySequenceNumber` (`SO/2026/00001`), `HasAttachments`, `Searchable`, `HasCurrency`, `HasCreatedBy`.

### UI components (`resources/views/components/ui/`)

Notable ones:

- `index-header` — sticky page-chrome with title, New button, gear menu, search slot, pagination, view toggle
- `searchbox-dropdown` — search input + chevron filter menu, with `activeFilterCount` pill and `clearAction` footer
- `status-badge` — enum-driven pill (label/color/icon)
- `chatter-buttons` + `chatter-forms` + `note-item` + `activity-item` — the timeline/notes UX on form pages
- `confirm-modal`, `delete-confirm-modal`, `import-modal` — common modal flows
- `selection-toolbar` — appears in the search slot when bulk items are selected

## Testing notes

- All tests use `RefreshDatabase` against in-memory SQLite. Postgres can still reject values that pass tests (CHECK constraints on enum columns aren't enforced by SQLite) — write driver-aware migrations.
- A few features (Postgres-only `whereRaw('LOWER(...)')` and `ILIKE` in Audit Trail filters) won't behave identically on SQLite — keep that in mind when reading those tests.

## Notifications + queue

The notification system writes to `system_notifications` (in-app inbox via
`NotificationDropdown`) and may dispatch mail. Most listeners implement
`ShouldQueue`, so in production the work runs via a queue worker rather
than blocking the user-facing request.

Already configured: `QUEUE_CONNECTION=database`. Production needs a
worker process (e.g. `supervisord`) running:

```
php artisan queue:work --tries=3 --timeout=60
```

For local dev, `composer dev` already runs `queue:listen` alongside the
server. Mail goes to `storage/logs/laravel.log` by default
(`MAIL_MAILER=log`); set the real provider's env vars to actually send.

`tests/Feature/Notifications/PipelineTest.php` is the smoke for the
event → listener → DB-row pipeline; if a future change breaks the
wiring, this fails before users notice silent dropouts.

## Composer platform pin

`composer.json` sets `config.platform.php = "8.4.99"` so the resolver
treats the runtime as 8.4 even though we run PHP 8.5+. This is
because `phpoffice/phpspreadsheet` (a transitive dep of
`maatwebsite/excel`) caps at PHP 8.4. The runtime is unaffected —
PHP 8.5 still actually executes the code; the override only stops
`composer require`/`update` from refusing to resolve. Drop the pin
when the upstream chain releases a PHP 8.5-compatible version.

## License

Internal — XenostechStudio.
