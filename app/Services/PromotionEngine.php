<?php

namespace App\Services;

use App\Models\Sales\Promotion;
use App\Models\Sales\PromotionUsage;
use App\Models\Sales\SalesOrder;
use App\Models\Inventory\Product;
use Illuminate\Support\Collection;

class PromotionEngine
{
    protected ?int $customerId = null;
    protected array $items = [];
    protected float $subtotal = 0;
    protected ?string $couponCode = null;

    /**
     * Set the customer for promotion evaluation
     */
    public function forCustomer(?int $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * Set the cart items
     * Each item should have: product_id, quantity, unit_price, category_id (optional)
     */
    public function withItems(array $items): self
    {
        $this->items = $items;
        $this->calculateSubtotal();
        return $this;
    }

    /**
     * Set coupon code
     */
    public function withCoupon(?string $code): self
    {
        $this->couponCode = $code;
        return $this;
    }

    /**
     * Calculate subtotal from items
     */
    protected function calculateSubtotal(): void
    {
        $this->subtotal = collect($this->items)->sum(function ($item) {
            return ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
        });
    }

    /**
     * Get all applicable promotions
     */
    public function getApplicablePromotions(): Collection
    {
        $promotions = Promotion::valid()
            ->with(['rules', 'rewards', 'rewards.product'])
            ->orderBy('priority')
            ->get();

        return $promotions->filter(function ($promotion) {
            return $this->isPromotionApplicable($promotion);
        });
    }

    /**
     * Check if a specific promotion is applicable
     */
    public function isPromotionApplicable(Promotion $promotion): bool
    {
        // Check if promotion requires coupon
        if ($promotion->requires_coupon) {
            if (!$this->couponCode || strtoupper($this->couponCode) !== strtoupper($promotion->code)) {
                return false;
            }
        }

        // Check usage limits
        if ($promotion->hasReachedLimit()) {
            return false;
        }

        // Check customer usage limit
        if ($this->customerId && $promotion->hasCustomerReachedLimit($this->customerId)) {
            return false;
        }

        // Check minimum order amount
        if ($promotion->min_order_amount && $this->subtotal < $promotion->min_order_amount) {
            return false;
        }

        // Check minimum quantity
        if ($promotion->min_quantity) {
            $totalQuantity = collect($this->items)->sum('quantity');
            if ($totalQuantity < $promotion->min_quantity) {
                return false;
            }
        }

        // Check all rules
        if ($promotion->rules->isNotEmpty()) {
            $context = $this->buildContext();
            foreach ($promotion->rules as $rule) {
                if (!$rule->matches($context)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Build context array for rule evaluation
     */
    protected function buildContext(): array
    {
        $productIds = collect($this->items)->pluck('product_id')->filter()->toArray();
        $categoryIds = collect($this->items)->pluck('category_id')->filter()->unique()->toArray();

        // If category_id not in items, fetch from products
        if (empty($categoryIds) && !empty($productIds)) {
            $categoryIds = Product::whereIn('id', $productIds)
                ->pluck('category_id')
                ->filter()
                ->unique()
                ->toArray();
        }

        return [
            'customer_id' => $this->customerId,
            'customer_groups' => [], // TODO: Implement customer groups
            'product_ids' => $productIds,
            'category_ids' => $categoryIds,
            'total_quantity' => collect($this->items)->sum('quantity'),
            'total_amount' => $this->subtotal,
            'subtotal' => $this->subtotal,
            'items' => $this->items,
        ];
    }

    /**
     * Calculate discount for a promotion
     */
    public function calculateDiscount(Promotion $promotion): array
    {
        $context = $this->buildContext();
        $totalDiscount = 0;
        $freeItems = [];
        $itemDiscounts = [];

        foreach ($promotion->rewards as $reward) {
            $discount = $reward->calculateDiscount($context);
            $totalDiscount += $discount;

            // Track free items
            if ($reward->reward_type === 'free_product' && $reward->product_id) {
                $freeItems[] = [
                    'product_id' => $reward->product_id,
                    'quantity' => $reward->get_quantity ?? 1,
                    'unit_price' => $reward->product->selling_price ?? 0,
                    'discount' => $reward->product->selling_price ?? 0,
                ];
            }

            // Track item-level discounts for buy_x_get_y
            if ($reward->reward_type === 'buy_x_get_y') {
                $itemDiscounts = $this->calculateBuyXGetYItemDiscounts($reward);
            }
        }

        return [
            'promotion_id' => $promotion->id,
            'promotion_name' => $promotion->name,
            'promotion_code' => $promotion->code,
            'total_discount' => round($totalDiscount, 2),
            'free_items' => $freeItems,
            'item_discounts' => $itemDiscounts,
        ];
    }

    /**
     * Calculate item-level discounts for Buy X Get Y
     */
    protected function calculateBuyXGetYItemDiscounts($reward): array
    {
        $discounts = [];
        $buyQty = $reward->buy_quantity ?? 1;
        $getQty = $reward->get_quantity ?? 1;
        $discountPercent = $reward->discount_value ?? 100;

        foreach ($this->items as $index => $item) {
            $quantity = $item['quantity'] ?? 0;
            $unitPrice = $item['unit_price'] ?? 0;

            $setSize = $buyQty + $getQty;
            $sets = floor($quantity / $setSize);
            $freeItems = $sets * $getQty;

            if ($freeItems > 0) {
                $discounts[$index] = [
                    'product_id' => $item['product_id'],
                    'free_quantity' => $freeItems,
                    'discount_per_item' => $unitPrice * ($discountPercent / 100),
                    'total_discount' => $freeItems * $unitPrice * ($discountPercent / 100),
                ];
            }
        }

        return $discounts;
    }

    /**
     * Get the best applicable promotion (highest discount)
     */
    public function getBestPromotion(): ?array
    {
        $applicablePromotions = $this->getApplicablePromotions();

        if ($applicablePromotions->isEmpty()) {
            return null;
        }

        $best = null;
        $bestDiscount = 0;

        foreach ($applicablePromotions as $promotion) {
            $result = $this->calculateDiscount($promotion);
            if ($result['total_discount'] > $bestDiscount) {
                $bestDiscount = $result['total_discount'];
                $best = $result;
            }
        }

        return $best;
    }

    /**
     * Get all applicable promotions with their discounts
     */
    public function getAllPromotionsWithDiscounts(): Collection
    {
        return $this->getApplicablePromotions()->map(function ($promotion) {
            return $this->calculateDiscount($promotion);
        });
    }

    /**
     * Apply promotion to a sales order
     */
    public function applyToOrder(SalesOrder $order, Promotion $promotion): void
    {
        $result = $this->calculateDiscount($promotion);

        // Update order
        $order->update([
            'promotion_id' => $promotion->id,
            'promotion_code' => $promotion->code,
            'promotion_discount' => $result['total_discount'],
        ]);

        // Record usage
        PromotionUsage::create([
            'promotion_id' => $promotion->id,
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'discount_amount' => $result['total_discount'],
        ]);

        // Increment usage count
        $promotion->incrementUsage();
    }

    /**
     * Validate a coupon code
     */
    public function validateCoupon(string $code): array
    {
        $promotion = Promotion::where('code', strtoupper($code))
            ->where('requires_coupon', true)
            ->first();

        if (!$promotion) {
            return [
                'valid' => false,
                'message' => 'Invalid coupon code.',
            ];
        }

        if (!$promotion->isValid()) {
            return [
                'valid' => false,
                'message' => 'This coupon has expired or is not yet active.',
            ];
        }

        if ($promotion->hasReachedLimit()) {
            return [
                'valid' => false,
                'message' => 'This coupon has reached its usage limit.',
            ];
        }

        if ($this->customerId && $promotion->hasCustomerReachedLimit($this->customerId)) {
            return [
                'valid' => false,
                'message' => 'You have already used this coupon the maximum number of times.',
            ];
        }

        $this->couponCode = $code;

        if (!$this->isPromotionApplicable($promotion)) {
            return [
                'valid' => false,
                'message' => 'This coupon is not applicable to your current order.',
            ];
        }

        $discount = $this->calculateDiscount($promotion);

        return [
            'valid' => true,
            'promotion' => $promotion,
            'discount' => $discount,
            'message' => "Coupon applied! You save Rp " . number_format($discount['total_discount'], 0, ',', '.'),
        ];
    }
}
