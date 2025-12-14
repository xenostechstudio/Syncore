<?php

namespace App\Livewire\Purchase\Orders;

use App\Livewire\Purchase\Rfq\Form as RfqForm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Purchase Order')]
class Form extends RfqForm
{
    public function render()
    {
        return view('livewire.purchase.orders.form');
    }
}
