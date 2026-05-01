@props([
    'title',
    'description',
])

<div class="space-y-2">
    <h2 class="text-3xl font-semibold leading-[1.1] tracking-tight text-zinc-50">
        {{ $title }}
    </h2>
    <p class="text-sm text-zinc-400">
        {{ $description }}
    </p>
</div>
