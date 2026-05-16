<?php

namespace App\Livewire\Settings\Localization;

use App\Livewire\Concerns\WithPermissions;
use App\Models\Settings\CompanyProfile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * This page mirrors a subset of the Company Profile model — the localization
 * tab on Settings → Company is the primary editing surface, but this standalone
 * page stays accessible as a deep-link (kept out of the sidebar). Both pages
 * read and write the same fields on CompanyProfile.
 */
#[Layout('components.layouts.settings')]
#[Title('Localization')]
class Index extends Component
{
    use WithPermissions;

    public string $timezone = 'Asia/Jakarta';

    public string $currency = 'IDR';

    public string $currency_symbol = 'Rp';

    public string $date_format = 'Y-m-d';

    public string $time_format = 'H:i';

    public string $language = 'en';

    public function mount(): void
    {
        $profile = CompanyProfile::getProfile();

        $this->timezone = $profile->timezone ?? 'Asia/Jakarta';
        $this->currency = $profile->currency ?? 'IDR';
        $this->currency_symbol = $profile->currency_symbol ?? 'Rp';
        $this->date_format = $profile->date_format ?? 'Y-m-d';
        $this->time_format = $profile->time_format ?? 'H:i';
        $this->language = $profile->language ?? 'en';
    }

    #[On('saveLocalizationSettings')]
    public function save(): void
    {
        $this->authorizePermission('settings.edit');

        // Always dispatch the completion event so the Alpine spinner resets,
        // even when validate() throws. See Company\Index::save() for the why.
        try {
            $this->validate([
                'timezone' => 'required|timezone',
                'currency' => 'required|string|max:10',
                'currency_symbol' => 'required|string|max:10',
                'date_format' => 'required|string|max:30',
                'time_format' => 'required|string|max:30',
                'language' => 'required|in:en,id',
            ]);

            $profile = CompanyProfile::getProfile();
            $profile->update([
                'timezone' => $this->timezone,
                'currency' => $this->currency,
                'currency_symbol' => $this->currency_symbol,
                'date_format' => $this->date_format,
                'time_format' => $this->time_format,
                'language' => $this->language,
            ]);

            session()->flash('success', 'Localization settings saved.');
        } finally {
            $this->dispatch('localization-settings-saved');
        }
    }

    public function render()
    {
        return view('livewire.settings.localization.index', [
            'timezones' => \DateTimeZone::listIdentifiers(),
            'currencies' => [
                'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
                'EUR' => ['name' => 'Euro', 'symbol' => '€'],
                'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
                'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
                'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
                'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
                'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
                'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
            ],
            'dateFormats' => [
                'Y-m-d' => 'YYYY-MM-DD (2024-12-07)',
                'd/m/Y' => 'DD/MM/YYYY (07/12/2024)',
                'm/d/Y' => 'MM/DD/YYYY (12/07/2024)',
                'd-m-Y' => 'DD-MM-YYYY (07-12-2024)',
                'd M Y' => 'DD Mon YYYY (07 Dec 2024)',
            ],
            'languages' => [
                'en' => 'English',
                'id' => 'Bahasa Indonesia',
            ],
        ]);
    }
}
