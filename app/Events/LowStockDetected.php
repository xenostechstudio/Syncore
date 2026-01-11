<?php

namespace App\Events;

use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Product $product,
        public Warehouse $warehouse,
        public int $currentStock,
        public int $reorderLevel
    ) {}
}
