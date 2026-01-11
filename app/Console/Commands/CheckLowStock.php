<?php

namespace App\Console\Commands;

use App\Events\LowStockDetected;
use App\Models\Inventory\InventoryStock;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products with low stock levels';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for low stock products...');

        $lowStockItems = InventoryStock::with(['product', 'warehouse'])
            ->whereHas('product', function ($query) {
                $query->whereNotNull('reorder_level')
                    ->where('reorder_level', '>', 0);
            })
            ->get()
            ->filter(function ($stock) {
                return $stock->quantity <= $stock->product->reorder_level;
            });

        $count = 0;

        foreach ($lowStockItems as $stock) {
            LowStockDetected::dispatch(
                $stock->product,
                $stock->warehouse,
                $stock->quantity,
                $stock->product->reorder_level
            );
            $count++;
        }

        $this->info("Found {$count} products with low stock.");

        return Command::SUCCESS;
    }
}
