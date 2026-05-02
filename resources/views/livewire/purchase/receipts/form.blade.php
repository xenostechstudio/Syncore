<div x-data="{
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    showValidateModal: false,
    showCancelModal: false,
}">
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
                                        <button type="button" @click="showCancelModal = true" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
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
                            @click="showValidateModal = true"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-600 bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 dark:border-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-600"
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

                    {{-- Top form area --}}
                    <div class="p-5">
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $reference ?: 'New Receipt' }}
                        </h1>

                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Left Column --}}
                            <div class="space-y-3">
                                {{-- Source PO --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Source PO</label>
                                    <div class="relative flex-1">
                                        @if($rfqReference)
                                            <a href="{{ route('purchase.orders.edit', $rfqId) }}" wire:navigate class="inline-flex items-center gap-1.5 font-mono text-sm text-zinc-900 hover:text-zinc-700 dark:text-zinc-100 dark:hover:text-zinc-300">
                                                {{ $rfqReference }}
                                                <flux:icon name="arrow-top-right-on-square" class="size-3.5 text-zinc-400" />
                                            </a>
                                        @else
                                            <span class="text-sm text-zinc-400">—</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Supplier --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Supplier</label>
                                    <div class="relative flex-1">
                                        <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $supplierName ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="space-y-3">
                                {{-- Warehouse --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Warehouse <span class="text-red-500">*</span></label>
                                    <div class="relative flex-1">
                                        @if($receiptState->canEdit())
                                            <div class="relative" x-data="{ open: false }">
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                                >
                                                    @php $selectedWh = $warehouses->firstWhere('id', $warehouseId); @endphp
                                                    @if($selectedWh)
                                                        <span class="font-normal text-zinc-900 dark:text-zinc-100">{{ $selectedWh->name }}</span>
                                                    @else
                                                        <span class="text-zinc-400">Select warehouse...</span>
                                                    @endif
                                                    <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                </button>
                                                <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                                    @foreach($warehouses as $warehouse)
                                                        <button type="button" wire:click="$set('warehouseId', {{ $warehouse->id }})" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                            {{ $warehouse->name }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $warehouses->firstWhere('id', $warehouseId)?->name ?? '—' }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Received Date --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-32 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Received On</label>
                                    <div class="relative flex-1">
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                            {{ $receivedAt ?: 'Pending validation' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Lines Table --}}
                    <div class="border-y border-zinc-100 dark:border-zinc-800">
                        <div class="overflow-visible">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                        <th class="w-24 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ordered</th>
                                        <th class="w-32 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Already Received</th>
                                        <th class="w-32 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Receiving Now</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @forelse($lines as $idx => $line)
                                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $line['product_name'] }}</div>
                                                @if(!empty($line['product_sku']))
                                                    <div class="font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $line['product_sku'] }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm text-zinc-700 tabular-nums dark:text-zinc-300">
                                                {{ rtrim(rtrim(number_format($line['quantity_ordered'], 2), '0'), '.') }}
                                            </td>
                                            <td class="px-3 py-3 text-right text-sm text-zinc-500 tabular-nums dark:text-zinc-400">
                                                {{ rtrim(rtrim(number_format($line['quantity_already_received'], 2), '0'), '.') }}
                                            </td>
                                            <td class="px-3 py-3 text-right">
                                                @if($receiptState->canEdit())
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        wire:model="lines.{{ $idx }}.quantity_received"
                                                        class="w-full rounded-md border border-zinc-200 bg-white px-2 py-1 text-right text-sm text-zinc-900 tabular-nums focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
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
                                            <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                                Nothing left to receive on this PO.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="p-5">
                        <h3 class="mb-3 text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Notes</h3>
                        @if($receiptState->canEdit())
                            <textarea
                                wire:model="notes"
                                rows="3"
                                placeholder="Optional notes about this receipt…"
                                class="w-full resize-none border-0 bg-transparent px-0 py-0 text-sm text-zinc-700 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500"
                            ></textarea>
                        @else
                            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $notes ?: '—' }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right rail: chatter --}}
            <div class="lg:col-span-3">
                <x-ui.chatter-forms :showMessage="false" />

                {{-- Activity Timeline --}}
                @if($receiptId)
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            @if($activities->isNotEmpty() && $activities->first()['created_at']->isToday())
                                Today
                            @else
                                Activity
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    <div class="space-y-3">
                        @if($activities->isNotEmpty())
                            @foreach($activities as $item)
                                @if(($item['type'] ?? null) === 'note')
                                    <x-ui.note-item :note="$item['data']" />
                                @else
                                    <x-ui.activity-item :activity="$item['data']" emptyMessage="Receipt created" />
                                @endif
                            @endforeach
                        @else
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ now()->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Receipt created</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once you save</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Validate confirmation modal --}}
    <x-ui.confirm-modal show="showValidateModal">
        <x-slot:icon>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <flux:icon name="inbox-arrow-down" class="size-7" />
            </div>
        </x-slot:icon>

        <x-slot:title>Validate this receipt?</x-slot:title>

        <x-slot:description>
            Validating will move the received quantities into stock at
            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $warehouses->firstWhere('id', $warehouseId)?->name ?? 'the selected warehouse' }}</span>
            and update the source purchase order. You can still cancel a validated receipt later, which reverses the stock.
        </x-slot:description>

        <x-slot:actions>
            <button
                type="button"
                @click="showValidateModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Not yet
            </button>
            <button
                type="button"
                wire:click="validateReceipt"
                @click="showValidateModal = false"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600"
            >
                Validate &amp; Move Stock
            </button>
        </x-slot:actions>
    </x-ui.confirm-modal>

    {{-- Cancel confirmation modal --}}
    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                <flux:icon name="exclamation-triangle" class="size-7" />
            </div>
        </x-slot:icon>

        <x-slot:title>Cancel this receipt?</x-slot:title>

        <x-slot:description>
            @if($receiptState === \App\Enums\PurchaseReceiptState::VALIDATED)
                This will reverse the stock movement and decrement the received quantities on the source purchase order.
            @else
                This will mark the draft receipt as cancelled. Nothing has been moved into stock yet, so no inventory will be affected.
            @endif
        </x-slot:description>

        <x-slot:actions>
            <button
                type="button"
                @click="showCancelModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Keep receipt
            </button>
            <button
                type="button"
                wire:click="cancelReceipt"
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Cancel receipt
            </button>
        </x-slot:actions>
    </x-ui.confirm-modal>
</div>
