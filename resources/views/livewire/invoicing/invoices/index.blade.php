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

        @if(session('warning'))
            <x-ui.alert type="warning" :duration="6000">
                {{ session('warning') }}
            </x-ui.alert>
        @endif

        @if(session('info'))
            <x-ui.alert type="info" :duration="5000">
                {{ session('info') }}
            </x-ui.alert>
        @endif
    </div>

    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
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
                        <button type="button" wire:click="openImportModal" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import records</span>
                        </button>
                        <button type="button" wire:click="exportSelected" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
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
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <span>{{ count($selected) }} selected</span>
                            <flux:icon name="x-mark" class="size-3.5" />
                        </button>

                        <div class="h-5 w-px bg-zinc-200 dark:bg-zinc-700"></div>

                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="printer" class="size-4" />
                            <span>Print</span>
                        </button>

                        <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Export</span>
                        </button>

                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="ellipsis-horizontal" class="size-4" />
                            </button>
                            <flux:menu class="w-48">
                                <button type="button" wire:click="bulkMarkSent" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="envelope" class="size-4" />
                                    <span>Mark as Sent</span>
                                </button>
                                <button type="button" wire:click="bulkMarkPaid" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="check-circle" class="size-4" />
                                    <span>Mark as Paid</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" wire:click="confirmBulkDelete" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    {{-- Search Input with Arrow Down Dropdown --}}
                    <x-ui.searchbox-dropdown placeholder="Search invoices..." widthClass="w-[520px]" width="520px">
                        <x-slot:badge>
                            @if($myInvoice)
                                <div class="flex items-center">
                                    <span class="inline-flex h-6 items-center gap-1.5 rounded-md bg-zinc-900 px-2 text-[10px] font-semibold text-white shadow-sm dark:bg-zinc-100 dark:text-zinc-900">
                                        <flux:icon name="user" class="size-3 text-white/70 dark:text-zinc-700" />
                                        <span>My Invoices</span>
                                        <button
                                            type="button"
                                            onclick="event.stopPropagation()"
                                            wire:click="$set('myInvoice', false)"
                                            class="-mr-0.5 inline-flex h-4 w-4 items-center justify-center rounded-md text-zinc-400 hover:bg-zinc-200 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                        >
                                            <flux:icon name="x-mark" class="size-3" />
                                        </button>
                                    </span>
                                </div>
                            @endif
                        </x-slot:badge>
                        <div class="flex flex-col gap-4 p-3 md:flex-row">
                            {{-- Filters column --}}
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="funnel" class="size-3.5" />
                                    <span>Filters</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('myInvoice', true)" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>My Invoices</span>
                                        @if($myInvoice)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('myInvoice', false)" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>All Invoices</span>
                                        @if(!$myInvoice)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <div class="my-2 border-t border-zinc-100 dark:border-zinc-700"></div>
                                    <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>All Status</span>
                                        @if(empty($status))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'draft')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-500"></span>
                                            <span>Draft</span>
                                        </div>
                                        @if($status === 'draft')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'sent')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                            <span>Sent</span>
                                        </div>
                                        @if($status === 'sent')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'partial')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            <span>Partial</span>
                                        </div>
                                        @if($status === 'partial')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'paid')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Paid</span>
                                        </div>
                                        @if($status === 'paid')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'overdue')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            <span>Overdue</span>
                                        </div>
                                        @if($status === 'overdue')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                </div>
                            </div>

                            {{-- Sort column --}}
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:px-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="arrows-up-down" class="size-3.5" />
                                    <span>Sort By</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('sort', 'latest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Latest</span>
                                        @if($sort === 'latest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('sort', 'oldest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Oldest</span>
                                        @if($sort === 'oldest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('sort', 'due_date')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Due Date</span>
                                        @if($sort === 'due_date')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('sort', 'total_high')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Total: High to Low</span>
                                        @if($sort === 'total_high')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('sort', 'total_low')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Total: Low to High</span>
                                        @if($sort === 'total_low')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                </div>
                            </div>

                            {{-- Group column --}}
                            <div class="flex-1 md:pl-3">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="rectangle-group" class="size-3.5" />
                                    <span>Group By</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('groupBy', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>None</span>
                                        @if(empty($groupBy))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('groupBy', 'salesperson')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Salesperson</span>
                                        @if($groupBy === 'salesperson')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('groupBy', 'customer')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Customer</span>
                                        @if($groupBy === 'customer')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('groupBy', 'date')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Invoice Date</span>
                                        @if($groupBy === 'date')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('groupBy', 'status')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>Status</span>
                                        @if($groupBy === 'status')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-ui.searchbox-dropdown>
                @endif
            </div>

            {{-- Right Group: Pagination Info + View Toggle --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $invoices->firstItem() ?? 0 }}-{{ $invoices->lastItem() ?? 0 }}/{{ $invoices->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="goToPreviousPage"
                            @disabled($invoices->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="goToNextPage"
                            @disabled(!$invoices->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- Stats Toggle --}}
                <div class="flex h-9 items-center rounded-lg border border-zinc-200 p-0.5 dark:border-zinc-700">
                    <button 
                        type="button"
                        wire:click="toggleStats"
                        class="{{ $showStats ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md p-1.5 transition-colors"
                        title="{{ $showStats ? 'Hide statistics' : 'Show statistics' }}"
                    >
                        <flux:icon name="chart-bar" class="size-[18px]" />
                    </button>
                </div>

                <x-ui.view-toggle :view="$view" :views="['list', 'grid', 'kanban']" />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        {{-- Statistics Cards --}}
        @if($showStats && $statistics && !$invoices->isEmpty())
            <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="document-text" class="size-4 text-zinc-400 dark:text-zinc-500" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Invoices</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['total']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($statistics['total_amount'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="clock" class="size-4 text-blue-500 dark:text-blue-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Awaiting Payment</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['sent'] + $statistics['partial']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $statistics['sent'] }} sent, {{ $statistics['partial'] }} partial</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="check-circle" class="size-4 text-emerald-500 dark:text-emerald-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Paid</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['paid']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($statistics['paid_amount'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="exclamation-circle" class="size-4 text-red-500 dark:text-red-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Overdue</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['overdue']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($statistics['overdue_amount'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($invoices->isEmpty())
            {{-- Empty State --}}
            <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="document-text" class="size-8 text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">No invoices found</p>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Get started by creating a new invoice</p>
                    </div>
                    <a href="{{ route('invoicing.invoices.create') }}" wire:navigate class="mt-2 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="plus" class="size-4" />
                        New Invoice
                    </a>
                </div>
            </div>
        @else
        @if($view === 'list')
            {{-- Table View --}}
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th scope="col" class="relative w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                {{-- Header selection indicator --}}
                                @if(count($selected) > 0)
                                    <div class="absolute inset-y-0 left-0 w-0.5 bg-zinc-900 dark:bg-zinc-100"></div>
                                @endif
                                <input 
                                    type="checkbox" 
                                    wire:model.live="selectAll"
                                    class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600 {{ count($selected) > 0 && !$selectAll ? 'indeterminate' : '' }}"
                                    @if(count($selected) > 0 && count($selected) < $invoices->count()) 
                                        x-init="$el.indeterminate = true"
                                    @endif
                                />
                            </th>
                            @if($visibleColumns['invoice_number'])
                                <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Invoice #</th>
                            @endif
                            @if($visibleColumns['customer'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Customer</th>
                            @endif
                            @if($visibleColumns['salesperson'])
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Salesperson</th>
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
                                            <input type="checkbox" wire:model.live="visibleColumns.salesperson" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>Salesperson</span>
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
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($invoices as $invoice)
                            @php $isSelected = in_array($invoice->id, $selected); @endphp
                            <tr 
                                onclick="window.location.href='{{ route('invoicing.invoices.edit', $invoice->id) }}'"
                                class="group cursor-pointer transition-all duration-150 {{ $isSelected 
                                    ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' 
                                    : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                            >
                                <td class="relative py-3 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    {{-- Selection indicator bar --}}
                                    <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selected" 
                                        value="{{ $invoice->id }}" 
                                        class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}"
                                    >
                                </td>
                                @if($visibleColumns['invoice_number'])
                                    <td class="py-3 pl-2 pr-4">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['customer'])
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $invoice->customer->name ?? '-' }}</span>
                                            @if($invoice->salesOrder)
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->salesOrder->order_number }}</span>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                                @if($visibleColumns['salesperson'])
                                    <td class="px-4 py-3" onclick="event.stopPropagation()">
                                        @if($invoice->user)
                                            <flux:dropdown position="bottom" align="start">
                                                <button type="button" class="flex items-center gap-2 rounded-md px-1.5 py-1 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-100 text-[11px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
                                                        {{ $invoice->user->initials() }}
                                                    </div>
                                                    <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ $invoice->user->name }}</span>
                                                </button>

                                                <flux:menu class="w-72">
                                                    <div class="px-3 py-2">
                                                        <div class="flex items-start gap-3">
                                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-sm font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                                                {{ $invoice->user->initials() }}
                                                            </div>
                                                            <div class="min-w-0">
                                                                <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoice->user->name }}</p>
                                                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->user->email }}</p>
                                                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->user->phone ?? '—' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <flux:menu.separator />
                                                    <a href="{{ route('settings.users.edit', $invoice->user->id) }}" wire:navigate class="flex w-full items-center justify-between px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                                        <span>View Profile</span>
                                                        <flux:icon name="arrow-up-right" class="size-4 text-zinc-400" />
                                                    </a>
                                                </flux:menu>
                                            </flux:dropdown>
                                        @else
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">-</span>
                                        @endif
                                    </td>
                                @endif
                                @if($visibleColumns['invoice_date'])
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->invoice_date?->format('M d, Y') ?? '-' }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['due_date'])
                                    <td class="px-4 py-3">
                                        @php
                                            $isOverdue = $invoice->due_date && $invoice->due_date->isPast() && !in_array($invoice->status, ['paid', 'cancelled']);
                                        @endphp
                                        <span class="text-sm {{ $isOverdue ? 'font-medium text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                                            {{ $invoice->due_date?->format('M d, Y') ?? '-' }}
                                        </span>
                                        @if($isOverdue)
                                            <span class="ml-1 text-xs text-red-500">Overdue</span>
                                        @endif
                                    </td>
                                @endif
                                @if($visibleColumns['total'])
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
                                    </td>
                                @endif
                                @if($visibleColumns['status'])
                                    <td class="px-4 py-3">
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
                                <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if($invoices->count() > 0)
                        @php
                            $visibleCount = 1 + ($visibleColumns['invoice_number'] ? 1 : 0) + ($visibleColumns['customer'] ? 1 : 0) + ($visibleColumns['salesperson'] ? 1 : 0) + ($visibleColumns['invoice_date'] ? 1 : 0) + ($visibleColumns['due_date'] ? 1 : 0);
                            $afterTotalCount = 1 + ($visibleColumns['status'] ? 1 : 0);
                        @endphp
                        <tfoot class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950">
                            <tr>
                                <td colspan="{{ $visibleCount }}" class="py-3 pl-4 pr-4 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Total</td>
                                @if($visibleColumns['total'])
                                    <td class="px-4 py-3 text-right text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                        Rp {{ number_format($invoices->sum('total'), 0, ',', '.') }}
                                    </td>
                                @endif
                                <td colspan="{{ $afterTotalCount }}"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @elseif($view === 'grid')
            {{-- Grid View --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($invoices as $invoice)
                    <a href="{{ route('invoicing.invoices.edit', $invoice->id) }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
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
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <div class="text-xs text-zinc-400 dark:text-zinc-500">
                                Due: {{ $invoice->due_date?->format('M d, Y') ?? '-' }}
                            </div>
                            <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoice->total, 0, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @elseif($view === 'kanban')
            {{-- Kanban View --}}
            @php
                $statuses = [
                    'draft' => ['label' => 'Draft', 'color' => 'zinc', 'headerBg' => 'bg-zinc-100 dark:bg-zinc-800'],
                    'sent' => ['label' => 'Sent', 'color' => 'blue', 'headerBg' => 'bg-blue-50 dark:bg-blue-900/20'],
                    'partial' => ['label' => 'Partial', 'color' => 'amber', 'headerBg' => 'bg-amber-50 dark:bg-amber-900/20'],
                    'paid' => ['label' => 'Paid', 'color' => 'emerald', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                    'overdue' => ['label' => 'Overdue', 'color' => 'red', 'headerBg' => 'bg-red-50 dark:bg-red-900/20'],
                    'cancelled' => ['label' => 'Cancelled', 'color' => 'zinc', 'headerBg' => 'bg-zinc-100 dark:bg-zinc-800'],
                ];
                $invoicesByStatus = $invoices->groupBy('status');
            @endphp
            <div class="flex gap-4 overflow-x-auto pb-4">
                @foreach($statuses as $statusKey => $statusInfo)
                    <div class="flex w-72 flex-shrink-0 flex-col rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
                        {{-- Column Header --}}
                        <div class="flex items-center justify-between rounded-t-lg {{ $statusInfo['headerBg'] }} px-3 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-{{ $statusInfo['color'] }}-500"></span>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $statusInfo['label'] }}</span>
                                <span class="rounded-full bg-white px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ $invoicesByStatus->get($statusKey)?->count() ?? 0 }}
                                </span>
                            </div>
                        </div>

                        {{-- Column Cards --}}
                        <div class="flex flex-1 flex-col gap-2 p-2">
                            @forelse($invoicesByStatus->get($statusKey, collect()) as $invoice)
                                @php
                                    $paidPercent = $invoice->total > 0 ? round(($invoice->paid_amount / $invoice->total) * 100) : 0;
                                    $isOverdue = $invoice->due_date && $invoice->due_date->isPast() && !in_array($invoice->status, ['paid', 'cancelled']);
                                @endphp
                                <a 
                                    href="{{ route('invoicing.invoices.edit', $invoice->id) }}"
                                    wire:navigate
                                    class="group rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                >
                                    <div class="mb-2 flex items-start justify-between">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->invoice_date?->format('M d') }}</span>
                                    </div>
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                                            <svg class="size-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ $invoice->customer->name ?? '-' }}</span>
                                    </div>
                                    
                                    {{-- Due Date Warning --}}
                                    @if($isOverdue)
                                        <div class="mb-2 flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">
                                            <flux:icon name="exclamation-circle" class="size-3.5" />
                                            <span>Overdue {{ $invoice->due_date->diffForHumans() }}</span>
                                        </div>
                                    @elseif($invoice->due_date && $statusKey !== 'paid')
                                        <div class="mb-2 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                            <flux:icon name="calendar" class="size-3.5" />
                                            <span>Due {{ $invoice->due_date->format('M d, Y') }}</span>
                                        </div>
                                    @endif

                                    {{-- Payment Progress for Partial --}}
                                    @if($statusKey === 'partial' && $paidPercent > 0)
                                        <div class="mb-3 space-y-1.5">
                                            <div class="flex items-center gap-1.5">
                                                <flux:icon name="banknotes" class="size-3 text-amber-500" />
                                                <div class="h-1 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-600">
                                                    <div class="h-full rounded-full bg-amber-500" style="width: {{ $paidPercent }}%"></div>
                                                </div>
                                                <span class="text-[9px] font-medium text-zinc-500">{{ $paidPercent }}%</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
                                        @if($invoice->user)
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-{{ $statusInfo['color'] }}-100 text-xs font-medium text-{{ $statusInfo['color'] }}-700 dark:bg-{{ $statusInfo['color'] }}-900/30 dark:text-{{ $statusInfo['color'] }}-400">
                                                {{ strtoupper(substr($invoice->user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-1 items-center justify-center py-8">
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">No invoices</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Column Footer --}}
                        @if($invoicesByStatus->get($statusKey)?->count() > 0)
                            <div class="border-t border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    Total: Rp {{ number_format($invoicesByStatus->get($statusKey)->sum('total'), 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @isset($showDeleteConfirm)
        <x-ui.delete-confirm-modal 
            wire:model="showDeleteConfirm"
            :validation="$deleteValidation ?? []"
            title="Confirm Delete"
            itemLabel="invoices"
        />
    @endisset

    {{-- Import Modal --}}
    <x-ui.import-modal
        wire:model="showImportModal"
        title="Import Invoices"
        :livewire="true"
        :result="$this->importResult"
        :importErrors="$this->importErrors"
    />
</div>
