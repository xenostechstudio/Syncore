<?php

namespace App\Livewire\Settings\Company;

use App\Models\Settings\CompanyProfile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('Company Profile')]
class Index extends Component
{
    use WithFileUploads;

    public string $company_name = '';
    public string $company_email = '';
    public string $company_phone = '';
    public string $company_address = '';
    public string $company_city = '';
    public string $company_country = '';
    public string $company_website = '';
    public string $tax_id = '';
    public $logo;
    public ?string $logo_path = null;

    public function mount(): void
    {
        $profile = CompanyProfile::getProfile();
        
        $this->company_name = $profile->company_name ?? 'Syncore';
        $this->company_email = $profile->company_email ?? '';
        $this->company_phone = $profile->company_phone ?? '';
        $this->company_address = $profile->company_address ?? '';
        $this->company_city = $profile->company_city ?? '';
        $this->company_country = $profile->company_country ?? '';
        $this->company_website = $profile->company_website ?? '';
        $this->tax_id = $profile->tax_id ?? '';
        $this->logo_path = $profile->logo_path;
    }

    public function save(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
            'company_city' => 'nullable|string|max:100',
            'company_country' => 'nullable|string|max:100',
            'company_website' => 'nullable|url|max:255',
            'tax_id' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:2048',
        ]);

        $profile = CompanyProfile::getProfile();

        $data = [
            'company_name' => $this->company_name,
            'company_email' => $this->company_email,
            'company_phone' => $this->company_phone,
            'company_address' => $this->company_address,
            'company_city' => $this->company_city,
            'company_country' => $this->company_country,
            'company_website' => $this->company_website,
            'tax_id' => $this->tax_id,
        ];

        if ($this->logo) {
            $path = $this->logo->store('company', 'public');
            $data['logo_path'] = $path;
            $this->logo_path = $path;
        }

        $profile->update($data);

        session()->flash('success', 'Company profile updated successfully.');
    }

    public function removeLogo(): void
    {
        $profile = CompanyProfile::getProfile();
        
        if ($profile->logo_path) {
            Storage::disk('public')->delete($profile->logo_path);
            $profile->update(['logo_path' => null]);
            $this->logo_path = null;
        }
    }

    public function render()
    {
        return view('livewire.settings.company.index');
    }
}
