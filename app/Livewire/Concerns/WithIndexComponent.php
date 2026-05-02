<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;

/**
 * WithIndexComponent Trait
 * 
 * Provides standardized functionality for Livewire index/list components.
 * Combines common patterns: search, filtering, sorting, pagination, bulk actions, and view modes.
 * 
 * Usage:
 * ```php
 * class Index extends Component
 * {
 *     use WithIndexComponent;
 *     
 *     protected function getQuery(): Builder
 *     {
 *         return MyModel::query()->search($this->search);
 *     }
 *     
 *     protected function getModelClass(): string
 *     {
 *         return MyModel::class;
 *     }
 * }
 * ```
 * 
 * @package App\Livewire\Concerns
 */
trait WithIndexComponent
{
    use WithBulkActions, WithManualPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $view = 'list';

    #[Url]
    public string $groupBy = '';

    public bool $showStats = false;

    /**
     * Reset page when search changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    /**
     * Reset page when status filter changes.
     */
    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Reset page when sort changes.
     */
    public function updatedSort(): void
    {
        $this->resetPage();
    }

    /**
     * Reset page when groupBy changes.
     */
    public function updatedGroupBy(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle statistics panel visibility.
     */
    public function toggleStats(): void
    {
        $this->showStats = !$this->showStats;
    }

    /**
     * Set the view mode (list, grid, kanban).
     */
    public function setView(string $view): void
    {
        if (in_array($view, $this->getAllowedViews())) {
            $this->view = $view;
        }
    }

    /**
     * Get allowed view modes.
     * Override in component to customize.
     */
    protected function getAllowedViews(): array
    {
        return ['list', 'grid', 'kanban'];
    }

    /**
     * Clear all filters and reset to defaults.
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy']);
        $this->resetPage();
        $this->clearSelection();
    }

    /**
     * Apply common sorting to a query.
     */
    protected function applySorting($query)
    {
        return match ($this->sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'total_high' => $query->orderByDesc('total'),
            'total_low' => $query->orderBy('total'),
            default => $query->latest(),
        };
    }

    /**
     * Get IDs for bulk selection.
     * Override in component to customize.
     */
    protected function getSelectableIds(): array
    {
        if (method_exists($this, 'getQuery')) {
            return $this->getQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }

        return [];
    }

    /**
     * Get the model class for bulk operations.
     * Override in component to specify the model.
     */
    protected function getBulkModel(): ?string
    {
        if (method_exists($this, 'getModelClass')) {
            return $this->getModelClass();
        }

        return null;
    }

    /**
     * Export selected or all records.
     * Override in component to implement.
     */
    public function exportSelected()
    {
        // Override in component
    }

    /**
     * Get statistics for the stats panel.
     * Override in component to implement.
     */
    protected function getStatistics(): array
    {
        return [];
    }

    /**
     * Number of non-default filters currently applied.
     *
     * Counts the standard $status / $sort / $groupBy filters provided by this
     * trait, then defers to getCustomActiveFilterCount() for any page-specific
     * filters (e.g. "My Quotations", date ranges).
     *
     * Used by <x-ui.searchbox-dropdown> to show a count pill on the chevron
     * and to decide whether to render the "Clear all filters" footer.
     */
    public function getActiveFilterCount(): int
    {
        $count = 0;

        $status = $this->status ?? '';
        if ($status !== '' && $status !== 'all') {
            $count++;
        }

        $sort = $this->sort ?? 'latest';
        if ($sort !== 'latest') {
            $count++;
        }

        $groupBy = $this->groupBy ?? '';
        if ($groupBy !== '') {
            $count++;
        }

        return $count + $this->getCustomActiveFilterCount();
    }

    /**
     * Override to add page-specific filter counts (e.g. "My Quotations"
     * toggle, date-range, custom selects). Returns 0 by default.
     */
    protected function getCustomActiveFilterCount(): int
    {
        return 0;
    }
}
