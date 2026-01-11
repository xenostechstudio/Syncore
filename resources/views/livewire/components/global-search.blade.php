<div
    x-data="{
        open: @entangle('isOpen'),
        selectedIndex: 0,
        resultsCount: 0,
        init() {
            this.$watch('open', (value) => {
                if (value) {
                    this.selectedIndex = 0;
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                }
            });
            
            window.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    $wire.open();
                }
            });
        },
        moveUp() {
            if (this.selectedIndex > 0) this.selectedIndex--;
            this.scrollToSelected();
        },
        moveDown() {
            if (this.selectedIndex < this.resultsCount - 1) this.selectedIndex++;
            this.scrollToSelected();
        },
        scrollToSelected() {
            this.$nextTick(() => {
                const el = this.$refs.resultsList?.querySelector(`[data-index='${this.selectedIndex}']`);
                el?.scrollIntoView({ block: 'nearest' });
            });
        },
        selectCurrent() {
            const el = this.$refs.resultsList?.querySelector(`[data-index='${this.selectedIndex}']`);
            if (el) el.click();
        }
    }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) { open = false; $wire.close(); }"
    class="fixed inset-0 z-[100] overflow-y-auto"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm" @click="open = false; $wire.close()"></div>

    {{-- Modal --}}
    <div class="relative flex min-h-full items-start justify-center p-4 pt-[15vh]">
        <div
            class="relative w-full max-w-xl overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900"
            @click.outside="open = false; $wire.close()"
            @keydown.arrow-up.prevent="moveUp()"
            @keydown.arrow-down.prevent="moveDown()"
            @keydown.enter.prevent="selectCurrent()"
        >
            {{-- Search Input --}}
            <div class="flex items-center gap-3 border-b border-zinc-100 px-4 dark:border-zinc-800">
                <flux:icon name="magnifying-glass" class="size-5 text-zinc-400" />
                <input
                    type="text"
                    x-ref="searchInput"
                    wire:model.live.debounce.400ms="query"
                    placeholder="Search orders, invoices, customers..."
                    class="h-12 w-full border-0 bg-transparent text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100"
                    autocomplete="off"
                />
                <kbd class="rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-400 dark:bg-zinc-800">ESC</kbd>
            </div>

            {{-- Results --}}
            <div class="max-h-[50vh] overflow-y-auto" x-ref="resultsList" x-effect="resultsCount = {{ count($results) }}; selectedIndex = 0;">
                @if(strlen($query) < 2)
                    <div class="px-4 py-6 text-center">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Type at least 2 characters</p>
                        <div class="mt-3 flex items-center justify-center gap-3 text-xs text-zinc-400">
                            <span><kbd class="rounded bg-zinc-100 px-1 dark:bg-zinc-800">↑↓</kbd> Navigate</span>
                            <span><kbd class="rounded bg-zinc-100 px-1 dark:bg-zinc-800">↵</kbd> Select</span>
                        </div>
                    </div>
                @elseif(empty($results))
                    <div class="px-4 py-6 text-center">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No results for "{{ $query }}"</p>
                    </div>
                @else
                    <div class="py-1">
                        @php $currentType = null; $index = 0; @endphp
                        @foreach($results as $result)
                            @if($currentType !== $result['type'])
                                @php $currentType = $result['type']; @endphp
                                <div class="px-3 py-1.5">
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">{{ ucfirst($result['type']) }}s</span>
                                </div>
                            @endif
                            <a
                                href="{{ $result['url'] }}"
                                wire:navigate
                                data-index="{{ $index }}"
                                @click="open = false; $wire.close()"
                                class="flex items-center gap-3 px-3 py-2 transition-colors"
                                :class="selectedIndex === {{ $index }} ? 'bg-zinc-100 dark:bg-zinc-800' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50'"
                                @mouseenter="selectedIndex = {{ $index }}"
                            >
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $result['color'] }}-100 dark:bg-{{ $result['color'] }}-900/30">
                                    <flux:icon name="{{ $result['icon'] }}" class="size-4 text-{{ $result['color'] }}-600 dark:text-{{ $result['color'] }}-400" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $result['title'] }}</p>
                                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $result['subtitle'] }}</p>
                                </div>
                                @if($result['meta'])
                                    <span class="text-xs text-zinc-400">{{ $result['meta'] }}</span>
                                @endif
                            </a>
                            @php $index++; @endphp
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            @if(count($results) > 0)
                <div class="border-t border-zinc-100 px-3 py-2 text-right dark:border-zinc-800">
                    <span class="text-xs text-zinc-400">{{ count($results) }} results</span>
                </div>
            @endif
        </div>
    </div>
</div>
