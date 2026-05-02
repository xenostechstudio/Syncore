<?php

namespace App\Models\Purchase;

use App\Models\Inventory\Product;
use Database\Factories\Purchase\PurchaseReceiptItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptItem extends Model
{
    /** @use HasFactory<PurchaseReceiptItemFactory> */
    use HasFactory;

    protected $table = 'purchase_receipt_items';

    protected $fillable = [
        'purchase_receipt_id',
        'purchase_rfq_item_id',
        'product_id',
        'quantity_received',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:2',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function rfqItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseRfqItem::class, 'purchase_rfq_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
