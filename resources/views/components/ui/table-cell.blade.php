@props([
    'primary' => false,
])

<td {{ $attributes->merge(['class' => 'whitespace-nowrap px-4 py-3 text-sm ' . ($primary ? 'font-medium text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400')]) }}>
    {{ $slot }}
</td>
