@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-zinc-200 rounded-lg dark:bg-zinc-900 dark:border-zinc-800' . ($padding ? ' p-5' : '')]) }}>
    {{ $slot }}
</div>
