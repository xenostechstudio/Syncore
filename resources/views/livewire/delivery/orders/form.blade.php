<div x-data="{ 
    activeTab: 'items',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    showCancelModal: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('delivery.orders.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Delivery Order</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $deliveryId ? ($delivery_number ?? 'Delivery') : 'New Delivery Order' }}
                        </span>
                        <flux:dropdown position="bottom" align="start">
                            <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                            </button>
                            <flux:menu class="w-40">
                                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-duplicate" class="size-4" />
                                    <span>Duplicate</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="printer" class="size-4" />
                                    <span>Print</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" wire:click="delete" wire:confirm="Are you sure?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:header>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
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

    {{-- Action Buttons Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            {{-- Left: Action Buttons (col-span-9) --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="printer" class="size-4" />
                        Print
                    </button>
                    @if($deliveryId && !in_array($status, ['delivered', 'returned']))
                        <button type="button" @click="showCancelModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="x-mark" class="size-4" />
                            Cancel
                        </button>
                    @endif
                </div>

                {{-- Stepper --}}
                @php
                    $steps = [
                        ['key' => 'pending', 'label' => 'Pending'],
                        ['key' => 'picked', 'label' => 'Picked'],
                        ['key' => 'in_transit', 'label' => 'In Transit'],
                        ['key' => 'delivered', 'label' => 'Delivered'],
                    ];
                    $currentIndex = collect($steps)->search(fn($s) => $s['key'] === $status);
                    if ($currentIndex === false) $currentIndex = 0;
                    $isFailed = $status === 'failed';
                    $isReturned = $status === 'returned';
                @endphp
                @if($isFailed)
                    <span class="inline-flex h-[38px] items-center rounded-lg bg-red-100 px-4 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Failed</span>
                @elseif($isReturned)
                    <span class="inline-flex h-[38px] items-center rounded-lg bg-amber-100 px-4 text-sm font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Returned</span>
                @else
                    <div class="flex items-center">
                        @foreach($steps as $index => $step)
                            @php
                                $isActive = $index === $currentIndex;
                                $isCompleted = $index < $currentIndex;
                                $isPending = $index > $currentIndex;
                                $isFirst = $index === 0;
                            @endphp
                            <div class="relative flex items-center {{ !$isFirst ? '-ml-2' : '' }}" style="z-index: {{ count($steps) - $index }};">
                                <div class="relative flex h-[38px] items-center px-4 {{ $isActive ? 'bg-violet-600 text-white' : '' }} {{ $isCompleted ? 'bg-emerald-500 text-white' : '' }} {{ $isPending ? 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' : '' }}" style="clip-path: polygon({{ $isFirst ? '0 0' : '10px 0' }}, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, {{ $isFirst ? '0 100%' : '10px 100%' }}, {{ $isFirst ? '0 50%' : '0 100%, 10px 50%, 0 0' }});">
                                    <span class="flex items-center gap-1 text-sm font-medium whitespace-nowrap">
                                        @if($isCompleted)<flux:icon name="check" class="size-4" />@endif
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right: Chatter Icons (col-span-3) --}}
            <div class="col-span-3 flex items-center justify-end gap-1">
                <button @click="showSendMessage = !showSendMessage; showLogNote = false; showScheduleActivity = false" :class="showSendMessage ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Send message">
                    <flux:icon name="chat-bubble-left" class="size-5" />
                </button>
                <button @click="showLogNote = !showLogNote; showSendMessage = false; showScheduleActivity = false" :class="showLogNote ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Log note">
                    <flux:icon name="pencil-square" class="size-5" />
                </button>
                <button @click="showScheduleActivity = !showScheduleActivity; showSendMessage = false; showLogNote = false" :class="showScheduleActivity ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Schedule activity">
                    <flux:icon name="clock" class="size-5" />
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left: Delivery Info --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        <div class="mb-6">
                            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $deliveryId ? ($delivery_number ?? 'Delivery Order') : 'New Delivery Order' }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            {{-- Sales Order --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Sales Order <span class="text-red-500">*</span></label>
                                <select wire:model="sales_order_id" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">Select sales order...</option>
                                    @foreach($salesOrders as $order)
                                        <option value="{{ $order->id }}">
                                            {{ $order->order_number }} - {{ $order->customer->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Warehouse --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Warehouse <span class="text-red-500">*</span></label>
                                <select wire:model="warehouse_id" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">Select warehouse...</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Delivery Date --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Delivery Date <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="delivery_date" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Actual Delivery Date --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Actual Date</label>
                                <input type="date" wire:model="actual_delivery_date" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Courier --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Courier</label>
                                <input type="text" wire:model="courier" placeholder="e.g., JNE, J&T" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Tracking Number --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Tracking #</label>
                                <input type="text" wire:model="tracking_number" placeholder="Tracking number" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Recipient Name --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Recipient <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="recipient_name" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Recipient Phone --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
                                <input type="text" wire:model="recipient_phone" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Shipping Address --}}
                            <div class="col-span-2 flex items-start gap-4">
                                <label class="w-32 shrink-0 pt-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Shipping Address</label>
                                <textarea wire:model="shipping_address" rows="3" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>

                            {{-- Notes --}}
                            <div class="col-span-2 flex items-start gap-4">
                                <label class="w-32 shrink-0 pt-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                                <textarea wire:model="notes" rows="3" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Items Summary (read-only, like order lines table style) --}}
                    @if($delivery && $delivery->items->count())
                        <div class="border-t border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                            <h3 class="mb-3 text-sm font-medium text-zinc-800 dark:text-zinc-200">Delivery Lines</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                                    <thead class="bg-zinc-100 dark:bg-zinc-900">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Ordered</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">To Deliver</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Delivered</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                        @foreach($delivery->items as $item)
                                            <tr>
                                                <td class="px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $item->salesOrderItem->product->name ?? '-' }}
                                                </td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-300">
                                                    {{ $item->salesOrderItem->quantity ?? 0 }}
                                                </td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-300">
                                                    {{ $item->quantity_to_deliver }}
                                                </td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-300">
                                                    {{ $item->quantity_delivered }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: Activity --}}
            <div class="lg:col-span-3">
                <div class="sticky top-20 space-y-4">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <h3 class="mb-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Activity</h3>
                        @if($deliveryId)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Delivery order history will appear here.</p>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No activity yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
