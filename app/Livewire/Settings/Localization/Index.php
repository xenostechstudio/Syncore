<?php

namespace App\Livewire\Settings\Localization;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('Localization')]
class Index extends Component
{
    public string $timezone = '';
    public string $currency = 'USD';
    public string $currency_symbol = '$';
    public string $date_format = 'Y-m-d';
    public string $time_format = 'H:i';
    public string $language = 'en';

    public function mount(): void
    {
        $this->timezone = config('app.timezone', 'UTC');
    }

    public function save(): void
    {
        // In a real app, save to database or config
        session()->flash('success', 'Localization settings saved.');
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
