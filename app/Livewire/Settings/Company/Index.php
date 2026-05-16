<?php

namespace App\Livewire\Settings\Company;

use App\Livewire\Concerns\WithPermissions;
use App\Models\Settings\CompanyProfile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.settings')]
#[Title('Company Profile')]
class Index extends Component
{
    use WithFileUploads, WithPermissions;

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
    public string $currency_symbol = 'Rp';
    public string $timezone = 'Asia/Jakarta';
    public string $language = 'en';
    public string $date_format = 'Y-m-d';
    public string $time_format = 'H:i';
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
        $this->currency_symbol = $profile->currency_symbol ?? 'Rp';
        $this->timezone = $profile->timezone ?? 'Asia/Jakarta';
        $this->language = $profile->language ?? 'en';
        $this->date_format = $profile->date_format ?? 'Y-m-d';
        $this->time_format = $profile->time_format ?? 'H:i';
        $this->logo_path = $profile->logo;
    }

    #[On('saveCompanyProfile')]
    public function save(): void
    {
        $this->authorizePermission('settings.edit');

        // Always dispatch the completion event — even when validate() throws —
        // so the Alpine "Saving…" spinner resets. Without the finally, a
        // ValidationException short-circuits past the success dispatch and the
        // button stays disabled until the user navigates away.
        try {
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
                'currency' => 'required|string|max:10',
                'currency_symbol' => 'required|string|max:10',
                'timezone' => 'required|timezone',
                'language' => 'required|in:en,id',
                'date_format' => 'required|string|max:30',
                'time_format' => 'required|string|max:30',
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
                'currency_symbol' => $this->currency_symbol,
                'timezone' => $this->timezone,
                'language' => $this->language,
                'date_format' => $this->date_format,
                'time_format' => $this->time_format,
            ];

            if ($this->logo) {
                $path = $this->logo->store('company', 'public');
                $data['logo'] = $path;
                $this->logo_path = $path;
                // Clear the temp upload so the head/Brand previews fall through
                // to the persisted Storage::url($logo_path) — the temp file is
                // moved by store() and its temporaryUrl will 404 after this.
                $this->logo = null;
            }

            $profile->update($data);

            session()->flash('success', 'Company profile updated successfully.');
        } finally {
            $this->dispatch('company-profile-saved');
        }
    }

    public function removeLogo(): void
    {
        $this->authorizePermission('settings.edit');

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
