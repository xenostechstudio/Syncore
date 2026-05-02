<?php

use App\Livewire\Accounting\JournalEntries\Index as JournalEntriesIndex;
use App\Livewire\HR\Leave\Requests\Index as LeaveRequestsIndex;
use App\Livewire\HR\Payroll\Form as PayrollForm;
use App\Livewire\Settings\Roles\Form as RolesForm;
use App\Models\Accounting\JournalEntry;
use App\Models\HR\LeaveRequest;
use App\Models\HR\PayrollPeriod;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Event::fake();
    $this->seed(ModulePermissionSeeder::class);
});

/**
 * These tests verify that the `WithPermissions::authorizePermission()`
 * gate added on high-stakes Livewire actions actually denies users who
 * lack the matching Spatie permission, and lets users with it through.
 *
 * Each action gets a pair: super-admin can; an unprivileged user is 403'd.
 */
describe('Action authorization', function () {
    it('blocks payroll approve without payroll.approve', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $this->actingAs($user);

        $period = PayrollPeriod::factory()->create();

        Livewire::test(PayrollForm::class, ['id' => $period->id])
            ->call('approve')
            ->assertStatus(403);
    });

    it('allows payroll approve for super-admin', function () {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $this->actingAs($user);

        $period = PayrollPeriod::factory()->create();

        Livewire::test(PayrollForm::class, ['id' => $period->id])
            ->call('approve')
            ->assertStatus(200);

        expect($period->fresh()->status)->toBe('approved');
    });

    it('blocks leave approveSelected without leave.approve', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $this->actingAs($user);

        $req = LeaveRequest::factory()->create();

        Livewire::test(LeaveRequestsIndex::class)
            ->set('selected', [(string) $req->id])
            ->call('approveSelected')
            ->assertStatus(403);

        expect($req->fresh()->status)->toBe('pending');
    });

    it('blocks leave rejectSelected without leave.reject', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $this->actingAs($user);

        $req = LeaveRequest::factory()->create();

        Livewire::test(LeaveRequestsIndex::class)
            ->set('selected', [(string) $req->id])
            ->call('rejectSelected')
            ->assertStatus(403);

        expect($req->fresh()->status)->toBe('pending');
    });

    it('allows leave approve/reject for hr-manager role', function () {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');
        $this->actingAs($user);

        $approveReq = LeaveRequest::factory()->create();
        Livewire::test(LeaveRequestsIndex::class)
            ->set('selected', [(string) $approveReq->id])
            ->call('approveSelected')
            ->assertStatus(200);

        $rejectReq = LeaveRequest::factory()->create();
        Livewire::test(LeaveRequestsIndex::class)
            ->set('selected', [(string) $rejectReq->id])
            ->call('rejectSelected')
            ->assertStatus(200);

        expect($approveReq->fresh()->status)->toBe('approved');
        expect($rejectReq->fresh()->status)->toBe('rejected');
    });

    it('blocks accounting post without accounting.post', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');
        $this->actingAs($user);

        $entry = JournalEntry::factory()->create(['status' => 'draft']);

        Livewire::test(JournalEntriesIndex::class)
            ->call('post', $entry->id)
            ->assertStatus(403);

        expect($entry->fresh()->status)->toBe('draft');
    });

    it('blocks role delete without roles.delete', function () {
        $user = User::factory()->create();
        $user->assignRole('manager'); // manager has settings.view but not roles.delete
        $this->actingAs($user);

        $role = Role::create(['name' => 'temp-role', 'guard_name' => 'web']);

        Livewire::test(RolesForm::class, ['id' => $role->id])
            ->call('delete')
            ->assertStatus(403);

        expect(Role::find($role->id))->not->toBeNull();
    });

    it('allows role delete for super-admin', function () {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $this->actingAs($user);

        $role = Role::create(['name' => 'doomed-role', 'guard_name' => 'web']);

        Livewire::test(RolesForm::class, ['id' => $role->id])
            ->call('delete');

        expect(Role::find($role->id))->toBeNull();
    });
});
