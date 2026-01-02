<?php

namespace App\Livewire\Public\SalesOrders;

use App\Enums\SalesOrderState;
use App\Models\Sales\SalesOrder;
use App\Models\Settings\CompanyProfile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('Sales Order')]
class Show extends Component
{
    public string $token;
    public ?SalesOrder $order = null;
    public bool $expired = false;
    public ?string $statusMessage = null;
    public bool $statusIsError = false;

    public function mount(string $token): void
    {
        $this->token = $token;

        $order = SalesOrder::with(['customer', 'items.product', 'items.tax', 'user'])
            ->where('share_token', $token)
            ->first();

        if (! $order || ($order->share_token_expires_at && $order->share_token_expires_at->isPast())) {
            $this->expired = true;
            return;
        }

        $this->order = $order;
    }

    public function confirmOrder(): void
    {
        if ($this->expired || ! $this->order) {
            return;
        }

        // Only allow confirmation if order is in quotation or quotation_sent status
        if (! in_array($this->order->status, [SalesOrderState::QUOTATION->value, SalesOrderState::QUOTATION_SENT->value])) {
            $this->statusIsError = true;
            $this->statusMessage = 'This order cannot be confirmed at this stage.';
            return;
        }

        $this->order->update(['status' => SalesOrderState::SALES_ORDER->value]);
        $this->order->refresh();

        $this->statusIsError = false;
        $this->statusMessage = 'Order confirmed successfully! Thank you for your business.';
    }

    public function render()
    {
        return view('livewire.public.sales-orders.show', [
            'company' => CompanyProfile::getProfile(),
        ]);
    }
}
