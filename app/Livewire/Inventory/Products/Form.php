<?php

namespace App\Livewire\Inventory\Products;

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductPricelistRule;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Pricelist;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Product')]
class Form extends Component
{
    public ?int $productId = null;
    public ?Product $product = null;
    public bool $editing = false;

    // General Info
    public string $name = '';
    public string $sku = '';
    public ?string $barcode = null;
    public string $product_type = 'goods';
    public ?string $internal_reference = null;
    public ?string $description = null;
    public ?int $category_id = null;
    public bool $is_favorite = false;

    // Pricing
    public ?float $cost_price = null;
    public ?float $selling_price = null;

    // Inventory/Logistics
    public int $quantity = 0;
    public ?int $warehouse_id = null;
    public ?int $responsible_id = null;
    public ?float $weight = null;
    public ?float $volume = null;
    public int $customer_lead_time = 0;

    // Notes
    public ?string $receipt_note = null;
    public ?string $delivery_note = null;
    public ?string $internal_notes = null;

    // Pricelist Rules Modal
    public bool $showPriceModal = false;
    public ?int $editingRuleId = null;
    public string $rule_price_type = 'fixed';
    public ?float $rule_fixed_price = null;
    public ?float $rule_discount_percentage = null;
    public int $rule_min_quantity = 1;
    public ?string $rule_date_start = null;
    public ?string $rule_date_end = null;
    public ?int $rule_pricelist_id = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->product = Product::with(['pricelistRules.pricelist'])->findOrFail($id);
            $this->productId = $this->product->id;
            $this->editing = true;

            $this->name = $this->product->name;
            $this->sku = $this->product->sku ?? '';
            $this->barcode = $this->product->barcode;
            $this->product_type = $this->product->product_type ?? 'goods';
            $this->internal_reference = $this->product->internal_reference;
            $this->description = $this->product->description;
            $this->category_id = $this->product->category_id;
            $this->is_favorite = $this->product->is_favorite ?? false;
            $this->cost_price = $this->product->cost_price;
            $this->selling_price = $this->product->selling_price;
            $this->quantity = $this->product->quantity ?? 0;
            $this->warehouse_id = $this->product->warehouse_id;
            $this->responsible_id = $this->product->responsible_id;
            $this->weight = $this->product->weight;
            $this->volume = $this->product->volume;
            $this->customer_lead_time = $this->product->customer_lead_time ?? 0;
            $this->receipt_note = $this->product->receipt_note;
            $this->delivery_note = $this->product->delivery_note;
            $this->internal_notes = $this->product->internal_notes;
        }
    }

    public function generateSku(): void
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $this->name), 0, 3));
        $random = strtoupper(substr(uniqid(), -4));
        $this->sku = $prefix . '-' . $random;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $this->productId,
            'barcode' => 'nullable|string|max:100',
            'product_type' => 'required|in:goods,service',
            'internal_reference' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:product_categories,id',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'responsible_id' => 'nullable|exists:users,id',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'customer_lead_time' => 'integer|min:0',
            'receipt_note' => 'nullable|string',
            'delivery_note' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'is_favorite' => 'boolean',
        ]);

        // Auto-set status based on quantity
        if ($validated['quantity'] === 0) {
            $validated['status'] = 'out_of_stock';
        } elseif ($validated['quantity'] < 10) {
            $validated['status'] = 'low_stock';
        } else {
            $validated['status'] = 'in_stock';
        }

        // Generate SKU if empty
        if (empty($validated['sku'])) {
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $validated['name']), 0, 3));
            $random = strtoupper(substr(uniqid(), -4));
            $validated['sku'] = $prefix . '-' . $random;
        }

        if ($this->editing && $this->product) {
            $this->product->update($validated);
            $product = $this->product->refresh();
            session()->flash('success', 'Product updated successfully.');
        } else {
            $product = Product::create($validated);
            session()->flash('success', 'Product created successfully.');
        }

        if (! empty($validated['warehouse_id'])) {
            InventoryStock::query()->updateOrCreate(
                [
                    'warehouse_id' => $validated['warehouse_id'],
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => (int) ($validated['quantity'] ?? 0),
                ]
            );

            $totalQty = (int) InventoryStock::query()
                ->where('product_id', $product->id)
                ->sum('quantity');

            $status = $totalQty === 0
                ? 'out_of_stock'
                : ($totalQty < 10 ? 'low_stock' : 'in_stock');

            $product->update([
                'quantity' => $totalQty,
                'status' => $status,
            ]);
        }

        $this->redirect(route('inventory.products.index'), navigate: true);
    }

    // Pricelist Rules Methods
    public function openPriceModal(?int $ruleId = null): void
    {
        if (! $this->productId) {
            session()->flash('error', 'Please save the product first before setting pricelist prices.');
            return;
        }

        $this->resetPriceModal();
        
        if ($ruleId) {
            $rule = ProductPricelistRule::query()
                ->where('product_id', $this->productId)
                ->find($ruleId);
            if ($rule) {
                $this->editingRuleId = $rule->id;
                $this->rule_price_type = $rule->price_type;
                $this->rule_fixed_price = $rule->fixed_price;
                $this->rule_discount_percentage = $rule->discount_percentage;
                $this->rule_min_quantity = $rule->min_quantity;
                $this->rule_date_start = $rule->date_start?->format('Y-m-d');
                $this->rule_date_end = $rule->date_end?->format('Y-m-d');
                $this->rule_pricelist_id = $rule->pricelist_id;
            }
        }
        
        $this->showPriceModal = true;
    }

    public function resetPriceModal(): void
    {
        $this->editingRuleId = null;
        $this->rule_price_type = 'fixed';
        $this->rule_fixed_price = null;
        $this->rule_discount_percentage = null;
        $this->rule_min_quantity = 1;
        $this->rule_date_start = null;
        $this->rule_date_end = null;
        $this->rule_pricelist_id = null;
    }

    public function savePriceRule(): void
    {
        if (! $this->productId || ! $this->product) {
            session()->flash('error', 'Please save the product first before setting pricelist prices.');
            return;
        }

        $rules = [
            'rule_price_type' => 'required|in:fixed,discount',
            'rule_min_quantity' => 'required|integer|min:1',
            'rule_date_start' => 'nullable|date',
            'rule_date_end' => 'nullable|date|after_or_equal:rule_date_start',
            'rule_pricelist_id' => 'required|exists:pricelists,id',
        ];

        if ($this->rule_price_type === 'fixed') {
            $rules['rule_fixed_price'] = 'required|numeric|min:0';
            $rules['rule_discount_percentage'] = 'nullable|numeric|min:0|max:100';
        } else {
            $rules['rule_fixed_price'] = 'nullable|numeric|min:0';
            $rules['rule_discount_percentage'] = 'required|numeric|min:0|max:100';
        }

        $validated = $this->validate($rules);

        $data = [
            'product_id' => $this->productId,
            'pricelist_id' => $this->rule_pricelist_id,
            'price_type' => $this->rule_price_type,
            'fixed_price' => $this->rule_price_type === 'fixed' ? $this->rule_fixed_price : null,
            'discount_percentage' => $this->rule_price_type === 'discount' ? $this->rule_discount_percentage : null,
            'min_quantity' => $this->rule_min_quantity,
            'date_start' => $this->rule_date_start,
            'date_end' => $this->rule_date_end,
        ];

        if ($this->editingRuleId) {
            ProductPricelistRule::query()
                ->where('product_id', $this->productId)
                ->find($this->editingRuleId)
                ?->update($data);
        } else {
            ProductPricelistRule::create($data);
        }

        $this->product->refresh();
        $this->showPriceModal = false;
        $this->resetPriceModal();
    }

    public function deletePriceRule(int $ruleId): void
    {
        if (! $this->productId || ! $this->product) {
            session()->flash('error', 'Please save the product first before modifying pricelist prices.');
            return;
        }

        ProductPricelistRule::query()
            ->where('product_id', $this->productId)
            ->find($ruleId)
            ?->delete();

        $this->product->refresh();
    }

    public function render()
    {
        return view('livewire.inventory.products.form', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'pricelists' => Pricelist::where('is_active', true)->orderBy('name')->get(),
            'pricelistRules' => $this->product?->pricelistRules()->with('pricelist')->get() ?? collect(),
        ]);
    }
}
