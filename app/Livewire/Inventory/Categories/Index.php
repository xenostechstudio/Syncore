<?php

namespace App\Livewire\Inventory\Categories;

use App\Exports\CategoriesExport;
use App\Models\Inventory\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Categories')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public array $visibleColumns = [
        'name' => true,
        'code' => true,
        'parent' => true,
        'items_count' => true,
        'status' => true,
    ];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = Category::query()
                ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Category::findOrFail($id)->delete();
        $this->selected = array_filter($this->selected, fn($s) => $s != $id);
    }

    public function deleteSelected(): void
    {
        $this->confirmBulkDelete();
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Validate which categories can be deleted (no products)
        $categories = Category::whereIn('id', $this->selected)
            ->withCount('items')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($categories as $category) {
            if ($category->items_count === 0) {
                $canDelete[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
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

        // Only delete categories without products
        $categoriesWithProducts = Category::whereIn('id', $this->selected)
            ->whereHas('items')
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

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkActivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Category::whereIn('id', $this->selected)
            ->update(['is_active' => true]);

        $this->clearSelection();
        session()->flash('success', "{$count} categories activated.");
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Category::whereIn('id', $this->selected)
            ->update(['is_active' => false]);

        $this->clearSelection();
        session()->flash('success', "{$count} categories deactivated.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new CategoriesExport(), 'categories-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new CategoriesExport($this->selected), 'categories-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('items')
            ->with('parent')
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('code', 'ilike', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.inventory.categories.index', [
            'categories' => $categories,
        ]);
    }
}
