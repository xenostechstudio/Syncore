# Architecture

Syncore is a single Laravel install organised one Livewire namespace per module
(Sales, Inventory, Purchase, Delivery, Invoicing, Accounting, CRM, HR,
Settings). This document is the canonical reference for the conventions and
shared pipelines that span those modules. Module-by-module feature lists live
in [APPLICATION_GUIDE.md](APPLICATION_GUIDE.md); user-facing setup lives in
[CONFIGURATION.md](CONFIGURATION.md); the operator runbook (deploys,
monitoring, incident response) is in [RUNBOOK.md](RUNBOOK.md); developer
onboarding lives in [DEVELOPMENT.md](DEVELOPMENT.md). Day-to-day rules
already encoded as project instructions live in [`CLAUDE.md`](../CLAUDE.md)
— this doc cross-links it rather than restating it.

## Stack

- **PHP 8.5** runtime. `composer.json` pins `config.platform.php = "8.4.99"`
  so the resolver treats the platform as 8.4 — `phpoffice/phpspreadsheet`
  (transitive of `maatwebsite/excel`) caps there. Drop the pin when that
  upstream releases an 8.5-compatible build.
- **Laravel 12**, **Livewire 3** (Volt), **Flux UI**, **Tailwind 4**, **Vite 7**.
- **SQLite (in-memory)** for local + tests, **Postgres 16** in production.
  CI runs both via a matrix; see [Testing](#testing).
- **Authorization**: Spatie Permission. Roles seeded by
  `ModulePermissionSeeder` (super-admin, admin, plus per-module roles).
- **Queue**: `database` driver. `composer dev` runs `queue:listen`; production
  needs a worker (supervisor) running `queue:work --tries=3 --timeout=60`.

## Directory layout

```
app/
├── Console/Commands/      Artisan commands (incl. notifications:smoke)
├── Enums/                 BackedEnums implementing HasDisplayMetadata
├── Events/, Listeners/    Domain events + ShouldQueue listeners
├── Exports/, Imports/     Maatwebsite Excel classes
├── Livewire/
│   ├── Concerns/          Shared component traits (see "Livewire traits")
│   └── <Module>/          One namespace per ERP module
├── Models/<Module>/       Eloquent models, organised per module
├── Services/              Cross-cutting services (NotificationService,
│                          DashboardService, PdfService, ExportService, …)
└── Traits/                Reusable model traits
bin/                       Audit shell scripts (run in CI)
docs/                      This directory
tests/Feature/             Pest feature suites; one per module + cross-cuts
```

## Trait stack

Domain models compose from `App\Traits\*`:

| Trait | Purpose |
|---|---|
| `LogsActivity` | Writes `created`/`updated`/`deleted` rows to `activity_logs`. Declare `protected array $logActions = ['created','updated','deleted']`. Custom log via `logStatusChange()` / `logCustomAction()`. |
| `HasNotes` | Polymorphic notes; `addNote(string, bool $isInternal)`. |
| `HasAttachments` | Polymorphic file attachments. |
| `HasSoftDeletes` | Wraps Eloquent `SoftDeletes`. **Every table that uses this must have a `deleted_at` column** — see commit `9c78e78` for the bug class this prevents. |
| `HasStateMachine` | Pairs a status column with a BackedEnum. Set `protected string $stateEnum = MyState::class;`. Provides `transitionTo(BackedEnum)`, `canEdit()`, `canCancel()`, `isTerminalState()`. |
| `HasYearlySequenceNumber` | Document numbering, e.g. `SO/2026/00001`. |
| `Searchable`, `HasCurrency`, `HasCreatedBy` | Domain helpers. |

### `HasStateMachine`: enum-cast gotcha

If the model casts its status column to an enum (e.g. `Opportunity::$status`),
`$model->{$column}` returns a `BackedEnum` instance, not a string.
`HasStateMachine::transitionTo()` coerces to `->value` before passing to
`logStatusChange()` for that reason — don't unwind the coercion in
`app/Traits/HasStateMachine.php:73-75`.

## State enums and status display

The full per-document state diagram (every state, every transition, every
trigger) lives in [STATE_MACHINES.md](STATE_MACHINES.md) — go there when
you want to answer "what happens when I click Confirm on a sales order?"
or "why is this invoice stuck in `sent`?". The notes below cover the
*mechanism*; the linked page covers the *content*.

State enums implement `App\Enums\Contracts\HasDisplayMetadata` (`label()`,
`color()`, `icon()`) and use the `ProvidesOptions` trait for select dropdowns.
Render via:

```blade
<x-ui.status-badge :status="$model->state" />
```

The badge accepts either a string value or an enum instance and resolves color
through a `match` block in `resources/views/components/ui/status-badge.blade.php`.
Tailwind JIT can't see interpolated class strings, so when you need a custom
variant (kanban column header, dot indicator) write a `match` block enumerating
literal class strings — see CLAUDE.md for the canonical example.

### Driver-aware migrations

When changing a state-enum's allowed values, write a driver-aware migration:
SQLite ignores `enum()` CHECK constraints (so tests are permissive), Postgres
will reject. Pattern lives in
`2026_04_24_082748_realign_inventory_transfer_status_enum`. When you delete an
enum case, also update factories (`->terminated()` etc.), seeders, Livewire
`rules()`, index filter dropdowns, and import classes in `app/Imports/`.

## Cross-resource identity: `ResourceType`

`app/Enums/ResourceType.php` is the single source of truth for cross-module
resource identity (sales_order, invoice, delivery_order, purchase_order,
vendor_bill, goods_receipt, …). Each case exposes:

- `tone()` — Tailwind palette key (emerald / blue / amber / violet / …)
- `icon()` — Heroicon name
- `label()` — Human name
- `route(?int $id)` — Edit URL when `$id` is given, index URL otherwise

Render the resource pill via `<x-ui.related-resource>`:

```blade
<x-ui.related-resource resource="invoice" :id="$invoice->id" :label="$invoice->invoice_number">
    <x-ui.status-badge :status="$invoice->state" />
</x-ui.related-resource>
```

Pass `:tone` or `:icon` to override the enum defaults. The component enumerates
literal Tailwind classes for every supported tone (Tailwind JIT requirement).
Add a new tone there in lockstep with adding a new `ResourceType` case.

## Livewire patterns

### Index pages

Render `<x-ui.index-header :bare="true">` **inside** `<x-slot:header>`. The
module layout already provides the sticky page-chrome — without `:bare` you
get a double border. Search and filters go through
`<x-ui.searchbox-dropdown>`. Components compose `WithIndexComponent`
(`$search/$status/$sort/$view/$groupBy` URL state, pagination, bulk
selection). Override `getCustomActiveFilterCount()` for page-specific filter
chips.

### Form pages

Canonical layout: `resources/views/livewire/sales/configuration/taxes/form.blade.php`.
12-col grid, 9-col form / 3-col chatter+timeline panel, action bar above with
status badge and chatter toggles. The Livewire component must
`use App\Livewire\Concerns\WithNotes;` and define `getNotableModel()` — that
wires `$this->activitiesAndNotes` and `$this->addNote()`.

### Livewire traits (`app/Livewire/Concerns/`)

| Trait | Provides |
|---|---|
| `WithIndexComponent` | URL-state for search/status/sort/view/groupBy, pagination + bulk selection |
| `WithBulkActions` | `$selected[]`, `selectAll()`, `clearSelection()` |
| `WithManualPagination` | `goToPreviousPage` / `goToNextPage` (wired by index-header pagination chevrons) |
| `WithNotes` | `addNote()` + `$activitiesAndNotes` |
| `WithImport`, `WithExport` | XLSX/CSV import-modal + export-selected helpers. Every shipped import class implements `SkipsOnFailure` — invalid rows are collected, valid rows continue to import. Per-importer column reference: [IMPORTS.md](IMPORTS.md). |
| `WithPermissions` | `can('module.action')`, `authorizePermission('module.action')` |

## Authorization

Authorization is enforced at the Livewire-action level via
`WithPermissions::authorizePermission('module.action')`. Read-side scoping is
enforced in component `mount()` and `getQuery()` methods — there are no
controllers and no FormRequest classes (validation lives in
`rules()` on the component; see CLAUDE.md).

`bin/audit-livewire-actions.sh` flags public methods that look destructive
(state transitions, `transitionTo()`, `Mail::*`, `Notification::send`, names
matching `delete|destroy|approve|reject|cancel|confirm|post|publish|archive|…`)
without a visible authz marker. Two valid markers:

- `$this->authorizePermission('module.action')` at the top of the method
- `// authz: <reason>` opt-out comment for actions that are inherently
  self-scoped (e.g. dropdown markAsRead scoped to `auth()->id()`)

Add new destructive verbs to the script's pattern list rather than papering
over them with `// authz:` opt-outs.

## Notification pipeline

The notification system is event-driven and writes both an in-app row and
optionally mail. Flow:

```
Domain action  →  Event::dispatch(...)  →  Listener implements ShouldQueue
                                            ↓
                                    NotificationService::create(...)
                                            ↓
                          ┌─────────────────┴──────────────────┐
                          ↓                                    ↓
            SystemNotification row                    optional Mail send
            (system_notifications table)              (MAIL_MAILER=log default)
                          ↓
            <livewire:components.notification-dropdown />
            in app + app-home layouts (bell icon)
```

Wired events live in `app/Events/` (OpportunityWon/Lost, InvoicePaid,
InvoiceOverdue, PayrollProcessed, PurchaseOrderReceived, VendorBillPaid,
LowStockDetected). Listeners live in `app/Listeners/<Module>/` and implement
`ShouldQueue` so the work runs on the queue worker. The dropdown's unread
count is cached for 60s under `notifications_unread_<userId>`; flush the
cache after writing test data.

### Smoke testing

```bash
php artisan notifications:smoke --clear   # fire one of each event
php artisan queue:work --stop-when-empty  # drain queue (composer dev runs this automatically)
```

`tests/Feature/Notifications/PipelineTest.php` is the regression smoke for
the event → listener → DB-row pipeline. If a future change breaks the wiring,
this fails before users notice silent dropouts.

### Common pitfalls (already fixed; don't reintroduce)

- The dropdown's `notifications` query selects only the columns the view
  reads. **Include `icon` and `color`** — Flux v2 turns `<flux:icon name="" />`
  into a lookup for `icon.` (with a trailing dot) and crashes the layout.
  See `app/Livewire/Components/NotificationDropdown.php:78-83`.
- `NotificationService::create()` fans out to admins when no `userId` is
  given via `User::role(['super-admin','admin'])`. The earlier `view-all` /
  `manage-all` permissions never existed; using them silently produced zero
  notifications.
- Listeners that loop over models must `loadMissing()` their relations —
  strict lazy-loading is on, so a single `$item->employee?->user_id` lookup
  in a payslip-fanout loop will throw.

## Testing

```bash
./vendor/bin/pest                   # full suite, sequential (~95s, 496+ tests)
./vendor/bin/pest --parallel        # 8-process, ~22s — sqlite only
./vendor/bin/pest --filter="..."    # subset
composer test:fast                  # alias for --parallel
```

Tests use `RefreshDatabase` against an in-memory SQLite. `tests/Pest.php`
seeds `ModulePermissionSeeder` in a global `beforeEach` and exposes an
`actAsAdmin()` helper — every Feature test runs as a super-admin user
unless it explicitly sets up a different role.

CI (`.github/workflows/tests.yml`) runs the suite in a sqlite/pgsql matrix.
The `--parallel` flag is conditional on the sqlite job — Paratest with
Postgres needs per-process databases + per-process seeders, which isn't wired
yet (deferred work).

### Query-budget regression tests

`tests/Feature/Profile/N1ProfileTest.php` enables the query log around each
component render and asserts the count is at most the budgeted number. Two
datasets:

- index pages (Sales/Orders=7, HR/Employees=11, …)
- module dashboards (Sales=19, HR=27, CRM=13, …)
- form pages — Sales/Orders/Form=22, Invoicing/Invoices/Form=17,
  Delivery/Orders/Form=20, etc.

Adding a new index/dashboard/form? Add a budget. **Bump a budget** only when
the new query is genuinely necessary; if a relation needs eager-loading,
`loadMissing()` it instead — the budget is the line.

### Audit scripts

```bash
bin/audit-index-pages.sh        # drift from <x-ui.index-header :bare> pattern
bin/audit-form-pages.sh         # drift from 9/3 form-page pattern
bin/audit-livewire-actions.sh   # missing authz on destructive actions
```

These run in `.github/workflows/audit-pages.yml` as separate jobs, so a
regression in any one fails CI independently.

## Performance: N+1 and caching

- **Strict lazy-loading** is enabled in non-prod (`Model::preventLazyLoading()`
  in `app/Providers/PerformanceServiceProvider.php`). Lazy-load violations
  throw rather than silently N+1.
- **Eager-load** in component `getQuery()` / `mount()`, never in the view.
- **Per-instance caching** for expensive predicates that get called multiple
  times per render — see `SalesOrder::isLocked()` cache in
  `app/Models/Sales/SalesOrder.php` (4 calls × 2 `exists()` queries → 1
  cached predicate). Override `refresh()` to bust the cache.
- **Notification unread count** is `Cache::remember`ed for 60s per user.
  Flush after smoke-firing notifications so the bell shows the new count.

## Activity log retention

`activity_logs` grows unbounded otherwise. The `activity-logs:cleanup
--days=N` artisan command (defaults to 90) prunes older entries and is
already scheduled weekly (Sunday 02:00) in `routes/console.php` —
alongside `invoices:check-overdue` (daily 08:00), `inventory:check-low-stock`
(daily 09:00), and an hourly dashboard cache flush. Make sure the
production cron is wired to `php artisan schedule:run` every minute or
none of these run.

## Dashboards

Each module's `Index` Livewire component is its dashboard
(`app/Livewire/<Module>/Index.php`). Heavy aggregations live in
`app/Services/<Module>ReportService.php` so the components stay thin and the
N1 budgets stay tight. The reports module wraps these services with shared
filter UI.

## Frontend conventions

- **Icons**: `<flux:icon name="..." class="size-4" />`. Heroicons set; full
  list under `vendor/livewire/flux/stubs/resources/views/flux/icon/`.
- **Buttons**: primary uses `bg-zinc-900 dark:bg-zinc-100`. Secondary/ghost
  variants are zinc-bordered.
- **Modals**: prefer `<x-ui.confirm-modal>` / `<x-ui.delete-confirm-modal>`
  over `wire:confirm` (the latter is a native `confirm()` dialog and
  doesn't theme).
- **Search**: Postgres uses `ILIKE`/`LOWER()` for case-insensitivity. SQLite
  is permissive — a few audit-trail filters won't behave identically there.

## Known cross-driver caveats

| Behaviour | SQLite | Postgres |
|---|---|---|
| `enum()` CHECK constraints | Ignored | Enforced — driver-aware migrations required |
| Case-insensitive search | `LIKE` is case-insensitive by default | Use `ILIKE` / `LOWER(...)` |
| `HAVING` referencing a SELECT alias | Permissive | Rejected — reference the underlying expression |
| Boolean columns | Stored as 0/1 | Stored as true/false |

When a SQLite test passes but Postgres-only logic might break, write the
driver branch and rely on the CI matrix to catch it. The pgsql job is
authoritative.

## Configuration files of note

- `config/database.php` — `pgsql` (prod) and `sqlite` (`:memory:` for tests)
- `config/permission.php` — Spatie Permission settings
- `config/queue.php` — `database` driver default
- `config/xendit.php` — payment-gateway credentials
- `config/auth.php` — Fortify + 2FA settings

See [CONFIGURATION.md](CONFIGURATION.md) for env var mapping.
