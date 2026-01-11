@props([
    'selected' => false,
    'href' => null,
    'wireNavigate' => true,
])

<tr 
    @if($href)
        onclick="window.location.href='{{ $href }}'"
    @endif
    {{ $attributes->class([
        'group transition-all duration-150',
        'cursor-pointer' => $href,
        'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' => $selected,
        'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' => !$selected,
    ]) }}
>
    {{ $slot }}
</tr>
