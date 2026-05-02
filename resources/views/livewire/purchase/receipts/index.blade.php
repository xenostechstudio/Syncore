<div>
    <x-ui.flash />

    <x-slot:header>
        <x-ui.index-header
            :bare="true"
            title="Goods Receipts"
            :paginator="$receipts"
            :view="$view"
            :views="['list']"
            searchPlaceholder="Search by GRN, PO, or supplier..."
        >
            <x-slot:filters>
                {{-- Status --}}
                <div class="flex-1 p-1">
                    <div class="mb-2 flex items-center gap-1.5">
                        <flux:icon name="funnel" class="size-4 text-zinc-400" />
                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</span>
                    </div>
                    <div class="space-y-1">
                        @php
                            $statusOpts = [
                                ['value' => 'all',                                              'label' => 'All Receipts'],
                                ['value' => \App\Enums\PurchaseReceiptState::DRAFT->value,      'label' => 'Draft'],
                                ['value' => \App\Enums\PurchaseReceiptState::VALIDATED->value,  'label' => 'Validated'],
                                ['value' => \App\Enums\PurchaseReceiptState::CANCELLED->value,  'label' => 'Cancelled'],
                            ];
                        @endphp
                        @foreach($statusOpts as $opt)
                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                <input type="radio" wire:model.live="status" value="{{ $opt['value'] }}" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span>{{ $opt['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Sort --}}
                <div class="flex-1 p-1">
                    <div class="mb-2 flex items-center gap-1.5">
                        <flux:icon name="arrows-up-down" class="size-4 text-zinc-400" />
                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Sort By</span>
                    </div>
                    <div class="space-y-1">
                        @foreach([
                            'latest' => 'Latest',
                            'oldest' => 'Oldest',
                        ] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                <input type="radio" wire:model.live="sort" value="{{ $value }}" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </x-slot:filters>
        </x-ui.index-header>
    </x-slot:header>

    {{-- Table --}}
    <div>
        <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                    <tr>
                        @if($visibleColumns['reference'])
                            <th class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Receipt</th>
                        @endif
                        @if($visibleColumns['source_po'])
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Source PO</th>
                        @endif
                        @if($visibleColumns['supplier'])
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Supplier</th>
                        @endif
                        @if($visibleColumns['warehouse'])
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Warehouse</th>
                        @endif
                        @if($visibleColumns['received_at'])
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Received</th>
                        @endif
                        @if($visibleColumns['status'])
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                        @endif
                        <th class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8">
                            <flux:dropdown position="bottom" align="end">
                                <button class="flex items-center justify-center rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="adjustments-horizontal" class="size-4" />
                                </button>
                                <flux:menu class="w-48">
                                    <div class="px-2 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Toggle Columns</div>
                                    <flux:menu.separator />
                                    @foreach([
                                        'reference' => 'Receipt',
                                        'source_po' => 'Source PO',
                                        'supplier' => 'Supplier',
                                        'warehouse' => 'Warehouse',
                                        'received_at' => 'Received',
                                        'status' => 'Status',
                                    ] as $key => $label)
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.{{ $key }}" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </flux:menu>
                            </flux:dropdown>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($receipts as $receipt)
                        <tr
                            onclick="window.location.href='{{ route('purchase.receipts.edit', $receipt->id) }}'"
                            class="group cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            @if($visibleColumns['reference'])
                                <td class="py-4 pl-4 pr-4 sm:pl-6 lg:pl-8">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon name="inbox-arrow-down" class="size-5 text-zinc-500 dark:text-zinc-400" />
                                        </div>
                                        <span class="font-mono text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $receipt->reference }}</span>
                                    </div>
                                </td>
                            @endif
                            @if($visibleColumns['source_po'])
                                <td class="px-4 py-4">
                                    @if($receipt->purchaseRfq)
                                        <span class="font-mono text-sm text-zinc-600 dark:text-zinc-400">{{ $receipt->purchaseRfq->reference }}</span>
                                    @else
                                        <span class="text-sm text-zinc-400 dark:text-zinc-600">—</span>
                                    @endif
                                </td>
                            @endif
                            @if($visibleColumns['supplier'])
                                <td class="px-4 py-4">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $receipt->supplier?->name ?? '—' }}</span>
                                </td>
                            @endif
                            @if($visibleColumns['warehouse'])
                                <td class="px-4 py-4">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $receipt->warehouse?->name ?? '—' }}</span>
                                </td>
                            @endif
                            @if($visibleColumns['received_at'])
                                <td class="px-4 py-4">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $receipt->received_at?->format('M d, Y') ?? '—' }}</span>
                                </td>
                            @endif
                            @if($visibleColumns['status'])
                                <td class="px-4 py-4">
                                    <x-ui.status-badge :status="$receipt->state" />
                                </td>
                            @endif
                            <td class="py-4 pr-4 sm:pr-6 lg:pr-8" onclick="event.stopPropagation()">
                                <a href="{{ route('purchase.receipts.edit', $receipt->id) }}" wire:navigate class="inline-flex rounded-md p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                    <flux:icon name="pencil-square" class="size-4" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon name="inbox-arrow-down" class="size-6 text-zinc-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No receipts found</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Open a Purchase Order and click "Receive Goods" to record a delivery.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
