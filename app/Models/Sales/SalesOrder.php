<?php

namespace App\Models\Sales;

use App\Models\Delivery\DeliveryOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesOrder extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'user_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'shipping_address',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'SO';
        $lastOrder = self::orderByDesc('id')->first();
        $sequence = 1;

        if ($lastOrder && $lastOrder->order_number) {
            $digits = preg_replace('/\D/', '', $lastOrder->order_number);
            if ($digits !== '') {
                $sequence = (int) substr($digits, -4) + 1;
            }
        }

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
