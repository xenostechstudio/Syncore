<div x-data="{ showSendMessage: false, showLogNote: false, showScheduleActivity: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('settings.users.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">
                    {{ $userId ? $name : 'New User' }}
                </span>
            </div>
        </div>
    </x-slot:header>

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

    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
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
                    @if($userId)
                        <button 
                            type="button"
                            wire:click="delete"
                            wire:confirm="Are you sure you want to delete this user?"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <flux:icon name="trash" class="size-4" />
                            Delete
                        </button>
                    @endif
                </div>

                <div class="hidden items-center lg:flex">
                    @if($is_active)
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

    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        <div class="mb-6">
                            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $userId ? $name : 'New User' }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text"
                                        wire:model="name"
                                        placeholder="Full name"
                                        class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                </div>
                                @error('name')
                                    <p class="ml-28 text-xs text-red-500">{{ $message }}</p>
                                @enderror

                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="email"
                                        wire:model="email"
                                        placeholder="user@example.com"
                                        class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                </div>
                                @error('email')
                                    <p class="ml-28 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $userId ? 'New Password' : 'Password' }}
                                        @if(!$userId)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    <input 
                                        type="password"
                                        wire:model="password"
                                        placeholder="{{ $userId ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}"
                                        class="flex-1 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                </div>
                                @error('password')
                                    <p class="ml-28 text-xs text-red-500">{{ $message }}</p>
                                @enderror

                                <div class="flex items-center gap-4">
                                    <label class="w-28 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Status
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2">
                                        <input 
                                            type="checkbox"
                                            wire:model="is_active"
                                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700"
                                        />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="sticky top-20 space-y-4">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <h3 class="mb-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">User Info</h3>
                        <dl class="space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                            @if($userId)
                                <div class="flex justify-between">
                                    <dt>Created</dt>
                                    <dd>{{ optional(App\Models\User::find($userId)->created_at)->format('M d, Y H:i') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt>Last Updated</dt>
                                    <dd>{{ optional(App\Models\User::find($userId)->updated_at)->format('M d, Y H:i') }}</dd>
                                </div>
                            @else
                                <p>No additional information yet. Save the user to see details.</p>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
