<div class="relative" x-data="{ open: false }">
    {{-- Notification Bell --}}
    <button 
        type="button"
        @click="open = !open"
        class="relative flex items-center justify-center rounded-lg p-2 text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
    >
        <flux:icon name="bell" class="size-5" />
        @if($this->unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-medium text-white">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div 
        x-show="open" 
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-full z-50 mt-2 w-96 rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Notifications</h3>
            @if($this->unreadCount > 0)
                <button 
                    type="button"
                    wire:click="markAllAsRead"
                    class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                >
                    Mark all as read
                </button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="max-h-96 overflow-y-auto">
            @forelse($this->notifications as $notification)
                <div 
                    wire:key="notification-{{ $notification->id }}"
                    class="flex items-start gap-3 border-b border-zinc-50 px-4 py-3 transition-colors last:border-0 hover:bg-zinc-50 dark:border-zinc-800/50 dark:hover:bg-zinc-800/50 {{ !$notification->isRead() ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}"
                >
                    {{-- Icon --}}
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-{{ $notification->color }}-100 text-{{ $notification->color }}-600 dark:bg-{{ $notification->color }}-900/30 dark:text-{{ $notification->color }}-400">
                        <flux:icon name="{{ $notification->icon }}" class="size-4" />
                    </div>

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $notification->title }}
                            </p>
                            @if(!$notification->isRead())
                                <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full bg-blue-500"></span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">
                            {{ $notification->message }}
                        </p>
                        <div class="mt-1.5 flex items-center gap-3">
                            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                            @if($notification->action_url)
                                <a 
                                    href="{{ $notification->action_url }}"
                                    wire:click="markAsRead({{ $notification->id }})"
                                    class="text-xs font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200"
                                >
                                    View →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="bell-slash" class="size-6 text-zinc-400" />
                    </div>
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No notifications</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($this->notifications->isNotEmpty())
            <div class="border-t border-zinc-100 px-4 py-2 dark:border-zinc-800">
                <a 
                    href="#" 
                    class="block text-center text-xs font-medium text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                >
                    View all notifications
                </a>
            </div>
        @endif
    </div>
</div>
