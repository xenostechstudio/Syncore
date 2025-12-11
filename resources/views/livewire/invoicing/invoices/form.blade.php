<div x-data="{ 
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left: Back + Title --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('invoicing.invoices.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    {{ $invoiceId ? ($invoice_number ?? 'Invoice') : 'New Invoice' }}
                </span>
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

    {{-- Action Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                    @if($invoiceId)
                        <button type="button" wire:click="delete" wire:confirm="Are you sure you want to delete this invoice?" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400">
                            <flux:icon name="trash" class="size-4" />
                            Delete
                        </button>
                    @endif
                </div>

                {{-- Status Badge --}}
                <span class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                    {{ ucfirst($status) }}
                </span>
            </div>

            {{-- Chatter Icons --}}
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
            {{-- Left: Invoice Info --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $invoiceId ? ($invoice_number ?? 'Invoice') : 'New Invoice' }}
                                </h2>
                                @if($invoiceId)
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Invoice details and status.</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            {{-- Customer --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Customer <span class="text-red-500">*</span></label>
                                <select wire:model="customer_id" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">Select customer...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Sales Order (optional link) --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Sales Order</label>
                                <select wire:model="sales_order_id" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">None</option>
                                    @foreach($salesOrders as $order)
                                        <option value="{{ $order->id }}">{{ $order->order_number }} - {{ $order->customer->name ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Invoice Date --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Invoice Date <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="invoice_date" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Due Date --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Due Date</label>
                                <input type="date" wire:model="due_date" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Paid Amount --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Paid Amount</label>
                                <input type="number" step="0.01" wire:model="paid_amount" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Paid Date --}}
                            <div class="flex items-center gap-4">
                                <label class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Paid Date</label>
                                <input type="date" wire:model="paid_date" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                            </div>

                            {{-- Terms --}}
                            <div class="col-span-2 flex items-start gap-4">
                                <label class="w-32 shrink-0 pt-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Terms</label>
                                <textarea wire:model="terms" rows="2" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>

                            {{-- Notes --}}
                            <div class="col-span-2 flex items-start gap-4">
                                <label class="w-32 shrink-0 pt-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                                <textarea wire:model="notes" rows="3" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Summary + Items (read-only) --}}
                    @if($invoice && $invoice->items->count())
                        <div class="border-t border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900/60">
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Invoice Lines</h3>
                                <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                    Total: <span class="font-semibold">Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
                                    <thead class="bg-zinc-100 dark:bg-zinc-900">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unit Price</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Discount</th>
                                            <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td class="px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $item->inventoryItem->name ?? '-' }}
                                                </td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-300">
                                                    {{ $item->quantity }}
                                                </td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-300">
                                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-300">
                                                    Rp {{ number_format($item->discount, 0, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-2 text-right text-sm text-zinc-900 dark:text-zinc-100">
                                                    Rp {{ number_format($item->total, 0, ',', '.') }}
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
                        @if($invoiceId)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Invoice history will appear here.</p>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No activity yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
