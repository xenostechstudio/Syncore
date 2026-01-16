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
            {{-- Left Group: New Button, Title --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.configuration.promotions.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Promotions</span>
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>
                    <flux:menu class="w-48">
                        <button type="button" wire:click="openImportModal" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import</span>
                        </button>
                        <button type="button" wire:click="export" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export{{ count($selected) > 0 ? ' (' . count($selected) . ')' : '' }}</span>
                        </button>
                        <flux:menu.separator />
                        <button type="button" wire:click="downloadTemplate" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="document-arrow-down" class="size-4" />
                            <span>Download Template</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center Group: Search or Selection Actions --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    <div class="flex items-center gap-2">
                        <button wire:click="$set('selected', [])" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200">
                            <flux:icon name="x-mark" class="size-4" />
                            <span>{{ count($selected) }} Selected</span>
                        </button>
                        <div class="h-5 w-px bg-zinc-300 dark:bg-zinc-600"></div>
                        <button wire:click="activateSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:bg-zinc-800 dark:text-emerald-400 dark:hover:bg-emerald-900/20">
                            <flux:icon name="check-circle" class="size-4" />
                            Activate
                        </button>
                        <button wire:click="deactivateSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700">
                            <flux:icon name="pause-circle" class="size-4" />
                            Deactivate
                        </button>
                        <button wire:click="duplicateSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700">
                            <flux:icon name="document-duplicate" class="size-4" />
                            Duplicate
                        </button>
                        <button wire:click="deleteSelected" wire:confirm="Are you sure you want to delete {{ count($selected) }} promotion(s)?" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="trash" class="size-4" />
                            Delete
                        </button>
                    </div>
                @else
                    <x-ui.searchbox-dropdown placeholder="Search promotions..." widthClass="w-[520px]" width="520px">
                        <div class="flex flex-col gap-4 p-3 md:flex-row">
                            {{-- Filters Section --}}
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-1.5">
                                    <flux:icon name="funnel" class="size-4 text-zinc-400" />
                                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</span>
                                </div>
                                <div class="space-y-1">
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="" name="status" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>All</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="active" name="status" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>Active</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="status" value="inactive" name="status" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>Inactive</span>
                                    </label>
                                </div>
                            </div>
                            {{-- Type Section --}}
                            <div class="flex-1 md:px-3">
                                <div class="mb-2 flex items-center gap-1.5">
                                    <flux:icon name="tag" class="size-4 text-zinc-400" />
                                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Type</span>
                                </div>
                                <div class="space-y-1">
                                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <input type="radio" wire:model.live="type" value="" name="type" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                        <span>All Types</span>
                                    </label>
                                    @foreach($types as $value => $label)
                                        <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <input type="radio" wire:model.live="type" value="{{ $value }}" name="type" class="border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-ui.searchbox-dropdown>
                @endif
            </div>

            {{-- Right Group: Pagination + View Toggle --}}
            <div class="flex items-center gap-3">
                {{-- Pagination Info & Navigation --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $promotions->firstItem() ?? 0 }}-{{ $promotions->lastItem() ?? 0 }}/{{ $promotions->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button type="button" wire:click="$set('page', {{ max(1, $page - 1) }})" @disabled($promotions->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" wire:click="$set('page', {{ $page + 1 }})" @disabled(!$promotions->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- View Toggle --}}
                <x-ui.view-toggle :view="$view" :views="['list', 'grid', 'kanban']" />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        @if($promotions->isEmpty())
            {{-- Empty State --}}
            <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="tag" class="size-8 text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">No promotions found</p>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Create your first promotion to get started</p>
                    </div>
                    <a href="{{ route('sales.configuration.promotions.create') }}" wire:navigate class="mt-2 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="plus" class="size-4" />
                        New Promotion
                    </a>
                </div>
            </div>
        @else
            @if($view === 'list')
            {{-- List View --}}
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                <tr>
                    <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800">
                    </th>
                    <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Name</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Code</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Type</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Validity</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Usage</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                    <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($promotions as $promotion)
                    @php $isSelected = in_array($promotion->id, $selected); @endphp
                    <tr onclick="window.location.href='{{ route('sales.configuration.promotions.edit', $promotion->id) }}'" class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                        <td class="relative py-4 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                            <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                            <input type="checkbox" wire:click="toggleSelect({{ $promotion->id }})" @checked($isSelected) class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}">
                        </td>
                        <td class="py-4 pl-2 pr-4">
                            <div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $promotion->name }}</span>
                                @if($promotion->description)
                                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-xs">{{ $promotion->description }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($promotion->code)
                                <span class="inline-flex items-center rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $promotion->code }}</span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @switch($promotion->type)
                                    @case('buy_x_get_y') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 @break
                                    @case('bundle') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @break
                                    @case('quantity_break') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @break
                                    @case('cart_discount') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 @break
                                    @case('product_discount') bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400 @break
                                    @case('coupon') bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 @break
                                    @default bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300
                                @endswitch
                            ">
                                {{ $types[$promotion->type] ?? $promotion->type }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                            @if($promotion->start_date || $promotion->end_date)
                                {{ $promotion->start_date?->format('M d') ?? '∞' }} - {{ $promotion->end_date?->format('M d, Y') ?? '∞' }}
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500">Always</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $promotion->usages_count }}</span>
                            @if($promotion->usage_limit)
                                <span class="text-zinc-400 dark:text-zinc-500">/ {{ $promotion->usage_limit }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($promotion->is_active && $promotion->isValid())
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                            @elseif($promotion->is_active)
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Scheduled</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                            @endif
                        </td>
                        <td class="py-4 pr-4 sm:pr-6 lg:pr-8"></td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
    @elseif($view === 'grid')
        {{-- Grid View --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($promotions as $promotion)
                @php
                    $isActive = $promotion->is_active && $promotion->isValid();
                    $isScheduled = $promotion->is_active && !$promotion->isValid();
                @endphp
                <a href="{{ route('sales.configuration.promotions.edit', $promotion->id) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        @if($promotion->code)
                            <span class="inline-flex items-center rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $promotion->code }}</span>
                        @else
                            <span class="text-xs text-zinc-400">—</span>
                        @endif
                        @if($isActive)
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                        @elseif($isScheduled)
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Scheduled</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $promotion->name }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                            @switch($promotion->type)
                                @case('buy_x_get_y') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 @break
                                @case('bundle') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @break
                                @case('quantity_break') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @break
                                @case('cart_discount') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 @break
                                @case('product_discount') bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400 @break
                                @case('coupon') bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 @break
                                @default bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300
                            @endswitch
                        ">
                            {{ $types[$promotion->type] ?? $promotion->type }}
                        </span>
                    </p>
                    <div class="mt-3 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            @if($promotion->start_date || $promotion->end_date)
                                {{ $promotion->start_date?->format('M d') ?? '∞' }} - {{ $promotion->end_date?->format('M d') ?? '∞' }}
                            @else
                                Always valid
                            @endif
                        </span>
                        <span class="text-xs text-zinc-400 dark:text-zinc-500">
                            {{ $promotion->usages_count }} uses
                            @if($promotion->usage_limit)
                                / {{ $promotion->usage_limit }}
                            @endif
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @elseif($view === 'kanban')
        {{-- Kanban View --}}
        @php
            $statusGroups = [
                'active' => ['label' => 'Active', 'color' => 'emerald', 'headerBg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                'scheduled' => ['label' => 'Scheduled', 'color' => 'amber', 'headerBg' => 'bg-amber-50 dark:bg-amber-900/20'],
                'inactive' => ['label' => 'Inactive', 'color' => 'zinc', 'headerBg' => 'bg-zinc-100 dark:bg-zinc-800'],
            ];
            $promotionsByStatus = $promotions->groupBy(function ($promotion) {
                if ($promotion->is_active && $promotion->isValid()) {
                    return 'active';
                } elseif ($promotion->is_active) {
                    return 'scheduled';
                }
                return 'inactive';
            });
        @endphp
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach($statusGroups as $statusKey => $statusInfo)
                <div class="flex w-80 flex-shrink-0 flex-col rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between rounded-t-lg {{ $statusInfo['headerBg'] }} px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-{{ $statusInfo['color'] }}-500"></span>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $statusInfo['label'] }}</span>
                            <span class="rounded-full bg-white px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                {{ $promotionsByStatus->get($statusKey)?->count() ?? 0 }}
                            </span>
                        </div>
                    </div>

                    {{-- Column Cards --}}
                    <div class="flex flex-1 flex-col gap-2 p-2 max-h-[60vh] overflow-y-auto">
                        @forelse($promotionsByStatus->get($statusKey, collect()) as $promotion)
                            <a 
                                href="{{ route('sales.configuration.promotions.edit', $promotion->id) }}"
                                wire:navigate
                                class="group rounded-lg border border-zinc-200 bg-white p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                            >
                                <div class="mb-2 flex items-start justify-between">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $promotion->name }}</span>
                                    @if($promotion->code)
                                        <span class="ml-2 inline-flex items-center rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">{{ $promotion->code }}</span>
                                    @endif
                                </div>
                                <div class="mb-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium
                                        @switch($promotion->type)
                                            @case('buy_x_get_y') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 @break
                                            @case('bundle') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 @break
                                            @case('quantity_break') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @break
                                            @case('cart_discount') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 @break
                                            @case('product_discount') bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400 @break
                                            @case('coupon') bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 @break
                                            @default bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300
                                        @endswitch
                                    ">
                                        {{ $types[$promotion->type] ?? $promotion->type }}
                                    </span>
                                </div>
                                @if($promotion->description)
                                    <p class="mb-2 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">{{ $promotion->description }}</p>
                                @endif
                                <div class="flex items-center justify-between text-xs text-zinc-400 dark:text-zinc-500">
                                    <span>
                                        @if($promotion->start_date || $promotion->end_date)
                                            {{ $promotion->start_date?->format('M d') ?? '∞' }} - {{ $promotion->end_date?->format('M d') ?? '∞' }}
                                        @else
                                            Always
                                        @endif
                                    </span>
                                    <span>{{ $promotion->usages_count }}@if($promotion->usage_limit)/{{ $promotion->usage_limit }}@endif uses</span>
                                </div>
                            </a>
                        @empty
                            <div class="flex flex-1 items-center justify-center py-8">
                                <p class="text-xs text-zinc-400 dark:text-zinc-500">No promotions</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    @endif
    </div>

    {{-- Import Modal --}}
    @if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeImportModal">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Import Promotions</h3>
                <button type="button" wire:click="closeImportModal" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800">
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            @if(!empty($importResult))
                @if($importResult['success'] ?? false)
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
                        <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400">
                            <flux:icon name="check-circle" class="size-5" />
                            <span class="font-medium">Import Successful</span>
                        </div>
                        <p class="mt-1 text-sm text-emerald-600 dark:text-emerald-500">
                            {{ $importResult['imported'] }} created, {{ $importResult['updated'] }} updated
                        </p>
                        @if(!empty($importResult['errors']))
                            <div class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                                <p class="font-medium">Warnings:</p>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach(array_slice($importResult['errors'], 0, 5) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="flex items-center gap-2 text-red-700 dark:text-red-400">
                            <flux:icon name="x-circle" class="size-5" />
                            <span class="font-medium">Import Failed</span>
                        </div>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $importResult['message'] ?? 'Unknown error' }}</p>
                    </div>
                @endif
            @endif

            <div class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Select File</label>
                    <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-zinc-100 file:px-3 file:py-1 file:text-sm file:font-medium file:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:file:bg-zinc-700 dark:file:text-zinc-300" />
                    @error('importFile') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <p class="text-xs text-zinc-600 dark:text-zinc-400">
                        <strong>Supported formats:</strong> .xlsx, .xls, .csv<br>
                        <strong>Max size:</strong> 10MB<br>
                        <a href="#" wire:click.prevent="downloadTemplate" class="text-zinc-900 underline dark:text-zinc-100">Download template</a> for the correct format.
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeImportModal" class="rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        Cancel
                    </button>
                    <button type="button" wire:click="import" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <span wire:loading.remove wire:target="import">Import</span>
                        <span wire:loading wire:target="import">Importing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>