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
    'sales-products archive'       => ['resources/views/livewire/sales/products/form.blade.php',         \App\Livewire\Sales\Products\Form::class,        'archiveProduct'],
    'sales-products delete'        => ['resources/views/livewire/sales/products/form.blade.php',         \App\Livewire\Sales\Products\Form::class,        'deleteProduct'],
    // sales-orders Duplicate/Delete moved to styled confirm modals
    // (window-event dispatch → form-level <x-ui.confirm-modal>) instead of
    // browser confirm(). See livewire/sales/orders/modals/{duplicate,delete}.blade.php.
    'invoicing-invoices duplicate' => ['resources/views/livewire/invoicing/invoices/form.blade.php',     \App\Livewire\Invoicing\Invoices\Form::class,    'duplicateInvoice'],
    'invoicing-invoices delete'    => ['resources/views/livewire/invoicing/invoices/form.blade.php',     \App\Livewire\Invoicing\Invoices\Form::class,    'deleteInvoice'],

    // === Chunk 2: Purchase + Delivery ===
    'purchase-suppliers delete'    => ['resources/views/livewire/purchase/suppliers/form.blade.php',     \App\Livewire\Purchase\Suppliers\Form::class,    'deleteSupplier'],
    'purchase-rfq duplicate'       => ['resources/views/livewire/purchase/rfq/form.blade.php',           \App\Livewire\Purchase\Rfq\Form::class,          'duplicateRfq'],
    'purchase-rfq delete'          => ['resources/views/livewire/purchase/rfq/form.blade.php',           \App\Livewire\Purchase\Rfq\Form::class,          'deleteRfq'],
    'purchase-orders duplicate'    => ['resources/views/livewire/purchase/orders/form.blade.php',        \App\Livewire\Purchase\Rfq\Form::class,          'duplicateRfq'], // Orders\Form extends Rfq\Form
    'purchase-orders delete'       => ['resources/views/livewire/purchase/orders/form.blade.php',        \App\Livewire\Purchase\Rfq\Form::class,          'deleteRfq'],
    'purchase-bills delete'        => ['resources/views/livewire/purchase/bills/form.blade.php',         \App\Livewire\Purchase\Bills\Form::class,        'deleteBill'],
    'delivery-orders duplicate'    => ['resources/views/livewire/delivery/orders/form.blade.php',        \App\Livewire\Delivery\Orders\Form::class,       'duplicateDelivery'],
    'delivery-orders delete'       => ['resources/views/livewire/delivery/orders/form.blade.php',        \App\Livewire\Delivery\Orders\Form::class,       'deleteDelivery'],

    // === Chunk 3: HR ===
    'hr-departments delete'        => ['resources/views/livewire/hr/departments/form.blade.php',         \App\Livewire\HR\Departments\Form::class,        'deleteDepartment'],
    'hr-positions delete'          => ['resources/views/livewire/hr/positions/form.blade.php',           \App\Livewire\HR\Positions\Form::class,          'deletePosition'],
    'hr-payroll delete'            => ['resources/views/livewire/hr/payroll/form.blade.php',             \App\Livewire\HR\Payroll\Form::class,            'deletePayroll'],
    'hr-payroll-components delete' => ['resources/views/livewire/hr/payroll/components/form.blade.php',  \App\Livewire\HR\Payroll\Components\Form::class, 'deletePayrollComponent'],
    'hr-leave-types delete'        => ['resources/views/livewire/hr/leave/types/form.blade.php',         \App\Livewire\HR\Leave\Types\Form::class,        'deleteLeaveType'],
    'hr-leave-requests cancel'     => ['resources/views/livewire/hr/leave/requests/form.blade.php',      \App\Livewire\HR\Leave\Requests\Form::class,     'cancelLeaveRequest'],
    'hr-leave-requests delete'     => ['resources/views/livewire/hr/leave/requests/form.blade.php',      \App\Livewire\HR\Leave\Requests\Form::class,     'deleteLeaveRequest'],
    'hr-attendance-schedules del'  => ['resources/views/livewire/hr/attendance/schedules/form.blade.php',\App\Livewire\HR\Attendance\Schedules\Form::class,'deleteSchedule'],
    'hr-employees archive'         => ['resources/views/livewire/hr/employees/form.blade.php',           \App\Livewire\HR\Employees\Form::class,          'archiveEmployee'],
    'hr-employees delete'          => ['resources/views/livewire/hr/employees/form.blade.php',           \App\Livewire\HR\Employees\Form::class,          'deleteEmployee'],

    // === Chunk 4: Accounting + CRM + Inventory ===
    'accounting-accounts delete'   => ['resources/views/livewire/accounting/accounts/form.blade.php',    \App\Livewire\Accounting\Accounts\Form::class,   'deleteAccount'],
    // crm-leads swept to modal pattern — see HeaderDropdownStructureTest.
    'crm-opportunities archive'    => ['resources/views/livewire/crm/opportunities/form.blade.php',      \App\Livewire\CRM\Opportunities\Form::class,     'archiveOpportunity'],
    'crm-opportunities delete'     => ['resources/views/livewire/crm/opportunities/form.blade.php',      \App\Livewire\CRM\Opportunities\Form::class,     'deleteOpportunity'],
    'inventory-products archive'   => ['resources/views/livewire/inventory/products/form.blade.php',     \App\Livewire\Inventory\Products\Form::class,    'archiveProduct'],
    'inventory-products delete'    => ['resources/views/livewire/inventory/products/form.blade.php',     \App\Livewire\Inventory\Products\Form::class,    'deleteProduct'],

    // === Duplicate buttons newly wired (was static dead UI before) ===
    // crm-leads duplicate swept to modal pattern.
    'crm-opportunities duplicate'      => ['resources/views/livewire/crm/opportunities/form.blade.php',            \App\Livewire\CRM\Opportunities\Form::class,          'duplicateOpportunity'],
    'inventory-transfers duplicate'    => ['resources/views/livewire/inventory/transfers/form.blade.php',          \App\Livewire\Inventory\Transfers\Form::class,        'duplicateTransfer'],
    'inventory-adjustments duplicate'  => ['resources/views/livewire/inventory/adjustments/form.blade.php',        \App\Livewire\Inventory\Adjustments\Form::class,      'duplicateAdjustment'],
    'purchase-bills duplicate'         => ['resources/views/livewire/purchase/bills/form.blade.php',               \App\Livewire\Purchase\Bills\Form::class,             'duplicateBill'],
    'accounting-accounts duplicate'    => ['resources/views/livewire/accounting/accounts/form.blade.php',          \App\Livewire\Accounting\Accounts\Form::class,        'duplicateAccount'],
    'accounting-journal duplicate'     => ['resources/views/livewire/accounting/journal-entries/form.blade.php',   \App\Livewire\Accounting\JournalEntries\Form::class,  'duplicateJournalEntry'],
    'inventory-products duplicate'     => ['resources/views/livewire/inventory/products/form.blade.php',           \App\Livewire\Inventory\Products\Form::class,         'duplicateProduct'],
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
