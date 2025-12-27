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
            <h1 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Purchase Order</h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                Module configuration
            </span>
        </div>
    </x-slot:header>

    <div class="space-y-8">
        {{-- Order Settings Section --}}
        <section>
            {{-- Section Header (Odoo-style) --}}
            <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Order Settings</h2>
            </div>

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:icon name="clipboard-document-list" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Order numbering format, prefix, and sequence</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Coming soon</p>
            </div>
        </section>

        {{-- Vendor Management Section --}}
        <section>
            {{-- Section Header (Odoo-style) --}}
            <div class="-mx-4 mb-6 border-y border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Vendor Management</h2>
            </div>

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:icon name="building-storefront" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Default payment terms, lead times, vendor ratings</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Coming soon</p>
            </div>
        </section>

        {{-- Approval Workflow Section --}}
        <section>
            {{-- Section Header (Odoo-style) --}}
            <div class="-mx-4 mb-6 border-y border-zinc-200 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Approval Workflow</h2>
            </div>

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:icon name="check-badge" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Approval levels, amount thresholds, auto-approval rules</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Coming soon</p>
            </div>
        </section>
    </div>
</div>
