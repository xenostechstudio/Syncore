<div>
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
        @endif
    </div>

    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.products.pricelists.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">New</a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Pricelists</span>
            </div>

            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    <div class="flex items-center gap-2">
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200">
                            <flux:icon name="x-mark" class="size-4" />
                            <span>{{ count($selected) }} Selected</span>
                        </button>
                        <button wire:click="deleteSelected" wire:confirm="Are you sure?" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400">
                            <flux:icon name="trash" class="size-4" />
                            Delete
                        </button>
                    </div>
                @else
                    <div class="relative flex h-9 w-[420px] items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search pricelists..." class="h-full w-full border-0 bg-transparent pl-9 pr-3 text-sm outline-none focus:ring-0" />
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $pricelists->firstItem() ?? 0 }}-{{ $pricelists->lastItem() ?? 0 }}/{{ $pricelists->total() }}
                </span>
                <div class="flex items-center gap-0.5">
                    <button type="button" wire:click="goToPreviousPage" @disabled($pricelists->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="chevron-left" class="size-4" />
                    </button>
                    <button type="button" wire:click="goToNextPage" @disabled(!$pricelists->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="chevron-right" class="size-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                <tr>
                    <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800">
                    </th>
                    <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Name</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Code</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Currency</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Discount</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Products</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                    <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($pricelists as $pricelist)
                    <tr onclick="window.location.href='{{ route('inventory.products.pricelists.edit', $pricelist->id) }}'" class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                            <input type="checkbox" wire:model.live="selected" value="{{ $pricelist->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800">
                        </td>
                        <td class="py-4 pl-2 pr-4">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $pricelist->name }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $pricelist->code }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $pricelist->currency }}</span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ number_format($pricelist->discount, 2) }}%</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $pricelist->items_count }}</span>
                        </td>
                        <td class="px-4 py-4">
                            @if($pricelist->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                            @endif
                        </td>
                        <td class="py-4 pr-4 sm:pr-6 lg:pr-8"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon name="tag" class="size-6 text-zinc-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-normal text-zinc-900 dark:text-zinc-100">No pricelists found</p>
                                    <p class="text-xs font-light text-zinc-500 dark:text-zinc-400">Create your first pricelist</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
