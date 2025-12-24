<?php

namespace App\Models\Sales;

use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'tax_id',
        'quantity',
        'quantity_invoiced',
        'quantity_delivered',
        'unit_price',
        'discount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_invoiced' => 'integer',
        'quantity_delivered' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function getQuantityToInvoiceAttribute(): int
    {
        return max(0, $this->quantity - $this->quantity_invoiced);
    }

    public function getQuantityToDeliverAttribute(): int
    {
        return max(0, $this->quantity - $this->quantity_delivered);
    }

    public function isFullyInvoiced(): bool
    {
        return $this->quantity_invoiced >= $this->quantity;
    }

    public function isFullyDelivered(): bool
    {
        return $this->quantity_delivered >= $this->quantity;
    }
}
