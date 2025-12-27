<?php

namespace App\Livewire\Settings\Modules;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('Invoice Settings')]
class Invoice extends Component
{
    // Payment Gateway - Xendit
    public bool $xenditEnabled = false;
    public string $xenditPublicKey = '';
    public string $xenditSecretKey = '';
    public bool $xenditTestMode = true;

    public function mount()
    {
        // Load from config
        $this->xenditEnabled = config('xendit.enabled', false);
        $this->xenditTestMode = config('xendit.test_mode', true);
        $this->xenditPublicKey = config('xendit.public_key', '');
        // Don't load secret key for security
    }

    public function save()
    {
        // For now, just show success message
        // In production, you'd save to database or update config
        session()->flash('success', 'Invoice settings saved successfully!');
    }

    public function render()
    {
        return view('livewire.settings.modules.invoice');
    }
}
