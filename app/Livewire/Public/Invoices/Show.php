<?php

namespace App\Livewire\Public\Invoices;

use App\Models\Invoicing\Invoice;
use App\Models\Settings\CompanyProfile;
use App\Services\XenditService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('Invoice')]
class Show extends Component
{
    public string $token;
    public ?Invoice $invoice = null;
    public bool $expired = false;
    public array $paymentMethods = [];
    public string $selectedPaymentMethod = 'online';
    public ?string $paymentLink = null;
    public ?string $statusMessage = null;
    public bool $statusIsError = false;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->paymentMethods = config('xendit.invoice.payment_methods', []);

        $invoice = Invoice::with(['customer', 'items.product', 'salesOrder.user'])
            ->where('share_token', $token)
            ->first();

        if (! $invoice || ($invoice->share_token_expires_at && $invoice->share_token_expires_at->isPast())) {
            $this->expired = true;
            return;
        }

        $this->invoice = $invoice;

        if ($invoice->xendit_invoice_url && ! in_array(Str::lower($invoice->xendit_status ?? ''), ['paid', 'expired'], true)) {
            $this->paymentLink = $invoice->xendit_invoice_url;
        }
    }

    public function selectPaymentMethod(string $method): void
    {
        $this->selectedPaymentMethod = $method;
    }

    public function requestPaymentLink(): void
    {
        if ($this->expired || ! $this->invoice) {
            return;
        }

        if ($this->invoice->xendit_invoice_url && ! in_array(Str::lower($this->invoice->xendit_status ?? ''), ['paid', 'expired'], true)) {
            $this->paymentLink = $this->invoice->xendit_invoice_url;
            $this->statusIsError = false;
            $this->statusMessage = 'Payment link ready.';
            return;
        }

        $xenditService = app(XenditService::class);

        if (! $xenditService->isConfigured()) {
            $this->statusIsError = true;
            $this->statusMessage = 'Online payment is not available right now.';
            return;
        }

        $result = $xenditService->createInvoice($this->invoice->fresh());

        if ($result['success']) {
            $this->invoice->refresh();
            $this->paymentLink = $result['invoice_url'];
            $this->statusIsError = false;
            $this->statusMessage = 'Payment link generated. You will be redirected shortly.';
        } else {
            $this->statusIsError = true;
            $this->statusMessage = $result['message'] ?? 'Failed to create payment link.';
        }
    }

    public function render()
    {
        return view('livewire.public.invoices.show', [
            'company' => CompanyProfile::getProfile(),
        ]);
    }
}
