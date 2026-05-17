<?php

/**
 * Regression: wire:click inside <x-slot:header> is hoisted by the module
 * layout outside the component's wire:id <div>, so the click is delegated
 * to nothing. Form gear dropdowns silently did nothing across 20 forms.
 *
 * The fix mirrors the Settings precedent (commit c030520 +
 * SaveButtonInScopeTest): replace wire:click with Alpine dispatch and
 * add a matching #[On('xxx')] listener on the component. This test
 * pairs the dispatch string in the blade with the listener attribute
 * on the component, statically. If either drifts, the menu silently
 * stops firing — which is exactly the regression we already paid for.
 */
$cases = [
    // === Chunk 1: Sales + Invoicing ===
    // sales-products swept to modal pattern — see HeaderDropdownStructureTest.
    // sales-orders Duplicate/Delete moved to styled confirm modals
    // (window-event dispatch → form-level <x-ui.confirm-modal>) instead of
    // browser confirm(). See livewire/sales/orders/modals/{duplicate,delete}.blade.php.
    // invoicing-invoices swept to modal pattern.

    // === Chunk 2: Purchase + Delivery ===
    // purchase-suppliers / purchase-rfq / purchase-orders / purchase-bills / delivery-orders all swept to modal pattern.

    // === Chunk 3: HR === all swept to modal pattern.

    // === Chunk 4: Accounting + CRM + Inventory ===
    'accounting-accounts delete'   => ['resources/views/livewire/accounting/accounts/form.blade.php',    \App\Livewire\Accounting\Accounts\Form::class,   'deleteAccount'],
    // crm-leads swept to modal pattern — see HeaderDropdownStructureTest.
    'crm-opportunities archive'    => ['resources/views/livewire/crm/opportunities/form.blade.php',      \App\Livewire\CRM\Opportunities\Form::class,     'archiveOpportunity'],
    'crm-opportunities delete'     => ['resources/views/livewire/crm/opportunities/form.blade.php',      \App\Livewire\CRM\Opportunities\Form::class,     'deleteOpportunity'],
    // inventory-products swept to modal pattern.

    // === Duplicate buttons newly wired (was static dead UI before) ===
    // crm-leads duplicate swept to modal pattern.
    'crm-opportunities duplicate'      => ['resources/views/livewire/crm/opportunities/form.blade.php',            \App\Livewire\CRM\Opportunities\Form::class,          'duplicateOpportunity'],
    'inventory-transfers duplicate'    => ['resources/views/livewire/inventory/transfers/form.blade.php',          \App\Livewire\Inventory\Transfers\Form::class,        'duplicateTransfer'],
    'inventory-adjustments duplicate'  => ['resources/views/livewire/inventory/adjustments/form.blade.php',        \App\Livewire\Inventory\Adjustments\Form::class,      'duplicateAdjustment'],
    // purchase-bills duplicate swept to modal pattern.
    'accounting-accounts duplicate'    => ['resources/views/livewire/accounting/accounts/form.blade.php',          \App\Livewire\Accounting\Accounts\Form::class,        'duplicateAccount'],
    'accounting-journal duplicate'     => ['resources/views/livewire/accounting/journal-entries/form.blade.php',   \App\Livewire\Accounting\JournalEntries\Form::class,  'duplicateJournalEntry'],
    // inventory-products duplicate swept to modal pattern.
    'sales-teams duplicate'            => ['resources/views/livewire/sales/teams/form.blade.php',                  \App\Livewire\Sales\Teams\Form::class,                'duplicateSalesTeam'],
    'inventory-warehouses duplicate'   => ['resources/views/livewire/inventory/warehouses/form.blade.php',         \App\Livewire\Inventory\Warehouses\Form::class,       'duplicateWarehouse'],
    'inventory-categories duplicate'   => ['resources/views/livewire/inventory/categories/form.blade.php',         \App\Livewire\Inventory\Categories\Form::class,       'duplicateCategory'],
];

it('header dropdown dispatches a Livewire event the component listens for', function (string $bladePath, string $componentClass, string $event) {
    $blade = file_get_contents(base_path($bladePath));
    expect($blade)->toContain("Livewire.dispatch('{$event}')");

    $source = file_get_contents((new ReflectionClass($componentClass))->getFileName());
    expect($source)->toContain("#[On('{$event}')]");
})->with($cases);

/**
 * Companion check: none of the patched header slots still carry a raw
 * wire:click on a destructive action. If someone re-adds wire:click
 * inside <x-slot:header> they'll get a silently-dead button again —
 * fail loudly here.
 */
it('header slots do not carry wire:click on destructive actions', function (string $bladePath) {
    $blade = file_get_contents(base_path($bladePath));

    // Header slot is everything between <x-slot:header> ... </x-slot:header>.
    if (! preg_match('/<x-slot:header>(.*?)<\/x-slot:header>/s', $blade, $m)) {
        // Some blades use <x-slot name="header"> form — also fine, skip.
        return;
    }
    $headerSlot = $m[1];

    foreach (['delete', 'archive', 'duplicate', 'cancel', 'downloadPdf'] as $action) {
        expect($headerSlot)->not->toMatch('/wire:click=["\']'.$action.'\b/');
    }
})->with(array_unique(array_column($cases, 0)));
