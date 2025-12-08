@props([
    'hoverable' => true,
])

<tr {{ $attributes->merge(['class' => $hoverable ? 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors' : '']) }}>
    {{ $slot }}
</tr>
