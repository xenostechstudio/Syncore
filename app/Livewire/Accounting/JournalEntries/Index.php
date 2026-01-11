<?php

namespace App\Livewire\Accounting\JournalEntries;

use App\Exports\JournalEntriesExport;
use App\Models\Accounting\JournalEntry;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Accounting'])]
#[Title('Journal Entries')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getEntriesQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function goToPreviousPage(): void
    {
        $this->previousPage();
    }

    public function goToNextPage(): void
    {
        $this->nextPage();
    }

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
        if (empty($this->selected)) {
            return Excel::download(new JournalEntriesExport(), 'journal-entries-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new JournalEntriesExport($this->selected), 'journal-entries-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getEntriesQuery()
    {
        return JournalEntry::query()
            ->with(['createdBy', 'lines.account'])
            ->when($this->search, fn ($q) => $q->where('entry_number', 'ilike', "%{$this->search}%")
                ->orWhere('reference', 'ilike', "%{$this->search}%")
                ->orWhere('description', 'ilike', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('entry_date')
            ->orderByDesc('id');
    }

    public function render()
    {
        $entries = $this->getEntriesQuery()->paginate(20);

        return view('livewire.accounting.journal-entries.index', [
            'entries' => $entries,
        ]);
    }
}
