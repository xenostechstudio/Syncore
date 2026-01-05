<?php

namespace App\Livewire\Settings\Company;

use App\Models\Settings\CompanyProfile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.settings')]
#[Title('Company Profile')]
class Index extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $legal_name = '';
    public string $email = '';
    public string $phone = '';
    public string $website = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $postal_code = '';
    public string $country = '';
    public string $tax_id = '';
    public string $currency = 'IDR';
    public string $timezone = 'Asia/Jakarta';
    public $logo;
    public ?string $logo_path = null;

    public function mount(): void
    {
        $profile = CompanyProfile::getProfile();
        
        $this->name = $profile->name ?? 'Syncore';
        $this->legal_name = $profile->legal_name ?? '';
        $this->email = $profile->email ?? '';
        $this->phone = $profile->phone ?? '';
        $this->website = $profile->website ?? '';
        $this->address = $profile->address ?? '';
        $this->city = $profile->city ?? '';
        $this->state = $profile->state ?? '';
        $this->postal_code = $profile->postal_code ?? '';
        $this->country = $profile->country ?? '';
        $this->tax_id = $profile->tax_id ?? '';
        $this->currency = $profile->currency ?? 'IDR';
        $this->timezone = $profile->timezone ?? 'Asia/Jakarta';
        $this->logo_path = $profile->logo;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:2048',
        ]);

        $profile = CompanyProfile::getProfile();

        $data = [
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'tax_id' => $this->tax_id,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
        ];

        if ($this->logo) {
            $path = $this->logo->store('company', 'public');
            $data['logo'] = $path;
            $this->logo_path = $path;
        }

        $profile->update($data);

        session()->flash('success', 'Company profile updated successfully.');
    }

    public function removeLogo(): void
    {
        $profile = CompanyProfile::getProfile();
        
        if ($profile->logo) {
            Storage::disk('public')->delete($profile->logo);
            $profile->update(['logo' => null]);
            $this->logo_path = null;
        }
    }

    public function render()
    {
        return view('livewire.settings.company.index');
    }
}
