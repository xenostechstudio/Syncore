<?php

namespace App\Livewire\CRM\Leads;

use App\Exports\LeadsExport;
use App\Models\CRM\Lead;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'CRM'])]
#[Title('Leads')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $source = '';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getLeadsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'source']);
        $this->resetPage();
        $this->clearSelection();
    }

    public function goToPreviousPage(): void
    {
        $this->previousPage();
    }

    public function goToNextPage(): void
    {
        $this->nextPage();
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $leads = Lead::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($leads as $lead) {
            if ($lead->status !== 'converted') {
                $canDelete[] = [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'status' => $lead->status,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'reason' => "Lead has been converted to customer",
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

        $count = Lead::whereIn('id', $this->selected)
            ->where('status', '!=', 'converted')
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} leads deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Lead::whereIn('id', $this->selected)
            ->where('status', '!=', 'converted')
            ->update(['status' => $status]);

        $this->clearSelection();
        session()->flash('success', "{$count} leads updated to {$status}.");
    }

    public function convertToCustomer(int $id): void
    {
        $lead = Lead::findOrFail($id);
        $customer = $lead->convertToCustomer();

        if ($customer) {
            session()->flash('success', "Lead converted to customer: {$customer->name}");
        } else {
            session()->flash('error', 'Failed to convert lead.');
        }
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new LeadsExport(), 'leads-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new LeadsExport($this->selected), 'leads-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getLeadsQuery()
    {
        return Lead::query()
            ->with('assignedTo')
            ->when($this->search, fn ($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('email', 'ilike', "%{$this->search}%")
                ->orWhere('company_name', 'ilike', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->source, fn ($q) => $q->where('source', $this->source))
            ->orderByDesc('created_at');
    }

    public function render()
    {
        $leads = $this->getLeadsQuery()->paginate(20);

        return view('livewire.crm.leads.index', [
            'leads' => $leads,
            'sources' => Lead::getSources(),
        ]);
    }
}
