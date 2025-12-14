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
                        Customer
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $customerId ? $name : 'New Customer' }}
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
                                        wire:confirm="Are you sure you want to delete this customer?"
                                        class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                    >
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
                                    </button>
                                @else
                                    <div class="px-2 py-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                                        No actions
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
                <span class="font-medium">Please fix the following errors:</span>
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

                    @if($customerId)
                        <button 
                            type="button"
                            wire:click="delete"
                            wire:confirm="Are you sure you want to delete this customer?"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <flux:icon name="trash" class="size-4" />
                            Delete
                        </button>
                    @endif
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
            If you leave this page, any unsaved changes to this customer will be lost.
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
                href="{{ route('sales.customers.index') }}"
                wire:navigate
                @click="showCancelModal = false"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600"
            >
                Discard & leave
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
                                            <span>Person</span>
                                        </button>
                                        <button 
                                            type="button"
                                            wire:click="$set('type', 'company')"
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 {{ $type === 'company'
                                                ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                                                : 'border border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-500' }}"
                                        >
                                            <span>Company</span>
                                        </button>
                                    </div>

                                    <div>
                                        <input 
                                            type="text"
                                            wire:model="name"
                                            placeholder="Customer Name"
                                            class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-3xl font-bold text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-200 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-700 dark:focus:border-zinc-700"
                                        />
                                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <flux:icon name="envelope" class="size-5 flex-shrink-0 text-zinc-400" />
                                        <input 
                                            type="email"
                                            wire:model="email"
                                            placeholder="Email"
                                            class="flex-1 border-0 border-b border-transparent bg-transparent px-0 py-1 text-sm text-zinc-700 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500 dark:hover:border-zinc-700"
                                        />
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <flux:icon name="phone" class="size-5 flex-shrink-0 text-zinc-400" />
                                        <input 
                                            type="text"
                                            wire:model="phone"
                                            placeholder="Phone"
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
                                Contact
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'sales_purchase'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'sales_purchase' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Sales &amp; Purchase
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'invoicing'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'invoicing' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Invoicing
                            </button>
                            <button 
                                type="button"
                                @click="activeTab = 'notes'"
                                class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                                :class="activeTab === 'notes' 
                                    ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' 
                                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            >
                                Notes
                            </button>
                        </nav>
                    </div>

                    {{-- Tab Content: Address --}}
                    <div x-show="activeTab === 'contact'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-6">
                                <div class="grid gap-4 lg:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">City</label>
                                        <input 
                                            type="text"
                                            wire:model="city"
                                            placeholder="Enter city..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Country</label>
                                        <input 
                                            type="text"
                                            wire:model="country"
                                            placeholder="Enter country..."
                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Full Address</label>
                                    <textarea 
                                        wire:model="address"
                                        rows="4"
                                        placeholder="Enter full address..."
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
                                    <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Sales</h3>

                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Salesperson</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="salesperson_id"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">None</option>
                                                    @foreach($salespeople as $salesperson)
                                                        <option value="{{ $salesperson->id }}">{{ $salesperson->name }}</option>
                                                    @endforeach
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>
                                        @error('salesperson_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Terms</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="payment_term_id"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">None</option>
                                                    @foreach($paymentTerms as $term)
                                                        <option value="{{ $term->id }}">{{ $term->name }} ({{ $term->formatted_days }})</option>
                                                    @endforeach
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>
                                        @error('payment_term_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Payment Method</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="payment_method"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">None</option>
                                                    <option value="bank_transfer">Bank Transfer</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="credit_card">Credit Card</option>
                                                    <option value="check">Check</option>
                                                </select>
                                                <flux:icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                            </div>
                                        </div>
                                        @error('payment_method') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                                        <div class="flex items-center gap-3">
                                            <label class="w-32 text-sm font-medium text-zinc-700 dark:text-zinc-300">Pricelist</label>
                                            <div class="flex-1 relative">
                                                <select
                                                    wire:model="pricelist_id"
                                                    class="w-full appearance-none rounded-lg border border-transparent bg-transparent px-3 py-2 pr-8 text-sm text-zinc-900 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                                                >
                                                    <option value="">None</option>
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
                                <h3 class="text-sm font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">General</h3>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Banks</label>
                                    <textarea
                                        wire:model="banks"
                                        rows="4"
                                        placeholder="Bank accounts / preferred banks..."
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
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                                <textarea 
                                    wire:model="notes"
                                    rows="6"
                                    placeholder="Add notes about this customer..."
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
                    {{-- Send Message Panel --}}
                    <div x-show="showSendMessage" x-collapse class="mb-4">
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                <flux:icon name="chat-bubble-left" class="size-4" />
                            </div>
                            <div class="flex-1">
                                <textarea 
                                    rows="3"
                                    placeholder="Send a message to followers..."
                                    class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-blue-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                                ></textarea>
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                                            <flux:icon name="paper-clip" class="size-4" />
                                        </button>
                                        <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                                            <flux:icon name="at-symbol" class="size-4" />
                                        </button>
                                    </div>
                                    <button type="button" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-blue-700">
                                        Send
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Log Note Panel --}}
                    <div x-show="showLogNote" x-collapse class="mb-4">
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                                <flux:icon name="pencil-square" class="size-4" />
                            </div>
                            <div class="flex-1">
                                <textarea 
                                    rows="3"
                                    placeholder="Log an internal note..."
                                    class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-amber-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
                                ></textarea>
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                                            <flux:icon name="paper-clip" class="size-4" />
                                        </button>
                                        <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                                            <flux:icon name="at-symbol" class="size-4" />
                                        </button>
                                    </div>
                                    <button type="button" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-amber-700">
                                        Log Note
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Schedule Activity Panel --}}
                    <div x-show="showScheduleActivity" x-collapse class="mb-4">
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                                <flux:icon name="clock" class="size-4" />
                            </div>
                            <div class="flex-1 space-y-3">
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Activity Type</label>
                                    <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <option value="">Select activity type...</option>
                                        <option value="call">Call</option>
                                        <option value="meeting">Meeting</option>
                                        <option value="todo">To-Do</option>
                                        <option value="email">Email</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Due Date</label>
                                    <input type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Summary</label>
                                    <input type="text" placeholder="Activity summary..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                </div>
                                <div class="flex justify-end">
                                    <button type="button" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-violet-700">
                                        Schedule
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Activity Timeline --}}
                    @if($customerId)
                        <div class="flex items-center gap-3 py-2">
                            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Today</span>
                            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        </div>

                        <div class="space-y-4">
                            @foreach($activities as $activity)
                                <div class="flex gap-3">
                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ strtoupper(substr($activity['user']->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity['user']->name ?? 'User' }}</span>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $activity['created_at']->format('H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activity['action'] }}</p>
                                    </div>
                                </div>
                            @endforeach
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
