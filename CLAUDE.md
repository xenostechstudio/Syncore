# Syncore — Project Conventions

Laravel 11 + Livewire 3 + Flux UI + Tailwind CSS ERP. Pest tests on SQLite in-memory; Postgres in production.

## Test & lint

```bash
./vendor/bin/pest                    # full suite (~22s, 435+ tests)
./vendor/bin/pest --filter="..."     # subset
./vendor/bin/pint                    # style fixer (CI runs it; auto-commit step is disabled)
php artisan migrate                  # always migrate after pulling — see Migration recipe below
```

Tests use `RefreshDatabase` against an in-memory SQLite. SQLite ignores `enum()` CHECK constraints, so tests are permissive — Postgres can still reject values that pass tests. When changing state-enum values, write a driver-aware migration (see below) and don't rely on tests catching the schema mismatch.

## Validation lives in Livewire, not FormRequest

The project handled validation entirely via `$this->validate([...])` in Livewire components. **There are no FormRequest classes** — `app/Http/Requests/` was removed in `ba49ac1` after every class proved to be orphan scaffolding with stale rules. Don't add new ones; put validation rules in the Livewire component's `rules()` method.

## Index list pages: `<x-ui.index-header :bare="true">`

The module layout already provides a sticky page-chrome bar via `<x-slot:header>`. Render `<x-ui.index-header>` **inside that slot with `:bare="true"`** so it skips its own outer wrapper. Without `:bare`, you get a double border + double padding.

```blade
<x-slot:header>
    <x-ui.index-header
        :bare="true"
        title="Categories"
        :createRoute="route('inventory.categories.create')"
        :paginator="$categories"
        :selected="$selected"
        :views="['list', 'grid']"
        :view="$view"
        searchPlaceholder="Search categories..."
    >
        <x-slot:actions>{{-- gear menu items --}}</x-slot:actions>
        <x-slot:filters>{{-- chevron-down filter dropdown --}}</x-slot:filters>
        <x-slot:selectionActions>{{-- bulk-action buttons --}}</x-slot:selectionActions>
    </x-ui.index-header>
</x-slot:header>
```

The component's pagination wires `wire:click="goToPreviousPage"` / `goToNextPage` — both provided by the `WithManualPagination` trait (used via `WithIndexComponent`).

If the page has a unique header shape that doesn't fit (e.g. `audit-trail` with its external filter button), leave it alone rather than expanding the component's API.

## Form pages: 12-col layout with right panel

Canonical reference: `resources/views/livewire/sales/configuration/taxes/form.blade.php`. Every form whose model uses `LogsActivity` + `HasNotes` should follow this shape.

```blade
<div x-data="{ showSendMessage: false, showLogNote: false, showScheduleActivity: false }">
    <x-slot:header>{{-- back arrow + breadcrumb-style title + gear --}}</x-slot:header>

    {{-- Action bar: 12-col grid --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center justify-between">
                {{-- Save / Cancel / Delete buttons --}}
                {{-- Status badge (right-aligned via justify-between) --}}
                <x-ui.status-badge status="active" />
            </div>
            <div class="col-span-3">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    {{-- Main grid: 9 form / 3 right panel --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-9">{{-- form card --}}</div>
            <div class="lg:col-span-3">
                <x-ui.chatter-forms :showMessage="false" />
                @if($modelId)
                    {{-- timeline rendering $this->activitiesAndNotes --}}
                @else
                    {{-- empty-state --}}
                @endif
            </div>
        </div>
    </div>
</div>
```

The Livewire component must `use App\Livewire\Concerns\WithNotes;` and define `protected function getNotableModel()` — that gives you `$this->activitiesAndNotes` and `$this->addNote()` for free. **Do not** put `mt-6` between the action bar and the main grid (it creates a visible gap; the action bar's `py-3` is enough).

## Status display: enum-driven

Backed enums implement `App\Enums\Contracts\HasDisplayMetadata` (`label()`, `color()`, `icon()`) and use the `ProvidesOptions` trait. Render via `<x-ui.status-badge :status="$model->state">` — accepts either a string value or an enum instance. Color names are Tailwind palette keys (`zinc`, `blue`, `amber`, `emerald`, `red`, `violet`, `purple`, `orange`, `yellow`, `green`, `gray`).

**Tailwind JIT requires literal class strings.** When you need a custom variant (e.g. dot indicator, kanban column header bg), don't interpolate `bg-{{ $color }}-500`. Use a `match` block:

```blade
@php
    [$bg, $text] = match ($state->color()) {
        'emerald' => ['bg-emerald-100 dark:bg-emerald-900/30', 'text-emerald-700 dark:text-emerald-400'],
        'amber'   => ['bg-amber-100 dark:bg-amber-900/30', 'text-amber-700 dark:text-amber-400'],
        // ...
        default   => ['bg-zinc-100 dark:bg-zinc-800', 'text-zinc-600 dark:text-zinc-400'],
    };
@endphp
```

## Models: standard trait stack

Domain models compose from `App\Traits\*`:

- `LogsActivity` — writes to `activity_logs` table; declare `protected array $logActions = ['created', 'updated', 'deleted'];`
- `HasNotes` — adds `addNote(string, bool)` method
- `HasSoftDeletes` — wraps `Illuminate\Database\Eloquent\SoftDeletes`. **Every table that uses this must have a `deleted_at` column** — see `9c78e78` for the kind of bug that bites when one is missing.
- `HasStateMachine` — for state-enum models. The model's `status` column stays a raw string; `$model->state` is the enum-cast accessor. Set `protected string $stateEnum = MyState::class;`.
- `HasYearlySequenceNumber`, `HasAttachments`, `Searchable` — domain-specific.

## Driver-aware status-enum migrations

When realigning a state-enum's allowed values:

```php
public function up(): void
{
    $driver = Schema::getConnection()->getDriverName();

    if ($driver === 'pgsql') {
        DB::statement('ALTER TABLE my_table DROP CONSTRAINT IF EXISTS my_table_status_check');
    }

    DB::table('my_table')->whereIn('status', ['old_a', 'old_b'])
        ->update(['status' => 'new']);

    if ($driver === 'pgsql') {
        DB::statement('ALTER TABLE my_table ALTER COLUMN status TYPE VARCHAR(20)');
    }
}
```

Recent examples to follow: `2026_04_24_082748_realign_inventory_transfer_status_enum`, `2026_04_25_120000_simplify_employee_status_enum`. SQLite does nothing with `enum()` constraints, so tests pass without the driver branch — but Postgres production needs it.

When deleting an old enum case, also update: factories (`->terminated()` etc.), seeders, validation rules in Livewire `rules()`, filter dropdowns in the index view, and the import classes in `app/Imports/`.

## Pre-existing style debt

Pint reports concat-spacing and other violations across many files (`tests/`, `database/seeders/`, etc.). The lint workflow runs `vendor/bin/pint` but the auto-commit step in `.github/workflows/lint.yml` is **commented out**, so violations don't block CI. Don't attempt a project-wide Pint fix in a feature branch — it produces a diff so noisy it obscures the real change. Fix Pint issues only on files you're already editing.

## Memory, planning, and intermediate files

The user's `~/.claude/projects/.../memory/` directory holds session memory across runs. Use it via the standard memory protocol — but don't write architecture/decision/planning docs to the repo unless the user asks. Brief commit messages already capture the why.
