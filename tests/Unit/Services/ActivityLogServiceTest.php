<?php

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('activity_logs')->truncate();
});

test('can log activity', function () {
    ActivityLogService::log('test_action', null, 'Test description');

    $log = DB::table('activity_logs')->first();

    expect($log)->not->toBeNull()
        ->and($log->action)->toBe('test_action')
        ->and($log->description)->toBe('Test description');
});

test('can log activity with model', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    // Clear auto-logged 'created' entry from LogsActivity trait
    DB::table('activity_logs')->truncate();

    ActivityLogService::log('viewed', $user, 'User profile viewed');

    $log = DB::table('activity_logs')->first();

    expect($log->model_type)->toBe(User::class)
        ->and($log->model_id)->toBe($user->id)
        ->and($log->model_name)->toBe('Test User');
});

test('can log created event', function () {
    $user = User::factory()->create();
    // Clear auto-logged entry, then manually log to test the service method
    DB::table('activity_logs')->truncate();

    ActivityLogService::logCreated($user);

    $log = DB::table('activity_logs')->first();

    expect($log->action)->toBe('created')
        ->and($log->model_type)->toBe(User::class);
});

test('can log updated event with changes', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    $oldValues = $user->getOriginal();
    // Clear auto-logged entries
    DB::table('activity_logs')->truncate();
    
    $user->update(['name' => 'New Name']);
    // Clear auto-logged 'updated' entry from trait
    DB::table('activity_logs')->truncate();

    ActivityLogService::logUpdated($user, $oldValues);

    $log = DB::table('activity_logs')->first();
    $properties = json_decode($log->properties, true);

    expect($log->action)->toBe('updated')
        ->and($properties)->toHaveKey('old')
        ->and($properties)->toHaveKey('new');
});

test('can log deleted event', function () {
    $user = User::factory()->create();
    // Clear auto-logged 'created' entry
    DB::table('activity_logs')->truncate();

    ActivityLogService::logDeleted($user);

    $log = DB::table('activity_logs')->first();

    expect($log->action)->toBe('deleted');
});

test('can log status change', function () {
    $user = User::factory()->create();
    // Clear auto-logged 'created' entry
    DB::table('activity_logs')->truncate();

    ActivityLogService::logStatusChanged($user, 'draft', 'confirmed');

    $log = DB::table('activity_logs')->first();
    $properties = json_decode($log->properties, true);

    expect($log->action)->toBe('status_changed')
        ->and($properties['old_status'])->toBe('draft')
        ->and($properties['new_status'])->toBe('confirmed');
});

test('can get activities for model', function () {
    $user = User::factory()->create();
    // Clear auto-logged 'created' entry
    DB::table('activity_logs')->truncate();

    ActivityLogService::log('action1', $user);
    ActivityLogService::log('action2', $user);
    ActivityLogService::log('action3', $user);

    $activities = ActivityLogService::getActivitiesFor($user);

    expect($activities)->toHaveCount(3);
});

test('can get activities by user', function () {
    $user = User::factory()->create();
    // Clear auto-logged 'created' entry
    DB::table('activity_logs')->truncate();
    $this->actingAs($user);

    ActivityLogService::log('action1');
    ActivityLogService::log('action2');

    $activities = ActivityLogService::getActivitiesByUser($user->id);

    expect($activities)->toHaveCount(2);
});

test('can search activities', function () {
    $user = User::factory()->create();
    // Clear auto-logged 'created' entry (which would also match 'created' action search)
    DB::table('activity_logs')->truncate();
    $this->actingAs($user);

    ActivityLogService::log('created', null, 'Created something');
    ActivityLogService::log('updated', null, 'Updated something');
    ActivityLogService::log('deleted', null, 'Deleted something');

    $results = ActivityLogService::search(['action' => 'created']);

    expect($results->total())->toBe(1);
});

test('can get statistics', function () {
    ActivityLogService::log('created');
    ActivityLogService::log('created');
    ActivityLogService::log('updated');

    $stats = ActivityLogService::getStatistics(
        now()->subDay(),
        now()->addDay()
    );

    expect($stats)->toHaveKey('by_action')
        ->and($stats)->toHaveKey('total')
        ->and($stats['total'])->toBe(3)
        ->and($stats['by_action']['created'])->toBe(2);
});

test('can cleanup old logs', function () {
    // Create old log
    DB::table('activity_logs')->insert([
        'action' => 'old_action',
        'description' => 'Old log',
        'created_at' => now()->subDays(100),
    ]);

    // Create recent log
    DB::table('activity_logs')->insert([
        'action' => 'recent_action',
        'description' => 'Recent log',
        'created_at' => now(),
    ]);

    $deleted = ActivityLogService::cleanup(90);

    expect($deleted)->toBe(1)
        ->and(DB::table('activity_logs')->count())->toBe(1);
});

test('filters sensitive fields', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'secret123',
    ]);

    ActivityLogService::logCreated($user);

    $log = DB::table('activity_logs')->first();
    $properties = json_decode($log->properties, true);

    // Password should be excluded
    expect($properties['attributes'])->not->toHaveKey('password');
    
    // Email should be masked
    if (isset($properties['attributes']['email'])) {
        expect($properties['attributes']['email'])->not->toBe('test@example.com');
    }
});

test('can export activities', function () {
    ActivityLogService::log('action1', null, 'Description 1');
    ActivityLogService::log('action2', null, 'Description 2');

    $export = ActivityLogService::export([], 100);

    expect($export)->toHaveCount(2)
        ->and($export[0])->toHaveKey('action')
        ->and($export[0])->toHaveKey('description');
});
