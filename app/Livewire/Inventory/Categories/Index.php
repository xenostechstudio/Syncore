<?php

namespace App\Livewire\Inventory\Categories;

use App\Exports\CategoriesExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Inventory\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Categories')]
class Index extends Component
{
    use WithIndexComponent;

    public array $visibleColumns = [
        'name' => true,
        'code' => true,
        'parent' => true,
        'items_count' => true,
        'status' => true,
    ];

    public function delete(int $id): void
    {
        Category::findOrFail($id)->delete();
        $this->selected = array_filter($this->selected, fn ($s) => $s != $id);
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $categories = Category::whereIn('id', $this->selected)
            ->withCount('products as items_count')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($categories as $category) {
            if ($category->items_count === 0) {
                $canDelete[] = ['id' => $category->id, 'name' => $category->name];
            } else {
                $cannotDelete[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'reason' => "Has {$category->items_count} products",
                ];
            }
        }

        $this->deleteValidation = [
            'canDelete' => $canDelete,
            'cannotDelete' => $cannotDelete,
            'totalSelected' => count($this->selected),
        ];

        $this->showDeleteConfirm = true;
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $categoriesWithProducts = Category::whereIn('id', $this->selected)
            ->whereHas('products')
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $categoriesWithProducts));

        if (empty($deletableIds)) {
            session()->flash('error', 'No categories can be deleted. All selected categories have products.');
            $this->cancelDelete();
            return;
        }

        $count = Category::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} categories deleted successfully.");
    }

    public function bulkActivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Category::whereIn('id', $this->selected)->update(['is_active' => true]);

        $this->clearSelection();
        session()->flash('success', "{$count} categories activated.");
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Category::whereIn('id', $this->selected)->update(['is_active' => false]);

        $this->clearSelection();
        session()->flash('success', "{$count} categories deactivated.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'categories-' . now()->format('Y-m-d') . '.xlsx'
            : 'categories-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new CategoriesExport($this->selected ?: null), $filename);
    }

    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);
    }

    protected function getQuery()
    {
        return Category::query()
            ->withCount('products as items_count')
            ->with('parent')
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")));
    }

    protected function getModelClass(): string
    {
        return Category::class;
    }

    public function render()
    {
        $categories = $this->getQuery()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.inventory.categories.index', [
            'categories' => $categories,
        ]);
    }
}
