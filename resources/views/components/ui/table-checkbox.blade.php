@props([
    'selected' => false,
    'value' => null,
    'model' => 'selected',
    'header' => false,
    'selectedCount' => 0,
    'totalCount' => 0,
])

<td class="relative py-3 pl-4 pr-1 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
    {{-- Selection indicator bar --}}
    <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $selected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
    
    @if($header)
        <input 
            type="checkbox" 
            wire:model.live="selectAll"
            class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600"
            @if($selectedCount > 0 && $selectedCount < $totalCount) 
                x-data x-init="$el.indeterminate = true"
            @endif
        >
    @else
        <input 
            type="checkbox" 
            wire:model.live="{{ $model }}"
            value="{{ $value }}"
            class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $selected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}"
        >
    @endif
</td>
