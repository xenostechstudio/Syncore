<div>
    <x-slot:header>
        <div class="flex items-center gap-3">
            <button 
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving...</span>
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
        {{-- Payment Gateway Section --}}
        <section>
            {{-- Section Header (Odoo-style) --}}
            <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
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
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Accept payments via Virtual Account, E-Wallet, Credit Card</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button 
                                type="button"
                                wire:click="$toggle('xenditEnabled')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $xenditEnabled ? 'bg-emerald-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"
                                role="switch"
                            >
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $xenditEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Xendit Configuration (shown when enabled) --}}
                    @if($xenditEnabled)
                    <div class="border-t border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-800/30">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Public Key</label>
                                <input 
                                    type="text"
                                    wire:model="xenditPublicKey"
                                    placeholder="xnd_public_..."
                                    class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Secret Key</label>
                                <input 
                                    type="password"
                                    wire:model="xenditSecretKey"
                                    placeholder="xnd_secret_..."
                                    class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="flex items-center gap-3">
                                    <input 
                                        type="checkbox"
                                        wire:model="xenditTestMode"
                                        class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700"
                                    />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">Test Mode (Sandbox)</span>
                                </label>
                                <p class="mt-1 ml-7 text-xs text-zinc-500 dark:text-zinc-400">Enable this for testing. Disable for production.</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Midtrans (Coming Soon) --}}
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between p-5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
                                <svg class="h-7 w-7 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Midtrans</h3>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">GoPay, OVO, QRIS, Bank Transfer</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">Coming soon</span>
                    </div>
                </div>

                {{-- Stripe (Coming Soon) --}}
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between p-5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-900/20">
                                <svg class="h-7 w-7 text-violet-600 dark:text-violet-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Stripe</h3>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Credit Card, Apple Pay, Google Pay</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">Coming soon</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Invoice Settings Section --}}
        <section>
            {{-- Section Header (Odoo-style) --}}
            <div class="-mx-4 mb-6 border-y border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Invoice Settings</h2>
            </div>

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:icon name="document-text" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Invoice numbering, templates, and default terms</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Coming soon</p>
            </div>
        </section>

        {{-- Tax Settings Section --}}
        <section>
            {{-- Section Header (Odoo-style) --}}
            <div class="-mx-4 mb-6 border-y border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Tax Configuration</h2>
            </div>

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:icon name="calculator" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Tax rates, tax groups, and calculation rules</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Coming soon</p>
            </div>
        </section>
    </div>
</div>
