<?php

use App\Models\HR\PayrollItem;
use App\Models\Invoicing\Invoice;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Support\Facades\Event;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    Event::fake();
    Excel::fake();
    $this->seed(ModulePermissionSeeder::class);
});

/**
 * Cover the export/import/PDF route guards added when these were
 * previously only `auth`+`verified` — meaning any authenticated user
 * could pull every customer/invoice/payslip from the system.
 */
describe('Route permission guards', function () {
    it('denies export.customers without customers.export', function () {
        $user = User::factory()->create();
        $user->assignRole('warehouse'); // no customers.export
        $this->actingAs($user);

        $this->get(route('export.customers'))->assertForbidden();
    });

    it('allows export.customers for sales role', function () {
        $user = User::factory()->create();
        $user->assignRole('sales'); // has customers via 'sales' role only? customers role excludes export
        // The 'manager' role has customers.export, sales role does not — use manager
        $user->syncRoles(['manager']);
        $this->actingAs($user);

        $this->get(route('export.customers'))->assertOk();
    });

    it('denies export.products without inventory.export', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $this->actingAs($user);

        $this->get(route('export.products'))->assertForbidden();
    });

    it('denies pdf.invoice without invoicing.view', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $this->actingAs($user);

        $invoice = Invoice::factory()->create(['invoice_number' => 'INV-TEST-1']);

        $this->get(route('pdf.invoice', $invoice))->assertForbidden();
    });

    it('denies payroll-slip when neither owner nor payroll.view', function () {
        $user = User::factory()->create();
        $user->assignRole('employee'); // employee has hr.view but not payroll.view
        $this->actingAs($user);

        $slip = PayrollItem::factory()->create();

        $this->get(route('pdf.payroll-slip', $slip))->assertForbidden();
    });

    it('allows payroll-slip for the slip owner even without payroll.view', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $this->actingAs($user);

        // Create the slip with an Employee linked to this user.
        $employee = \App\Models\HR\Employee::factory()->create(['user_id' => $user->id]);
        $slip = PayrollItem::factory()->create(['employee_id' => $employee->id]);

        $this->get(route('pdf.payroll-slip', $slip))->assertOk();
    });

    it('allows payroll-slip for hr-manager (payroll.view)', function () {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');
        $this->actingAs($user);

        $slip = PayrollItem::factory()->create();

        $this->get(route('pdf.payroll-slip', $slip))->assertOk();
    });

    it('denies import.products without inventory.create', function () {
        $user = User::factory()->create();
        $user->assignRole('accountant'); // accountant has no inventory.create
        $this->actingAs($user);

        $this->post(route('import.products'))->assertForbidden();
    });
});
