<?php

namespace App\Livewire\Settings\Modules;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('Purchase Order Settings')]
class PurchaseOrder extends Component
{
    public function save()
    {
        // TODO: Implement save logic when settings are added
        $this->dispatch('notify', type: 'success', message: 'Settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.settings.modules.purchase-order');
    }
}
