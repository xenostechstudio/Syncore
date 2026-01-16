<?php

namespace App\Models\Sales;

use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionReward extends Model
{
    protected $fillable = [
        'promotion_id',
        'reward_type',
        'product_id',
        'buy_quantity',
        'get_quantity',
        'discount_value',
        'max_discount',
        'apply_to',
    ];

    protected $casts = [
        'buy_quantity' => 'integer',
        'get_quantity' => 'integer',
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    public const REWARD_TYPES = [
        'free_product' => 'Free Product',
        'discount_percent' => 'Percentage Discount',
        'discount_fixed' => 'Fixed Amount Discount',
        'free_shipping' => 'Free Shipping',
        'buy_x_get_y' => 'Buy X Get Y',
    ];

    public const APPLY_TO = [
        'order' => 'Entire Order',
        'product' => 'Specific Products',
        'cheapest' => 'Cheapest Item',
        'expensive' => 'Most Expensive Item',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get reward type label
     */
    public function getRewardTypeLabelAttribute(): string
    {
        return self::REWARD_TYPES[$this->reward_type] ?? $this->reward_type;
    }

    /**
     * Get apply to label
     */
    public function getApplyToLabelAttribute(): string
    {
        return self::APPLY_TO[$this->apply_to] ?? $this->apply_to;
    }

    /**
     * Calculate discount for given context
     */
    public function calculateDiscount(array $context): float
    {
        return match ($this->reward_type) {
            'discount_percent' => $this->calculatePercentageDiscount($context),
            'discount_fixed' => $this->calculateFixedDiscount($context),
            'buy_x_get_y' => $this->calculateBuyXGetYDiscount($context),
            'free_product' => $this->calculateFreeProductValue($context),
            default => 0,
        };
    }

    protected function calculatePercentageDiscount(array $context): float
    {
        $baseAmount = $this->getBaseAmount($context);
        $discount = $baseAmount * ($this->discount_value / 100);

        // Apply max discount cap if set
        if ($this->max_discount !== null && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return round($discount, 2);
    }

    protected function calculateFixedDiscount(array $context): float
    {
        $baseAmount = $this->getBaseAmount($context);
        $discount = min($this->discount_value, $baseAmount);

        return round($discount, 2);
    }

    protected function calculateBuyXGetYDiscount(array $context): float
    {
        $items = $context['items'] ?? [];
        $totalDiscount = 0;

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 0;
            $unitPrice = $item['unit_price'] ?? 0;

            // Calculate how many "sets" of buy_quantity + get_quantity
            $setSize = $this->buy_quantity + $this->get_quantity;
            $sets = floor($quantity / $setSize);

            // Free items = sets * get_quantity
            $freeItems = $sets * $this->get_quantity;

            // Discount = free items * unit price * discount percentage
            $discountPercent = $this->discount_value ?? 100; // Default 100% = free
            $totalDiscount += $freeItems * $unitPrice * ($discountPercent / 100);
        }

        return round($totalDiscount, 2);
    }

    protected function calculateFreeProductValue(array $context): float
    {
        if (!$this->product_id) {
            return 0;
        }

        $product = $this->product;
        if (!$product) {
            return 0;
        }

        $quantity = $this->get_quantity ?? 1;
        return round($product->selling_price * $quantity, 2);
    }

    protected function getBaseAmount(array $context): float
    {
        return match ($this->apply_to) {
            'order' => $context['subtotal'] ?? 0,
            'product' => $this->getProductAmount($context),
            'cheapest' => $this->getCheapestItemAmount($context),
            'expensive' => $this->getExpensiveItemAmount($context),
            default => $context['subtotal'] ?? 0,
        };
    }

    protected function getProductAmount(array $context): float
    {
        $items = $context['items'] ?? [];
        $total = 0;

        foreach ($items as $item) {
            if ($this->product_id && $item['product_id'] == $this->product_id) {
                $total += ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
            }
        }

        return $total;
    }

    protected function getCheapestItemAmount(array $context): float
    {
        $items = $context['items'] ?? [];
        $cheapest = null;

        foreach ($items as $item) {
            $price = $item['unit_price'] ?? 0;
            if ($cheapest === null || $price < $cheapest) {
                $cheapest = $price;
            }
        }

        return $cheapest ?? 0;
    }

    protected function getExpensiveItemAmount(array $context): float
    {
        $items = $context['items'] ?? [];
        $expensive = 0;

        foreach ($items as $item) {
            $price = $item['unit_price'] ?? 0;
            if ($price > $expensive) {
                $expensive = $price;
            }
        }

        return $expensive;
    }
}
