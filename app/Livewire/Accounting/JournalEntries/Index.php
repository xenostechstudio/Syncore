<?php

namespace App\Livewire\Accounting\JournalEntries;

use App\Exports\JournalEntriesExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Accounting\JournalEntry;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Accounting'])]
#[Title('Journal Entries')]
class Index extends Component
{
    use WithIndexComponent;

    public function post(int $id): void
    {
        $entry = JournalEntry::findOrFail($id);

        if ($entry->post()) {
            session()->flash('success', 'Journal entry posted successfully.');
        } else {
            session()->flash('error', 'Failed to post journal entry. Ensure it is balanced.');
        }
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'journal-entries-' . now()->format('Y-m-d') . '.xlsx'
            : 'journal-entries-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new JournalEntriesExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return JournalEntry::query()
            ->with(['createdBy', 'lines.account'])
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('entry_number', 'like', "%{$this->search}%")
                ->orWhere('reference', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return JournalEntry::class;
    }

    public function render()
    {
        $entries = $this->getQuery()
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'page', $this->page);

        return view('livewire.accounting.journal-entries.index', [
            'entries' => $entries,
        ]);
    }
}
