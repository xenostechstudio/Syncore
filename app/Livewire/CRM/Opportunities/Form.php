<?php

namespace App\Livewire\CRM\Opportunities;

use App\Models\CRM\Lead;
use App\Models\CRM\Opportunity;
use App\Models\CRM\Pipeline;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'CRM'])]
#[Title('Opportunity')]
class Form extends Component
{
    public ?int $opportunityId = null;
    public ?Opportunity $opportunity = null;

    public string $name = '';
    public ?int $leadId = null;
    public ?int $pipelineId = null;
    public float $expectedRevenue = 0;
    public float $probability = 10;
    public ?string $expectedCloseDate = null;
    public string $description = '';
    public ?int $assignedTo = null;

    #[Computed]
    public function selectedLead(): ?Lead
    {
        return $this->leadId ? Lead::find($this->leadId) : null;
    }

    #[Computed]
    public function selectedUser(): ?User
    {
        return $this->assignedTo ? User::find($this->assignedTo) : null;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'leadId' => 'nullable|exists:leads,id',
            'pipelineId' => 'required|exists:pipelines,id',
            'expectedRevenue' => 'required|numeric|min:0',
            'probability' => 'required|numeric|min:0|max:100',
            'expectedCloseDate' => 'nullable|date',
            'description' => 'nullable|string',
            'assignedTo' => 'nullable|exists:users,id',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->opportunityId = $id;

        if ($id) {
            $this->opportunity = Opportunity::with(['lead', 'pipeline', 'assignedTo'])->findOrFail($id);
            $this->name = $this->opportunity->name;
            $this->leadId = $this->opportunity->lead_id;
            $this->pipelineId = $this->opportunity->pipeline_id;
            $this->expectedRevenue = $this->opportunity->expected_revenue;
            $this->probability = $this->opportunity->probability;
            $this->expectedCloseDate = $this->opportunity->expected_close_date?->format('Y-m-d');
            $this->description = $this->opportunity->description ?? '';
            $this->assignedTo = $this->opportunity->assigned_to;
        } else {
            $this->pipelineId = Pipeline::orderBy('sequence')->first()?->id;
            $this->probability = 10;
            
            // Pre-fill lead from URL parameter
            if (request()->has('lead_id')) {
                $lead = Lead::find(request()->get('lead_id'));
                if ($lead) {
                    $this->leadId = $lead->id;
                    $this->name = "Opportunity - {$lead->name}";
                    $this->assignedTo = $lead->assigned_to;
                }
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'lead_id' => $this->leadId,
            'pipeline_id' => $this->pipelineId,
            'expected_revenue' => $this->expectedRevenue,
            'probability' => $this->probability,
            'expected_close_date' => $this->expectedCloseDate ?: null,
            'description' => $this->description ?: null,
            'assigned_to' => $this->assignedTo,
        ];

        if ($this->opportunityId) {
            $this->opportunity->update($data);
            session()->flash('success', 'Opportunity updated successfully.');
        } else {
            Opportunity::create($data);
            session()->flash('success', 'Opportunity created successfully.');
        }

        $this->redirect(route('crm.opportunities.index'), navigate: true);
    }

    public function markAsWon(): void
    {
        if (!$this->opportunity) return;

        $this->opportunity->markAsWon();
        
        session()->flash('success', "'{$this->opportunity->name}' marked as Won!");
        
        $this->redirect(route('crm.opportunities.edit', $this->opportunityId), navigate: true);
    }

    public function convertLeadToCustomer(): void
    {
        if (!$this->opportunity || !$this->opportunity->lead) return;

        $lead = $this->opportunity->lead;
        
        if ($lead->status === 'converted') {
            session()->flash('error', 'Lead is already converted to customer.');
            return;
        }

        $customer = $lead->convertToCustomer();
        
        if ($customer) {
            session()->flash('success', "Lead '{$lead->name}' converted to Customer '{$customer->name}'.");
        } else {
            session()->flash('error', 'Failed to convert lead to customer.');
        }
        
        $this->redirect(route('crm.opportunities.edit', $this->opportunityId), navigate: true);
    }

    public function markAsLost(): void
    {
        if (!$this->opportunity) return;

        $this->opportunity->markAsLost();
        session()->flash('success', "'{$this->opportunity->name}' marked as Lost.");
        $this->redirect(route('crm.opportunities.index'), navigate: true);
    }

    public function delete(): void
    {
        if (!$this->opportunity) return;

        $this->opportunity->delete();
        session()->flash('success', 'Opportunity deleted successfully.');
        $this->redirect(route('crm.opportunities.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.crm.opportunities.form', [
            'pipelines' => Pipeline::where('is_active', true)->orderBy('sequence')->get(),
            'leads' => Lead::whereNotIn('status', ['converted', 'lost'])->orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
