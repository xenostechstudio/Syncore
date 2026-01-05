<?php

namespace App\Livewire\Inventory\Categories;

use App\Livewire\Concerns\WithNotes;
use App\Models\Inventory\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

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

    public function getActivitiesAndNotesProperty(): \Illuminate\Support\Collection
    {
        if (!$this->categoryId) {
            return collect();
        }

        $category = Category::find($this->categoryId);
        
        // Get activity logs
        $activities = Activity::where('subject_type', Category::class)
            ->where('subject_id', $this->categoryId)
            ->with('causer')
            ->get()
            ->map(function ($activity) {
                return [
                    'type' => 'activity',
                    'data' => $activity,
                    'created_at' => $activity->created_at,
                ];
            });

        // Get notes
        $notes = $category->notes()->with('user')->get()->map(function ($note) {
            return [
                'type' => 'note',
                'data' => $note,
                'created_at' => $note->created_at,
            ];
        });

        // Merge and sort by created_at descending
        return $activities->concat($notes)
            ->sortByDesc('created_at')
            ->take(30)
            ->values();
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
