@props([
    'user' => null,
    'position' => 'bottom',
    'align' => 'start',
])

@php
    // Check if user is a proper model with isOutOfOffice method
    $isOutOfOffice = ($user && method_exists($user, 'isOutOfOffice')) ? $user->isOutOfOffice() : false;
    
    // Generate initials - handle both User model and stdClass
    $initials = '';
    if ($user) {
        if (method_exists($user, 'initials')) {
            $initials = $user->initials();
        } elseif (isset($user->name)) {
            // Fallback: generate initials from name
            $words = explode(' ', $user->name);
            $initials = count($words) >= 2 
                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                : strtoupper(substr($user->name, 0, 2));
        }
    }
@endphp

@if($user)
    <flux:dropdown position="{{ $position }}" align="{{ $align }}">
        <button type="button" class="text-sm font-medium text-zinc-900 hover:text-zinc-700 dark:text-zinc-100 dark:hover:text-zinc-300" onclick="event.stopPropagation()">
            {{ $user->name }}
        </button>

        <flux:menu class="w-72">
            <div class="px-3 py-2">
                <div class="flex items-start gap-3">
                    <div class="relative flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-zinc-200 text-sm font-semibold text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                        {{ $initials }}
                        @if($isOutOfOffice)
                            <div class="absolute -right-0.5 -top-0.5 h-3 w-3 rounded-full border-2 border-white bg-amber-500 dark:border-zinc-900"></div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name ?? 'Unknown' }}</p>
                        @if(!empty($user->email))
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                        @endif
                        @if(!empty($user->phone))
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->phone }}</p>
                        @endif
                    </div>
                </div>
                
                @if($isOutOfOffice)
                    <div class="mt-3 rounded-lg bg-amber-50 p-2.5 dark:bg-amber-900/20">
                        <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400">
                            <flux:icon name="calendar-days" class="size-4" />
                            <span class="text-xs font-medium">Out of Office</span>
                        </div>
                        @if($user->out_of_office_message)
                            <p class="mt-1.5 text-xs text-amber-600 dark:text-amber-300">{{ $user->out_of_office_message }}</p>
                        @endif
                        <p class="mt-1 text-[10px] text-amber-500 dark:text-amber-400/70">
                            {{ $user->out_of_office_start->format('M d') }} - {{ $user->out_of_office_end->format('M d, Y') }}
                        </p>
                    </div>
                @endif
            </div>
            <flux:menu.separator />
            <a href="{{ route('settings.users.edit', $user->id) }}" wire:navigate class="flex w-full items-center justify-between px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-200 dark:hover:bg-zinc-800">
                <span>View Profile</span>
                <flux:icon name="arrow-up-right" class="size-4 text-zinc-400" />
            </a>
        </flux:menu>
    </flux:dropdown>
@else
    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">System</span>
@endif
