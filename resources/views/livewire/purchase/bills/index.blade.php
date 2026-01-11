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

    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group: New Button, Title, Gear --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase.bills.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    Vendor Bills
                </span>
                
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
                            <flux:icon name="x-mark" class="size-4" />
                        </button>

                        <div class="h-5 w-px bg-zinc-200 dark:bg-zinc-700"></div>

                        <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export</span>
                        </button>

                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="ellipsis-horizontal" class="size-4" />
                            </button>

                            <flux:menu class="w-56">
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="printer" class="size-4" />
                                    <span>Print</span>
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
                <flux:dropdown position="bottom" align="center" class="w-[480px]">
                    <div class="relative flex h-9 w-full items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search bills..." 
                            class="h-full w-full border-0 bg-transparent pl-9 pr-10 text-sm outline-none focus:ring-0" 
                        />
                        <button type="button" class="absolute right-0 top-0 flex h-full items-center border-l border-zinc-200 px-2.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:border-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-down" class="size-4" />
                        </button>
                    </div>

                    <flux:menu class="w-[480px]">
                        <div class="flex divide-x divide-zinc-200 dark:divide-zinc-700">
                            {{-- Filters Section --}}
                            <div class="flex-1 p-3">
                                <div class="mb-2 flex items-center gap-1.5">
                                    <flux:icon name="funnel" class="size-4 text-zinc-400" />
                                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</span>
                                </div>
                                <div class="space-y-1">
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>All Status</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="draft" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>Draft</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="pending" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>Pending</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="partial" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>Partially Paid</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="paid" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>Paid</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="overdue" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>Overdue</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="cancelled" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>Cancelled</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </flux:menu>
                </flux:dropdown>
                @endif
            </div>

            {{-- Right Group: Pagination --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $bills->firstItem() ?? 0 }}-{{ $bills->lastItem() ?? 0 }}/{{ $bills->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button 
                            type="button"
                            wire:click="previousPage"
                            @disabled($bills->onFirstPage())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button 
                            type="button"
                            wire:click="nextPage"
                            @disabled(!$bills->hasMorePages())
                            class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        >
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                <tr>
                    <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
                    </th>
                    <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Bill #</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Supplier</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Bill Date</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Due Date</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Balance</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                    <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse($bills as $bill)
                    @php $isSelected = in_array($bill->id, $selected); @endphp
                    <tr 
                        onclick="window.location.href='{{ route('purchase.bills.edit', $bill->id) }}'"
                        class="group cursor-pointer transition-all duration-150 {{ $isSelected 
                            ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' 
                            : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}"
                    >
                        <td class="relative py-4 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                            <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                            <input type="checkbox" wire:model.live="selected" value="{{ $bill->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}">
                        </td>
                        <td class="py-4 pl-2 pr-4">
                            <div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $bill->bill_number }}</span>
                                @if($bill->vendor_reference)
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $bill->vendor_reference }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $bill->supplier->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $bill->bill_date->format('M d, Y') }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $bill->due_date?->format('M d, Y') ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($bill->total, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <span class="text-sm font-medium {{ $bill->balance_due > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Rp {{ number_format($bill->balance_due, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
                                    'pending' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                                    'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
                                    'partial' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
                                    'overdue' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                                    'cancelled' => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$bill->status] ?? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                {{ $bill->state->label() }}
                            </span>
                        </td>
                        <td class="py-4 pr-4 sm:pr-6 lg:pr-8" onclick="event.stopPropagation()">
                            <a href="{{ route('purchase.bills.edit', $bill->id) }}" wire:navigate class="inline-flex rounded-md p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                <flux:icon name="pencil-square" class="size-4" />
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="document-text" class="size-6 text-zinc-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No vendor bills found</p>
                                    <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Try adjusting your search or filters</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Delete Confirmation Modal --}}
    @isset($showDeleteConfirm)
        <x-ui.delete-confirm-modal 
            wire:model="showDeleteConfirm"
            :validation="$deleteValidation ?? []"
            title="Confirm Delete"
            itemLabel="bills"
        />
    @endisset

    {{-- Import Modal --}}
    <x-ui.import-modal
        wire:model="showImportModal"
        title="Import Vendor Bills"
        :livewire="true"
        :result="$this->importResult"
        :importErrors="$this->importErrors"
    />
</div>
