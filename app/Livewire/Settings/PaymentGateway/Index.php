<?php

namespace App\Livewire\Settings\PaymentGateway;

use App\Services\XenditService;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('Payment Gateway')]
class Index extends Component
{
    public string $xendit_secret_key = '';
    public string $xendit_public_key = '';
    public string $xendit_webhook_token = '';
    public bool $xendit_is_production = false;
    public array $xendit_invoice_payment_methods = [];
    public int $xendit_invoice_duration = 86400;

    public bool $showSecretKey = false;
    public bool $showWebhookToken = false;

    public function mount(): void
    {
        // Load from config (which reads from .env)
        $this->xendit_secret_key = config('xendit.secret_key', '') ?: '';
        $this->xendit_public_key = config('xendit.public_key', '') ?: '';
        $this->xendit_webhook_token = config('xendit.webhook_token', '') ?: '';
        $this->xendit_is_production = (bool) config('xendit.is_production', false);
        $this->xendit_invoice_payment_methods = array_values(array_filter((array) config('xendit.invoice.payment_methods', [])));
        $this->xendit_invoice_duration = (int) config('xendit.invoice.invoice_duration', 86400);
    }

    public function save(): void
    {
        $this->validate([
            'xendit_secret_key' => 'nullable|string|max:255',
            'xendit_public_key' => 'nullable|string|max:255',
            'xendit_webhook_token' => 'nullable|string|max:255',
            'xendit_invoice_payment_methods' => 'array',
            'xendit_invoice_payment_methods.*' => 'string|max:50',
            'xendit_invoice_duration' => 'required|integer|min:60|max:2592000',
        ]);

        // Update .env file
        $this->updateEnvFile([
            'XENDIT_SECRET_KEY' => $this->xendit_secret_key,
            'XENDIT_PUBLIC_KEY' => $this->xendit_public_key,
            'XENDIT_WEBHOOK_TOKEN' => $this->xendit_webhook_token,
            'XENDIT_IS_PRODUCTION' => $this->xendit_is_production ? 'true' : 'false',
            'XENDIT_INVOICE_PAYMENT_METHODS' => implode(',', array_values(array_filter($this->xendit_invoice_payment_methods))),
            'XENDIT_INVOICE_DURATION' => (string) $this->xendit_invoice_duration,
        ]);

        // Clear config cache
        Artisan::call('config:clear');

        session()->flash('success', 'Payment gateway settings saved successfully.');
    }

    protected function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // Escape special characters in value
            $escapedValue = str_contains($value, ' ') ? "\"{$value}\"" : $value;

            // Check if key exists
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$escapedValue}",
                    $envContent
                );
            } else {
                // Add new key at the end
                $envContent .= "\n{$key}={$escapedValue}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    public function testConnection(): void
    {
        $xenditService = app(XenditService::class);

        if (!$xenditService->isConfigured()) {
            session()->flash('error', 'Please configure your Xendit API keys first.');
            return;
        }

        // Try to make a simple API call to verify credentials
        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->xendit_secret_key, '')
                ->timeout(10)
                ->get('https://api.xendit.co/balance');

            if ($response->successful()) {
                $balance = $response->json();
                session()->flash('success', 'Connection successful! Balance: Rp ' . number_format($balance['balance'] ?? 0, 0, ',', '.'));
            } else {
                session()->flash('error', 'Connection failed: ' . ($response->json()['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function getWebhookUrlProperty(): string
    {
        return url('/api/webhooks/xendit/invoice');
    }

    public function getIsConfiguredProperty(): bool
    {
        return !empty($this->xendit_secret_key);
    }

    public function toggleXenditInvoicePaymentMethod(string $code): void
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return;
        }

        $current = array_values(array_filter(array_map('strval', $this->xendit_invoice_payment_methods)));

        if (in_array($code, $current, true)) {
            $this->xendit_invoice_payment_methods = array_values(array_filter($current, fn ($m) => $m !== $code));
            return;
        }

        $current[] = $code;
        $this->xendit_invoice_payment_methods = array_values(array_unique($current));
    }

    public function selectXenditPaymentMethodsGroup(string $group): void
    {
        $groups = $this->xenditPaymentMethodGroups();
        if (!isset($groups[$group])) {
            return;
        }

        $current = array_values(array_filter(array_map('strval', $this->xendit_invoice_payment_methods)));
        $this->xendit_invoice_payment_methods = array_values(array_unique(array_merge($current, $groups[$group])));
    }

    public function clearXenditPaymentMethodsGroup(string $group): void
    {
        $groups = $this->xenditPaymentMethodGroups();
        if (!isset($groups[$group])) {
            return;
        }

        $toRemove = array_flip($groups[$group]);
        $current = array_values(array_filter(array_map('strval', $this->xendit_invoice_payment_methods)));
        $this->xendit_invoice_payment_methods = array_values(array_filter($current, fn ($m) => !isset($toRemove[$m])));
    }

    public function resetXenditInvoicePaymentMethods(): void
    {
        $this->xendit_invoice_payment_methods = $this->xenditDefaultInvoicePaymentMethods();
    }

    protected function xenditPaymentMethodGroups(): array
    {
        return [
            'bank' => ['BCA', 'BNI', 'BSI', 'BRI', 'MANDIRI', 'PERMATA'],
            'emoney' => ['OVO', 'DANA', 'SHOPEEPAY', 'LINKAJA', 'QRIS'],
            'merchant' => ['ALFAMART', 'INDOMARET'],
            'card' => ['CREDIT_CARD'],
        ];
    }

    protected function xenditDefaultInvoicePaymentMethods(): array
    {
        return [
            'CREDIT_CARD',
            'BCA',
            'BNI',
            'BSI',
            'BRI',
            'MANDIRI',
            'PERMATA',
            'ALFAMART',
            'INDOMARET',
            'OVO',
            'DANA',
            'SHOPEEPAY',
            'LINKAJA',
            'QRIS',
        ];
    }

    public function render()
    {
        return view('livewire.settings.payment-gateway.index');
    }
}
