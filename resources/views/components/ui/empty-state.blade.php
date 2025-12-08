@props([
    'icon' => 'inbox',
    'title' => 'No data',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 text-center']) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
        <flux:icon :name="$icon" class="size-7 text-zinc-400" />
    </div>
    <h3 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
    @if(isset($action))
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
