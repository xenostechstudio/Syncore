<div>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
    </div>

    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.departments.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Departments</span>
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>
                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export All</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center Group: Search --}}
            <div class="flex flex-1 items-center justify-center">
                <x-ui.searchbox-dropdown placeholder="Search departments..." widthClass="w-[400px]" width="400px">
                    <x-slot:badge>
                        @if($status !== '')
                            <div class="flex items-center">
                                <span class="inline-flex h-6 items-center gap-1.5 rounded-md bg-zinc-900 px-2 text-[10px] font-semibold text-white shadow-sm dark:bg-zinc-100 dark:text-zinc-900">
                                    <span>{{ $status === 'active' ? 'Active' : 'Inactive' }}</span>
                                    <button type="button" onclick="event.stopPropagation()" wire:click="$set('status', '')" class="-mr-0.5 inline-flex h-4 w-4 items-center justify-center rounded-md text-zinc-400 hover:bg-zinc-200 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                                        <flux:icon name="x-mark" class="size-3" />
                                    </button>
                                </span>
                            </div>
                        @endif
                    </x-slot:badge>
                    <div class="flex flex-col gap-4 p-3 md:flex-row">
                        {{-- Filters column --}}
                        <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="funnel" class="size-3.5" />
                                <span>Filters</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>All Status</span>
                                    @if($status === '')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('status', 'active')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Active</span>
                                    </div>
                                    @if($status === 'active')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('status', 'inactive')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                        <span>Inactive</span>
                                    </div>
                                    @if($status === 'inactive')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                            </div>
                        </div>
                        {{-- Sort column --}}
                        <div class="flex-1 md:pl-3">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <flux:icon name="arrows-up-down" class="size-3.5" />
                                <span>Sort By</span>
                            </div>
                            <div class="space-y-1">
                                <button type="button" wire:click="$set('sort', 'name_asc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Name: A to Z</span>
                                    @if($sort === 'name_asc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'name_desc')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Name: Z to A</span>
                                    @if($sort === 'name_desc')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'code')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Code</span>
                                    @if($sort === 'code')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                                <button type="button" wire:click="$set('sort', 'latest')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <span>Latest</span>
                                    @if($sort === 'latest')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                </button>
                            </div>
                        </div>
                    </div>
                </x-ui.searchbox-dropdown>
            </div>

            {{-- Right Group --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $departments->firstItem() ?? 0 }}-{{ $departments->lastItem() ?? 0 }}/{{ $departments->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button type="button" wire:click="goToPreviousPage" @disabled($departments->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" wire:click="goToNextPage" @disabled(!$departments->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>
                <x-ui.view-toggle :view="$view" :views="['list', 'grid']" />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        @if($view === 'grid')
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($departments as $dept)
                    <a href="{{ route('hr.departments.edit', $dept->id) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $dept->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $dept->code ?? '-' }}</p>
                            </div>
                            @if($dept->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                            @endif
                        </div>
                        <div class="mt-3 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ $dept->employees->count() }} employees</span>
                            @if($dept->manager)
                                <span>{{ $dept->manager->name }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center">
                        <flux:icon name="building-office" class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500">No departments found</p>
                    </div>
                @endforelse
            </div>
        @else
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
                            </th>
                            <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Name</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Code</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Manager</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Employees</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                            <th scope="col" class="w-10 py-3 pr-4 sm:pr-6 lg:pr-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($departments as $dept)
                            <tr wire:key="dept-{{ $dept->id }}" onclick="window.Livewire.navigate('{{ route('hr.departments.edit', $dept->id) }}')" class="cursor-pointer transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <input type="checkbox" wire:model.live="selected" value="{{ $dept->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
                                </td>
                                <td class="py-3 pl-2 pr-4">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $dept->name }}</p>
                                    @if($dept->parent)
                                        <p class="text-xs text-zinc-500">↳ {{ $dept->parent->name }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $dept->code ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $dept->manager?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $dept->employees->count() }}</td>
                                <td class="px-4 py-3">
                                    @if($dept->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 sm:pr-6 lg:pr-8"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-400">No departments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
