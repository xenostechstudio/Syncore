<?php

namespace App\Livewire\Invoicing;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Invoicing')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.invoicing.index');
    }
}
