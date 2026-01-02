@props([
    'activities',
    'model' => null,
    'emptyMessage' => 'Record created',
    'createdAt' => null,
])

@php
    $firstActivity = $activities->first();
    $isToday = $firstActivity && isset($firstActivity->created_at) && $firstActivity->created_at?->isToday();
@endphp

{{-- Date Separator --}}
<div class="flex items-center gap-3 py-2">
    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
        {{ $isToday ? 'Today' : 'Activity' }}
    </span>
    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
</div>

{{-- Activity Items --}}
<div class="space-y-4">
    @forelse($activities as $activity)
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <x-ui.user-avatar :user="$activity->causer ?? null" size="md" :showPopup="true" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <x-ui.user-name :user="$activity->causer ?? null" />
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $activity->created_at?->diffForHumans() ?? '' }}</span>
                </div>
                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                    @if(($activity->type ?? null) === 'note')
                        {{-- Note display --}}
                        {{ $activity->content }} <span class="text-zinc-400 dark:text-zinc-500">(Internal Note)</span>
                    @elseif(($activity->event ?? null) === 'created')
                        {{ $emptyMessage }}
                    @elseif(($activity->properties ?? collect())->has('old') && ($activity->event ?? null) === 'updated')
                        @php
                            $old = $activity->properties->get('old', []);
                            $new = $activity->properties->get('attributes', []);
                            $changes = collect($new)->filter(fn($val, $key) => isset($old[$key]) && $old[$key] !== $val);
                        @endphp
                        @if($changes->isNotEmpty())
                            @foreach($changes as $key => $newVal)
                                @php
                                    $oldVal = $old[$key] ?? '-';
                                    $label = ucfirst(str_replace('_', ' ', $key));
                                @endphp
                                <div>
                                    Updated <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $label }}</span>
                                    <span class="text-zinc-400">{{ is_string($oldVal) ? ucfirst($oldVal) : $oldVal }}</span>
                                    <flux:icon name="arrow-right" class="inline size-3 mx-1" />
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ is_string($newVal) ? ucfirst($newVal) : $newVal }}</span>
                                </div>
                            @endforeach
                        @else
                            {{ $activity->description ?? '' }}
                        @endif
                    @else
                        {{ $activity->description ?? '' }}
                    @endif
                </div>
            </div>
        </div>
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
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $emptyMessage }}</p>
            </div>
        </div>
    @endforelse
</div>
