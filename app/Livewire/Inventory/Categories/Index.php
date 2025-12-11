<?php

namespace App\Livewire\Inventory\Categories;

use App\Models\Inventory\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
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
        Category::whereIn('id', $this->selected)->delete();
        $this->clearSelection();
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
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.inventory.categories.index', [
            'categories' => $categories,
        ]);
    }
}
