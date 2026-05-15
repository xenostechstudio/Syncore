<?php

namespace App\Models\Inventory;

use App\Enums\ProductStockLevel;
use App\Models\User;
use App\Traits\HasAttachments;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Database\Factories\Inventory\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasAttachments, HasFactory, HasNotes, HasSoftDeletes, LogsActivity, Searchable;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected array $searchable = ['name', 'sku', 'barcode', 'internal_reference'];

    protected $table = 'products';

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'product_type',
        'internal_reference',
        'description',
        'quantity',
        'cost_price',
        'selling_price',
        'status',
        'warehouse_id',
        'category_id',
        'responsible_id',
        'weight',
        'volume',
        'customer_lead_time',
        'receipt_note',
        'delivery_note',
        'internal_notes',
        'is_favorite',
        'sales_tax_id',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'volume' => 'decimal:3',
        'customer_lead_time' => 'integer',
        'is_favorite' => 'boolean',
    ];

    public function getStockLevelAttribute(): ProductStockLevel
    {
        return ProductStockLevel::tryFrom((string) $this->status) ?? ProductStockLevel::IN_STOCK;
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function pricelistRules(): HasMany
    {
        return $this->hasMany(ProductPricelistRule::class, 'product_id');
    }

    /**
     * Tables that carry a `product_id` FK to this product, excluding
     * `product_pricelist_rules` (owned config, cascades on delete).
     * A reference in any of these means a hard delete would corrupt or
     * orphan a real document, so it must be Archived instead. See
     * "Destructive actions" in CLAUDE.md.
     */
    private const REFERENCING_TABLES = [
        'sales_order_items',
        'invoice_items',
        'delivery_order_items',
        'purchase_rfq_items',
        'vendor_bill_items',
        'purchase_receipt_items',
        'inventory_stocks',
        'inventory_transfer_items',
        'inventory_adjustment_items',
        'pricelist_items',
        'promotion_rewards',
    ];

    /**
     * Whether any document or stock record references this product.
     * Used to gate the form's hard Delete action — see CLAUDE.md.
     */
    public function isReferenced(): bool
    {
        foreach (self::REFERENCING_TABLES as $table) {
            if (DB::table($table)->where('product_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }
}
