<div x-data="{ 
    activeTab: 'details',
    showSendMessage: false,
    showLogNote: false,
    showScheduleActivity: false
}">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.customers.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    {{ $customerId ? $name : 'New Customer' }}
                </span>
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

                {{-- Status Badge --}}
                <div class="hidden items-center lg:flex">
                    @if($status === 'active')
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <flux:icon name="check-circle" class="mr-1 size-3" />
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                            Inactive
                        </span>
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

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Header Section --}}
                    <div class="p-5">
                        {{-- Title --}}
                        <div class="mb-6">
                            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $customerId ? $name : 'New Customer' }}
                            </h2>
                        </div>

                        {{-- Form Fields --}}
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            {{-- Left Column --}}
                            <div class="space-y-4">
                                {{-- Name --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text"
                                        wire:model="name"
                                        placeholder="Enter customer name..."
                                        class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                </div>

                                {{-- Email --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                                    <input 
                                        type="email"
                                        wire:model="email"
                                        placeholder="email@example.com"
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>

                                {{-- Phone --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
                                    <input 
                                        type="text"
                                        wire:model="phone"
                                        placeholder="+62 xxx xxxx xxxx"
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="space-y-4">
                                {{-- City --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">City</label>
                                    <input 
                                        type="text"
                                        wire:model="city"
                                        placeholder="Enter city..."
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>

                                {{-- Country --}}
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Country</label>
                                    <input 
                                        type="text"
                                        wire:model="country"
                                        placeholder="Enter country..."
                                        class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700"
                                    />
                                </div>

                                {{-- Status --}}
                                <div class="flex items-center gap-4" x-data="{ open: false }">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                                    <div class="relative flex-1">
                                        <button 
                                            type="button"
                                            @click="open = !open"
                                            class="flex w-full items-center justify-between rounded-lg border border-transparent bg-transparent px-3 py-2 text-left text-sm transition-colors hover:border-zinc-200 dark:hover:border-zinc-700"
                                        >
                                            <span class="text-zinc-900 dark:text-zinc-100">{{ $status === 'active' ? 'Active' : 'Inactive' }}</span>
                                            <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                        </button>
                                        <div 
                                            x-show="open" 
                                            @click.outside="open = false"
                                            x-transition
                                            class="absolute left-0 top-full z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                        >
                                            <button type="button" wire:click="$set('status', 'active')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Active</button>
                                            <button type="button" wire:click="$set('status', 'inactive')" @click="open = false" class="block w-full px-4 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">Inactive</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Headers --}}
                    <div class="flex items-center border-y border-zinc-100 dark:border-zinc-800">
                        <button 
                            type="button"
                            @click="activeTab = 'details'"
                            :class="activeTab === 'details' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Address
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'orders'"
                            :class="activeTab === 'orders' ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                            class="px-5 py-3 text-sm font-medium transition-colors"
                        >
                            Orders
                        </button>
                    </div>

                    {{-- Tab Content: Address --}}
                    <div x-show="activeTab === 'details'" class="p-5">
                        <div class="space-y-4">
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

                    {{-- Tab Content: Orders --}}
                    <div x-show="activeTab === 'orders'" class="p-5">
                        @if($customerId)
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Order history will be displayed here.</p>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Save the customer first to view orders.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Log --}}
            <div class="lg:col-span-3">
                <div class="sticky top-20 space-y-4">
                    {{-- Activity Timeline --}}
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <h3 class="mb-4 text-sm font-medium text-zinc-900 dark:text-zinc-100">Activity</h3>
                        
                        @if($customerId)
                            {{-- Today separator --}}
                            <div class="mb-4 flex items-center gap-2">
                                <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Today</span>
                                <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                            </div>

                            <div class="space-y-4">
                                @foreach($activities as $activity)
                                    <div class="flex gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ strtoupper(substr($activity['user']->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity['user']->name ?? 'User' }}</span>
                                                <span class="text-xs text-zinc-400">{{ $activity['created_at']->format('H:i') }}</span>
                                            </div>
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activity['action'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No activity yet. Save the customer to start tracking.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
