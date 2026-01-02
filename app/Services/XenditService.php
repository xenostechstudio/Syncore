<?php

namespace App\Services;

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    protected string $baseUrl;
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('xendit.secret_key');
        $this->baseUrl = config('xendit.is_production')
            ? 'https://api.xendit.co'
            : 'https://api.xendit.co'; // Xendit uses same URL, mode is determined by API key
    }

    /**
     * Check if Xendit is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->secretKey);
    }

    /**
     * Create a Xendit invoice for payment
     */
    public function createInvoice(Invoice $invoice): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Xendit is not configured. Please add your API keys in Settings.',
            ];
        }

        // Ensure share token exists for redirect URL
        $invoice->ensureShareToken();

        $customer = $invoice->customer;
        $externalId = 'INV-' . $invoice->id . '-' . time();

        $payload = [
            'external_id' => $externalId,
            'amount' => (int) $invoice->total,
            'payer_email' => $customer->email ?? 'customer@example.com',
            'description' => "Payment for Invoice #{$invoice->invoice_number}",
            'invoice_duration' => config('xendit.invoice.invoice_duration', 86400),
            'currency' => config('xendit.invoice.currency', 'IDR'),
            'success_redirect_url' => route('public.invoices.show', $invoice->share_token),
            'failure_redirect_url' => route('public.invoices.show', $invoice->share_token) . '?payment=failed',
            'payment_methods' => config('xendit.invoice.payment_methods', []),
            'customer' => [
                'given_names' => $customer->name ?? 'Customer',
                'email' => $customer->email ?? 'customer@example.com',
                'mobile_number' => $customer->phone ?? null,
            ],
            'customer_notification_preference' => [
                'invoice_created' => ['email'],
                'invoice_reminder' => ['email'],
                'invoice_paid' => ['email'],
            ],
            'items' => $this->buildInvoiceItems($invoice),
        ];

        // Remove null values from customer
        $payload['customer'] = array_filter($payload['customer']);

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->timeout(30)
                ->post("{$this->baseUrl}/v2/invoices", $payload);

            if ($response->successful()) {
                $data = $response->json();

                // Update invoice with Xendit data
                $invoice->update([
                    'xendit_invoice_id' => $data['id'],
                    'xendit_invoice_url' => $data['invoice_url'],
                    'xendit_external_id' => $externalId,
                    'xendit_status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'invoice_url' => $data['invoice_url'],
                    'xendit_invoice_id' => $data['id'],
                    'message' => 'Payment link created successfully.',
                ];
            }

            Log::error('Xendit invoice creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to create payment link.',
            ];
        } catch (\Exception $e) {
            Log::error('Xendit API error', [
                'message' => $e->getMessage(),
                'invoice_id' => $invoice->id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect to payment gateway: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build invoice items array for Xendit
     */
    protected function buildInvoiceItems(Invoice $invoice): array
    {
        $items = [];

        foreach ($invoice->items as $item) {
            $items[] = [
                'name' => $item->product->name ?? $item->description ?? 'Item',
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->unit_price,
            ];
        }

        // If no items, add a generic item
        if (empty($items)) {
            $items[] = [
                'name' => "Invoice #{$invoice->invoice_number}",
                'quantity' => 1,
                'price' => (int) $invoice->total,
            ];
        }

        return $items;
    }

    /**
     * Get invoice status from Xendit
     */
    public function getInvoiceStatus(string $xenditInvoiceId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->timeout(30)
                ->get("{$this->baseUrl}/v2/invoices/{$xenditInvoiceId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Xendit get invoice status error', [
                'message' => $e->getMessage(),
                'xendit_invoice_id' => $xenditInvoiceId,
            ]);

            return null;
        }
    }

    /**
     * Handle webhook callback from Xendit
     */
    public function handleWebhook(array $payload): bool
    {
        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;
        $paidAmount = $payload['paid_amount'] ?? 0;
        $paymentMethod = $payload['payment_method'] ?? 'xendit';
        $paymentChannel = $payload['payment_channel'] ?? '';

        if (!$externalId || !$status) {
            Log::warning('Xendit webhook missing required fields', $payload);
            return false;
        }

        // Extract invoice ID from external_id (format: INV-{id}-{timestamp})
        preg_match('/^INV-(\d+)-/', $externalId, $matches);
        $invoiceId = $matches[1] ?? null;

        if (!$invoiceId) {
            Log::warning('Xendit webhook: could not extract invoice ID', ['external_id' => $externalId]);
            return false;
        }

        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            Log::warning('Xendit webhook: invoice not found', ['invoice_id' => $invoiceId]);
            return false;
        }

        if ($status === 'PAID') {
            return $this->markInvoiceAsPaid($invoice, $paidAmount, $paymentMethod, $paymentChannel, $payload);
        }

        if ($status === 'EXPIRED') {
            $invoice->update(['xendit_status' => 'expired']);
            Log::info('Xendit invoice expired', ['invoice_id' => $invoiceId]);
        }

        return true;
    }

    /**
     * Mark invoice as paid and create payment record
     */
    protected function markInvoiceAsPaid(
        Invoice $invoice,
        float $paidAmount,
        string $paymentMethod,
        string $paymentChannel,
        array $payload
    ): bool {
        // Check if payment already recorded
        $existingPayment = Payment::where('invoice_id', $invoice->id)
            ->where('reference', 'LIKE', 'XENDIT-%')
            ->where('amount', $paidAmount)
            ->first();

        if ($existingPayment) {
            Log::info('Xendit payment already recorded', ['invoice_id' => $invoice->id]);
            return true;
        }

        // Generate payment number
        $year = now()->year;
        $prefix = "PAY/{$year}/";
        $lastPayment = Payment::where('payment_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();
        $nextNumber = $lastPayment ? ((int) substr($lastPayment->payment_number, strlen($prefix))) + 1 : 1;
        $paymentNumber = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        Log::info('Xendit creating payment record', [
            'invoice_id' => $invoice->id,
            'payment_number' => $paymentNumber,
            'amount' => $paidAmount,
        ]);

        $paidBefore = (float) $invoice->payments()->sum('amount');

        // Create payment record
        $paymentMethodDisplay = $this->formatPaymentMethod($paymentMethod, $paymentChannel);
        
        Payment::create([
            'payment_number' => $paymentNumber,
            'invoice_id' => $invoice->id,
            'amount' => $paidAmount,
            'payment_date' => now(),
            'payment_method' => $paymentMethodDisplay,
            'reference' => 'XENDIT-' . ($payload['id'] ?? $payload['external_id']),
            'notes' => "Paid via Xendit ({$paymentChannel})",
            'status' => 'completed',
        ]);

        // Update invoice status
        $totalPaid = $paidBefore + $paidAmount;

        if ($totalPaid >= $invoice->total) {
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $totalPaid,
                'xendit_status' => 'paid',
            ]);
        } else {
            $invoice->update([
                'status' => 'partial',
                'paid_amount' => $totalPaid,
                'xendit_status' => 'partial',
            ]);
        }

        Log::info('Xendit payment recorded', [
            'invoice_id' => $invoice->id,
            'amount' => $paidAmount,
            'payment_number' => $paymentNumber,
        ]);

        return true;
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $callbackToken): bool
    {
        $webhookToken = config('xendit.webhook_token');

        if (empty($webhookToken)) {
            // If no webhook token configured, skip verification (not recommended for production)
            return true;
        }

        return hash_equals($webhookToken, $callbackToken);
    }

    /**
     * Format payment method for display
     */
    protected function formatPaymentMethod(string $method, string $channel): string
    {
        $method = strtoupper($method);
        $channel = strtoupper($channel);

        // Bank Transfer - show bank name
        if ($method === 'BANK_TRANSFER' || $method === 'VIRTUAL_ACCOUNT') {
            $bankNames = [
                'BCA' => 'BCA Virtual Account',
                'BNI' => 'BNI Virtual Account',
                'BRI' => 'BRI Virtual Account',
                'MANDIRI' => 'Mandiri Virtual Account',
                'PERMATA' => 'Permata Virtual Account',
                'CIMB' => 'CIMB Virtual Account',
                'SAHABAT_SAMPOERNA' => 'Sahabat Sampoerna VA',
                'BJB' => 'BJB Virtual Account',
                'BSI' => 'BSI Virtual Account',
            ];
            return $bankNames[$channel] ?? "Bank Transfer ({$channel})";
        }

        // E-Wallet - show wallet name
        if ($method === 'EWALLET') {
            $walletNames = [
                'OVO' => 'OVO',
                'DANA' => 'DANA',
                'SHOPEEPAY' => 'ShopeePay',
                'LINKAJA' => 'LinkAja',
                'ASTRAPAY' => 'AstraPay',
                'JENIUSPAY' => 'Jenius Pay',
            ];
            return $walletNames[$channel] ?? "E-Wallet ({$channel})";
        }

        // Credit/Debit Card
        if ($method === 'CREDIT_CARD' || $method === 'DEBIT_CARD') {
            return 'Credit/Debit Card';
        }

        // QR Code
        if ($method === 'QR_CODE' || $channel === 'QRIS') {
            return 'QRIS';
        }

        // Retail Outlet
        if ($method === 'RETAIL_OUTLET') {
            $outletNames = [
                'ALFAMART' => 'Alfamart',
                'INDOMARET' => 'Indomaret',
            ];
            return $outletNames[$channel] ?? "Retail ({$channel})";
        }

        // Paylater
        if ($method === 'PAYLATER') {
            $paylaterNames = [
                'KREDIVO' => 'Kredivo',
                'AKULAKU' => 'Akulaku',
                'UANGME' => 'UangMe',
                'ATOME' => 'Atome',
            ];
            return $paylaterNames[$channel] ?? "PayLater ({$channel})";
        }

        // Direct Debit
        if ($method === 'DIRECT_DEBIT') {
            return "Direct Debit ({$channel})";
        }

        // Fallback
        return $channel ?: $method;
    }
}
