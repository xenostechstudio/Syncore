<div class="space-y-6">
    {{-- Header --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('settings.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Payment Gateway</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    <flux:icon name="check" class="size-4" />
                    Save Settings
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">
                {{ session('error') }}
            </x-ui.alert>
        @endif
    </div>

    {{-- Content --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left Column: Info --}}
        <div class="space-y-6 lg:col-span-4">
            {{-- Status Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-gradient-to-b from-white to-zinc-50 dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Connection Status</h2>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3">
                        @if($this->isConfigured)
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                                <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Configured</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Xendit API keys are set</p>
                            </div>
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                                <flux:icon name="exclamation-triangle" class="size-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Not Configured</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Add your API keys to enable payments</p>
                            </div>
                        @endif
                    </div>

                    @if($this->isConfigured)
                        <button type="button" wire:click="testConnection" wire:loading.attr="disabled" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="signal" class="size-4" />
                            <span wire:loading.remove wire:target="testConnection">Test Connection</span>
                            <span wire:loading wire:target="testConnection">Testing...</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Webhook Info Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Webhook URL</h2>
                </div>
                <div class="p-5">
                    <p class="mb-3 text-xs text-zinc-500 dark:text-zinc-400">
                        Add this URL to your Xendit Dashboard under Settings → Webhooks to receive payment notifications.
                    </p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 truncate rounded-lg bg-zinc-100 px-3 py-2 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $this->webhookUrl }}
                        </code>
                        <button 
                            type="button" 
                            onclick="navigator.clipboard.writeText('{{ $this->webhookUrl }}'); alert('Copied!');"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="clipboard-document" class="size-4" />
                        </button>
                    </div>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Getting Started</h2>
                </div>
                <div class="p-5">
                    <ol class="space-y-3 text-xs text-zinc-600 dark:text-zinc-400">
                        <li class="flex gap-3">
                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">1</span>
                            <span>Create a Xendit account at <a href="https://dashboard.xendit.co" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">dashboard.xendit.co</a></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">2</span>
                            <span>Go to Settings → API Keys and copy your Secret Key</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">3</span>
                            <span>Paste your API keys in the form on this page</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">4</span>
                            <span>Add the webhook URL to your Xendit Dashboard</span>
                        </li>
                        <li class="flex gap-3">
                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">5</span>
                            <span>Test the connection and start accepting payments!</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- Right Column: Settings Form --}}
        <div class="space-y-6 lg:col-span-8">
            {{-- Xendit Settings Card --}}
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3 border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <svg class="size-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Xendit Configuration</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Configure your Xendit payment gateway credentials</p>
                    </div>
                </div>

                <div class="space-y-5 p-5">
                    {{-- Environment Toggle --}}
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Production Mode</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Enable this when you're ready to accept real payments</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" wire:model="xendit_is_production" class="peer sr-only">
                            <div class="peer h-6 w-11 rounded-full bg-zinc-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-zinc-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:border-zinc-600 dark:bg-zinc-700"></div>
                        </label>
                    </div>

                    @if($xendit_is_production)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                            <div class="flex items-start gap-3">
                                <flux:icon name="exclamation-triangle" class="size-5 flex-shrink-0 text-amber-600 dark:text-amber-400" />
                                <div>
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Production Mode Enabled</p>
                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-400">Real transactions will be processed. Make sure your API keys are for production.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Secret Key --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Secret Key</label>
                        <div class="relative">
                            <input 
                                type="{{ $showSecretKey ? 'text' : 'password' }}" 
                                wire:model="xendit_secret_key" 
                                placeholder="xnd_development_..."
                                class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 pr-12 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                            />
                            <button 
                                type="button" 
                                wire:click="$toggle('showSecretKey')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                            >
                                <flux:icon name="{{ $showSecretKey ? 'eye-slash' : 'eye' }}" class="size-5" />
                            </button>
                        </div>
                        <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">Your Xendit secret API key (starts with xnd_development_ or xnd_production_)</p>
                    </div>

                    {{-- Public Key --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Public Key</label>
                        <input 
                            type="text" 
                            wire:model="xendit_public_key" 
                            placeholder="xnd_public_development_..."
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                        />
                        <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">Your Xendit public API key (optional, used for client-side integrations)</p>
                    </div>

                    {{-- Webhook Token --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Webhook Verification Token</label>
                        <div class="relative">
                            <input 
                                type="{{ $showWebhookToken ? 'text' : 'password' }}" 
                                wire:model="xendit_webhook_token" 
                                placeholder="Your webhook verification token"
                                class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 pr-12 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                            />
                            <button 
                                type="button" 
                                wire:click="$toggle('showWebhookToken')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                            >
                                <flux:icon name="{{ $showWebhookToken ? 'eye-slash' : 'eye' }}" class="size-5" />
                            </button>
                        </div>
                        <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">Found in Xendit Dashboard → Settings → Webhooks → Verification Token</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Link Expiry (seconds)</label>
                        <input
                            type="number"
                            min="60"
                            step="60"
                            wire:model="xendit_invoice_duration"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                            placeholder="86400"
                        />
                        @error('xendit_invoice_duration') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">This controls how long the customer can pay using the generated Xendit payment link (e.g., 86400 = 24 hours).</p>
                    </div>
                </div>
            </div>

            {{-- Supported Payment Methods --}}
            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Supported Payment Methods</h2>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">These payment methods are enabled by default when creating Xendit invoices</p>
                        </div>
                        <button
                            type="button"
                            wire:click="resetXenditInvoicePaymentMethods"
                            class="text-xs font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                        >
                            Reset to default
                        </button>
                    </div>
                </div>
                <div class="p-5">
                    <div>
                        @php
                            $groups = [
                                'bank' => [
                                    'label' => 'Bank Transfer',
                                    'hint' => 'VA / Bank channels',
                                    'items' => [
                                        ['code' => 'BCA', 'name' => 'BCA', 'icon' => 'building-library'],
                                        ['code' => 'BNI', 'name' => 'BNI', 'icon' => 'building-library'],
                                        ['code' => 'BSI', 'name' => 'BSI', 'icon' => 'building-library'],
                                        ['code' => 'BRI', 'name' => 'BRI', 'icon' => 'building-library'],
                                        ['code' => 'MANDIRI', 'name' => 'Mandiri', 'icon' => 'building-library'],
                                        ['code' => 'PERMATA', 'name' => 'Permata', 'icon' => 'building-library'],
                                    ],
                                ],
                                'emoney' => [
                                    'label' => 'E-Money',
                                    'hint' => 'Wallet payments',
                                    'items' => [
                                        ['code' => 'OVO', 'name' => 'OVO', 'icon' => 'device-phone-mobile'],
                                        ['code' => 'DANA', 'name' => 'DANA', 'icon' => 'device-phone-mobile'],
                                        ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'icon' => 'device-phone-mobile'],
                                        ['code' => 'LINKAJA', 'name' => 'LinkAja', 'icon' => 'device-phone-mobile'],
                                        ['code' => 'QRIS', 'name' => 'QRIS', 'icon' => 'qr-code'],
                                    ],
                                ],
                                'merchant' => [
                                    'label' => 'Merchant / OTC',
                                    'hint' => 'Pay at retail stores',
                                    'items' => [
                                        ['code' => 'ALFAMART', 'name' => 'Alfamart', 'icon' => 'building-storefront'],
                                        ['code' => 'INDOMARET', 'name' => 'Indomaret', 'icon' => 'building-storefront'],
                                    ],
                                ],
                                'card' => [
                                    'label' => 'Card',
                                    'hint' => 'Credit card payments',
                                    'items' => [
                                        ['code' => 'CREDIT_CARD', 'name' => 'Credit Card', 'icon' => 'credit-card'],
                                    ],
                                ],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                            @foreach($groups as $groupKey => $group)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $group['label'] }}</p>
                                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $group['hint'] }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    wire:click="selectXenditPaymentMethodsGroup('{{ $groupKey }}')"
                                                    class="text-xs font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                                                >
                                                    All
                                                </button>
                                                <span class="text-xs text-zinc-300 dark:text-zinc-700">|</span>
                                                <button
                                                    type="button"
                                                    wire:click="clearXenditPaymentMethodsGroup('{{ $groupKey }}')"
                                                    class="text-xs font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                                                >
                                                    Clear
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-3 space-y-2">
                                            @foreach($group['items'] as $method)
                                                @php
                                                    $isSelected = in_array($method['code'], $xendit_invoice_payment_methods ?? [], true);
                                                @endphp
                                                <button
                                                    type="button"
                                                    wire:click="toggleXenditInvoicePaymentMethod('{{ $method['code'] }}')"
                                                    class="flex w-full items-center gap-3 rounded-lg border px-3 py-2.5 text-left text-sm font-medium transition-colors
                                                        {{ $isSelected
                                                            ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200 dark:hover:bg-emerald-900/30'
                                                            : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                                                >
                                                    <flux:icon name="{{ $method['icon'] }}" class="size-4 flex-shrink-0" />
                                                    <span>{{ $method['name'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('xendit_invoice_payment_methods')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>
