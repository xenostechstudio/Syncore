<div>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">
                {{ session('error') }}
            </x-ui.alert>
        @endif
    </div>

    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: New Button, Title, Gear --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('invoicing.invoices.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Invoices</span>
                
                {{-- Actions Menu (Gear) --}}
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import records</span>
                        </button>
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export All</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center Group: Search or Selection Toolbar --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    {{-- Selection Toolbar --}}
                    <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                            <flux:icon name="x-mark" class="size-4" />
                            <span>{{ count($selected) }} Selected</span>
                        </button>
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="printer" class="size-4" />
                            <span>Print</span>
                        </button>
                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                                <span>Actions</span>
                                <flux:icon name="chevron-down" class="size-3" />
                            </button>
                            <flux:menu class="w-48">
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="envelope" class="size-4" />
                                    <span>Send Email</span>
                                </button>
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="arrow-up-tray" class="size-4" />
                                    <span>Export</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" wire:click="deleteSelected" wire:confirm="Are you sure you want to delete the selected invoices?" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    {{-- Search Input with Filter Dropdown --}}
                    <div class="relative flex h-9 w-[480px] items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search invoices..." 
                            class="h-full w-full border-0 bg-transparent pl-9 pr-10 text-sm outline-none focus:ring-0" 
                        />
                        <flux:dropdown position="bottom" align="end">
                            <button class="absolute right-0 top-0 flex h-full items-center border-l border-zinc-200 px-2.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                <flux:icon name="chevron-down" class="size-4" />
                            </button>
                            <flux:menu class="w-48">
                                <div class="px-2 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Filter by Status</div>
                                <flux:menu.separator />
                                <button type="button" wire:click="$set('status', '')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm {{ $status === '' ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <span>All</span>
                                </button>
                                <button type="button" wire:click="$set('status', 'draft')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm {{ $status === 'draft' ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
                                    <span>Draft</span>
                                </button>
                                <button type="button" wire:click="$set('status', 'sent')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm {{ $status === 'sent' ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                    <span>Sent</span>
                                </button>
                                <button type="button" wire:click="$set('status', 'partial')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm {{ $status === 'partial' ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    <span>Partial</span>
                                </button>
                                <button type="button" wire:click="$set('status', 'paid')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm {{ $status === 'paid' ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    <span>Paid</span>
                                </button>
                                <button type="button" wire:click="$set('status', 'overdue')" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm {{ $status === 'overdue' ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                    <span>Overdue</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @endif
            </div>

            {{-- Right Group: View Toggle --}}
            <div class="flex items-center gap-2">
                <div class="flex items-center rounded-lg border border-zinc-200 bg-white p-0.5 dark:border-zinc-700 dark:bg-zinc-800">
                    <button 
                        wire:click="setView('list')"
                        class="rounded-md p-1.5 transition-colors {{ $view === 'list' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }}"
                    >
                        <flux:icon name="list-bullet" class="size-4" />
                    </button>
                    <button 
                        wire:click="setView('grid')"
                        class="rounded-md p-1.5 transition-colors {{ $view === 'grid' ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }}"
                    >
                        <flux:icon name="squares-2x2" class="size-4" />
                    </button>
                </div>
            </div>
        </div>
    </x-slot:header>

    {{-- Content --}}
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        @if($view === 'list')
            {{-- Table View --}}
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600" />
                            </th>
                            @if($visibleColumns['invoice_number'])
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice #</th>
                            @endif
                            @if($visibleColumns['customer'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Customer</th>
                            @endif
                            @if($visibleColumns['invoice_date'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice Date</th>
                            @endif
                            @if($visibleColumns['due_date'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Due Date</th>
                            @endif
                            @if($visibleColumns['total'])
                                <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</th>
                            @endif
                            @if($visibleColumns['status'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                            @endif
                            <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8">
                                {{-- Column Config --}}
                                <flux:dropdown position="bottom" align="end">
                                    <button class="flex items-center justify-center rounded p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                        <flux:icon name="adjustments-horizontal" class="size-4" />
                                    </button>
                                    <flux:menu class="w-48">
                                        <div class="px-2 py-1.5 text-xs font-medium text-zinc-500 dark:text-zinc-400">Toggle Columns</div>
                                        <flux:menu.separator />
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.invoice_number" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Invoice #</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.customer" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Customer</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.invoice_date" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Invoice Date</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.due_date" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Due Date</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.total" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Total</span>
                                        </label>
                                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                            <input type="checkbox" wire:model.live="visibleColumns.status" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Status</span>
                                        </label>
                                    </flux:menu>
                                </flux:dropdown>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                        @forelse($invoices as $invoice)
                            <tr 
                                onclick="window.location.href='{{ route('invoicing.invoices.edit', $invoice->id) }}'"
                                class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <input type="checkbox" wire:model.live="selected" value="{{ $invoice->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600" />
                                </td>
                                @if($visibleColumns['invoice_number'])
                                    <td class="py-4 pl-2 pr-4">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['customer'])
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $invoice->customer->name ?? '-' }}</span>
                                            @if($invoice->salesOrder)
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->salesOrder->order_number }}</span>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['invoice_date'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->invoice_date?->format('M d, Y') ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['due_date'])
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->due_date?->format('M d, Y') ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['total'])
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-4">
                                        @php
                                            $statusConfig = match($invoice->status) {
                                                'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => 'Draft'],
                                                'sent' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'label' => 'Sent'],
                                                'paid' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'label' => 'Paid'],
                                                'partial' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'label' => 'Partial'],
                                                'overdue' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'label' => 'Overdue'],
                                                'cancelled' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-500 dark:text-zinc-500', 'label' => 'Cancelled'],
                                                default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'label' => ucfirst($invoice->status)],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                            {{ $statusConfig['label'] }}
                                        </span>
                                    </td>
                                @endif
                                <td class="py-4 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="size-12 text-zinc-300 dark:text-zinc-600" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                        </svg>
                                        <p>No invoices found</p>
                                        <a href="{{ route('invoicing.invoices.create') }}" wire:navigate class="text-sm font-medium text-zinc-900 hover:underline dark:text-zinc-100">
                                            Create your first invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($invoices->count() > 0)
                        @php
                            $totalColspan = 1 + ($visibleColumns['invoice_number'] ? 1 : 0) + ($visibleColumns['customer'] ? 1 : 0) + ($visibleColumns['invoice_date'] ? 1 : 0) + ($visibleColumns['due_date'] ? 1 : 0) + ($visibleColumns['total'] ? 1 : 0) + ($visibleColumns['status'] ? 1 : 0) + 1;
                        @endphp
                        <tfoot class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                            <tr>
                                <td colspan="{{ $totalColspan }}" class="py-3 pl-4 pr-4 sm:pl-6 lg:pl-8">
                                    <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
                                        {{-- Left: Stats --}}
                                        <div class="flex flex-wrap items-center gap-4">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-zinc-500 dark:text-zinc-400">Total:</span>
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['total'] ?? 0 }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
                                                <span class="text-zinc-500 dark:text-zinc-400">Draft:</span>
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['draft'] ?? 0 }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                                <span class="text-zinc-500 dark:text-zinc-400">Sent:</span>
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['sent'] ?? 0 }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                                <span class="text-zinc-500 dark:text-zinc-400">Partial:</span>
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['partial'] ?? 0 }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                <span class="text-zinc-500 dark:text-zinc-400">Paid:</span>
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['paid'] ?? 0 }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                                <span class="text-zinc-500 dark:text-zinc-400">Overdue:</span>
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['overdue'] ?? 0 }}</span>
                                            </div>
                                        </div>
                                        {{-- Right: Total Amount --}}
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Amount:</span>
                                            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($stats['totalAmount'] ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @else
            {{-- Grid View --}}
            <div class="grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:p-6 lg:p-8">
                @forelse($invoices as $invoice)
                    <a href="{{ route('invoicing.invoices.edit', $invoice->id) }}" wire:navigate class="group rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $invoice->customer->name ?? '-' }}</p>
                            </div>
                            @php
                                $statusConfig = match($invoice->status) {
                                    'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                    'sent' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400'],
                                    'paid' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                    'partial' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                    'overdue' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                    default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        <div class="mt-4 flex items-end justify-between">
                            <div class="text-xs text-zinc-400 dark:text-zinc-500">
                                Due: {{ $invoice->due_date?->format('M d, Y') ?? '-' }}
                            </div>
                            <p class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                                Rp {{ number_format($invoice->total, 0, ',', '.') }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        No invoices found
                    </div>
                @endforelse
            </div>
        @endif

        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800 sm:px-6 lg:px-8">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
