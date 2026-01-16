<?php

namespace App\Livewire\Sales\Configuration\Promotions;

use App\Livewire\Concerns\WithNotes;
use App\Models\Sales\Promotion;
use App\Models\Sales\PromotionRule;
use App\Models\Sales\PromotionReward;
use App\Models\Inventory\Product;
use App\Models\Inventory\Category;
use App\Models\Sales\Customer;
use App\Services\PromotionEngine;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Promotion')]
class Form extends Component
{
    use WithNotes;

    public ?int $promotionId = null;

    // Basic Info
    public string $name = '';
    public string $code = '';
    public string $type = 'product_discount';
    public int $priority = 10;
    public bool $is_combinable = false;
    public bool $requires_coupon = false;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?int $usage_limit = null;
    public ?int $usage_per_customer = null;
    public ?float $min_order_amount = null;
    public ?int $min_quantity = null;
    public bool $is_active = true;
    public string $description = '';

    // Rules
    public array $rules = [];

    // Reward
    public string $reward_type = 'discount_percent';
    public ?int $reward_product_id = null;
    public ?int $buy_quantity = null;
    public ?int $get_quantity = null;
    public ?float $discount_value = null;
    public ?float $max_discount = null;
    public string $apply_to = 'order';

    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    // Search
    public string $productSearch = '';
    public string $categorySearch = '';
    public string $customerSearch = '';

    // Simulator
    public bool $showSimulator = false;
    public array $simulatorItems = [];
    public ?int $simulatorCustomerId = null;
    public string $simulatorCoupon = '';
    public ?array $simulatorResult = null;

    protected function getNotableModel()
    {
        return $this->promotionId ? Promotion::find($this->promotionId) : null;
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->promotionId = $id;
            $promotion = Promotion::with(['rules', 'reward'])->findOrFail($id);

            $this->name = $promotion->name;
            $this->code = $promotion->code ?? '';
            $this->type = $promotion->type;
            $this->priority = $promotion->priority;
            $this->is_combinable = $promotion->is_combinable;
            $this->requires_coupon = $promotion->requires_coupon;
            $this->start_date = $promotion->start_date?->format('Y-m-d');
            $this->end_date = $promotion->end_date?->format('Y-m-d');
            $this->usage_limit = $promotion->usage_limit;
            $this->usage_per_customer = $promotion->usage_per_customer;
            $this->min_order_amount = $promotion->min_order_amount;
            $this->min_quantity = $promotion->min_quantity;
            $this->is_active = $promotion->is_active;
            $this->description = $promotion->description ?? '';

            // Load rules
            $this->rules = $promotion->rules->map(fn($rule) => [
                'id' => $rule->id,
                'rule_type' => $rule->rule_type,
                'operator' => $rule->operator,
                'value' => $rule->value,
            ])->toArray();

            // Load reward
            if ($promotion->reward) {
                $this->reward_type = $promotion->reward->reward_type;
                $this->reward_product_id = $promotion->reward->product_id;
                $this->buy_quantity = $promotion->reward->buy_quantity;
                $this->get_quantity = $promotion->reward->get_quantity;
                $this->discount_value = $promotion->reward->discount_value;
                $this->max_discount = $promotion->reward->max_discount;
                $this->apply_to = $promotion->reward->apply_to;
            }

            $this->createdAt = $promotion->created_at?->format('M d, Y \a\t H:i');
            $this->updatedAt = $promotion->updated_at?->format('M d, Y \a\t H:i');

            // Initialize simulator with one empty row
            $this->simulatorItems = [[
                'product_id' => null,
                'product_name' => '',
                'quantity' => 1,
                'unit_price' => 0,
            ]];
        }
    }

    public function updatedType(): void
    {
        // Set smart defaults based on type
        match ($this->type) {
            'buy_x_get_y' => $this->setBuyXGetYDefaults(),
            'bundle' => $this->setBundleDefaults(),
            'quantity_break' => $this->setQuantityBreakDefaults(),
            'cart_discount' => $this->setCartDiscountDefaults(),
            'product_discount' => $this->setProductDiscountDefaults(),
            'coupon' => $this->setCouponDefaults(),
            default => null,
        };
    }

    protected function setBuyXGetYDefaults(): void
    {
        $this->reward_type = 'buy_x_get_y';
        $this->buy_quantity = 2;
        $this->get_quantity = 1;
        $this->discount_value = 100;
        $this->requires_coupon = false;
        $this->apply_to = 'product';
    }

    protected function setBundleDefaults(): void
    {
        $this->reward_type = 'discount_percent';
        $this->discount_value = 10;
        $this->requires_coupon = false;
        $this->apply_to = 'product';
        $this->buy_quantity = null;
        $this->get_quantity = null;
    }

    protected function setQuantityBreakDefaults(): void
    {
        $this->reward_type = 'discount_percent';
        $this->discount_value = 15;
        $this->min_quantity = 5;
        $this->requires_coupon = false;
        $this->apply_to = 'product';
    }

    protected function setCartDiscountDefaults(): void
    {
        $this->reward_type = 'discount_percent';
        $this->discount_value = 10;
        $this->min_order_amount = 100000;
        $this->requires_coupon = false;
        $this->apply_to = 'order';
    }

    protected function setProductDiscountDefaults(): void
    {
        $this->reward_type = 'discount_percent';
        $this->discount_value = 10;
        $this->requires_coupon = false;
        $this->apply_to = 'product';
    }

    /**
     * Get filtered reward types based on promotion type
     */
    public function getFilteredRewardTypes(): array
    {
        $allTypes = PromotionReward::REWARD_TYPES;

        return match ($this->type) {
            'buy_x_get_y' => array_intersect_key($allTypes, array_flip(['buy_x_get_y', 'free_product'])),
            'bundle' => array_intersect_key($allTypes, array_flip(['discount_percent', 'discount_fixed'])),
            'quantity_break' => array_intersect_key($allTypes, array_flip(['discount_percent', 'discount_fixed'])),
            'cart_discount' => array_intersect_key($allTypes, array_flip(['discount_percent', 'discount_fixed', 'free_shipping'])),
            'product_discount' => array_intersect_key($allTypes, array_flip(['discount_percent', 'discount_fixed', 'free_product'])),
            'coupon' => $allTypes, // Coupon can have any reward type
            default => $allTypes,
        };
    }

    protected function setCouponDefaults(): void
    {
        $this->requires_coupon = true;
        $this->reward_type = 'discount_percent';
        $this->discount_value = 10;
        $this->apply_to = 'order';
    }

    /**
     * Get validation warnings for the promotion
     */
    public function getValidationWarnings(): array
    {
        $warnings = [];

        // No end date warning
        if ($this->start_date && !$this->end_date) {
            $warnings[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'message' => 'No end date set - promotion will run indefinitely',
            ];
        }

        // No usage limit warning
        if (!$this->usage_limit && !$this->usage_per_customer) {
            $warnings[] = [
                'type' => 'info',
                'icon' => 'information-circle',
                'message' => 'No usage limits set - unlimited redemptions allowed',
            ];
        }

        // High discount warning
        if ($this->reward_type === 'discount_percent' && $this->discount_value > 50) {
            $warnings[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'message' => 'High discount percentage (' . $this->discount_value . '%) - verify this is intentional',
            ];
        }

        // Coupon type without code
        if ($this->type === 'coupon' && empty($this->code)) {
            $warnings[] = [
                'type' => 'error',
                'icon' => 'x-circle',
                'message' => 'Coupon promotion requires a code',
            ];
        }

        // Check for overlapping promotions (same type, overlapping dates)
        if ($this->promotionId && $this->start_date) {
            $overlapping = Promotion::where('id', '!=', $this->promotionId)
                ->where('type', $this->type)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereNull('end_date')
                           ->orWhere('end_date', '>=', $this->start_date);
                    });
                    if ($this->end_date) {
                        $q->where(function ($q2) {
                            $q2->whereNull('start_date')
                               ->orWhere('start_date', '<=', $this->end_date);
                        });
                    }
                })
                ->exists();

            if ($overlapping) {
                $warnings[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'message' => 'Overlapping promotion of same type exists',
                ];
            }
        }

        return $warnings;
    }

    /**
     * Get promotion summary for preview card
     */
    public function getPromotionSummary(): array
    {
        $summary = [
            'name' => $this->name ?: 'Untitled Promotion',
            'code' => $this->code ?: null,
            'type' => Promotion::TYPES[$this->type] ?? $this->type,
            'status' => $this->is_active ? 'Active' : 'Inactive',
            'priority' => $this->priority,
        ];

        // Validity
        if ($this->start_date || $this->end_date) {
            $start = $this->start_date ? date('M d, Y', strtotime($this->start_date)) : 'Now';
            $end = $this->end_date ? date('M d, Y', strtotime($this->end_date)) : 'No end';
            $summary['validity'] = "$start → $end";
        } else {
            $summary['validity'] = 'Always valid';
        }

        // Conditions summary
        $conditions = [];
        if ($this->min_order_amount) {
            $conditions[] = 'Min order: Rp ' . number_format($this->min_order_amount, 0, ',', '.');
        }
        if ($this->min_quantity) {
            $conditions[] = 'Min qty: ' . $this->min_quantity;
        }
        if (count($this->rules) > 0) {
            $conditions[] = count($this->rules) . ' rule(s)';
        }
        $summary['conditions'] = $conditions ?: ['No conditions'];

        // Reward summary
        $reward = match ($this->reward_type) {
            'discount_percent' => ($this->discount_value ?? 0) . '% off',
            'discount_fixed' => 'Rp ' . number_format($this->discount_value ?? 0, 0, ',', '.') . ' off',
            'buy_x_get_y' => 'Buy ' . ($this->buy_quantity ?? 0) . ' Get ' . ($this->get_quantity ?? 0),
            'free_product' => 'Free product',
            'free_shipping' => 'Free shipping',
            default => 'Not configured',
        };
        if ($this->max_discount && $this->reward_type === 'discount_percent') {
            $reward .= ' (max Rp ' . number_format($this->max_discount, 0, ',', '.') . ')';
        }
        $summary['reward'] = $reward;

        // Usage
        $model = $this->getNotableModel();
        $summary['usage_count'] = $model?->usage_count ?? 0;
        $summary['usage_limit'] = $this->usage_limit;
        $summary['usage_per_customer'] = $this->usage_per_customer;

        return $summary;
    }

    public function addRule(): void
    {
        $this->rules[] = [
            'id' => null,
            'rule_type' => 'product',
            'operator' => 'in',
            'value' => [],
        ];
    }

    public function removeRule(int $index): void
    {
        unset($this->rules[$index]);
        $this->rules = array_values($this->rules);
    }

    public function generateCode(): void
    {
        $this->code = strtoupper(substr(str_replace(' ', '', $this->name), 0, 6) . rand(100, 999));
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:promotions,code,' . $this->promotionId,
            'type' => 'required|in:buy_x_get_y,bundle,quantity_break,cart_discount,product_discount,coupon',
            'priority' => 'required|integer|min:1|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_customer' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'discount_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'code' => $this->code ? strtoupper($this->code) : null,
            'type' => $this->type,
            'priority' => $this->priority,
            'is_combinable' => $this->is_combinable,
            'requires_coupon' => $this->requires_coupon,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'usage_limit' => $this->usage_limit,
            'usage_per_customer' => $this->usage_per_customer,
            'min_order_amount' => $this->min_order_amount,
            'min_quantity' => $this->min_quantity,
            'is_active' => $this->is_active,
            'description' => $this->description ?: null,
        ];

        if ($this->promotionId) {
            $promotion = Promotion::findOrFail($this->promotionId);
            $promotion->update($data);
        } else {
            $promotion = Promotion::create($data);
            $this->promotionId = $promotion->id;
        }

        // Save rules
        $this->saveRules($promotion);

        // Save reward
        $this->saveReward($promotion);

        $this->createdAt = $promotion->created_at->format('M d, Y \a\t H:i');
        $this->updatedAt = $promotion->updated_at->format('M d, Y \a\t H:i');

        if (!$this->promotionId) {
            session()->flash('success', 'Promotion created successfully.');
            $this->redirect(route('sales.configuration.promotions.edit', $promotion->id), navigate: true);
        } else {
            session()->flash('success', 'Promotion updated successfully.');
        }
    }

    protected function saveRules(Promotion $promotion): void
    {
        // Get existing rule IDs
        $existingIds = collect($this->rules)->pluck('id')->filter()->toArray();

        // Delete removed rules
        $promotion->rules()->whereNotIn('id', $existingIds)->delete();

        // Update or create rules
        foreach ($this->rules as $ruleData) {
            if ($ruleData['id']) {
                PromotionRule::where('id', $ruleData['id'])->update([
                    'rule_type' => $ruleData['rule_type'],
                    'operator' => $ruleData['operator'],
                    'value' => $ruleData['value'],
                ]);
            } else {
                $promotion->rules()->create([
                    'rule_type' => $ruleData['rule_type'],
                    'operator' => $ruleData['operator'],
                    'value' => $ruleData['value'],
                ]);
            }
        }
    }

    protected function saveReward(Promotion $promotion): void
    {
        $rewardData = [
            'reward_type' => $this->reward_type,
            'product_id' => $this->reward_product_id,
            'buy_quantity' => $this->buy_quantity,
            'get_quantity' => $this->get_quantity,
            'discount_value' => $this->discount_value,
            'max_discount' => $this->max_discount,
            'apply_to' => $this->apply_to,
        ];

        $promotion->rewards()->delete();
        $promotion->rewards()->create($rewardData);
    }

    public function delete(): void
    {
        if ($this->promotionId) {
            Promotion::destroy($this->promotionId);
            session()->flash('success', 'Promotion deleted successfully.');
            $this->redirect(route('sales.configuration.promotions.index'), navigate: true);
        }
    }

    // Simulator Methods
    public function openSimulator(): void
    {
        $this->showSimulator = true;
        $this->simulatorResult = null;
        
        // Add default item if empty
        if (empty($this->simulatorItems)) {
            $this->addSimulatorItem();
        }
    }

    public function closeSimulator(): void
    {
        $this->showSimulator = false;
    }

    public function addSimulatorItem(): void
    {
        $this->simulatorItems[] = [
            'product_id' => null,
            'product_name' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    public function removeSimulatorItem(int $index): void
    {
        unset($this->simulatorItems[$index]);
        $this->simulatorItems = array_values($this->simulatorItems);
    }

    public function updatedSimulatorItems($value, $key): void
    {
        // Auto-fill product details when product is selected
        if (str_contains($key, 'product_id')) {
            $parts = explode('.', $key);
            $index = $parts[0];
            $productId = $value;
            
            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $this->simulatorItems[$index]['product_name'] = $product->name;
                    $this->simulatorItems[$index]['unit_price'] = $product->selling_price ?? 0;
                    if (empty($this->simulatorItems[$index]['quantity'])) {
                        $this->simulatorItems[$index]['quantity'] = 1;
                    }
                }
            }
        }
    }

    public function runSimulation(): void
    {
        if (!$this->promotionId) {
            $this->simulatorResult = [
                'success' => false,
                'message' => 'Please save the promotion first before testing.',
            ];
            return;
        }

        // Validate items
        $validItems = collect($this->simulatorItems)
            ->filter(fn($item) => !empty($item['product_id']) && $item['quantity'] > 0)
            ->values()
            ->toArray();

        if (empty($validItems)) {
            $this->simulatorResult = [
                'success' => false,
                'message' => 'Please add at least one product to test.',
            ];
            return;
        }

        // Build cart items for engine
        $cartItems = collect($validItems)->map(fn($item) => [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'category_id' => Product::find($item['product_id'])?->category_id,
        ])->toArray();

        // Calculate subtotal
        $subtotal = collect($cartItems)->sum(fn($item) => $item['unit_price'] * $item['quantity']);

        // Get the promotion
        $promotion = Promotion::with(['rules', 'rewards'])->find($this->promotionId);

        // Create engine and test
        $engine = new PromotionEngine();
        $engine->forCustomer($this->simulatorCustomerId)
               ->withItems($cartItems);

        if ($this->requires_coupon && $this->code) {
            $engine->withCoupon($this->simulatorCoupon ?: $this->code);
        }

        // Check if applicable
        $isApplicable = $engine->isPromotionApplicable($promotion);

        if (!$isApplicable) {
            $this->simulatorResult = [
                'success' => false,
                'applicable' => false,
                'subtotal' => $subtotal,
                'message' => $this->getNotApplicableReason($promotion, $cartItems, $subtotal),
            ];
            return;
        }

        // Calculate discount
        $discount = $engine->calculateDiscount($promotion);

        $this->simulatorResult = [
            'success' => true,
            'applicable' => true,
            'subtotal' => $subtotal,
            'discount' => $discount['total_discount'],
            'final_total' => $subtotal - $discount['total_discount'],
            'free_items' => $discount['free_items'] ?? [],
            'message' => 'Promotion applies! Customer saves Rp ' . number_format($discount['total_discount'], 0, ',', '.'),
        ];
    }

    protected function getNotApplicableReason(Promotion $promotion, array $items, float $subtotal): string
    {
        if ($promotion->requires_coupon && empty($this->simulatorCoupon)) {
            return 'Coupon code required. Enter the code to test.';
        }

        if ($promotion->requires_coupon && strtoupper($this->simulatorCoupon) !== strtoupper($promotion->code)) {
            return 'Invalid coupon code.';
        }

        if ($promotion->min_order_amount && $subtotal < $promotion->min_order_amount) {
            return 'Minimum order amount not met. Required: Rp ' . number_format($promotion->min_order_amount, 0, ',', '.') . ', Current: Rp ' . number_format($subtotal, 0, ',', '.');
        }

        $totalQty = collect($items)->sum('quantity');
        if ($promotion->min_quantity && $totalQty < $promotion->min_quantity) {
            return 'Minimum quantity not met. Required: ' . $promotion->min_quantity . ', Current: ' . $totalQty;
        }

        if ($promotion->hasReachedLimit()) {
            return 'Promotion has reached its usage limit.';
        }

        if ($this->simulatorCustomerId && $promotion->hasCustomerReachedLimit($this->simulatorCustomerId)) {
            return 'Customer has reached their usage limit for this promotion.';
        }

        // Check rules
        if ($promotion->rules->isNotEmpty()) {
            return 'Cart does not match promotion conditions (product/category/customer rules).';
        }

        return 'Promotion conditions not met.';
    }

    public function clearSimulation(): void
    {
        $this->simulatorItems = [[
            'product_id' => null,
            'product_name' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ]];
        $this->simulatorCustomerId = null;
        $this->simulatorCoupon = '';
        $this->simulatorResult = null;
        $this->addSimulatorItem();
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->productSearch, fn($q) => $q->where('name', 'like', "%{$this->productSearch}%"))
            ->limit(20)
            ->get();

        $categories = Category::query()
            ->when($this->categorySearch, fn($q) => $q->where('name', 'like', "%{$this->categorySearch}%"))
            ->limit(20)
            ->get();

        $customers = Customer::query()
            ->when($this->customerSearch, fn($q) => $q->where('name', 'like', "%{$this->customerSearch}%"))
            ->limit(20)
            ->get();

        return view('livewire.sales.configuration.promotions.form', [
            'activities' => $this->activitiesAndNotes,
            'products' => $products,
            'categories' => $categories,
            'customers' => $customers,
            'promotionTypes' => Promotion::TYPES,
            'ruleTypes' => PromotionRule::RULE_TYPES,
            'operators' => PromotionRule::OPERATORS,
            'rewardTypes' => $this->getFilteredRewardTypes(),
            'applyToOptions' => PromotionReward::APPLY_TO,
            'validationWarnings' => $this->getValidationWarnings(),
            'promotionSummary' => $this->getPromotionSummary(),
        ]);
    }
}
