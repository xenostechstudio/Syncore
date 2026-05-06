<?php

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * N+1 regression guard. Mounts each major Livewire index/dashboard with the
 * full demo dataset and asserts the render fires no more than the budgeted
 * number of queries. Bump the budget here only if a new query is genuinely
 * required (a new feature column, a new stat). If you can keep it, fix the
 * lazy load instead — the budget is the line.
 *
 * Tip: when this fails, run with `--filter=...` and add a fwrite to dump the
 * grouped query log.
 */

beforeEach(function () {
    // Use the same demo pipeline an end user hits — query counts then match
    // real-world rather than a stripped-down test scaffold.
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $admin = \App\Models\User::where('email', 'rifqi@mail.com')->first()
        ?? \App\Models\User::factory()->create();
    $admin->assignRole('super-admin');
    test()->actingAs($admin);
});

dataset('budgets', [
    // [component class, max queries per render]
    'Sales/Orders'        => [\App\Livewire\Sales\Orders\Index::class,           8],
    'Invoicing/Invoices'  => [\App\Livewire\Invoicing\Invoices\Index::class,    10],
    'Delivery/Orders'     => [\App\Livewire\Delivery\Orders\Index::class,       10],
    'Sales/Customers'     => [\App\Livewire\Sales\Customers\Index::class,        6],
    'Sales/Products'      => [\App\Livewire\Sales\Products\Index::class,         6],
    'Inventory/Items'     => [\App\Livewire\Inventory\Items\Index::class,       12],
    'CRM/Activities'      => [\App\Livewire\CRM\Activities\Index::class,        15],
    'CRM/Opportunities'   => [\App\Livewire\CRM\Opportunities\Index::class,     10],
    'CRM/Leads'           => [\App\Livewire\CRM\Leads\Index::class,              8],
    'HR/Employees'        => [\App\Livewire\HR\Employees\Index::class,          14],
    'Purchase/Orders'     => [\App\Livewire\Purchase\Orders\Index::class,        6],
    'Purchase/Bills'      => [\App\Livewire\Purchase\Bills\Index::class,         5],
    'Purchase/Suppliers'  => [\App\Livewire\Purchase\Suppliers\Index::class,     6],
    'Settings/Users'      => [\App\Livewire\Settings\Users\Index::class,         6],

    // Module dashboards aggregate many independent stats — looser budgets.
    'Dashboard/Sales'     => [\App\Livewire\Sales\Index::class,                 25],
    'Dashboard/HR'        => [\App\Livewire\HR\Index::class,                    35],
    'Dashboard/CRM'       => [\App\Livewire\CRM\Index::class,                   20],
    'Dashboard/Inventory' => [\App\Livewire\Inventory\Index::class,             18],
    'Dashboard/Accounting'=> [\App\Livewire\Accounting\Index::class,            18],
]);

it('renders within its query budget', function (string $component, int $budget) {
    // Mount + flip "my X" filters off without measuring — the render we
    // care about is the steady-state one, not the mount + N reactive
    // updates that happen during setup.
    $test = Livewire::test($component);
    foreach (['myQuotations', 'myInvoice', 'myOrders', 'myActivities'] as $flag) {
        try { $test->set($flag, false); } catch (\Throwable $e) {}
    }

    DB::enableQueryLog();
    DB::flushQueryLog();
    $test->call('$refresh');
    $count = count(DB::getQueryLog());

    expect($count)->toBeLessThanOrEqual(
        $budget,
        "Component fired {$count} queries (budget: {$budget}). Either eager-load the missing relation or, if the new query is justified, raise the budget here."
    );
})->with('budgets');
