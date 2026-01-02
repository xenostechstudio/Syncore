<?php

namespace App\Livewire\CRM\Leads;

use App\Models\CRM\Lead;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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

    public function setView(string $view): void
    {
        $this->view = $view;
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

    public function goToPreviousPage(): void
    {
        $this->previousPage();
    }

    public function goToNextPage(): void
    {
        $this->nextPage();
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
