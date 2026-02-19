<div>
    <flux:dropdown position="bottom" align="end">
        <button class="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100">
            <flux:icon name="language" class="size-4" />
            <span class="hidden sm:inline">{{ $locales[$currentLocale] ?? $currentLocale }}</span>
            <flux:icon name="chevron-down" class="size-3" />
        </button>

        <flux:menu class="w-40">
            @foreach($locales as $code => $name)
                <button 
                    type="button" 
                    wire:click="switchLocale('{{ $code }}')"
                    class="flex w-full items-center justify-between px-3 py-2 text-sm {{ $currentLocale === $code ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' : 'text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
                >
                    <span>{{ $name }}</span>
                    @if($currentLocale === $code)
                        <flux:icon name="check" class="size-4 text-emerald-500" />
                    @endif
                </button>
            @endforeach
        </flux:menu>
    </flux:dropdown>
</div>
