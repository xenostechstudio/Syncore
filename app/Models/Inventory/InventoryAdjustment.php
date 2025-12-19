<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Product;
use App\Models\Inventory\InventoryStock;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'adjustment_number',
        'warehouse_id',
        'user_id',
        'adjustment_date',
        'adjustment_type',
        'status',
        'posted_at',
        'source_delivery_order_id',
        'source_delivery_return_id',
        'reason',
        'notes',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryAdjustmentItem::class);
    }

    public static function generateAdjustmentNumber(?string $adjustmentType = null): string
    {
        $prefix = match ($adjustmentType) {
            'increase' => 'WH/IN/',
            'decrease' => 'WH/OUT/',
            default => 'ADJ/',
        };

        $last = self::query()
            ->where('adjustment_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('adjustment_number');

        $sequence = 1;

        if ($last) {
            $suffix = substr($last, strlen($prefix));
            if (ctype_digit($suffix)) {
                $sequence = ((int) $suffix) + 1;
            }
        }

        return $prefix . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    public function isPosted(): bool
    {
        return $this->posted_at !== null;
    }

    public function post(): void
    {
        if ($this->isPosted()) {
            return;
        }

        DB::transaction(function () {
            $this->loadMissing(['items.product']);

            $productIds = $this->items
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $stocks = InventoryStock::query()
                ->where('warehouse_id', $this->warehouse_id)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            foreach ($productIds as $productId) {
                if (! $stocks->has($productId)) {
                    $stocks->put($productId, InventoryStock::create([
                        'warehouse_id' => $this->warehouse_id,
                        'product_id' => $productId,
                        'quantity' => 0,
                    ]));
                }
            }

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($this->items as $line) {
                $product = $products->get($line->product_id);
                $stock = $stocks->get($line->product_id);
                if (! $product || ! $stock) {
                    continue;
                }

                $qty = (int) ($line->counted_quantity ?? 0);

                if ($this->adjustment_type === 'increase') {
                    if ($qty <= 0) {
                        continue;
                    }
                    $stock->quantity = (int) $stock->quantity + $qty;
                } elseif ($this->adjustment_type === 'decrease') {
                    if ($qty <= 0) {
                        continue;
                    }
                    $newQty = (int) $stock->quantity - $qty;
                    if ($newQty < 0) {
                        throw new \RuntimeException('Insufficient stock for ' . ($product->name ?? ('Product #' . $product->id)));
                    }
                    $stock->quantity = $newQty;
                } elseif ($this->adjustment_type === 'count') {
                    $stock->quantity = max(0, $qty);
                }

                $stock->save();
            }

            $totals = InventoryStock::query()
                ->whereIn('product_id', $productIds)
                ->selectRaw('product_id, SUM(quantity) as total_qty')
                ->groupBy('product_id')
                ->pluck('total_qty', 'product_id');

            foreach ($products as $product) {
                $product->quantity = (int) ($totals[$product->id] ?? 0);
                $product->save();
            }

            $this->status = 'completed';
            $this->posted_at = now();
            $this->save();
        });
    }
}
