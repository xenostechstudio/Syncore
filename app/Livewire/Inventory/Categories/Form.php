<?php

namespace App\Livewire\Inventory\Categories;

use App\Livewire\Concerns\WithNotes;
use App\Models\Inventory\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Category')]
class Form extends Component
{
    use WithNotes;
    public ?int $categoryId = null;
    public string $name = '';
    public ?string $code = null;
    public ?string $description = null;
    public ?int $parent_id = null;
    public ?string $color = null;
    public bool $is_active = true;
    public int $sort_order = 0;

    protected function getNotableModel()
    {
        return $this->categoryId ? Category::find($this->categoryId) : null;
    }

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
            'code' => 'nullable|string|max:50|unique:product_categories,code,' . $this->categoryId,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
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

    /**
     * Duplicate the category into a new draft. `code` is unique so the
     * copy gets a numeric suffix until a free code is found.
     */
    #[On('duplicateCategory')]
    public function duplicate(): void
    {
        if (! $this->categoryId) {
            return;
        }

        $source = Category::findOrFail($this->categoryId);

        $newCode = $source->code ? $source->code.'-COPY' : null;
        if ($newCode) {
            $suffix = 1;
            while (Category::where('code', $newCode)->exists()) {
                $suffix++;
                $newCode = $source->code.'-COPY-'.$suffix;
            }
        }

        $new = Category::create([
            'name' => $source->name.' (Copy)',
            'code' => $newCode,
            'description' => $source->description,
            'parent_id' => $source->parent_id,
            'color' => $source->color,
            'is_active' => $source->is_active,
            'sort_order' => $source->sort_order,
        ]);

        session()->flash('success', 'Category duplicated successfully.');
        $this->redirect(route('inventory.categories.edit', $new->id), navigate: true);
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
