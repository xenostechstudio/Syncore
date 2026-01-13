@props([
    'note',
    'showUser' => true,
])

@php
    $user = $note->user ?? null;
    $createdAt = $note->created_at ?? null;
    if (is_string($createdAt)) {
        $createdAt = \Carbon\Carbon::parse($createdAt);
    }
@endphp

<div class="flex items-start gap-3">
    <div class="flex-shrink-0">
        <x-ui.user-avatar :user="$user" size="md" :showPopup="true" />
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name ?? 'System' }}</span>
            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt?->diffForHumans() ?? '' }}</span>
        </div>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            <flux:icon name="pencil-square" class="inline size-3.5 text-amber-500 mr-1" />
            {{ $note->content }}
        </p>
    </div>
</div>
