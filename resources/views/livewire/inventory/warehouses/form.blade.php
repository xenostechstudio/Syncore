<div x-data="{ 
    activeTab: 'details',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    showCancelModal: false,
    showDeleteModal: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.warehouses.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Warehouse
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $editing && $warehouse ? $warehouse->name : 'New Warehouse' }}
                        </span>
                        @if($editing)
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
                                        <flux:icon name="archive-box" class="size-4" />
                                        <span>Archive</span>
                                    </button>
                                    <flux:menu.separator />
                                    <button type="button" @click="showDeleteModal = true" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
                                    </button>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </div>
                </div>
            </div>

            @if($editing && $warehouse)
                @php
                    $whInCount = $warehouse->warehouseIns()->count() ?? 0;
                    $whOutCount = $warehouse->warehouseOuts()->count() ?? 0;
                @endphp
                <div class="flex items-center gap-2">
                    <div class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900/30 dark:text-zinc-300">
                        <flux:icon name="arrows-right-left" class="size-4" />
                        <span>Transfers</span>
                        <span class="rounded px-1.5 py-0.5 text-xs font-medium bg-emerald-200 text-emerald-700 dark:bg-emerald-800 dark:text-emerald-300">
                            IN {{ $whInCount }}
                        </span>
                        <span class="rounded px-1.5 py-0.5 text-xs font-medium bg-red-200 text-red-700 dark:bg-red-800 dark:text-red-300">
                            OUT {{ $whOutCount }}
                        </span>
                    </div>
                </div>
            @endif
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
            {{-- Left: Actions --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button"
                        wire:click="save"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>

                    <button 
                        type="button"
                        @click="showCancelModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                        Cancel
                    </button>
                </div>
            </div>

            {{-- Right: Chatter Icons --}}
            <div class="col-span-3 flex items-center justify-end gap-1">
                <button 
                    @click="showSendMessage = !showSendMessage; showLogNote = false; showScheduleActivity = false" 
                    :class="showSendMessage ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Send message"
                >
                    <flux:icon name="chat-bubble-left" class="size-5" />
                </button>
                <button 
                    @click="showLogNote = !showLogNote; showSendMessage = false; showScheduleActivity = false" 
                    :class="showLogNote ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Log note"
                >
                    <flux:icon name="pencil-square" class="size-5" />
                </button>
                <button 
                    @click="showScheduleActivity = !showScheduleActivity; showSendMessage = false; showLogNote = false" 
                    :class="showScheduleActivity ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
                    class="rounded-lg p-2 transition-colors" 
                    title="Schedule activity"
                >
                    <flux:icon name="clock" class="size-5" />
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        {{-- Header with icon and name field --}}
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Warehouse</p>

                                <div class="mt-2 flex items-center gap-2">
                                    {{-- Warehouse name inline input --}}
                                    <input 
                                        type="text"
                                        wire:model="name"
                                        placeholder="Warehouse name..."
                                        class="flex-1 rounded-md border border-transparent bg-transparent px-2 py-1 text-lg text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                    />
                                </div>
                                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex h-20 w-20 items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                <flux:icon name="building-storefront" class="size-8 text-zinc-300 dark:text-zinc-600" />
                            </div>
                        </div>

                        {{-- Tabs --}}
                        <div class="mb-4 border-b border-zinc-200 dark:border-zinc-800">
                            <nav class="-mb-px flex space-x-4 text-sm">
                                <button 
                                    type="button"
                                    @click="activeTab = 'details'"
                                    class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                    :class="activeTab === 'details' 
                                        ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                >
                                    Details
                                </button>
                                @if($editing)
                                <button 
                                    type="button"
                                    @click="activeTab = 'products'"
                                    class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                    :class="activeTab === 'products' 
                                        ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                >
                                    Products
                                </button>
                                <button 
                                    type="button"
                                    @click="activeTab = 'transfers'"
                                    class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                    :class="activeTab === 'transfers' 
                                        ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                        : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                >
                                    Transfers
                                </button>
                                @endif
                            </nav>
                        </div>

                        {{-- Tab: Details --}}
                        <div x-show="activeTab === 'details'">
                            <div class="grid grid-cols-2 gap-x-8">
                                {{-- Left column --}}
                                <div class="space-y-4">
                                    {{-- Contact Info --}}
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Contact Info</label>
                                        <div class="flex-1">
                                            <input 
                                                type="text"
                                                wire:model="contact_info"
                                                placeholder="Phone, email, etc."
                                                class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                            />
                                        </div>
                                    </div>
                                    @error('contact_info') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Right column --}}
                                <div class="space-y-4">
                                    {{-- Status (for editing) --}}
                                    @if($editing)
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                                        <div class="flex-1">
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Divider before Location --}}
                            <div class="mt-6 border-t border-zinc-200 dark:border-zinc-800"></div>

                            {{-- Location --}}
                            <div class="mt-4">
                                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Location / Address</label>
                                <textarea 
                                    wire:model="location"
                                    rows="3"
                                    placeholder="Full warehouse address..."
                                    class="w-full resize-none border-none bg-transparent px-0 py-1 text-sm text-zinc-900 placeholder-zinc-400 focus:ring-0 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500"
                                ></textarea>
                                @error('location') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Tab: Products --}}
                        @if($editing)
                        <div x-show="activeTab === 'products'" x-cloak>
                            <div class="-mx-5 -mb-5 overflow-visible">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">SKU</th>
                                            <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                        @forelse($this->stocks as $stock)
                                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                                <td class="px-4 py-3">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $stock->product->name ?? '-' }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stock->product->barcode ?? '-' }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($stock->quantity) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-4 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                                    No products in this warehouse
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab: Transfers --}}
                        <div x-show="activeTab === 'transfers'" x-cloak>
                            <div class="-mx-5 -mb-5 overflow-visible">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Reference</th>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Type</th>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Date</th>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                        @forelse($this->transfers as $transfer)
                                            @php
                                                $isIn = $transfer->adjustment_type === 'increase';
                                                $route = $isIn ? 'inventory.warehouse-in.edit' : 'inventory.warehouse-out.edit';
                                            @endphp
                                            <tr class="cursor-pointer hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" onclick="window.location.href='{{ route($route, $transfer->id) }}'">
                                                <td class="px-4 py-3">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transfer->adjustment_number }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($isIn)
                                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">IN</span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">OUT</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $transfer->adjustment_date?->format('d M Y') ?? '-' }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($transfer->status === 'completed')
                                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">Completed</span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ ucfirst($transfer->status ?? 'Draft') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                                    No transfers for this warehouse
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms />

                {{-- Activity Timeline --}}
                @if($editing && $warehouse)
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Activity</span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    <div class="space-y-4">
                        @if(isset($activities) && $activities->isNotEmpty())
                            @foreach($activities as $item)
                                @if($item['type'] === 'note')
                                    {{-- Note Item --}}
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <x-ui.user-avatar :user="$item['data']->user" size="md" :showPopup="true" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <x-ui.user-name :user="$item['data']->user" />
                                                <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                                    {{ $item['created_at']->diffForHumans() }}
                                                </span>
                                            </div>
                                            <div class="mt-1 rounded-lg bg-amber-50 px-3 py-2 text-sm text-zinc-700 dark:bg-amber-900/20 dark:text-zinc-300">
                                                <div class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 mb-1">
                                                    <flux:icon name="pencil-square" class="size-3" />
                                                    <span>Internal Note</span>
                                                </div>
                                                {{ $item['data']->content }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Activity Log Item --}}
                                    @php $activity = $item['data']; @endphp
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <x-ui.user-avatar :user="$activity->causer" size="md" :showPopup="true" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <x-ui.user-name :user="$activity->causer" />
                                                <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                                {{ $activity->description }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            {{-- Fallback when no activities yet --}}
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ now()->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Warehouse created</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Empty State for New Warehouse --}}
                    <div class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once you save</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Cancel Confirmation Modal --}}
    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
        </x-slot:icon>

        <x-slot:title>
            Discard changes?
        </x-slot:title>

        <x-slot:description>
            If you leave this page, any unsaved changes to this warehouse will be lost.
        </x-slot:description>

        <x-slot:actions>
            <button 
                type="button"
                @click="showCancelModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Keep editing
            </button>

            <a 
                href="{{ route('inventory.warehouses.index') }}"
                wire:navigate
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Discard & leave
            </a>
        </x-slot:actions>
    </x-ui.confirm-modal>

    {{-- Delete Confirmation Modal --}}
    <x-ui.confirm-modal show="showDeleteModal">
        <x-slot:icon>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                <flux:icon name="trash" class="size-6" />
            </div>
        </x-slot:icon>

        <x-slot:title>
            Delete warehouse?
        </x-slot:title>

        <x-slot:description>
            This action cannot be undone. All associated data will be permanently removed.
        </x-slot:description>

        <x-slot:actions>
            <button 
                type="button"
                @click="showDeleteModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Cancel
            </button>

            <button 
                type="button"
                wire:click="delete"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Delete
            </button>
        </x-slot:actions>
    </x-ui.confirm-modal>
</div>
