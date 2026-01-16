<?php

namespace App\Models\Sales;

use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionRule extends Model
{
    protected $fillable = [
        'promotion_id',
        'rule_type',
        'operator',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public const RULE_TYPES = [
        'product' => 'Specific Products',
        'category' => 'Product Categories',
        'customer' => 'Specific Customers',
        'customer_group' => 'Customer Groups',
        'min_quantity' => 'Minimum Quantity',
        'min_amount' => 'Minimum Amount',
    ];

    public const OPERATORS = [
        'in' => 'Is In',
        'not_in' => 'Is Not In',
        '>=' => 'Greater Than or Equal',
        '<=' => 'Less Than or Equal',
        '=' => 'Equals',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get rule type label
     */
    public function getRuleTypeLabelAttribute(): string
    {
        return self::RULE_TYPES[$this->rule_type] ?? $this->rule_type;
    }

    /**
     * Get operator label
     */
    public function getOperatorLabelAttribute(): string
    {
        return self::OPERATORS[$this->operator] ?? $this->operator;
    }

    /**
     * Check if cart/order matches this rule
     */
    public function matches(array $context): bool
    {
        return match ($this->rule_type) {
            'product' => $this->matchesProduct($context),
            'category' => $this->matchesCategory($context),
            'customer' => $this->matchesCustomer($context),
            'customer_group' => $this->matchesCustomerGroup($context),
            'min_quantity' => $this->matchesMinQuantity($context),
            'min_amount' => $this->matchesMinAmount($context),
            default => false,
        };
    }

    protected function matchesProduct(array $context): bool
    {
        $productIds = $context['product_ids'] ?? [];
        $ruleProductIds = $this->value ?? [];

        return match ($this->operator) {
            'in' => !empty(array_intersect($productIds, $ruleProductIds)),
            'not_in' => empty(array_intersect($productIds, $ruleProductIds)),
            default => false,
        };
    }

    protected function matchesCategory(array $context): bool
    {
        $categoryIds = $context['category_ids'] ?? [];
        $ruleCategoryIds = $this->value ?? [];

        return match ($this->operator) {
            'in' => !empty(array_intersect($categoryIds, $ruleCategoryIds)),
            'not_in' => empty(array_intersect($categoryIds, $ruleCategoryIds)),
            default => false,
        };
    }

    protected function matchesCustomer(array $context): bool
    {
        $customerId = $context['customer_id'] ?? null;
        $ruleCustomerIds = $this->value ?? [];

        if (!$customerId) {
            return false;
        }

        return match ($this->operator) {
            'in' => in_array($customerId, $ruleCustomerIds),
            'not_in' => !in_array($customerId, $ruleCustomerIds),
            default => false,
        };
    }

    protected function matchesCustomerGroup(array $context): bool
    {
        $customerGroups = $context['customer_groups'] ?? [];
        $ruleGroups = $this->value ?? [];

        return match ($this->operator) {
            'in' => !empty(array_intersect($customerGroups, $ruleGroups)),
            'not_in' => empty(array_intersect($customerGroups, $ruleGroups)),
            default => false,
        };
    }

    protected function matchesMinQuantity(array $context): bool
    {
        $totalQuantity = $context['total_quantity'] ?? 0;
        $ruleQuantity = $this->value[0] ?? 0;

        return match ($this->operator) {
            '>=' => $totalQuantity >= $ruleQuantity,
            '<=' => $totalQuantity <= $ruleQuantity,
            '=' => $totalQuantity == $ruleQuantity,
            default => false,
        };
    }

    protected function matchesMinAmount(array $context): bool
    {
        $totalAmount = $context['total_amount'] ?? 0;
        $ruleAmount = $this->value[0] ?? 0;

        return match ($this->operator) {
            '>=' => $totalAmount >= $ruleAmount,
            '<=' => $totalAmount <= $ruleAmount,
            '=' => $totalAmount == $ruleAmount,
            default => false,
        };
    }
}
