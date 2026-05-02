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
        />
    </x-slot:header>

    {{-- Status filter strip --}}
    <div class="-mx-4 -mt-6 flex items-center gap-2 overflow-x-auto bg-white px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-950">
        @php
            $tabs = [
                ['value' => 'all',                                              'label' => 'All'],
                ['value' => \App\Enums\PurchaseReceiptState::DRAFT->value,      'label' => 'Draft'],
                ['value' => \App\Enums\PurchaseReceiptState::VALIDATED->value,  'label' => 'Validated'],
                ['value' => \App\Enums\PurchaseReceiptState::CANCELLED->value,  'label' => 'Cancelled'],
            ];
        @endphp
        @foreach($tabs as $tab)
            <button
                type="button"
                wire:click="$set('status', '{{ $tab['value'] }}')"
                class="whitespace-nowrap rounded-md px-3 py-1.5 text-xs font-medium transition-colors
                    {{ $status === $tab['value']
                        ? 'bg-zinc-900 text-zinc-50 dark:bg-zinc-100 dark:text-zinc-900'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        <div class="overflow-hidden bg-white dark:bg-zinc-950">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                    <tr>
                        <th scope="col" class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Receipt</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Source PO</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Supplier</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Warehouse</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Received</th>
                        <th scope="col" class="py-3 pl-4 pr-4 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pr-6 lg:pr-8 dark:text-zinc-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                    @forelse($receipts as $receipt)
                        <tr
                            onclick="window.location.href='{{ route('purchase.receipts.edit', $receipt->id) }}'"
                            class="group cursor-pointer transition-all duration-150 hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="whitespace-nowrap py-4 pl-4 pr-4 sm:pl-6 lg:pl-8">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon name="inbox-arrow-down" class="size-5 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <span class="font-mono text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $receipt->reference }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                @if($receipt->purchaseRfq)
                                    <span class="font-mono">{{ $receipt->purchaseRfq->reference }}</span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $receipt->supplier?->name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $receipt->warehouse?->name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $receipt->received_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-4 text-right sm:pr-6 lg:pr-8">
                                <x-ui.status-badge :status="$receipt->state" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon name="inbox-arrow-down" class="size-6 text-zinc-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No receipts found</p>
                                        <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">
                                            Open a Purchase Order and click "Receive Goods" to record a delivery.
                                        </p>
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
