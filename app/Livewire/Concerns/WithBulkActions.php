<?php

namespace App\Livewire\Concerns;

trait WithBulkActions
{
    public array $selected = [];
    public bool $selectAll = false;
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getSelectableIds();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function getSelectedCount(): int
    {
        return count($this->selected);
    }

    public function hasSelection(): bool
    {
        return count($this->selected) > 0;
    }

    /**
     * Override this method in your component to return the IDs that can be selected
     */
    protected function getSelectableIds(): array
    {
        return [];
    }

    /**
     * Open delete confirmation modal with validation
     */
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->deleteValidation = $this->validateBulkDelete();
        $this->showDeleteConfirm = true;
    }

    /**
     * Override this method to provide custom delete validation
     * Returns array with 'canDelete' and 'cannotDelete' items
     */
    protected function validateBulkDelete(): array
    {
        return [
            'canDelete' => collect($this->selected)->map(fn($id) => [
                'id' => $id,
                'name' => "Record #{$id}",
            ])->toArray(),
            'cannotDelete' => [],
            'totalSelected' => count($this->selected),
        ];
    }

    /**
     * Bulk delete selected records
     */
    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = $this->performBulkDelete();
        
        $this->cancelDelete();
        session()->flash('success', "{$count} records deleted successfully.");
    }

    /**
     * Override this method to perform the actual delete
     */
    protected function performBulkDelete(): int
    {
        $model = $this->getBulkModel();
        if (!$model) {
            return 0;
        }

        return $model::whereIn('id', $this->selected)->delete();
    }

    /**
     * Cancel delete and close modal
     */
    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selected)) {
            return;
        }

        $model = $this->getBulkModel();
        if (!$model) {
            return;
        }

        $count = $model::whereIn('id', $this->selected)->update(['status' => $status]);
        
        $this->clearSelection();
        session()->flash('success', "{$count} records updated successfully.");
    }

    /**
     * Override this method to return the model class for bulk operations
     */
    protected function getBulkModel(): ?string
    {
        return null;
    }
}
