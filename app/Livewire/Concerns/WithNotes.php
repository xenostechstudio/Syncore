<?php

namespace App\Livewire\Concerns;

trait WithNotes
{
    public string $noteContent = '';

    public function addNote(): void
    {
        if (empty(trim($this->noteContent))) {
            return;
        }

        $model = $this->getNotableModel();
        
        if (!$model || !method_exists($model, 'addNote')) {
            session()->flash('error', 'Cannot add note to this record.');
            return;
        }

        $model->addNote($this->noteContent, true);
        $this->noteContent = '';
        
        session()->flash('success', 'Note added successfully.');
    }

    /**
     * Override this method in your Livewire component to return the model instance
     */
    protected function getNotableModel()
    {
        // Default implementation - override in component
        return null;
    }
}
