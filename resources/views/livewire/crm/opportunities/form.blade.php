<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('crm.opportunities.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Opportunity</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $opportunityId ? $name : 'New Opportunity' }}
                        </span>
                        @if($this->selectedLead)
                            <a href="{{ route('crm.leads.edit', $this->selectedLead->id) }}" wire:navigate class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                <flux:icon name="user" class="size-3" />
                                {{ $this->selectedLead->name }}
                            </a>
                        @endif
                        @if($opportunityId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="document-duplicate" class="size-4" />
                                        <span>Duplicate</span>
                                    </button>
                                    <flux:menu.separator />
                                    <button type="button" wire:click="delete" wire:confirm="Delete this opportunity?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
                                    </button>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot:header>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">Please fix the following errors:</span>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>

    {{-- Action Buttons Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                    @if($opportunityId && $opportunity && !$opportunity->isWon() && !$opportunity->isLost())
                        <button type="button" wire:click="markAsWon" wire:confirm="Mark this opportunity as Won?" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/30">
                            <flux:icon name="trophy" class="size-4" />
                            Mark as Won
                        </button>
                        <button type="button" wire:click="markAsLost" wire:confirm="Mark this opportunity as Lost?" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="x-circle" class="size-4" />
                            Mark as Lost
                        </button>
                    @endif
                    @if($opportunityId && $opportunity && $opportunity->isWon() && $this->selectedLead && $this->selectedLead->status !== 'converted')
                        <button type="button" wire:click="convertLeadToCustomer" wire:confirm="Convert the linked lead to customer?" class="inline-flex items-center gap-1.5 rounded-lg border border-violet-300 bg-violet-50 px-4 py-2 text-sm font-medium text-violet-700 transition-colors hover:bg-violet-100 dark:border-violet-700 dark:bg-violet-900/20 dark:text-violet-400 dark:hover:bg-violet-900/30">
                            <flux:icon name="user-plus" class="size-4" />
                            Convert to Customer
                        </button>
                    @endif
                </div>

                {{-- Status Badge --}}
                @if($opportunityId && $opportunity)
                    @if($opportunity->isWon())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <flux:icon name="trophy" class="size-3.5" />
                            Won
                        </span>
                    @elseif($opportunity->isLost())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            <flux:icon name="x-circle" class="size-3.5" />
                            Lost
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium" style="background-color: {{ $opportunity->pipeline->color ?? '#6b7280' }}20; color: {{ $opportunity->pipeline->color ?? '#6b7280' }}">
                            {{ $opportunity->pipeline->name }}
                        </span>
                    @endif
                @endif
            </div>

            <div class="col-span-3">
                <x-ui.chatter-buttons />
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="p-5">
                        <div class="mb-5">
                            <label class="mb-1 block text-sm font-light text-zinc-500 dark:text-zinc-400">Opportunity Name</label>
                            <input type="text" wire:model="name" placeholder="e.g., Enterprise Deal - Acme Corp" class="w-full border-0 bg-transparent p-0 text-3xl font-bold text-zinc-900 placeholder-zinc-300 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-600">
                            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Lead Selection (Searchable) --}}
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Lead</label>
                                <div class="relative" x-data="{ open: false, search: '' }">
                                    <button 
                                        type="button"
                                        @click="open = !open; $nextTick(() => { if(open) $refs.leadSearch.focus() })"
                                        class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                    >
                                        @if($this->selectedLead)
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-normal text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                                    {{ strtoupper(substr($this->selectedLead->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $this->selectedLead->name }}</p>
                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $this->selectedLead->company_name ?? $this->selectedLead->email }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-zinc-400">Select a lead...</span>
                                        @endif
                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                    </button>
                                    <div 
                                        x-show="open" 
                                        @click.outside="open = false; search = ''"
                                        x-transition
                                        class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                    >
                                        <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                            <input 
                                                type="text"
                                                x-ref="leadSearch"
                                                x-model="search"
                                                placeholder="Search leads..."
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                @keydown.escape="open = false; search = ''"
                                            />
                                        </div>
                                        <div class="max-h-60 overflow-auto py-1">
                                            <button 
                                                type="button"
                                                x-show="search === ''"
                                                wire:click="$set('leadId', null)"
                                                @click="open = false; search = ''"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-zinc-400 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            >
                                                No lead
                                            </button>
                                            @foreach($leads as $lead)
                                                <button 
                                                    type="button"
                                                    x-show="'{{ strtolower($lead->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($lead->company_name ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($lead->email ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                    wire:click="$set('leadId', {{ $lead->id }})"
                                                    @click="open = false; search = ''"
                                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $leadId === $lead->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                                                >
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-normal text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                                        {{ strtoupper(substr($lead->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-normal text-zinc-900 dark:text-zinc-100">{{ $lead->name }}</p>
                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $lead->company_name ?? $lead->email }}</p>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Stage <span class="text-red-500">*</span></label>
                                <select wire:model="pipelineId" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                    @foreach($pipelines as $pipeline)
                                        <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Expected Close Date</label>
                                <input type="date" wire:model="expectedCloseDate" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Assigned To</label>
                                <div class="relative" x-data="{ open: false, search: '' }">
                                    <button 
                                        type="button"
                                        @click="open = !open; $nextTick(() => { if(open) $refs.userSearch.focus() })"
                                        class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                    >
                                        @if($this->selectedUser)
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-[10px] font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                    {{ strtoupper(substr($this->selectedUser->name, 0, 2)) }}
                                                </div>
                                                <span class="text-zinc-900 dark:text-zinc-100">{{ $this->selectedUser->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-zinc-400">Unassigned</span>
                                        @endif
                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                    </button>
                                    <div 
                                        x-show="open" 
                                        @click.outside="open = false; search = ''"
                                        x-transition
                                        class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                    >
                                        <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                            <input 
                                                type="text"
                                                x-ref="userSearch"
                                                x-model="search"
                                                placeholder="Search users..."
                                                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                @keydown.escape="open = false; search = ''"
                                            />
                                        </div>
                                        <div class="max-h-48 overflow-auto py-1">
                                            <button 
                                                type="button"
                                                x-show="search === ''"
                                                wire:click="$set('assignedTo', null)"
                                                @click="open = false; search = ''"
                                                class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-zinc-400 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            >
                                                Unassigned
                                            </button>
                                            @foreach($users as $user)
                                                <button 
                                                    type="button"
                                                    x-show="'{{ strtolower($user->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($user->email ?? '') }}'.includes(search.toLowerCase()) || search === ''"
                                                    wire:click="$set('assignedTo', {{ $user->id }})"
                                                    @click="open = false; search = ''"
                                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $assignedTo === $user->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                                                >
                                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-[10px] font-normal text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $user->name }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Expected Revenue <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-zinc-400">Rp</span>
                                    <input type="number" wire:model="expectedRevenue" placeholder="0" class="w-full rounded-lg border border-zinc-200 bg-white py-2.5 pl-10 pr-4 text-sm text-zinc-900 transition-colors [appearance:textfield] hover:border-zinc-300 focus:border-zinc-400 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                </div>
                                @error('expectedRevenue')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Probability (%)</label>
                                <input type="number" wire:model="probability" min="0" max="100" placeholder="0-100" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors [appearance:textfield] hover:border-zinc-300 focus:border-zinc-400 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Description</label>
                                <textarea wire:model="description" rows="4" placeholder="Additional details about this opportunity..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Sidebar --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- Opportunity Info (only for existing) --}}
                @if($opportunityId && $opportunity)
                    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Info</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Weighted Revenue</span>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($opportunity->getWeightedRevenue(), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Created</span>
                                <span class="text-zinc-900 dark:text-zinc-100">{{ $opportunity->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($opportunity->isWon())
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Won Date</span>
                                    <span class="text-emerald-600 dark:text-emerald-400">{{ $opportunity->won_at->format('M d, Y') }}</span>
                                </div>
                            @elseif($opportunity->isLost())
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Lost Date</span>
                                    <span class="text-red-600 dark:text-red-400">{{ $opportunity->lost_at->format('M d, Y') }}</span>
                                </div>
                            @endif
                            @if($opportunity->assignedTo)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Assigned</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $opportunity->assignedTo->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Chatter Forms --}}
                <x-ui.chatter-forms />

                {{-- Activity Timeline --}}
                @if($opportunityId)
                    {{-- Date Separator --}}
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            @if($activities->isNotEmpty() && $activities->first()['created_at']->isToday())
                                Today
                            @else
                                Activity
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    {{-- Activity Items --}}
                    <div class="space-y-3">
                        @forelse($activities as $item)
                            @if($item['type'] === 'note')
                                {{-- Note Item - Compact --}}
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                {{-- Activity Log Item --}}
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Opportunity created" />
                            @endif
                        @empty
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Opportunity created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State for New Opportunity --}}
                    <div class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once you save</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
