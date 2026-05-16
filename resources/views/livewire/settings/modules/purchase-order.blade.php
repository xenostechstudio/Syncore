<div>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
        @endif
    </div>

    <x-slot:header>
        <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Purchase Order</h1>
        <button
            type="button"
            x-data="{ saving: false }"
            x-on:click="saving = true; Livewire.dispatch('savePurchaseOrderSettings')"
            x-on:purchase-order-saved.window="saving = false"
            x-bind:disabled="saving"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
            <svg x-show="saving" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-show="!saving">Save</span>
            <span x-show="saving">Saving…</span>
        </button>
    </x-slot:header>

    <form wire:submit.prevent="save" class="space-y-8">
        {{-- Document Numbering --}}
        <section>
            <x-ui.section-bar title="Document Numbering" :first="true" />
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                How RFQ and Purchase Order numbers are generated. Suppliers see this on every PO they receive.
            </p>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Prefix</label>
                    <input type="text" wire:model.live="doc_number_prefix" maxlength="20"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('doc_number_prefix') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Separator</label>
                    <input type="text" wire:model.live="doc_number_separator" maxlength="5" placeholder="(none)"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('doc_number_separator') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Padding</label>
                    <input type="number" wire:model.live="doc_number_padding" min="1" max="10"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('doc_number_padding') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="lg:col-span-5 flex items-center">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model.live="doc_number_yearly_reset"
                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800">
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Reset sequence each year (include year in number)</span>
                    </label>
                </div>
            </div>

            <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-800 dark:bg-zinc-900/50">
                <p class="text-xs uppercase tracking-wider text-zinc-400">Preview</p>
                <p class="mt-0.5 font-mono text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->numberPreview }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    Existing PO numbers in your database are not renamed — only new POs use this format.
                </p>
            </div>
        </section>

        {{-- Receipt defaults --}}
        <section>
            <x-ui.section-bar title="Receipt Defaults" />
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                Pre-filled on every new RFQ / Purchase Order to save typing.
            </p>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Default warehouse</label>
                    <div class="mt-1">
                        <x-ui.select wire:model.live="default_warehouse_id">
                            <option value="">— Ask every time —</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('default_warehouse_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="lg:col-span-6">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Default lead time (days)</label>
                    <input type="number" wire:model.live="default_lead_time_days" min="0" max="365"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Auto-fills the expected arrival date on new POs (today + lead time).</p>
                    @error('default_lead_time_days') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Workflow --}}
        <section>
            <x-ui.section-bar title="Workflow" />
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                Approval gate and supplier email behavior.
            </p>

            <div class="space-y-4">
                <div class="flex items-center">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model.live="auto_send_to_supplier"
                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800">
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Automatically email the supplier when a PO is issued</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Approval threshold</label>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">Rp</span>
                        <input type="number" wire:model.live="approval_threshold" min="0" step="1" placeholder="Leave empty for no approval workflow"
                            class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    </div>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        POs at or above this amount require manager approval before being sent. Leave empty to disable the approval flow entirely.
                    </p>
                    @error('approval_threshold') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Boilerplate text --}}
        <section>
            <x-ui.section-bar title="Boilerplate Text" />
            <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                Pre-filled on every new RFQ / PO so the buyer doesn't retype the same lines.
            </p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Default terms &amp; conditions</label>
                    <textarea wire:model.live="default_terms" rows="4" maxlength="5000"
                        placeholder="e.g. Delivery in 7-14 working days. Payment Net 30. Quality must match approved sample."
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Default notes</label>
                    <textarea wire:model.live="default_notes" rows="3" maxlength="5000"
                        placeholder="Internal notes pre-filled on each new RFQ / PO."
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                </div>
            </div>
        </section>
    </form>
</div>
