@props([
    'headers' => [],
    'empty' => 'No data found.',
])

<x-ui.card :padding="false">
    @if(isset($toolbar))
        <div class="flex items-center justify-between gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700">
            {{ $toolbar }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ $header }}
                        </th>
                    @endforeach
                    @if(isset($actions))
                        <th scope="col" class="relative px-4 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($pagination))
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            {{ $pagination }}
        </div>
    @endif
</x-ui.card>
