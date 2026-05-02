<?php

use App\Enums\PurchaseReceiptState;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('logs only one entry when transitioning a state-machine model (no duplicate updated/status_changed)', function () {
    $receipt = PurchaseReceipt::factory()->create([
        'warehouse_id' => Warehouse::factory(),
    ]);

    DB::table('activity_logs')->where('model_type', PurchaseReceipt::class)
        ->where('model_id', $receipt->id)
        ->delete();

    $receipt->transitionTo(PurchaseReceiptState::VALIDATED);

    $logs = DB::table('activity_logs')
        ->where('model_type', PurchaseReceipt::class)
        ->where('model_id', $receipt->id)
        ->orderBy('created_at')
        ->get();

    expect($logs)->toHaveCount(1);
    expect($logs[0]->action)->toBe('status_changed');
});

it('still logs both an updated and status_changed entry when status changes alongside other fields', function () {
    $receipt = PurchaseReceipt::factory()->create([
        'warehouse_id' => Warehouse::factory(),
    ]);

    DB::table('activity_logs')->where('model_type', PurchaseReceipt::class)
        ->where('model_id', $receipt->id)
        ->delete();

    // Mimic the validate() flow: save metadata first, then transition.
    $receipt->update(['notes' => 'Truck arrived 15 min late']);
    $receipt->transitionTo(PurchaseReceiptState::VALIDATED);

    $actions = DB::table('activity_logs')
        ->where('model_type', PurchaseReceipt::class)
        ->where('model_id', $receipt->id)
        ->orderBy('created_at')
        ->pluck('action')
        ->all();

    expect($actions)->toBe(['updated', 'status_changed']);
});

it('stores resolved foreign-key labels in the formatted payload (regression: chatter showed raw IDs)', function () {
    $oldWh = Warehouse::factory()->create(['name' => 'Main Warehouse']);
    $newWh = Warehouse::factory()->create(['name' => 'Backup Depot']);

    $receipt = PurchaseReceipt::factory()->create(['warehouse_id' => $oldWh->id]);

    DB::table('activity_logs')->where('model_type', PurchaseReceipt::class)
        ->where('model_id', $receipt->id)
        ->delete();

    $receipt->update(['warehouse_id' => $newWh->id]);

    $log = DB::table('activity_logs')
        ->where('model_type', PurchaseReceipt::class)
        ->where('model_id', $receipt->id)
        ->where('action', 'updated')
        ->first();

    expect($log)->not->toBeNull();

    $properties = json_decode($log->properties, true);

    expect($properties)->toHaveKey('old_formatted')
        ->and($properties)->toHaveKey('new_formatted')
        ->and($properties['old_formatted']['warehouse_id'])->toBe('Main Warehouse')
        ->and($properties['new_formatted']['warehouse_id'])->toBe('Backup Depot');
});
