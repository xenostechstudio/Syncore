<div x-data="{ 
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    activeTab: 'settings'
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.configuration.promotions.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Promotion</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $promotionId ? ($name ?: 'Untitled Promotion') : 'New Promotion' }}</span>
                        @if($promotionId)
                        <flux:dropdown position="bottom" align="start">
                            <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                            </button>
                            <flux:menu class="w-40">
                                <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                    <flux:icon name="document-duplicate" class="size-4" /><span>Duplicate</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" wire:click="delete" wire:confirm="Are you sure you want to delete this promotion?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" /><span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:icon name="bars-arrow-up" class="size-4 text-zinc-400" />
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Priority</span>
                    <input type="number" wire:model.blur="priority" min="1" max="100" class="w-12 border-0 bg-transparent p-0 text-center text-sm font-medium text-zinc-900 focus:ring-0 dark:text-zinc-100" />
                </div>
                @if($promotionId)
                <div class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900/30 dark:text-zinc-300">
                    <flux:icon name="chart-bar" class="size-4" />
                    <span>Usage</span>
                    <span class="rounded px-1.5 py-0.5 text-xs font-medium bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $this->getNotableModel()?->usage_count ?? 0 }}</span>
                    @if($this->getNotableModel()?->usage_limit)
                    <span class="text-zinc-400">/</span>
                    <span class="rounded px-1.5 py-0.5 text-xs font-medium bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">{{ $this->getNotableModel()->usage_limit }}</span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </x-slot:header>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))<x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>@endif
        @if($errors->any())
        <x-ui.alert type="error" :duration="10000">
            <span class="font-medium">Please fix the following errors:</span>
            <ul class="mt-1 list-inside list-disc text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </x-ui.alert>
        @endif
    </div>

    {{-- Action Buttons Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" class="size-4" />Save
                    </button>
                    @if($promotionId)
                    <button type="button" wire:click="delete" wire:confirm="Are you sure you want to delete this promotion?" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400">
                        <flux:icon name="trash" class="size-4" />Delete
                    </button>
                    @endif
                </div>
            </div>
            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Form (col-span-9) --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        {{-- Header: Name --}}
                        <div class="mb-5">
                            <input type="text" wire:model="name" placeholder="Promotion name..." class="w-full rounded-md border border-transparent bg-transparent px-0 py-1 text-3xl font-bold text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500" />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Promotion Type & Validity/Status --}}
                        <div class="mb-5 grid gap-6 sm:grid-cols-2">
                            {{-- Promotion Type --}}
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Promotion Type <span class="text-red-500">*</span></label>
                                <div class="relative" x-data="{ open: false }">
                                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $typeConfig = match($type) {
                                                    'buy_x_get_y' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-700 dark:text-purple-400', 'icon' => 'gift'],
                                                    'bundle' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'icon' => 'cube'],
                                                    'quantity_break' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'icon' => 'squares-plus'],
                                                    'cart_discount' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'icon' => 'shopping-cart'],
                                                    'product_discount' => ['bg' => 'bg-cyan-100 dark:bg-cyan-900/30', 'text' => 'text-cyan-700 dark:text-cyan-400', 'icon' => 'tag'],
                                                    'coupon' => ['bg' => 'bg-rose-100 dark:bg-rose-900/30', 'text' => 'text-rose-700 dark:text-rose-400', 'icon' => 'ticket'],
                                                    default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-700 dark:text-zinc-300', 'icon' => 'tag'],
                                                };
                                            @endphp
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $typeConfig['bg'] }}">
                                                <flux:icon name="{{ $typeConfig['icon'] }}" class="size-4 {{ $typeConfig['text'] }}" />
                                            </div>
                                            <div>
                                                <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $promotionTypes[$type] ?? 'Select type' }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    @switch($type)
                                                        @case('buy_x_get_y') Buy X items, get Y free @break
                                                        @case('bundle') Discount on product bundles @break
                                                        @case('quantity_break') Tiered quantity discounts @break
                                                        @case('cart_discount') Discount on entire cart @break
                                                        @case('product_discount') Discount on specific products @break
                                                        @case('coupon') Requires coupon code @break
                                                        @default Select a promotion type
                                                    @endswitch
                                                </p>
                                            </div>
                                        </div>
                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-transition class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                        <div class="max-h-80 overflow-auto py-1">
                                            @foreach($promotionTypes as $value => $label)
                                                @php
                                                    $optionConfig = match($value) {
                                                        'buy_x_get_y' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-700 dark:text-purple-400', 'icon' => 'gift', 'desc' => 'Buy X items, get Y free'],
                                                        'bundle' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'icon' => 'cube', 'desc' => 'Discount on product bundles'],
                                                        'quantity_break' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'icon' => 'squares-plus', 'desc' => 'Tiered quantity discounts'],
                                                        'cart_discount' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'icon' => 'shopping-cart', 'desc' => 'Discount on entire cart'],
                                                        'product_discount' => ['bg' => 'bg-cyan-100 dark:bg-cyan-900/30', 'text' => 'text-cyan-700 dark:text-cyan-400', 'icon' => 'tag', 'desc' => 'Discount on specific products'],
                                                        'coupon' => ['bg' => 'bg-rose-100 dark:bg-rose-900/30', 'text' => 'text-rose-700 dark:text-rose-400', 'icon' => 'ticket', 'desc' => 'Requires coupon code'],
                                                        default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-700 dark:text-zinc-300', 'icon' => 'tag', 'desc' => ''],
                                                    };
                                                @endphp
                                                <button type="button" wire:click="$set('type', '{{ $value }}')" @click="open = false" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $type === $value ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $optionConfig['bg'] }}">
                                                        <flux:icon name="{{ $optionConfig['icon'] }}" class="size-4 {{ $optionConfig['text'] }}" />
                                                    </div>
                                                    <div>
                                                        <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $label }}</p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $optionConfig['desc'] }}</p>
                                                    </div>
                                                    @if($type === $value)<flux:icon name="check" class="ml-auto size-4 text-zinc-900 dark:text-zinc-100" />@endif
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Validity & Status --}}
                            <div class="space-y-3">
                                <div class="flex items-center gap-4">
                                    <label class="w-16 text-sm font-light text-zinc-600 dark:text-zinc-400">Validity</label>
                                    <div class="flex flex-1 items-center gap-2">
                                        <input type="date" wire:model="start_date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        <span class="text-sm text-zinc-400">to</span>
                                        <input type="date" wire:model="end_date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="w-16 text-sm font-light text-zinc-600 dark:text-zinc-400">Status</label>
                                    <flux:switch wire:model.live="is_active" />
                                    <span class="text-sm font-medium {{ $is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400' }}">{{ $is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Code Field --}}
                        <div class="mb-5 grid gap-6 sm:grid-cols-2">
                            <div class="flex items-center gap-4 transition-opacity {{ !$requires_coupon && $type !== 'coupon' ? 'opacity-40' : '' }}" x-data="{ focused: false }">
                                <label class="w-16 text-sm font-light text-zinc-600 dark:text-zinc-400">Code</label>
                                <div class="flex flex-1 items-center gap-1">
                                    <input type="text" wire:model="code" placeholder="{{ $requires_coupon || $type === 'coupon' ? 'e.g., SAVE20' : 'Optional' }}" @focus="focused = true" @blur="focused = false" class="flex-1 rounded-lg border bg-transparent px-3 py-2 text-sm uppercase text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-500" :class="focused ? 'border-zinc-300 dark:border-zinc-600' : 'border-transparent hover:border-zinc-200 dark:hover:border-zinc-700'" />
                                    <button type="button" wire:click="generateCode" class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Generate code">
                                        <flux:icon name="sparkles" class="size-4" />
                                    </button>
                                </div>
                            </div>
                            @error('code') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Tabs: Settings | Conditions | Reward | Summary | Simulator --}}
                        <div class="mb-4 border-b border-zinc-200 dark:border-zinc-800">
                            <nav class="-mb-px flex space-x-4 text-sm">
                                <button type="button" @click="activeTab = 'settings'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1" :class="activeTab === 'settings' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'">Settings</button>
                                <button type="button" @click="activeTab = 'conditions'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1" :class="activeTab === 'conditions' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'">Conditions</button>
                                <button type="button" @click="activeTab = 'reward'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1" :class="activeTab === 'reward' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'">Reward</button>
                                <button type="button" @click="activeTab = 'summary'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1" :class="activeTab === 'summary' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'">Summary</button>
                                @if($promotionId)
                                <button type="button" @click="activeTab = 'simulator'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1 flex items-center gap-1.5" :class="activeTab === 'simulator' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400'">
                                    <flux:icon name="beaker" class="size-4" />Simulator
                                </button>
                                @endif
                            </nav>
                        </div>

                        {{-- Tab: Settings --}}
                        <div x-show="activeTab === 'settings'" class="space-y-6">
                            <div class="grid grid-cols-2 gap-x-8">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Requires Coupon</label>
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input type="checkbox" wire:model.live="requires_coupon" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Customer must enter code</span>
                                        </label>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Combinable</label>
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input type="checkbox" wire:model="is_combinable" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700" />
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Can stack with other promotions</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Total Uses</label>
                                        <input type="number" wire:model="usage_limit" min="1" placeholder="Unlimited" class="w-32 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Per Customer</label>
                                        <input type="number" wire:model="usage_per_customer" min="1" placeholder="Unlimited" class="w-32 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-800">
                                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                                <textarea wire:model="description" rows="2" placeholder="Optional description..." class="w-full resize-none border-none bg-transparent px-0 py-1 text-sm text-zinc-900 placeholder-zinc-400 focus:ring-0 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500"></textarea>
                            </div>
                        </div>

                        {{-- Tab: Conditions --}}
                        <div x-show="activeTab === 'conditions'" x-cloak class="space-y-6">
                            {{-- Minimum Requirements --}}
                            <div>
                                <h4 class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Minimum Requirements</h4>
                                <div class="grid grid-cols-2 gap-x-8">
                                    <div class="flex items-center gap-3">
                                        <label class="w-36 text-sm text-zinc-600 dark:text-zinc-400">Min Order Amount</label>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-zinc-500">Rp</span>
                                            <input type="number" wire:model="min_order_amount" min="0" placeholder="0" class="w-40 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="w-36 text-sm text-zinc-600 dark:text-zinc-400">Min Quantity</label>
                                        <input type="number" wire:model="min_quantity" min="1" placeholder="Any" class="w-32 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                </div>
                            </div>

                            {{-- Rules --}}
                            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-800">
                                <div class="mb-3 flex items-center justify-between">
                                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Conditions (All must match)</label>
                                    <button type="button" wire:click="addRule" class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        <flux:icon name="plus" class="size-3" />Add Condition
                                    </button>
                                </div>
                                @if(empty($rules))
                                <div class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                                    <flux:icon name="funnel" class="mx-auto size-8 text-zinc-400" />
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No conditions added</p>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">Promotion applies to all orders</p>
                                </div>
                                @else
                                <div class="space-y-3">
                                    @foreach($rules as $index => $rule)
                                    <div class="flex items-center gap-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                        <select wire:model="rules.{{ $index }}.rule_type" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            @foreach($ruleTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                                        </select>
                                        <select wire:model="rules.{{ $index }}.operator" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            @foreach($operators as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                                        </select>
                                        <div class="flex-1">
                                            @if(in_array($rule['rule_type'], ['product', 'category', 'customer']))
                                            <select wire:model="rules.{{ $index }}.value" multiple class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                @if($rule['rule_type'] === 'product')
                                                    @foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach
                                                @elseif($rule['rule_type'] === 'category')
                                                    @foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
                                                @elseif($rule['rule_type'] === 'customer')
                                                    @foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                                                @endif
                                            </select>
                                            @else
                                            <input type="number" wire:model="rules.{{ $index }}.value.0" placeholder="Value" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                            @endif
                                        </div>
                                        <button type="button" wire:click="removeRule({{ $index }})" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-red-500 dark:hover:bg-zinc-700">
                                            <flux:icon name="x-mark" class="size-4" />
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Tab: Reward --}}
                        <div x-show="activeTab === 'reward'" x-cloak class="space-y-6">
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <div class="mb-3 flex items-center justify-between">
                                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Reward Type</label>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Based on: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $promotionTypes[$type] ?? $type }}</span></span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    @forelse($rewardTypes as $value => $label)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 bg-white p-3 transition-colors hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600 {{ $reward_type === $value ? 'ring-2 ring-zinc-900 dark:ring-zinc-100' : '' }}">
                                        <input type="radio" wire:model.live="reward_type" value="{{ $value }}" class="mt-0.5 border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600" />
                                        <div>
                                            <span class="block text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $label }}</span>
                                            <span class="mt-0.5 block text-xs text-zinc-500 dark:text-zinc-400">
                                                @switch($value)
                                                    @case('discount_percent') Apply percentage off @break
                                                    @case('discount_fixed') Apply fixed amount off @break
                                                    @case('buy_x_get_y') Buy X items, get Y at discount @break
                                                    @case('free_product') Give free product @break
                                                    @case('free_shipping') Waive shipping cost @break
                                                @endswitch
                                            </span>
                                        </div>
                                    </label>
                                    @empty
                                    <div class="col-span-2 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No reward types available</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-800">
                                <h4 class="mb-4 text-sm font-medium text-zinc-700 dark:text-zinc-300">Reward Configuration</h4>
                                <div class="space-y-4">
                                    @if($reward_type === 'discount_percent')
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Discount</label>
                                        <input type="number" wire:model="discount_value" step="0.01" min="0" max="100" placeholder="0" class="w-32 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        <span class="text-sm text-zinc-500">%</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Max Discount</label>
                                        <span class="text-sm text-zinc-500">Rp</span>
                                        <input type="number" wire:model="max_discount" min="0" placeholder="No limit" class="w-40 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                    @elseif($reward_type === 'discount_fixed')
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Discount</label>
                                        <span class="text-sm text-zinc-500">Rp</span>
                                        <input type="number" wire:model="discount_value" min="0" placeholder="0" class="w-40 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                    @elseif($reward_type === 'buy_x_get_y')
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Buy</label>
                                        <input type="number" wire:model="buy_quantity" min="1" placeholder="2" class="w-24 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        <span class="text-sm text-zinc-500">items</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Get</label>
                                        <input type="number" wire:model="get_quantity" min="1" placeholder="1" class="w-24 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        <span class="text-sm text-zinc-500">items at</span>
                                        <input type="number" wire:model="discount_value" step="0.01" min="0" max="100" placeholder="100" class="w-24 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                        <span class="text-sm text-zinc-500">% off</span>
                                    </div>
                                    @elseif($reward_type === 'free_product')
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Free Product</label>
                                        <select wire:model="reward_product_id" class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            <option value="">Select product...</option>
                                            @foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Quantity</label>
                                        <input type="number" wire:model="get_quantity" min="1" placeholder="1" class="w-24 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                    </div>
                                    @elseif($reward_type === 'free_shipping')
                                    <div class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                                        <flux:icon name="truck" class="mx-auto size-8 text-zinc-400" />
                                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Free shipping will be applied automatically</p>
                                    </div>
                                    @endif

                                    @if(in_array($reward_type, ['discount_percent', 'discount_fixed']))
                                    <div class="flex items-center gap-3">
                                        <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Apply To</label>
                                        <select wire:model="apply_to" class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            @foreach($applyToOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                                        </select>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Tab: Summary --}}
                        <div x-show="activeTab === 'summary'" x-cloak class="space-y-6">
                            {{-- Summary Card --}}
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <div class="grid gap-6 sm:grid-cols-2">
                                    {{-- Left: Basic Info --}}
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Promotion Details</h4>
                                        <div class="space-y-3 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-zinc-500 dark:text-zinc-400">Name</span>
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $promotionSummary['name'] }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-zinc-500 dark:text-zinc-400">Type</span>
                                                @php
                                                    $summaryTypeConfig = match($type) {
                                                        'buy_x_get_y' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                                        'bundle' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                        'quantity_break' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                                        'cart_discount' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                        'product_discount' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                                                        'coupon' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                                        default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                                    };
                                                @endphp
                                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $summaryTypeConfig }}">{{ $promotionSummary['type'] }}</span>
                                            </div>
                                            @if($promotionSummary['code'])
                                            <div class="flex justify-between">
                                                <span class="text-zinc-500 dark:text-zinc-400">Code</span>
                                                <span class="font-mono text-xs font-medium text-zinc-900 dark:text-zinc-100">{{ $promotionSummary['code'] }}</span>
                                            </div>
                                            @endif
                                            <div class="flex justify-between">
                                                <span class="text-zinc-500 dark:text-zinc-400">Validity</span>
                                                <span class="text-zinc-700 dark:text-zinc-300">{{ $promotionSummary['validity'] }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-zinc-500 dark:text-zinc-400">Status</span>
                                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">{{ $promotionSummary['status'] }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-zinc-500 dark:text-zinc-400">Priority</span>
                                                <span class="text-zinc-700 dark:text-zinc-300">{{ $promotionSummary['priority'] }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Right: Reward & Conditions --}}
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Reward & Conditions</h4>
                                        <div class="space-y-3 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-zinc-500 dark:text-zinc-400">Reward</span>
                                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $promotionSummary['reward'] }}</span>
                                            </div>
                                            <div>
                                                <span class="text-zinc-500 dark:text-zinc-400">Conditions</span>
                                                <ul class="mt-1 space-y-1">
                                                    @foreach($promotionSummary['conditions'] as $condition)
                                                    <li class="flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                                                        <flux:icon name="check" class="size-3 text-zinc-400" />{{ $condition }}
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @if($promotionId)
                                            <div class="flex justify-between border-t border-zinc-200 pt-3 dark:border-zinc-700">
                                                <span class="text-zinc-500 dark:text-zinc-400">Usage</span>
                                                <span class="text-zinc-700 dark:text-zinc-300">
                                                    {{ $promotionSummary['usage_count'] }}@if($promotionSummary['usage_limit']) / {{ $promotionSummary['usage_limit'] }}@endif
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Validation Warnings --}}
                            @if(count($validationWarnings) > 0)
                            <div>
                                <h4 class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Warnings & Suggestions</h4>
                                <div class="space-y-2">
                                    @foreach($validationWarnings as $warning)
                                    <div class="flex items-start gap-2 rounded-lg border p-3 text-sm {{ match($warning['type']) {
                                        'error' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400',
                                        'warning' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
                                        default => 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                                    } }}">
                                        <flux:icon name="{{ $warning['icon'] }}" class="size-4 flex-shrink-0 mt-0.5" />
                                        <span>{{ $warning['message'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <div class="rounded-lg border border-dashed border-emerald-300 bg-emerald-50 p-4 text-center dark:border-emerald-800 dark:bg-emerald-900/20">
                                <flux:icon name="check-circle" class="mx-auto size-8 text-emerald-500" />
                                <p class="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">All good!</p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-500">No warnings or issues detected</p>
                            </div>
                            @endif
                        </div>

                        {{-- Tab: Simulator --}}
                        @if($promotionId)
                        <div x-show="activeTab === 'simulator'" x-cloak class="space-y-6">
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    <flux:icon name="information-circle" class="size-5" />
                                    <span>Test how this promotion applies to a sample cart. Add products and see the calculated discount.</span>
                                </div>
                            </div>

                            {{-- Cart Items Table --}}
                            <div x-data="{ 
                                activeDropdown: null,
                                search: '',
                                dropdownStyle: {},
                                openDropdown(index, buttonEl, tableEl) {
                                    this.activeDropdown = index;
                                    this.search = '';
                                    const tableRect = tableEl.getBoundingClientRect();
                                    const buttonRect = buttonEl.getBoundingClientRect();
                                    this.dropdownStyle = {
                                        position: 'fixed',
                                        top: (buttonRect.bottom + 4) + 'px',
                                        left: tableRect.left + 'px',
                                        width: tableRect.width + 'px',
                                        zIndex: 200
                                    };
                                    this.$nextTick(() => this.$refs.productSearchInput?.focus());
                                },
                                closeDropdown() {
                                    this.activeDropdown = null;
                                    this.search = '';
                                }
                            }">
                                <div class="mb-3 flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Cart Items</h4>
                                </div>

                                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <table class="w-full" x-ref="simulatorTable">
                                        <thead>
                                            <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                                <th class="px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Product</th>
                                                <th class="w-24 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Qty</th>
                                                <th class="w-36 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unit Price</th>
                                                <th class="w-36 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Subtotal</th>
                                                <th class="w-10 px-2 py-2.5"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                            @foreach($simulatorItems as $index => $item)
                                            <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" wire:key="sim-item-{{ $index }}">
                                                {{-- Product Selection (Searchable) --}}
                                                <td class="px-3 py-2">
                                                    @if($item['product_id'])
                                                        <button type="button" @click="openDropdown({{ $index }}, $el, $refs.simulatorTable)" class="flex w-full items-center gap-2 text-left">
                                                            <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $item['product_name'] ?: 'Product #' . $item['product_id'] }}</p>
                                                        </button>
                                                    @else
                                                        <button type="button" @click="openDropdown({{ $index }}, $el, $refs.simulatorTable)" class="text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                                            Select a product...
                                                        </button>
                                                    @endif
                                                </td>

                                                {{-- Quantity --}}
                                                <td class="w-24 px-3 py-2 pr-5">
                                                    <input type="number" wire:model="simulatorItems.{{ $index }}.quantity" min="1" class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100" />
                                                </td>

                                                {{-- Unit Price --}}
                                                <td class="w-36 px-3 py-2 pr-5">
                                                    <div class="flex items-center justify-end gap-1">
                                                        <span class="text-xs text-zinc-400">Rp</span>
                                                        <input type="number" wire:model="simulatorItems.{{ $index }}.unit_price" min="0" class="w-28 bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100" />
                                                    </div>
                                                </td>

                                                {{-- Subtotal --}}
                                                <td class="w-36 px-3 py-2 text-right text-sm text-zinc-600 dark:text-zinc-400">
                                                    Rp {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 0, ',', '.') }}
                                                </td>

                                                {{-- Remove --}}
                                                <td class="w-10 px-2 py-2">
                                                    <button type="button" wire:click="removeSimulatorItem({{ $index }})" class="rounded p-1 text-zinc-400 opacity-0 transition-opacity hover:bg-zinc-100 hover:text-red-500 group-hover:opacity-100 dark:hover:bg-zinc-700">
                                                        <flux:icon name="x-mark" class="size-4" />
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach

                                            {{-- Add Line Row --}}
                                            <tr class="border-t border-zinc-100 dark:border-zinc-800">
                                                <td colspan="5" class="px-3 py-2">
                                                    <button type="button" wire:click="addSimulatorItem" class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                                                        + Add a product
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Product Dropdown (Teleported outside table for proper z-index) --}}
                                <template x-teleport="body">
                                    <div 
                                        x-show="activeDropdown !== null" 
                                        @click.outside="closeDropdown()"
                                        @keydown.escape.window="closeDropdown()"
                                        x-transition
                                        class="rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
                                        :style="dropdownStyle"
                                    >
                                        <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                            <input 
                                                type="text"
                                                x-ref="productSearchInput"
                                                x-model="search"
                                                placeholder="Search products..."
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            />
                                        </div>
                                        <div class="max-h-48 overflow-auto py-1">
                                            @foreach($products as $product)
                                                <button 
                                                    type="button"
                                                    x-show="'{{ strtolower($product->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($product->sku ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                    @click="$wire.set('simulatorItems.' + activeDropdown + '.product_id', {{ $product->id }}); closeDropdown()"
                                                    class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                >
                                                    <div class="flex-1">
                                                        <p class="text-zinc-900 dark:text-zinc-100">{{ $product->name }}</p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku ?? '-' }} · Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</p>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Additional Options --}}
                            <div class="grid grid-cols-2 gap-4">
                                @if($requires_coupon)
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Coupon Code</label>
                                    <input type="text" wire:model="simulatorCoupon" placeholder="Enter coupon code..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm uppercase focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                                @endif
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Customer (Optional)</label>
                                    <select wire:model="simulatorCustomerId" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <option value="">Guest customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Run Simulation --}}
                            <div class="flex items-center gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                                <button type="button" wire:click="runSimulation" class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                    <flux:icon name="play" class="size-4" />
                                    Test Promotion
                                </button>
                                <button type="button" wire:click="clearSimulation" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    <flux:icon name="arrow-path" class="size-4" />
                                    Reset
                                </button>

                                {{-- Cart Total --}}
                                @php
                                    $cartTotal = collect($simulatorItems)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));
                                @endphp
                                <div class="ml-auto text-right">
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Cart Total:</span>
                                    <span class="ml-2 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            {{-- Simulation Result --}}
                            @if($simulatorResult)
                            <div class="rounded-lg border {{ $simulatorResult['applicable'] ?? false ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20' : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' }} p-4">
                                <div class="flex items-start gap-3">
                                    @if($simulatorResult['applicable'] ?? false)
                                        <flux:icon name="check-circle" class="size-6 text-emerald-500 flex-shrink-0" />
                                    @else
                                        <flux:icon name="x-circle" class="size-6 text-red-500 flex-shrink-0" />
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-medium {{ $simulatorResult['applicable'] ?? false ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                            {{ $simulatorResult['message'] }}
                                        </p>
                                        @if($simulatorResult['applicable'] ?? false)
                                        <div class="mt-4 grid grid-cols-3 gap-4 rounded-lg bg-white/60 p-4 dark:bg-zinc-800/40">
                                            <div class="text-center">
                                                <span class="block text-xs text-zinc-500 dark:text-zinc-400">Subtotal</span>
                                                <p class="mt-1 text-lg font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($simulatorResult['subtotal'] ?? 0, 0, ',', '.') }}</p>
                                            </div>
                                            <div class="text-center border-x border-emerald-200 dark:border-emerald-700">
                                                <span class="block text-xs text-zinc-500 dark:text-zinc-400">Discount</span>
                                                <p class="mt-1 text-lg font-medium text-emerald-600 dark:text-emerald-400">- Rp {{ number_format($simulatorResult['discount'] ?? 0, 0, ',', '.') }}</p>
                                            </div>
                                            <div class="text-center">
                                                <span class="block text-xs text-zinc-500 dark:text-zinc-400">Final Total</span>
                                                <p class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($simulatorResult['final_total'] ?? 0, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                        @if(!empty($simulatorResult['free_items']))
                                        <div class="mt-3 border-t border-emerald-200 pt-3 dark:border-emerald-700">
                                            <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Free Items Included:</span>
                                            <ul class="mt-1 space-y-1">
                                                @foreach($simulatorResult['free_items'] as $freeItem)
                                                <li class="text-xs text-emerald-600 dark:text-emerald-500">• {{ $freeItem['quantity'] }}x Product #{{ $freeItem['product_id'] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Notes & Activity Only (col-span-3) --}}
            <div class="lg:col-span-3">
                @if($promotionId)
                <x-ui.chatter-forms :showMessage="false" />
                <div class="flex items-center gap-3 py-2">
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Activity</span>
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                </div>
                <div class="space-y-3">
                    @forelse($activities as $item)
                        @if($item['type'] === 'note')
                            <x-ui.note-item :note="$item['data']" />
                        @else
                            <x-ui.activity-item :activity="$item['data']" emptyMessage="Promotion created" />
                        @endif
                    @empty
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <x-ui.user-name :user="auth()->user()" />
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                            </div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">Promotion created</p>
                        </div>
                    </div>
                    @endforelse
                </div>
                @else
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
</div>
