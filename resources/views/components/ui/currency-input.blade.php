@props([
    'symbol' => '$',
    'label' => null,
    'error' => null,
    'hint' => null,
    'type' => 'number',
    'placeholder' => '0.00',
    'step' => '0.01',
    'min' => '0',
])

<div {{ $attributes->except(['wire:model', 'wire:model.live']) }}>
    @if($label)
        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">{{ $label }}</label>
    @endif
    
    <div class="relative flex rounded-lg shadow-sm">
        <div class="pointer-events-none z-10 flex items-center rounded-l-lg border border-r-0 border-zinc-200 bg-zinc-50 px-3 text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
            <span class="text-sm">{{ $symbol }}</span>
        </div>
        <input 
            {{ $attributes->only(['wire:model', 'wire:model.live']) }}
            type="{{ $type }}"
            step="{{ $step }}"
            min="{{ $min }}"
            placeholder="{{ $placeholder }}"
            class="block w-full flex-1 rounded-none rounded-r-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-zinc-600"
        />
    </div>

    @if($error)
        <p class="mt-1 text-xs text-red-500">{{ $error }}</p>
    @elseif($hint)
        <p class="mt-1 text-xs text-zinc-500">{{ $hint }}</p>
    @endif
</div>
