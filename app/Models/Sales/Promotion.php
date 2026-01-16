<?php

namespace App\Models\Sales;

use App\Traits\HasNotes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Promotion extends Model
{
    use LogsActivity, HasNotes;

    protected array $logActions = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'name',
        'code',
        'type',
        'priority',
        'is_combinable',
        'requires_coupon',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_per_customer',
        'usage_count',
        'min_order_amount',
        'min_quantity',
        'is_active',
        'description',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_combinable' => 'boolean',
        'requires_coupon' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'usage_limit' => 'integer',
        'usage_per_customer' => 'integer',
        'usage_count' => 'integer',
        'min_order_amount' => 'decimal:2',
        'min_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'buy_x_get_y' => 'Buy X Get Y',
        'bundle' => 'Bundle Discount',
        'quantity_break' => 'Quantity Break',
        'cart_discount' => 'Cart Discount',
        'product_discount' => 'Product Discount',
        'coupon' => 'Coupon Code',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(PromotionRule::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(PromotionReward::class);
    }

    public function reward(): HasOne
    {
        return $this->hasOne(PromotionReward::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Check if promotion is currently valid (date-wise)
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now()->startOfDay();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if promotion has reached usage limit
     */
    public function hasReachedLimit(): bool
    {
        if ($this->usage_limit === null) {
            return false;
        }

        return $this->usage_count >= $this->usage_limit;
    }

    /**
     * Check if customer has reached their usage limit
     */
    public function hasCustomerReachedLimit(int $customerId): bool
    {
        if ($this->usage_per_customer === null) {
            return false;
        }

        $customerUsage = $this->usages()
            ->where('customer_id', $customerId)
            ->count();

        return $customerUsage >= $this->usage_per_customer;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Scope for active promotions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid promotions (active + within date range)
     */
    public function scopeValid($query)
    {
        $now = now()->startOfDay();

        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            });
    }

    /**
     * Scope for automatic promotions (no coupon required)
     */
    public function scopeAutomatic($query)
    {
        return $query->where('requires_coupon', false);
    }

    /**
     * Scope for coupon promotions
     */
    public function scopeCoupon($query)
    {
        return $query->where('requires_coupon', true);
    }
}
