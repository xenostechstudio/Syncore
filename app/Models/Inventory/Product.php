<?php

namespace App\Models\Inventory;

use App\Models\User;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity, HasNotes;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'sku', 'barcode', 'product_type', 'internal_reference',
                'description', 'quantity', 'cost_price', 'selling_price', 'status',
                'warehouse_id', 'category_id', 'responsible_id', 'weight', 'volume',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => __('activity.product_created'),
                'updated' => __('activity.product_updated'),
                'deleted' => __('activity.product_deleted'),
                default => __('activity.product_event', ['event' => $eventName]),
            });
    }
}
