<?php

namespace App\Livewire\Concerns;

trait WithDuplicate
{
    public function duplicate(): void
    {
        if (!$this->canDuplicate()) {
            session()->flash('error', 'Cannot duplicate this record.');
            return;
        }

        $newRecord = $this->performDuplicate();

        if ($newRecord) {
            session()->flash('success', 'Record duplicated successfully.');
            $this->redirect($this->getDuplicateRedirectUrl($newRecord), navigate: true);
        } else {
            session()->flash('error', 'Failed to duplicate record.');
        }
    }

    protected function canDuplicate(): bool
    {
        return true;
    }

    protected function performDuplicate(): mixed
    {
        return null;
    }

    protected function getDuplicateRedirectUrl(mixed $newRecord): string
    {
        return request()->url();
    }
}
