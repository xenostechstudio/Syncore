<?php

namespace App\Models\Inventory;

use App\Enums\AdjustmentState;
use App\Models\User;
use App\Traits\HasNotes;
use App\Traits\HasSoftDeletes;
use App\Traits\HasStateMachine;
use App\Traits\HasYearlySequenceNumber;
use App\Traits\LogsActivity;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Product;
use App\Models\Inventory\InventoryStock;

class InventoryAdjustment extends Model
{
    use LogsActivity, HasNotes, HasSoftDeletes, Searchable, HasStateMachine, HasYearlySequenceNumber;

    protected string $stateEnum = AdjustmentState::class;

    public const NUMBER_COLUMN = 'adjustment_number';
    public const NUMBER_DIGITS = 5;

    protected array $logActions = ['created', 'updated', 'deleted'];
    
    protected array $searchable = ['adjustment_number', 'reason', 'notes'];

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

    /**
     * Get the number prefix based on adjustment type.
     */
    public function getNumberPrefix(): string
    {
        return match ($this->adjustment_type) {
            'increase' => 'WH/IN',
            'decrease' => 'WH/OUT',
            default => 'ADJ',
        };
    }

    public function cancelAdjustment(): bool
    {
        if (!$this->state->canCancel()) {
            return false;
        }
        return $this->transitionTo(AdjustmentState::CANCELLED);
    }

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

            $this->transitionTo(AdjustmentState::COMPLETED);
            $this->posted_at = now();
            $this->save();
        });
    }
}
