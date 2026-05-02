<?php

namespace App\Livewire\CRM\Leads;

use App\Exports\LeadsExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\CRM\Lead;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'CRM'])]
#[Title('Leads')]
class Index extends Component
{
    use WithIndexComponent;

    #[Url]
    public string $source = '';

    public function updatedSource(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy', 'source']);
        $this->resetPage();
        $this->clearSelection();
    }

    protected function getCustomActiveFilterCount(): int
    {
        return $this->source !== '' ? 1 : 0;
    }

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
                    'reason' => 'Lead has been converted to customer',
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
        $filename = empty($this->selected)
            ? 'leads-' . now()->format('Y-m-d') . '.xlsx'
            : 'leads-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new LeadsExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return Lead::query()
            ->with('assignedTo')
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('company_name', 'like', "%{$this->search}%")))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->source, fn ($q) => $q->where('source', $this->source));
    }

    protected function getModelClass(): string
    {
        return Lead::class;
    }

    public function render()
    {
        $leads = $this->getQuery()
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'page', $this->page);

        return view('livewire.crm.leads.index', [
            'leads' => $leads,
            'sources' => Lead::getSources(),
        ]);
    }
}
