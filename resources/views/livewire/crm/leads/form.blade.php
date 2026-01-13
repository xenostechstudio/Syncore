<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('crm.leads.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Lead</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $leadId ? $name : 'New Lead' }}
                        </span>
                        @if($leadId)
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
                                    <button type="button" wire:click="delete" wire:confirm="Delete this lead?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
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
                    @if($leadId && !in_array($leadStatus, ['converted', 'lost']))
                        <a href="{{ route('crm.opportunities.create') }}?lead_id={{ $leadId }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="briefcase" class="size-4" />
                            Create Opportunity
                        </a>
                    @endif
                    @if($leadId && $leadStatus !== 'converted')
                        <button type="button" wire:click="convertToCustomer" wire:confirm="Convert this lead to customer directly? (Usually done when Opportunity is Won)" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700">
                            <flux:icon name="user-plus" class="size-4" />
                            Convert to Customer
                        </button>
                    @endif
                </div>

                {{-- Status Badge --}}
                @if($leadId)
                    @php
                        $statusColors = [
                            'new' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'contacted' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                            'qualified' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                            'converted' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
                            'lost' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusColors[$leadStatus] ?? 'bg-zinc-100 text-zinc-700' }}">
                        {{ ucfirst($leadStatus) }}
                    </span>
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
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $leadId ? $name : 'New' }}
                        </h1>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Name <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name" placeholder="Full name" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Email</label>
                                <input type="email" wire:model="email" placeholder="email@example.com" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Phone</label>
                                <input type="text" wire:model="phone" placeholder="+62 xxx" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Company</label>
                                <input type="text" wire:model="companyName" placeholder="Company name" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Job Title</label>
                                <input type="text" wire:model="jobTitle" placeholder="Position" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Website</label>
                                <input type="url" wire:model="website" placeholder="https://" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Address</label>
                                <textarea wire:model="address" rows="2" placeholder="Full address" class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Notes</label>
                                <textarea wire:model="notes" rows="3" placeholder="Additional notes..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Sidebar --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- Lead Settings Card --}}
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Lead Settings</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Source</label>
                            <select wire:model="leadSource" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">Select source...</option>
                                @foreach($sources as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Status</label>
                            <select wire:model="leadStatus" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="converted">Converted</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Assigned To</label>
                            <select wire:model="assignedTo" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Lead Info (only for existing leads) --}}
                @if($leadId && $lead)
                    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Lead Info</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Created</span>
                                <span class="text-zinc-900 dark:text-zinc-100">{{ $lead->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($lead->updated_at && $lead->updated_at != $lead->created_at)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Updated</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $lead->updated_at->format('M d, Y') }}</span>
                                </div>
                            @endif
                            @if($lead->assignedTo)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Assigned</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $lead->assignedTo->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Linked Opportunities --}}
                    @if($lead->opportunities->count() > 0)
                        <div class="overflow-hidden rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20">
                            <div class="border-b border-amber-100 bg-amber-100/50 px-4 py-3 dark:border-amber-800 dark:bg-amber-900/30">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-amber-900 dark:text-amber-100">Opportunities ({{ $lead->opportunities->count() }})</h3>
                                </div>
                            </div>
                            <div class="divide-y divide-amber-100 dark:divide-amber-800">
                                @foreach($lead->opportunities as $opportunity)
                                    <a href="{{ route('crm.opportunities.edit', $opportunity->id) }}" wire:navigate class="flex items-center justify-between p-3 hover:bg-amber-100/50 dark:hover:bg-amber-900/30 transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $opportunity->name }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Rp {{ number_format($opportunity->expected_revenue, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="ml-3 flex-shrink-0">
                                            @if($opportunity->isWon())
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Won</span>
                                            @elseif($opportunity->isLost())
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Lost</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium" style="background-color: {{ $opportunity->pipeline->color ?? '#6b7280' }}20; color: {{ $opportunity->pipeline->color ?? '#6b7280' }}">
                                                    {{ $opportunity->pipeline->name ?? 'Open' }}
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Converted Customer Info --}}
                    @if($lead->convertedCustomer)
                        <div class="overflow-hidden rounded-lg border border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20">
                            <div class="border-b border-emerald-100 bg-emerald-100/50 px-4 py-3 dark:border-emerald-800 dark:bg-emerald-900/30">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-emerald-900 dark:text-emerald-100">Converted Customer</h3>
                                    <a href="{{ route('sales.customers.edit', $lead->convertedCustomer->id) }}" wire:navigate class="text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                        View →
                                    </a>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-medium text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                                        {{ strtoupper(substr($lead->convertedCustomer->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $lead->convertedCustomer->name }}</p>
                                        @if($lead->converted_at)
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Converted {{ $lead->converted_at->format('M d, Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Chatter Forms --}}
                <x-ui.chatter-forms />

                {{-- Activity Timeline --}}
                @if($leadId)
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
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Lead created" />
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
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Lead created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State for New Lead --}}
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
