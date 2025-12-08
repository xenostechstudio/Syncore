<?php

namespace App\Livewire\Settings\Company;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('Company Profile')]
class Index extends Component
{
    public string $company_name = '';
    public string $company_email = '';
    public string $company_phone = '';
    public string $company_address = '';
    public string $company_city = '';
    public string $company_country = '';
    public string $company_website = '';
    public string $tax_id = '';

    public function mount(): void
    {
        // Load from config or database
        $this->company_name = config('app.name', 'Syncore');
    }

    public function save(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
        ]);

        // Save to database or config
        session()->flash('success', 'Company profile updated.');
    }

    public function render()
    {
        return view('livewire.settings.company.index');
    }
}
