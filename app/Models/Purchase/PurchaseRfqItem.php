<?php

namespace App\Models\Purchase;

use App\Models\Inventory\Product;
use Database\Factories\Purchase\PurchaseRfqItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRfqItem extends Model
{
    /** @use HasFactory<PurchaseRfqItemFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_rfq_id',
        'product_id',
        'description',
        'quantity',
        'quantity_received',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function quantityRemaining(): float
    {
        return max(0, (float) $this->quantity - (float) $this->quantity_received);
    }

    public function purchaseRfq(): BelongsTo
    {
        return $this->belongsTo(PurchaseRfq::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
