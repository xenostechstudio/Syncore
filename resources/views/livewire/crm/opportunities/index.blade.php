<div
    x-data="{
        dragging: null,
        dragOverPipeline: null,
        startDrag(e, oppId) {
            this.dragging = oppId;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', oppId);
        },
        endDrag() {
            this.dragging = null;
            this.dragOverPipeline = null;
        },
        dragOver(e, pipelineId) {
            e.preventDefault();
            this.dragOverPipeline = pipelineId;
        },
        dragLeave() {
            this.dragOverPipeline = null;
        },
        drop(e, pipelineId) {
            e.preventDefault();
            const oppId = e.dataTransfer.getData('text/plain');
            if (oppId) {
                $wire.moveToStage(parseInt(oppId), pipelineId);
            }
            this.dragging = null;
            this.dragOverPipeline = null;
        }
    }"
>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
        @endif
    </div>

    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('crm.opportunities.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Opportunities</span>
                
                {{-- Gear Menu --}}
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>
                    <flux:menu class="w-48">
                        <button type="button" wire:click="openImportModal" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import opportunities</span>
                        </button>
                        <button type="button" wire:click="exportSelected" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export All</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center: Search --}}
            <div class="flex flex-1 items-center justify-center">
                <x-ui.searchbox-dropdown placeholder="Search opportunities..." widthClass="w-[520px]" width="520px">
                    <div class="flex flex-col gap-4 p-3 md:flex-row">
                        {{-- Stage Filter --}}
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="funnel" class="size-3.5" />
                                <span>Stage</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button" wire:click="$set('stage', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>All Stages</span>
                                    @if(empty($stage))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                @foreach($pipelines as $pipeline)
                                    <button type="button" wire:click="$set('stage', '{{ $pipeline->id }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $pipeline->color ?? '#6b7280' }}"></span>
                                            <span>{{ $pipeline->name }}</span>
                                        </div>
                                        @if($stage == $pipeline->id)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-ui.searchbox-dropdown>
            </div>

            {{-- Right Group --}}
            <div class="flex items-center gap-3">
                @if($view === 'list')
                    {{-- Pagination Info --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $opportunities->firstItem() ?? 0 }}-{{ $opportunities->lastItem() ?? 0 }}/{{ $opportunities->total() }}
                        </span>
                        <div class="flex items-center gap-0.5">
                            <button type="button" wire:click="goToPreviousPage" @disabled($opportunities->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="chevron-left" class="size-4" />
                            </button>
                            <button type="button" wire:click="goToNextPage" @disabled(!$opportunities->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="chevron-right" class="size-4" />
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Stats Toggle --}}
                <div class="flex h-9 items-center rounded-lg border border-zinc-200 p-0.5 dark:border-zinc-700">
                    <button 
                        type="button"
                        wire:click="toggleStats"
                        class="{{ $showStats ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300' }} rounded-md p-1.5 transition-colors"
                        title="{{ $showStats ? 'Hide statistics' : 'Show statistics' }}"
                    >
                        <flux:icon name="chart-bar" class="size-[18px]" />
                    </button>
                </div>

                {{-- View Toggle --}}
                <x-ui.view-toggle :view="$view" :views="['list', 'kanban']" />
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    @if($showStats && $statistics)
        <div class="-mx-4 -mt-6 mb-6 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="briefcase" class="size-4 text-zinc-400 dark:text-zinc-500" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Open Opportunities</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['total_opportunities']) }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($statistics['total_revenue'] / 1000000, 1) }}M expected</p>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2">
                        <flux:icon name="trophy" class="size-4 text-emerald-500 dark:text-emerald-400" />
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Won</p>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($statistics['won_count']) }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($statistics['won_revenue'] / 1000000, 1) }}M revenue</p>
                </div>
                @foreach(array_slice($statistics['pipelines'], 0, 2) as $pipelineId => $pipelineStat)
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full" style="background-color: {{ $pipelineStat['color'] }}"></span>
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">{{ $pipelineStat['name'] }}</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($pipelineStat['count']) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($pipelineStat['total'] / 1000000, 1) }}M</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Content --}}
    <div>
        @if($view === 'kanban')
            {{-- Kanban View with Drag & Drop --}}
            <div class="flex gap-4 overflow-x-auto pb-4">
                @foreach($pipelines as $pipeline)
                    @php $pipelineOpps = $opportunities[$pipeline->id] ?? collect(); @endphp
                    <div class="w-72 shrink-0">
                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full" style="background-color: {{ $pipeline->color ?? '#6b7280' }}"></div>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $pipeline->name }}</span>
                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">{{ $pipelineOpps->count() }}</span>
                            </div>
                            <span class="text-xs text-zinc-500">Rp {{ number_format($pipelineOpps->sum('expected_revenue') / 1000000, 1) }}M</span>
                        </div>
                        <div 
                            class="space-y-3 rounded-lg p-3 transition-colors"
                            :class="dragOverPipeline === {{ $pipeline->id }} ? 'bg-zinc-200 dark:bg-zinc-700' : 'bg-zinc-50 dark:bg-zinc-800/50'"
                            style="min-height: 400px;"
                            @dragover="dragOver($event, {{ $pipeline->id }})"
                            @dragleave="dragLeave()"
                            @drop="drop($event, {{ $pipeline->id }})"
                        >
                            @foreach($pipelineOpps as $opp)
                                <div 
                                    class="cursor-grab rounded-lg border border-zinc-200 bg-white p-3 shadow-sm transition-opacity dark:border-zinc-700 dark:bg-zinc-800"
                                    :class="dragging == {{ $opp->id }} ? 'opacity-50' : ''"
                                    draggable="true"
                                    @dragstart="startDrag($event, {{ $opp->id }})"
                                    @dragend="endDrag()"
                                >
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $opp->name }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $opp->customer?->name ?? 'No customer' }}</p>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Rp {{ number_format($opp->expected_revenue / 1000000, 1) }}M</span>
                                        <span class="text-xs text-zinc-500">{{ $opp->probability }}%</span>
                                    </div>
                                    <div class="mt-2 flex gap-1">
                                        <a href="{{ route('crm.opportunities.edit', $opp->id) }}" wire:navigate class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300">
                                            <flux:icon name="pencil" class="size-3.5" />
                                        </a>
                                        @if(!$pipeline->is_won && !$pipeline->is_lost)
                                            <button wire:click="markAsWon({{ $opp->id }})" class="rounded p-1 text-zinc-400 hover:bg-emerald-100 hover:text-emerald-600 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400">
                                                <flux:icon name="check" class="size-3.5" />
                                            </button>
                                            <button wire:click="markAsLost({{ $opp->id }})" class="rounded p-1 text-zinc-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400">
                                                <flux:icon name="x-mark" class="size-3.5" />
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- List View --}}
            @if($opportunities->isEmpty())
                <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                    <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="briefcase" class="size-8 text-zinc-400" />
                        </div>
                        <div>
                            <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">No opportunities found</p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Get started by creating a new opportunity</p>
                        </div>
                        <a href="{{ route('crm.opportunities.create') }}" wire:navigate class="mt-2 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="plus" class="size-4" />
                            New Opportunity
                        </a>
                    </div>
                </div>
            @else
                <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                            <tr>
                                <th scope="col" class="py-3 pl-4 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pl-6 lg:pl-8 dark:text-zinc-400">Opportunity</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Customer</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Stage</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Expected Revenue</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Probability</th>
                                <th scope="col" class="px-4 py-3 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pr-6 lg:pr-8 dark:text-zinc-400">Assigned</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($opportunities as $opp)
                                <tr wire:key="opp-{{ $opp->id }}" onclick="window.Livewire.navigate('{{ route('crm.opportunities.edit', $opp->id) }}')" class="group cursor-pointer transition-all duration-150 hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="relative py-3 pl-4 pr-4 sm:pl-6 lg:pl-8">
                                        <div class="absolute inset-y-0 left-0 w-0.5 bg-transparent transition-all duration-150 group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700"></div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $opp->name }}</p>
                                        @if($opp->expected_close_date)
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Close: {{ $opp->expected_close_date->format('M d, Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $opp->customer?->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" style="background-color: {{ $opp->pipeline->color ?? '#6b7280' }}20; color: {{ $opp->pipeline->color ?? '#6b7280' }}">
                                            {{ $opp->pipeline->name }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($opp->expected_revenue, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $opp->probability }}%</span>
                                    </td>
                                    <td class="px-4 py-3 pr-4 sm:pr-6 lg:pr-8">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $opp->assignedTo?->name ?? '-' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>

    {{-- Import Modal --}}
    <x-ui.import-modal
        wire:model="showImportModal"
        title="Import Opportunities"
        :livewire="true"
        :result="$this->importResult"
        :importErrors="$this->importErrors"
    />
</div>
