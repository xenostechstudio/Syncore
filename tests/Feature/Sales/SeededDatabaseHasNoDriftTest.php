<?php

/**
 * Architectural smoke test: after running the seeders, the
 * sales-orders:reconcile-fulfillment --dry-run command must report
 * zero drift. This pins the contract that every seeder write path
 * goes through Eloquent (so the fulfillment observers fire), not raw
 * DB::table()->insert() that would silently bypass them.
 *
 * If this test fails after a seeder change, the seeder is producing
 * data that doesn't round-trip through the observers. Either fix the
 * seeder to use Eloquent ::create() (preferred), or document why a
 * raw insert is justified and add a manual recompute call after it.
 */

use Database\Seeders\DeliverySeeder;
use Database\Seeders\InventorySeeder;
use Database\Seeders\InvoicingSeeder;
use Database\Seeders\ModulePermissionSeeder;
use Database\Seeders\SalesSeeder;
use Database\Seeders\UserSeeder;

it('the seeded SO/Invoice/Delivery data is drift-free under reconcile --dry-run', function () {
    $this->seed([
        ModulePermissionSeeder::class,
        UserSeeder::class,
        InventorySeeder::class,
        SalesSeeder::class,
        InvoicingSeeder::class,
        DeliverySeeder::class,
    ]);

    $this->artisan('sales-orders:reconcile-fulfillment', ['--dry-run' => true])
        ->expectsOutputToContain('All sales orders are consistent')
        ->assertExitCode(0);
});
