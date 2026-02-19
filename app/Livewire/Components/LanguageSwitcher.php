<?php

namespace App\Livewire\Components;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public string $currentLocale;

    public function mount(): void
    {
        $this->currentLocale = app()->getLocale();
    }

    public function switchLocale(string $locale): void
    {
        if (array_key_exists($locale, config('app.available_locales', []))) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
            
            // Update user preference if logged in
            if (auth()->check()) {
                auth()->user()->update(['language' => $locale]);
            }
            
            $this->currentLocale = $locale;
            
            // Force full page reload to apply translations everywhere
            $this->js('window.location.reload()');
        }
    }

    public function render()
    {
        return view('livewire.components.language-switcher', [
            'locales' => config('app.available_locales', []),
        ]);
    }
}
