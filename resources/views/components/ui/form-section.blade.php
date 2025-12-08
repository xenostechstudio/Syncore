@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-3']) }}>
    @if($title || $description)
        <div class="lg:col-span-1">
            @if($title)
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
            @endif
            @if($description)
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
            @endif
        </div>
    @endif
    
    <div class="{{ ($title || $description) ? 'lg:col-span-2' : 'lg:col-span-3' }}">
        <x-ui.card>
            <div class="space-y-6">
                {{ $slot }}
            </div>
        </x-ui.card>
    </div>
</div>
