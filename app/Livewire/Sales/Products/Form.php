<?php

namespace App\Livewire\Sales\Products;

use App\Livewire\Concerns\WithNotes;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Tax;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Product')]
class Form extends Component
{
    use WithNotes;

    public ?Product $item = null;
    public bool $editing = false;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:50|unique:products,sku')]
    public ?string $sku = null;

    #[Validate('nullable|string|max:1000')]
    public ?string $description = null;

    #[Validate('required|integer|min:0')]
    public int $quantity = 0;

    #[Validate('nullable|numeric|min:0')]
    public ?float $cost_price = null;

    #[Validate('nullable|numeric|min:0')]
    public ?float $selling_price = null;

    #[Validate('required|in:in_stock,low_stock,out_of_stock')]
    public string $status = 'in_stock';

    public ?int $warehouse_id = null;

    #[Validate('boolean')]
    public bool $is_favorite = false;

    #[Validate('required|in:goods,service')]
    public string $product_type = 'goods';

    #[Validate('required|in:ordered,delivered')]
    public string $invoicing_policy = 'ordered';

    #[Validate('nullable|integer|exists:taxes,id')]
    public ?int $sales_tax_id = null;

    #[Validate('nullable|string|max:255')]
    public ?string $category = null;

    #[Validate('nullable|string|max:255')]
    public ?string $reference = null;

    #[Validate('nullable|string|max:255')]
    public ?string $barcode = null;

    #[Validate('nullable|string')]
    public ?string $internal_notes = null;

    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    protected function getNotableModel()
    {
        return $this->item;
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->item = Product::findOrFail($id);
            $this->editing = true;

            $this->name = $this->item->name;
            $this->sku = $this->item->sku;
            $this->description = $this->item->description;
            $this->quantity = $this->item->quantity;
            $this->cost_price = $this->item->cost_price;
            $this->selling_price = $this->item->selling_price;
            $this->status = $this->item->status;
            $this->warehouse_id = $this->item->warehouse_id;
            $this->is_favorite = (bool) ($this->item->is_favorite ?? false);
            $this->sales_tax_id = $this->item->sales_tax_id;
            $this->createdAt = $this->item->created_at?->format('M d, Y \a\t H:i');
            $this->updatedAt = $this->item->updated_at?->format('M d, Y \a\t H:i');
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
        $rules = $this->getRules();

        if ($this->editing && $this->item) {
            $rules['sku'] = 'nullable|string|max:50|unique:products,sku,' . $this->item->id;
        }

        $validated = $this->validate($rules);

        if ($validated['quantity'] === 0) {
            $validated['status'] = 'out_of_stock';
        } elseif ($validated['quantity'] < 10) {
            $validated['status'] = 'low_stock';
        } else {
            $validated['status'] = 'in_stock';
        }

        $itemData = [
            'name' => $validated['name'],
            'sku' => (isset($validated['sku']) && $validated['sku'] !== '') ? $validated['sku'] : null,
            'description' => $validated['description'] ?? null,
            'quantity' => $validated['quantity'],
            'cost_price' => $validated['cost_price'] ?? null,
            'selling_price' => $validated['selling_price'] ?? null,
            'sales_tax_id' => $validated['sales_tax_id'] ?? null,
            'status' => $validated['status'],
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'is_favorite' => $validated['is_favorite'] ?? false,
        ];

        if ($this->editing && $this->item) {
            $this->item->update($itemData);
            $this->updatedAt = $this->item->updated_at->format('M d, Y \a\t H:i');
            session()->flash('success', 'Product updated successfully.');
        } else {
            $this->item = Product::create($itemData);
            $this->editing = true;
            $this->createdAt = $this->item->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $this->item->updated_at->format('M d, Y \a\t H:i');
            session()->flash('success', 'Product created successfully.');
            $this->redirect(route('sales.products.edit', $this->item->id), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.sales.products.form', [
            'warehouses' => Warehouse::all(),
            'taxes' => Tax::where('is_active', true)->orderBy('name')->get(),
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
