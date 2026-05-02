<div x-data="{ showSendMessage: false, showLogNote: false, showScheduleActivity: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase.receipts.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Goods Receipt</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $receiptId ? $reference : 'New Receipt' }}
                        </span>
                        @if($receiptId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-44">
                                    @if($rfqId)
                                        <a href="{{ route('purchase.orders.edit', $rfqId) }}" wire:navigate class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <flux:icon name="arrow-top-right-on-square" class="size-4" />
                                            <span>View source PO</span>
                                        </a>
                                        <flux:menu.separator />
                                    @endif
                                    @if($receiptState->canCancel())
                                        <button type="button" wire:click="cancelReceipt" wire:confirm="Cancel this receipt? Stock will be reversed if it was already validated." class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                            <flux:icon name="x-mark" class="size-4" />
                                            <span>Cancel Receipt</span>
                                        </button>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot:header>

    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">Please fix the following errors:</span>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>

    {{-- Action Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if($receiptState->canEdit())
                        <button
                            type="button"
                            wire:click="save"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                            <span wire:loading.remove wire:target="save">Save</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <button
                            type="button"
                            wire:click="validateReceipt"
                            wire:loading.attr="disabled"
                            wire:target="validateReceipt"
                            wire:confirm="Validate this receipt? This will move stock and update the source PO."
                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-600 bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 disabled:opacity-50 dark:border-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                        >
                            <flux:icon name="check-circle" class="size-4" />
                            <span>Validate</span>
                        </button>
                    @endif
                    <a href="{{ route('purchase.receipts.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        Back to list
                    </a>
                </div>
                <x-ui.status-badge :status="$receiptState" />
            </div>
            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">

            {{-- Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $reference ?: 'New Receipt' }}
                        </h1>

                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Source PO --}}
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Source Purchase Order</label>
                                @if($rfqReference)
                                    <a href="{{ route('purchase.orders.edit', $rfqId) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-mono text-zinc-700 hover:border-zinc-300 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-600">
                                        {{ $rfqReference }}
                                        <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                                    </a>
                                @else
                                    <span class="text-sm text-zinc-400">—</span>
                                @endif
                            </div>

                            {{-- Supplier --}}
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Supplier</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $supplierName ?? '—' }}</p>
                            </div>

                            {{-- Warehouse --}}
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Warehouse <span class="text-red-500">*</span></label>
                                @if($receiptState->canEdit())
                                    <select wire:model="warehouseId" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <option value="">Select warehouse…</option>
                                        @foreach($warehouses as $w)
                                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $warehouses->firstWhere('id', $warehouseId)?->name ?? '—' }}
                                    </p>
                                @endif
                            </div>

                            {{-- Received At --}}
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Received On</label>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $receivedAt ?? 'Pending validation' }}
                                </p>
                            </div>
                        </div>

                        {{-- Lines --}}
                        <div class="mt-8">
                            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-700 dark:text-zinc-300">Lines</h2>
                            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                            <th class="px-4 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ordered</th>
                                            <th class="px-4 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Already Received</th>
                                            <th class="px-4 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Receiving Now</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                        @forelse($lines as $idx => $line)
                                            <tr>
                                                <td class="px-4 py-3 text-sm">
                                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $line['product_name'] }}</div>
                                                    @if(!empty($line['product_sku']))
                                                        <div class="font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $line['product_sku'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right text-sm text-zinc-700 tabular-nums dark:text-zinc-300">
                                                    {{ rtrim(rtrim(number_format($line['quantity_ordered'], 2), '0'), '.') }}
                                                </td>
                                                <td class="px-4 py-3 text-right text-sm text-zinc-500 tabular-nums dark:text-zinc-400">
                                                    {{ rtrim(rtrim(number_format($line['quantity_already_received'], 2), '0'), '.') }}
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    @if($receiptState->canEdit())
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            wire:model="lines.{{ $idx }}.quantity_received"
                                                            class="w-28 rounded-md border border-zinc-200 bg-white px-2 py-1 text-right text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                        />
                                                    @else
                                                        <span class="text-sm font-medium text-zinc-900 tabular-nums dark:text-zinc-100">
                                                            {{ rtrim(rtrim(number_format($line['quantity_received'], 2), '0'), '.') }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                                    Nothing left to receive on this PO.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="mt-6">
                            <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Notes</label>
                            @if($receiptState->canEdit())
                                <textarea
                                    wire:model="notes"
                                    rows="3"
                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    placeholder="Optional notes about this receipt…"
                                ></textarea>
                            @else
                                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $notes ?: '—' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right rail: chatter --}}
            <div class="lg:col-span-3">
                <x-ui.chatter-forms :showMessage="false" />

                @if($receiptId)
                    <div class="mt-4 space-y-3">
                        @forelse($activities as $item)
                            @if(($item['type'] ?? null) === 'note')
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Receipt created" />
                            @endif
                        @empty
                            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-center text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-500">
                                Activity will appear here once you save or validate.
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="mt-4 rounded-lg border border-dashed border-zinc-300 p-4 text-center text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-500">
                        Save the receipt to start tracking activity.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
