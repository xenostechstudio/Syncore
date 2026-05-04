<?php

use App\Livewire\HR\Attendance\Index;
use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

describe('Attendance Index', function () {
    it('mounts with current-month date range', function () {
        Livewire::test(Index::class)
            ->assertSet('dateFrom', now()->startOfMonth()->format('Y-m-d'))
            ->assertSet('dateTo', now()->endOfMonth()->format('Y-m-d'));
    });

    it('renders attendance records within the date range', function () {
        $today = now()->startOfMonth()->format('Y-m-d');
        Attendance::factory()->count(3)->create(['date' => $today]);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('attendances', fn ($p) => $p->total() === 3);
    });

    it('filters by employeeId', function () {
        $e = Employee::factory()->create();
        Attendance::factory()->create(['employee_id' => $e->id, 'date' => now()->startOfMonth()]);
        Attendance::factory()->count(2)->create(['date' => now()->startOfMonth()]);

        Livewire::test(Index::class)
            ->set('employeeId', (string) $e->id)
            ->assertViewHas('attendances', fn ($p) => $p->total() === 1);
    });

    it('confirmBulkDelete allows manual/absent only', function () {
        $manual = Attendance::factory()->manual()->create(['date' => now()->startOfMonth()]);
        $absent = Attendance::factory()->absent()->create(['date' => now()->startOfMonth()]);
        $present = Attendance::factory()->create(['date' => now()->startOfMonth()]);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $manual->id, (string) $absent->id, (string) $present->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(2);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkDelete removes only manual/absent records', function () {
        $manual = Attendance::factory()->manual()->create(['date' => now()->startOfMonth()]);
        $present = Attendance::factory()->create(['date' => now()->startOfMonth()]);

        Livewire::test(Index::class)
            ->set('selected', [(string) $manual->id, (string) $present->id])
            ->call('bulkDelete');

        expect(Attendance::find($manual->id))->toBeNull();
        expect(Attendance::find($present->id))->not->toBeNull();
    });

    it('statistics property returns status counts', function () {
        Attendance::factory()->count(2)->create(['date' => now()->startOfMonth(), 'status' => 'present']);
        Attendance::factory()->absent()->create(['date' => now()->startOfMonth()]);

        $c = Livewire::test(Index::class);
        $s = $c->viewData('statistics');
        expect($s['total'])->toBe(3);
        expect($s['present'])->toBe(2);
        expect($s['absent'])->toBe(1);
    });
});
