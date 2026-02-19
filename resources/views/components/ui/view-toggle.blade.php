@props([
    'view' => 'list',
    'views' => ['list', 'grid'], // Options: list, grid, kanban
])

<div class="flex h-9 items-center gap-0.5 rounded-lg border border-zinc-200 p-0.5 dark:border-zinc-700">
    {{-- List View --}}
    @if(in_array('list', $views))
        <button 
            type="button"
            wire:click="setView('list')"
            wire:loading.attr="disabled"
            class="{{ $view === 'list' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md p-1.5 transition-colors disabled:opacity-50"
            title="{{ __('common.list_view') }}"
        >
            <svg class="size-[18px]" wire:loading.class="animate-pulse" wire:target="setView('list')" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
    @endif

    {{-- Grid/Thumbnail View --}}
    @if(in_array('grid', $views))
        <button 
            type="button"
            wire:click="setView('grid')"
            wire:loading.attr="disabled"
            class="{{ $view === 'grid' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md p-1.5 transition-colors disabled:opacity-50"
            title="{{ __('common.grid_view') }}"
        >
            <svg class="size-[18px]" wire:loading.class="animate-pulse" wire:target="setView('grid')" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
        </button>
    @endif

    {{-- Kanban View --}}
    @if(in_array('kanban', $views))
        <button 
            type="button"
            wire:click="setView('kanban')"
            wire:loading.attr="disabled"
            class="{{ $view === 'kanban' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md p-1.5 transition-colors disabled:opacity-50"
            title="{{ __('common.kanban_view') }}"
        >
            <svg class="size-[18px]" wire:loading.class="animate-pulse" wire:target="setView('kanban')" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        </button>
    @endif
</div>
