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

            {{-- Center Group: Search --}}
            <div class="flex flex-1 items-center justify-center">
                <div class="relative w-full max-w-md">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search invoices..."
                        class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                    />
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                </div>
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

    {{-- Stats Bar --}}
    <div class="-mx-4 border-b border-zinc-200 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-900/50">
        <div class="flex flex-wrap items-center gap-6 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-zinc-500 dark:text-zinc-400">Total:</span>
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['total'] ?? 0 }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
                <span class="text-zinc-500 dark:text-zinc-400">Draft:</span>
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['draft'] ?? 0 }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                <span class="text-zinc-500 dark:text-zinc-400">Sent:</span>
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['sent'] ?? 0 }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-zinc-500 dark:text-zinc-400">Paid:</span>
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['paid'] ?? 0 }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                <span class="text-zinc-500 dark:text-zinc-400">Overdue:</span>
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stats['overdue'] ?? 0 }}</span>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <span class="text-zinc-500 dark:text-zinc-400">Total Amount:</span>
                <span class="font-medium text-emerald-600 dark:text-emerald-400">Rp {{ number_format($stats['totalAmount'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        @if($view === 'list')
            {{-- Table View --}}
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-900">
                        <tr>
                            <th scope="col" class="w-12 px-4 py-3">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800" />
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice #</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Customer</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Due Date</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                        @forelse($invoices as $invoice)
                            <tr class="group transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                <td class="px-4 py-3">
                                    <input type="checkbox" wire:model.live="selected" value="{{ $invoice->id }}" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <a href="{{ route('invoicing.invoices.edit', $invoice->id) }}" wire:navigate class="font-medium text-zinc-900 hover:text-zinc-600 dark:text-zinc-100 dark:hover:text-zinc-300">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->customer->name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->invoice_date?->format('M d, Y') ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $invoice->due_date?->format('M d, Y') ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($invoice->total, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    @php
                                        $statusConfig = match($invoice->status) {
                                            'draft' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                            'sent' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400'],
                                            'paid' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                            'partial' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                            'overdue' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                                            'cancelled' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-500 dark:text-zinc-500'],
                                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
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
                        <tfoot class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                    Rp {{ number_format($invoices->sum('total'), 0, ',', '.') }}
                                </td>
                                <td></td>
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
