<?php

namespace App\Livewire\Inventory\Categories;

use App\Models\Inventory\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Category')]
class Form extends Component
{
    public ?int $categoryId = null;
    public string $name = '';
    public ?string $code = null;
    public ?string $description = null;
    public ?int $parent_id = null;
    public ?string $color = null;
    public bool $is_active = true;
    public int $sort_order = 0;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $category = Category::findOrFail($id);
            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->code = $category->code;
            $this->description = $category->description;
            $this->parent_id = $category->parent_id;
            $this->color = $category->color;
            $this->is_active = $category->is_active;
            $this->sort_order = $category->sort_order;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:inventory_categories,code,' . $this->categoryId,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:inventory_categories,id',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->update($validated);
            session()->flash('success', 'Category updated successfully.');
        } else {
            Category::create($validated);
            session()->flash('success', 'Category created successfully.');
        }

        $this->redirect(route('inventory.categories.index'), navigate: true);
    }

    public function render()
    {
        $parentCategories = Category::query()
            ->when($this->categoryId, fn($q) => $q->where('id', '!=', $this->categoryId))
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('livewire.inventory.categories.form', [
            'parentCategories' => $parentCategories,
        ]);
    }
}
