@props([
    'activity',
    'emptyMessage' => 'Record created',
])

@php
    // Handle both Spatie Activity Log (causer) and custom ActivityLogService (user_id/user_name)
    $causer = $activity->causer ?? null;
    if (!$causer && isset($activity->user_id)) {
        $causer = (object) [
            'id' => $activity->user_id,
            'name' => $activity->user_name ?? 'System',
        ];
    }
    
    // Parse created_at if it's a string
    $activityCreatedAt = $activity->created_at ?? null;
    if (is_string($activityCreatedAt)) {
        $activityCreatedAt = \Carbon\Carbon::parse($activityCreatedAt);
    }
    
    // Parse properties if it's a JSON string
    $properties = $activity->properties ?? null;
    if (is_string($properties)) {
        $properties = collect(json_decode($properties, true) ?? []);
    } elseif (is_array($properties)) {
        $properties = collect($properties);
    } elseif (!$properties instanceof \Illuminate\Support\Collection) {
        $properties = collect();
    }
    
    // Get action/event
    $event = $activity->event ?? $activity->action ?? null;
@endphp

<div class="flex items-start gap-3">
    <div class="flex-shrink-0">
        <x-ui.user-avatar :user="$causer" size="md" :showPopup="true" />
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <x-ui.user-name :user="$causer" />
            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                {{ $activityCreatedAt?->diffForHumans() ?? '' }}
            </span>
        </div>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            @if($event === 'created')
                {{ $emptyMessage }}
            @elseif($properties->has('old') && $event === 'updated')
                @php
                    $old = $properties->get('old', []);
                    $new = $properties->get('new', $properties->get('attributes', []));
                    $changes = collect($new)->filter(fn($val, $key) => isset($old[$key]) && $old[$key] !== $val);
                @endphp
                @if($changes->isNotEmpty())
                    @foreach($changes as $key => $newVal)
                        @php
                            $oldVal = $old[$key] ?? '-';
                            $label = ucfirst(str_replace('_', ' ', $key));
                        @endphp
                        <span class="block">
                            Updated <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $label }}</span>:
                            <span class="text-zinc-400 line-through">{{ is_string($oldVal) ? $oldVal : $oldVal }}</span>
                            <flux:icon name="arrow-right" class="inline size-3 mx-1" />
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ is_string($newVal) ? $newVal : $newVal }}</span>
                        </span>
                    @endforeach
                @else
                    {{ $activity->description ?? '' }}
                @endif
            @else
                {{ $activity->description ?? '' }}
            @endif
        </p>
    </div>
</div>
