<?php

namespace App\Imports;

use App\Models\Sales\Promotion;
use App\Models\Sales\PromotionReward;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PromotionsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $name = trim($row['name'] ?? '');
                $code = trim($row['code'] ?? '');

                if (empty($name)) {
                    continue;
                }

                // Find existing by code or name
                $promotion = null;
                if (!empty($code)) {
                    $promotion = Promotion::where('code', strtoupper($code))->first();
                }
                if (!$promotion) {
                    $promotion = Promotion::where('name', $name)->first();
                }

                $data = [
                    'name' => $name,
                    'code' => !empty($code) ? strtoupper($code) : null,
                    'type' => $this->normalizeType($row['type'] ?? 'product_discount'),
                    'priority' => (int) ($row['priority'] ?? 10),
                    'is_combinable' => $this->parseBoolean($row['is_combinable'] ?? $row['combinable'] ?? false),
                    'requires_coupon' => $this->parseBoolean($row['requires_coupon'] ?? false),
                    'start_date' => $this->parseDate($row['start_date'] ?? null),
                    'end_date' => $this->parseDate($row['end_date'] ?? null),
                    'usage_limit' => $this->parseNullableInt($row['usage_limit'] ?? null),
                    'usage_per_customer' => $this->parseNullableInt($row['per_customer'] ?? $row['usage_per_customer'] ?? null),
                    'min_order_amount' => $this->parseNullableFloat($row['min_order_amount'] ?? null),
                    'min_quantity' => $this->parseNullableInt($row['min_quantity'] ?? null),
                    'is_active' => $this->parseBoolean($row['is_active'] ?? $row['status'] ?? true),
                    'description' => $row['description'] ?? null,
                ];

                if ($promotion) {
                    $promotion->update($data);
                    $this->updated++;
                } else {
                    $promotion = Promotion::create($data);
                    $this->imported++;
                }

                // Handle reward data
                $this->saveReward($promotion, $row);

            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    protected function saveReward(Promotion $promotion, $row): void
    {
        $rewardType = $this->normalizeRewardType($row['reward_type'] ?? 'discount_percent');
        
        $rewardData = [
            'reward_type' => $rewardType,
            'discount_value' => $this->parseNullableFloat($row['discount_value'] ?? null),
            'max_discount' => $this->parseNullableFloat($row['max_discount'] ?? null),
            'buy_quantity' => $this->parseNullableInt($row['buy_quantity'] ?? null),
            'get_quantity' => $this->parseNullableInt($row['get_quantity'] ?? null),
            'apply_to' => $row['apply_to'] ?? 'order',
        ];

        $promotion->rewards()->delete();
        $promotion->rewards()->create($rewardData);
    }

    protected function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));
        $map = [
            'buy x get y' => 'buy_x_get_y',
            'buyxgety' => 'buy_x_get_y',
            'bundle discount' => 'bundle',
            'quantity break' => 'quantity_break',
            'cart discount' => 'cart_discount',
            'product discount' => 'product_discount',
            'coupon code' => 'coupon',
        ];
        return $map[$type] ?? $type;
    }

    protected function normalizeRewardType(string $type): string
    {
        $type = strtolower(trim($type));
        $map = [
            'percentage discount' => 'discount_percent',
            'percent' => 'discount_percent',
            'fixed amount discount' => 'discount_fixed',
            'fixed' => 'discount_fixed',
            'buy x get y' => 'buy_x_get_y',
            'free product' => 'free_product',
            'free shipping' => 'free_shipping',
        ];
        return $map[$type] ?? $type;
    }

    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['yes', 'true', '1', 'active']);
        }
        return (bool) $value;
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) return null;
        if ($value instanceof \DateTime) return $value->format('Y-m-d');
        try {
            return date('Y-m-d', strtotime($value));
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseNullableInt($value): ?int
    {
        if ($value === null || $value === '' || $value === '-') return null;
        return (int) $value;
    }

    protected function parseNullableFloat($value): ?float
    {
        if ($value === null || $value === '' || $value === '-') return null;
        return (float) $value;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'nullable|string',
            'priority' => 'nullable|integer|min:1|max:100',
        ];
    }
}
