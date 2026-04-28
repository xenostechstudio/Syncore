<div>
    <x-slot:header>
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    <span wire:loading.remove wire:target="save">Save</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <h1 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Sales Order</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs text-zinc-400 dark:text-zinc-500">
                    Module configuration
                </span>
            </div>
        </div>
    </x-slot:header>

    <div class="space-y-8">
        {{-- Order Settings Section --}}
        <section>
            <x-ui.section-bar title="Order Settings" :first="true" />

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:icon name="shopping-cart" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Order numbering format, prefix, and sequence</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Coming soon</p>
            </div>
        </section>

        {{-- Quotation Section --}}
        <section>
            <x-ui.section-bar title="Quotation" />

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:icon name="document-duplicate" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Default validity period, auto-expiry, templates</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Coming soon</p>
            </div>
        </section>

        {{-- Pricing Section --}}
        <section>
            <x-ui.section-bar title="Pricing" />

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:icon name="currency-dollar" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Price lists, discount policies, margin settings</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Coming soon</p>
            </div>
        </section>
    </div>
</div>
