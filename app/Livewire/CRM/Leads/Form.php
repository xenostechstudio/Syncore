<?php

namespace App\Livewire\CRM\Leads;

use App\Livewire\Concerns\WithNotes;
use App\Models\CRM\Lead;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'CRM'])]
#[Title('Lead')]
class Form extends Component
{
    use WithNotes;

    public ?int $leadId = null;
    public ?Lead $lead = null;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $companyName = '';
    public string $jobTitle = '';
    public string $website = '';
    public string $address = '';
    public string $leadSource = '';
    public string $leadStatus = 'new';
    public string $notes = '';
    public ?int $assignedTo = null;

    // Timestamps
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    protected function getNotableModel()
    {
        return $this->leadId ? Lead::find($this->leadId) : null;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'companyName' => 'nullable|string|max:255',
            'jobTitle' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'leadSource' => 'nullable|string|max:50',
            'leadStatus' => 'required|in:new,contacted,qualified,converted,lost',
            'notes' => 'nullable|string',
            'assignedTo' => 'nullable|exists:users,id',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->leadId = $id;

        if ($id) {
            $this->lead = Lead::with(['opportunities.pipeline', 'convertedCustomer', 'assignedTo'])->findOrFail($id);
            $this->name = $this->lead->name;
            $this->email = $this->lead->email ?? '';
            $this->phone = $this->lead->phone ?? '';
            $this->companyName = $this->lead->company_name ?? '';
            $this->jobTitle = $this->lead->job_title ?? '';
            $this->website = $this->lead->website ?? '';
            $this->address = $this->lead->address ?? '';
            $this->leadSource = $this->lead->source ?? '';
            $this->leadStatus = $this->lead->status;
            $this->notes = $this->lead->notes ?? '';
            $this->assignedTo = $this->lead->assigned_to;
            $this->createdAt = $this->lead->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $this->lead->updated_at->format('M d, Y \a\t H:i');
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'company_name' => $this->companyName ?: null,
            'job_title' => $this->jobTitle ?: null,
            'website' => $this->website ?: null,
            'address' => $this->address ?: null,
            'source' => $this->leadSource ?: null,
            'status' => $this->leadStatus,
            'notes' => $this->notes ?: null,
            'assigned_to' => $this->assignedTo,
        ];

        if ($this->leadId) {
            $this->lead->update($data);
            session()->flash('success', 'Lead updated successfully.');
        } else {
            $lead = Lead::create($data);
            session()->flash('success', 'Lead created successfully.');
            $this->redirect(route('crm.leads.edit', $lead->id), navigate: true);
        }
    }

    public function convertToCustomer(): void
    {
        if (!$this->lead) return;

        $customer = $this->lead->convertToCustomer();

        if ($customer) {
            session()->flash('success', "Lead converted to customer: {$customer->name}");
        } else {
            session()->flash('error', 'Failed to convert lead.');
        }
    }

    public function duplicate(): void
    {
        if (!$this->leadId) {
            session()->flash('error', 'Please save the lead first.');
            return;
        }

        $lead = Lead::findOrFail($this->leadId);

        $newLead = Lead::create([
            'name' => $lead->name . ' (Copy)',
            'email' => null, // Don't duplicate email to avoid conflicts
            'phone' => $lead->phone,
            'company_name' => $lead->company_name,
            'job_title' => $lead->job_title,
            'website' => $lead->website,
            'address' => $lead->address,
            'source' => $lead->source,
            'status' => 'new',
            'notes' => $lead->notes,
            'assigned_to' => $lead->assigned_to,
        ]);

        session()->flash('success', 'Lead duplicated successfully.');
        $this->redirect(route('crm.leads.edit', $newLead->id), navigate: true);
    }

    public function delete(): void
    {
        if (!$this->lead) return;

        $this->lead->delete();
        session()->flash('success', 'Lead deleted successfully.');
        $this->redirect(route('crm.leads.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.crm.leads.form', [
            'users' => User::orderBy('name')->get(),
            'sources' => Lead::getSources(),
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
