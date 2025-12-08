@if ($paginator->hasPages())
    <nav class="flex items-center gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="rounded-lg border border-zinc-200 px-3 py-1.5 text-sm font-light text-zinc-300 dark:border-zinc-700 dark:text-zinc-600">
                Previous
            </span>
        @else
            <button wire:click="previousPage" class="rounded-lg border border-zinc-200 px-3 py-1.5 text-sm font-light text-zinc-600 transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                Previous
            </button>
        @endif

        {{-- Page Numbers --}}
        <div class="flex items-center gap-1">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-sm text-zinc-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-light text-white dark:bg-zinc-100 dark:text-zinc-900">
                                {{ $page }}
                            </span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" class="rounded-lg px-3 py-1.5 text-sm font-light text-zinc-600 transition-colors hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" class="rounded-lg border border-zinc-200 px-3 py-1.5 text-sm font-light text-zinc-600 transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                Next
            </button>
        @else
            <span class="rounded-lg border border-zinc-200 px-3 py-1.5 text-sm font-light text-zinc-300 dark:border-zinc-700 dark:text-zinc-600">
                Next
            </span>
        @endif
    </nav>
@endif
