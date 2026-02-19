<div x-data="{ 
    activeTab: 'contact',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false,
    showCancelModal: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.customers.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        {{ __('common.customer') }}
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $customerId ? $name : __('common.new_customer') }}
                        </span>

                        <flux:dropdown position="bottom" align="start">
                            <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                            </button>

                            <flux:menu class="w-40">
                                @if($customerId)
                                    <button
                                        type="button"
                                        wire:click="delete"
                                        wire:confirm="{{ __('common.confirm_delete') }}"
                                        class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                    >
                                        <flux:icon name="trash" class="size-4" />
                                        <span>{{ __('common.delete') }}</span>
                                    </button>
                                @else
                                    <div class="px-2 py-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('common.no_actions') }}
                                    </div>
                                @endif
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

        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">{{ __('common.please_fix_errors') }}</span>
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
            {{-- Left: Action Buttons --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                        <span wire:loading.remove wire:target="save">{{ __('common.save') }}</span>
                        <span wire:loading wire:target="save">{{ __('common.saving') }}</span>
                    </button>

                    <button 
                        type="button"
                        @click="showCancelModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                        {{ __('common.cancel') }}
                    </button>

                    @if($customerId)
                        <button 
                            type="button"
                            wire:click="delete"
                            wire:confirm="{{ __('common.confirm_delete') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <flux:icon name="trash" class="size-4" />
                            {{ __('common.delete') }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Right: Chatter Icons --}}
            <div class="col-span-3">
                <x-ui.chatter-buttons />
            </div>
        </div>
    </div>

    <x-ui.confirm-modal show="showCancelModal">
        <x-slot:icon>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
        </x-slot:icon>

        <x-slot:title>
            {{ __('common.discard_changes') }}
        </x-slot:title>

        <x-slot:description>
            {{ __('common.unsaved_changes_warning') }}
        </x-slot:description>

        <x-slot:actions>
            <button 
                type="button"
                @click="showCancelModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                {{ __('common.keep_editing') }}
            </button>

            <a 
                href="{{ route('sales.customers.index') }}"
                wire:navigate
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                {{ __('common.discard_leave') }}
            </a>
        </x-slot:actions>
    </x-ui.confirm-modal>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Header Section --}}
                    <div class="p-5">
                        {{-- Title --}}
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                            <div class="flex items-start gap-6 flex-1">
                                <div class="relative flex-shrink-0">
                                    <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        @if($customerId && $name)
                                            <span class="text-4xl font-medium text-zinc-400 dark:text-zinc-500">
                                                {{ strtoupper(substr($name, 0, 2)) }}
                                            </span>
                                        @else
                                            <flux:icon name="user" class="size-12 text-zinc-300 dark:text-zinc-600" />
                                        @endif
                                    </div>
                                    <button type="button" class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-zinc-100 text-zinc-500 transition-colors hover:bg-zinc-200 dark:border-zinc-900 dark:bg-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-600" title="Change photo">
                                        <flux:icon name="camera" class="size-4" />
                                    </button>
                                </div>

                                <div class="flex-1 space-y-3">
                                    <div class="flex items-center gap-2 text-xs">
                                        <button 
                                            type="button"
                                            wire:click="$set('type', 'person')"
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 {{ $type === 'person'
                                                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                                : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500' }}"
                                        >
                                            <span>{{ __('common.person') }}</span>
                                        </button>
                                        <button 
                                            type="button"
                                            wire:click="$set('type', 'company')"
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 {{ $type === 'company'
                                                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                                : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500' }}"
                                        >
                                            <span>{{ __('common.company') }}</span>
                                        </button>
                                    </div>

                                    <div>
                                        <input 
                                            type="text"
                                            wire:model="name"
                                            placeholder="{{ __('common.customer_name') }}"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-3xl font-bold text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-200 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-700 dark:focus:border-zinc-700"
                                        />
                                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <flux:icon name="envelope" class="size-5 flex-shrink-0 text-zinc-400" />
                                        <input 
                                            type="email"
                                            wire:model="email"
                                            placeholder="{{ __('common.email') }}"
                                            class="flex-1 border-0 border-b border-transparent bg-transparent px-0 py-1 text-sm text-zinc-700 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500 dark:hover:border-zinc-700"
                                        />
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <flux:icon name="phone" class="size-5 flex-shrink-0 text-zinc-400" />
                                        <input 
                                            type="text"
                                            wire:model="phone"
                                            placeholder="{{ __('common.phone') }}"
                                            class="flex-1 border-0 border-b border-transparent bg-transparent px-0 py-1 text-sm text-zinc-700 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500 dark:hover:border-zinc-700"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Headers --}}
                    <div class="mx-5 mb-4 border-b border-zinc-200 dark:border-zinc-800">
                        <nav class="-mb-px flex space-x-4 text-sm">
                            <button 
                                type="button"
                                @click="activeTab = 'contact'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'contact' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                {{ __('common.tab_contact') }}
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'sales_purchase'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'sales_purchase' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                {{ __('common.tab_sales_purchase') }}
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'invoicing'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'invoicing' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                {{ __('common.tab_invoicing') }}
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'notes'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'notes' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                {{ __('common.tab_notes') }}
                            </button>
                        </nav>
                    </div>

                    {{-- Tab Content: Address --}}
                    <div x-show="activeTab === 'contact'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-6">
                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.city') }}</label>
                                        <input 
                                            type="text"
                                            wire:model="city"
                                            placeholder="{{ __('common.city') }}..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.country') }}</label>
                                        <input 
                                            type="text"
                                            wire:model="country"
                                            placeholder="{{ __('common.country') }}..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.full_address') }}</label>
                                    <textarea 
                                        wire:model="address"
                                        rows="4"
                                        placeholder="{{ __('common.full_address') }}..."
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="activeTab === 'sales_purchase'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="grid gap-8 lg:grid-cols-2">
                                <div class="space-y-4">
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('sales.title') }}</h3>

                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.salesperson') }}</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="salesperson_id"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">{{ __('common.none') }}</option>
                                                    @foreach($salespeople as $salesperson)
                                                        <option value="{{ $salesperson->id }}">{{ $salesperson->name }}</option>
                                                    @endforeach
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>
                                        @error('salesperson_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.payment_terms') }}</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="payment_term_id"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">{{ __('common.none') }}</option>
                                                    @foreach($paymentTerms as $term)
                                                        <option value="{{ $term->id }}">{{ $term->name }} ({{ $term->formatted_days }})</option>
                                                    @endforeach
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>
                                        @error('payment_term_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.payment_method') }}</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="payment_method"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">{{ __('common.none') }}</option>
                                                    <option value="bank_transfer">{{ __('common.bank_transfer') }}</option>
                                                    <option value="cash">{{ __('common.cash') }}</option>
                                                    <option value="credit_card">{{ __('common.credit_card') }}</option>
                                                    <option value="check">{{ __('common.check') }}</option>
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>
                                        @error('payment_method') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.pricelist') }}</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="pricelist_id"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">{{ __('common.none') }}</option>
                                                    @foreach($pricelists as $pricelist)
                                                        <option value="{{ $pricelist->id }}">{{ $pricelist->name }}</option>
                                                    @endforeach
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>
                                        @error('pricelist_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="activeTab === 'invoicing'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('common.tab_general') }}</h3>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.banks') }}</label>
                                    <textarea
                                        wire:model="banks"
                                        rows="4"
                                        placeholder="{{ __('common.banks') }}..."
                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    ></textarea>
                                    @error('banks') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="activeTab === 'notes'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('common.internal_notes') }}</label>
                                <textarea 
                                    wire:model="notes"
                                    rows="6"
                                    placeholder="{{ __('common.internal_notes') }}..."
                                    class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity & Notes (match sales order chatter) --}}
            <div class="lg:col-span-3">
                <div class="sticky top-20 space-y-4">
                    {{-- Chatter Forms --}}
                    <x-ui.chatter-forms />

                    {{-- Activity Timeline --}}
                    @if($customerId)
                        <div class="flex items-center gap-3 py-2">
                            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                @if($activities->isNotEmpty() && $activities->first()['created_at']->isToday())
                                    Today
                                @else
                                    Activity
                                @endif
                            </span>
                            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        </div>

                        <div class="space-y-3">
                            @forelse($activities as $item)
                                @if($item['type'] === 'note')
                                    {{-- Note Item - Compact --}}
                                    <x-ui.note-item :note="$item['data']" />
                                @else
                                    {{-- Activity Log Item --}}
                                    <x-ui.activity-item :activity="$item['data']" emptyMessage="Customer created" />
                                @endif
                            @empty
                                {{-- Customer Created (fallback when no activities yet) --}}
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <x-ui.user-name :user="auth()->user()" />
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Customer created</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    @else
                        <div class="py-6 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                            </div>
                            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500">Save the customer to start tracking updates</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
