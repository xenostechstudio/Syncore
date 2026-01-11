@props([
    'selectable' => false,
    'selectAll' => false,
    'selectedCount' => 0,
    'showBulkActions' => false,
])

<div {{ $attributes->merge(['class' => '-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900']) }}>
    {{-- Bulk Actions Bar --}}
    @if($showBulkActions && $selectedCount > 0)
        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-200 bg-violet-50 px-4 py-2 sm:px-6 lg:px-8 dark:border-zinc-700 dark:bg-violet-900/20">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-violet-700 dark:text-violet-300">
                    {{ $selectedCount }} item{{ $selectedCount > 1 ? 's' : '' }} selected
                </span>
            </div>
            <div class="flex items-center gap-2">
                {{ $bulkActions ?? '' }}
            </div>
        </div>
    @endif

    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
        <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <tr>
                @if($selectable)
                    <th scope="col" class="w-10 py-3 pl-4 sm:pl-6 lg:pl-8">
                        <input 
                            type="checkbox" 
                            wire:model.live="selectAll"
                            class="rounded border-zinc-300 text-violet-600 focus:ring-violet-500 dark:border-zinc-600 dark:bg-zinc-700"
                        />
                    </th>
                @endif
                {{ $head }}
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
            {{ $body }}
        </tbody>
    </table>
</div>
