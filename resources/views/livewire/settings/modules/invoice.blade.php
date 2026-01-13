<div>
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

    <x-slot:header>
        <div class="flex items-center gap-3">
            <button 
                type="button"
                x-data="{ saving: false }"
                x-on:click="saving = true; Livewire.dispatch('saveInvoiceSettings')"
                x-on:invoice-saved.window="saving = false"
                x-bind:disabled="saving"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <svg x-show="saving" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-show="!saving">Save</span>
                <span x-show="saving">Saving...</span>
            </button>
            <h1 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Invoice</h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                Module configuration
            </span>
        </div>
    </x-slot:header>

    <div class="space-y-8">
        {{-- Invoice Template Section --}}
        <section>
            <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Invoice Template</h2>
            </div>

            <div class="space-y-6">
                {{-- Template Style --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Style & Colors</h3>
                    
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Template Style</label>
                            <div class="flex gap-2">
                                @foreach(['modern' => 'Modern', 'classic' => 'Classic', 'minimal' => 'Minimal'] as $value => $label)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 transition-colors {{ $template_style === $value ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600' }}">
                                        <input type="radio" wire:model.live="template_style" value="{{ $value }}" class="sr-only" />
                                        <span class="text-sm font-medium">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Primary Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" wire:model.live="primary_color" class="h-9 w-12 cursor-pointer rounded border border-zinc-200 dark:border-zinc-700" />
                                    <input type="text" wire:model.live="primary_color" class="w-20 rounded-lg border border-zinc-200 px-2 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800" />
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Accent Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" wire:model.live="accent_color" class="h-9 w-12 cursor-pointer rounded border border-zinc-200 dark:border-zinc-700" />
                                    <input type="text" wire:model.live="accent_color" class="w-20 rounded-lg border border-zinc-200 px-2 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logo & Header --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Header Options</h3>
                    
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input type="checkbox" wire:model.live="show_logo" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show Company Logo</span>
                            </label>

                            @if($show_logo)
                                <div class="flex gap-3 pl-7">
                                    <div>
                                        <label class="mb-1 block text-xs text-zinc-500">Position</label>
                                        <select wire:model.live="logo_position" class="rounded-lg border border-zinc-200 px-2 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                            <option value="left">Left</option>
                                            <option value="center">Center</option>
                                            <option value="right">Right</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-zinc-500">Size (px)</label>
                                        <input type="number" wire:model.live="logo_size" min="60" max="200" class="w-16 rounded-lg border border-zinc-200 px-2 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800" />
                                    </div>
                                </div>
                            @endif

                            <label class="flex items-center gap-3">
                                <input type="checkbox" wire:model.live="show_status_badge" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show Status Badge</span>
                            </label>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Invoice Title</label>
                            <input type="text" wire:model="invoice_title" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="INVOICE" />
                        </div>
                    </div>
                </div>

                {{-- Display Options --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Display Options</h3>
                    
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input type="checkbox" wire:model="show_tax_breakdown" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show Tax Breakdown</span>
                            </label>
                            <label class="flex items-center gap-3">
                                <input type="checkbox" wire:model="show_discount" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show Discount Line</span>
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Currency</label>
                                    <input type="text" wire:model="currency_symbol" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="Rp" />
                                </div>
                                <div class="flex-1">
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Position</label>
                                    <select wire:model="currency_position" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                        <option value="before">Before</option>
                                        <option value="after">After</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Date Format</label>
                                    <select wire:model="date_format" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                        <option value="M d, Y">Jan 12, 2026</option>
                                        <option value="d M Y">12 Jan 2026</option>
                                        <option value="d/m/Y">12/01/2026</option>
                                        <option value="Y-m-d">2026-01-12</option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Number Format</label>
                                    <select wire:model="number_format" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                        <option value="id">1.000.000</option>
                                        <option value="en">1,000,000.00</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Watermark & Signature --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Watermark & Signature</h3>
                    
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input type="checkbox" wire:model.live="show_watermark" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show Watermark on Draft</span>
                            </label>
                            @if($show_watermark)
                                <div class="pl-7">
                                    <input type="text" wire:model="watermark_text" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="DRAFT" />
                                </div>
                            @endif
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input type="checkbox" wire:model.live="show_signature" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show Signature Line</span>
                            </label>
                            @if($show_signature)
                                <div class="pl-7">
                                    <input type="text" wire:model="signature_label" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="Authorized Signature" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Default Content --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Default Content</h3>
                    
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Default Notes</label>
                            <textarea wire:model="default_notes" rows="2" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="Notes on every invoice..."></textarea>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Default Terms</label>
                            <textarea wire:model="default_terms" rows="2" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="Payment terms..."></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Footer Text</label>
                        <input type="text" wire:model="footer_text" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="Thank you for your business!" />
                    </div>
                </div>
            </div>
        </section>

        {{-- Payment Information Section --}}
        <section>
            <div class="-mx-4 mb-6 border-y border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Payment Information</h2>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <label class="mb-4 flex items-center gap-3">
                        <input type="checkbox" wire:model.live="show_payment_info" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show Payment Information on Invoice</span>
                    </label>

                    @if($show_payment_info)
                        <div class="grid gap-4 lg:grid-cols-2">
                            {{-- Bank Account 1 --}}
                            <div class="space-y-3 rounded-lg border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Primary Bank Account</p>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500">Bank Name</label>
                                    <input type="text" wire:model="bank_name" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="BCA, Mandiri, BNI..." />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500">Account Number</label>
                                    <input type="text" wire:model="bank_account" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="1234567890" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500">Account Holder</label>
                                    <input type="text" wire:model="bank_holder" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="PT Company Name" />
                                </div>
                            </div>

                            {{-- Bank Account 2 --}}
                            <div class="space-y-3 rounded-lg border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Secondary Bank (Optional)</p>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500">Bank Name</label>
                                    <input type="text" wire:model="bank_name_2" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="BCA, Mandiri, BNI..." />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500">Account Number</label>
                                    <input type="text" wire:model="bank_account_2" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="1234567890" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500">Account Holder</label>
                                    <input type="text" wire:model="bank_holder_2" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="PT Company Name" />
                                </div>
                            </div>
                        </div>

                        {{-- QR Code --}}
                        <div class="mt-4 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <label class="mb-3 flex items-center gap-3">
                                <input type="checkbox" wire:model.live="show_qr_code" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Show QR Code (QRIS)</span>
                            </label>
                            @if($show_qr_code)
                                <div class="pl-7">
                                    <input type="text" wire:model="qr_code_content" class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800" placeholder="QRIS code or payment link" />
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Payment Gateway Section --}}
        <section>
            <div class="-mx-4 mb-6 border-y border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Payment Gateway</h2>
            </div>

            <div class="space-y-4">
                {{-- Xendit --}}
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between p-5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/20">
                                <svg class="h-7 w-7 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Xendit</h3>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Virtual Account, E-Wallet, Credit Card</p>
                            </div>
                        </div>
                        <button 
                            type="button"
                            wire:click="$toggle('xenditEnabled')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $xenditEnabled ? 'bg-emerald-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"
                        >
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $xenditEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>

                    @if($xenditEnabled)
                    <div class="border-t border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-800/30">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Public Key</label>
                                <input type="text" wire:model="xenditPublicKey" placeholder="xnd_public_..." class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800" />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Secret Key</label>
                                <input type="password" wire:model="xenditSecretKey" placeholder="xnd_secret_..." class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm dark:border-zinc-700 dark:bg-zinc-800" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="xenditTestMode" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Test Mode (Sandbox)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Midtrans --}}
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between p-5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
                                <flux:icon name="credit-card" class="size-7 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Midtrans</h3>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">GoPay, OVO, QRIS, Bank Transfer</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">Coming soon</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
