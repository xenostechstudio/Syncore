<div>
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
                <a href="{{ route('crm.leads.create') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Leads</span>
                
                {{-- Gear Menu --}}
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>
                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Import leads</span>
                        </button>
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export All</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center Group: Search or Selection Toolbar --}}
            <div class="flex flex-1 items-center justify-center">
                @if(count($selected) > 0)
                    {{-- Selection Toolbar --}}
                    <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-2 duration-200">
                        {{-- Count Selected Button --}}
                        <button wire:click="clearSelection" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <span>{{ count($selected) }} selected</span>
                            <flux:icon name="x-mark" class="size-3.5" />
                        </button>

                        <div class="h-5 w-px bg-zinc-200 dark:bg-zinc-700"></div>

                        {{-- Export --}}
                        <button wire:click="exportSelected" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-down-tray" class="size-4" />
                            <span>Export</span>
                        </button>

                        {{-- Actions Dropdown --}}
                        <flux:dropdown position="bottom" align="center">
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="ellipsis-horizontal" class="size-4" />
                            </button>

                            <flux:menu class="w-56">
                                <button type="button" wire:click="bulkUpdateStatus('contacted')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="phone" class="size-4 text-amber-500" />
                                    <span>Mark as Contacted</span>
                                </button>
                                <button type="button" wire:click="bulkUpdateStatus('qualified')" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="check-circle" class="size-4 text-emerald-500" />
                                    <span>Mark as Qualified</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                    <flux:icon name="user-plus" class="size-4" />
                                    <span>Convert to Customer</span>
                                </button>
                                <flux:menu.separator />
                                <button type="button" wire:click="confirmBulkDelete" class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <flux:icon name="trash" class="size-4" />
                                    <span>Delete</span>
                                </button>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                @else
                    {{-- Search & Filter --}}
                    <x-ui.searchbox-dropdown placeholder="Search leads..." widthClass="w-[520px]" width="520px">
                        <div class="flex flex-col gap-4 p-3 md:flex-row">
                            {{-- Status Filter --}}
                            <div class="flex-1 border-b border-zinc-100 pb-3 md:border-b-0 md:border-r md:pb-0 md:pr-3 dark:border-zinc-700">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="funnel" class="size-3.5" />
                                    <span>Status</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('status', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>All Status</span>
                                        @if(empty($status))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'new')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                            <span>New</span>
                                        </div>
                                        @if($status === 'new')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'contacted')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            <span>Contacted</span>
                                        </div>
                                        @if($status === 'contacted')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'qualified')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Qualified</span>
                                        </div>
                                        @if($status === 'qualified')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'converted')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                                            <span>Converted</span>
                                        </div>
                                        @if($status === 'converted')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    <button type="button" wire:click="$set('status', 'lost')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <div class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            <span>Lost</span>
                                        </div>
                                        @if($status === 'lost')<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                </div>
                            </div>

                            {{-- Source Filter --}}
                            <div class="flex-1">
                                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="globe-alt" class="size-3.5" />
                                    <span>Source</span>
                                </div>
                                <div class="space-y-1">
                                    <button type="button" wire:click="$set('source', '')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                        <span>All Sources</span>
                                        @if(empty($source))<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                    </button>
                                    @foreach($sources as $key => $label)
                                        <button type="button" wire:click="$set('source', '{{ $key }}')" class="flex w-full items-center justify-between rounded-md px-2.5 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                            <span>{{ $label }}</span>
                                            @if($source === $key)<flux:icon name="check" class="size-3.5 text-violet-500" />@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-ui.searchbox-dropdown>
                @endif
            </div>

            {{-- Right Group: Pagination Info + View Toggle --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $leads->firstItem() ?? 0 }}-{{ $leads->lastItem() ?? 0 }}/{{ $leads->total() }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button type="button" wire:click="goToPreviousPage" @disabled($leads->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-left" class="size-4" />
                        </button>
                        <button type="button" wire:click="goToNextPage" @disabled(!$leads->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                            <flux:icon name="chevron-right" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- View Toggle --}}
                <x-ui.view-toggle :view="$view" :views="['list', 'grid']" />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div>
        @if($leads->isEmpty())
            {{-- Empty State --}}
            <div class="-mx-4 -mt-6 -mb-6 flex min-h-[70vh] items-center justify-center bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <div class="-mt-16 flex flex-col items-center gap-4 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon name="user-group" class="size-8 text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-base font-medium text-zinc-900 dark:text-zinc-100">No leads found</p>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Get started by creating a new lead</p>
                    </div>
                    <a href="{{ route('crm.leads.create') }}" wire:navigate class="mt-2 inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="plus" class="size-4" />
                        New Lead
                    </a>
                </div>
            </div>
        @elseif($view === 'grid')
            {{-- Grid View --}}
            <div class="-mx-4 -mt-6 -mb-6 bg-white p-4 sm:-mx-6 sm:p-6 lg:-mx-8 lg:p-8 dark:bg-zinc-900">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($leads as $lead)
                        @php
                            $statusColors = [
                                'new' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400'],
                                'contacted' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400'],
                                'qualified' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400'],
                                'converted' => ['bg' => 'bg-violet-100 dark:bg-violet-900/30', 'text' => 'text-violet-700 dark:text-violet-400'],
                                'lost' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400'],
                            ];
                            $colors = $statusColors[$lead->status] ?? ['bg' => 'bg-zinc-100', 'text' => 'text-zinc-700'];
                        @endphp
                        <a href="{{ route('crm.leads.edit', $lead->id) }}" wire:navigate class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ strtoupper(substr($lead->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $lead->name }}</p>
                                            @if($lead->company_name)
                                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $lead->company_name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="ml-2 inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $colors['bg'] }} {{ $colors['text'] }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </div>
                            <div class="mt-3 space-y-1">
                                @if($lead->email)
                                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $lead->email }}</p>
                                @endif
                                @if($lead->phone)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $lead->phone }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            {{-- List View (Table) --}}
            <div class="-mx-4 -mt-6 -mb-6 overflow-x-auto bg-white sm:-mx-6 lg:-mx-8 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
                        <tr>
                            <th scope="col" class="w-10 py-3 pl-4 pr-2 sm:pl-6 lg:pl-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-zinc-600">
                            </th>
                            <th scope="col" class="py-3 pl-2 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Name</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Company</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Contact</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Source</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                            <th scope="col" class="px-4 py-3 pr-4 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 sm:pr-6 lg:pr-8 dark:text-zinc-400">Assigned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($leads as $lead)
                            @php $isSelected = in_array($lead->id, $selected); @endphp
                            <tr wire:key="lead-{{ $lead->id }}" onclick="window.Livewire.navigate('{{ route('crm.leads.edit', $lead->id) }}')" class="group cursor-pointer transition-all duration-150 {{ $isSelected ? 'bg-zinc-900/[0.03] dark:bg-zinc-100/[0.03]' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                                <td class="relative py-3 pl-4 pr-2 sm:pl-6 lg:pl-8" onclick="event.stopPropagation()">
                                    <div class="absolute inset-y-0 left-0 w-0.5 transition-all duration-150 {{ $isSelected ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-transparent group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700' }}"></div>
                                    <input type="checkbox" wire:model.live="selected" value="{{ $lead->id }}" class="rounded border-zinc-300 bg-white text-zinc-900 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:ring-zinc-600 {{ $isSelected ? 'ring-1 ring-zinc-900/20 dark:ring-zinc-100/20' : '' }}">
                                </td>
                                <td class="py-3 pl-2 pr-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ strtoupper(substr($lead->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $lead->name }}</p>
                                            @if($lead->job_title)<p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $lead->job_title }}</p>@endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $lead->company_name ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $lead->email ?? '-' }}</p>
                                    @if($lead->phone)<p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $lead->phone }}</p>@endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $sources[$lead->source] ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'new' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'contacted' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'qualified' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'converted' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
                                            'lost' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$lead->status] ?? 'bg-zinc-100 text-zinc-700' }}">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 pr-4 sm:pr-6 lg:pr-8">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $lead->assignedTo?->name ?? '-' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @isset($showDeleteConfirm)
        <x-ui.delete-confirm-modal 
            wire:model="showDeleteConfirm"
            :validation="$deleteValidation ?? []"
            title="Confirm Delete"
            itemLabel="leads"
        />
    @endisset
</div>
