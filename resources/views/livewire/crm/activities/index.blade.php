<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <button wire:click="openModal" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    New
                </button>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Activities</span>
            </div>

            {{-- Right Group: Filters --}}
            <div class="flex items-center gap-3">
                <select wire:model.live="filter" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="upcoming">Upcoming</option>
                    <option value="today">Today</option>
                    <option value="overdue">Overdue</option>
                    <option value="all">All</option>
                </select>
                <select wire:model.live="type" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="">All Types</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="status" class="rounded-lg border-zinc-200 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                    <option value="">All Status</option>
                    <option value="planned">Planned</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Activities List --}}
    <div class="space-y-3">
        @forelse($activities as $activity)
            @php
                $typeColors = ['call' => 'blue', 'meeting' => 'violet', 'email' => 'emerald', 'task' => 'amber'];
                $typeIcons = ['call' => 'phone', 'meeting' => 'users', 'email' => 'envelope', 'task' => 'clipboard-document-check'];
                $color = $typeColors[$activity->type] ?? 'zinc';
                $icon = $typeIcons[$activity->type] ?? 'calendar';
            @endphp
            <div class="flex items-start gap-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30">
                    <flux:icon name="{{ $icon }}" class="size-5 text-{{ $color }}-600 dark:text-{{ $color }}-400" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $activity->subject }}</p>
                            <p class="mt-1 text-sm text-zinc-500">
                                {{ $types[$activity->type] ?? $activity->type }}
                                @if($activity->activitable)
                                    · {{ class_basename($activity->activitable_type) }}: {{ $activity->activitable->name ?? 'Unknown' }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $activity->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : ($activity->status === 'cancelled' ? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                {{ ucfirst($activity->status) }}
                            </span>
                        </div>
                    </div>
                    @if($activity->description)
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ Str::limit($activity->description, 100) }}</p>
                    @endif
                    <div class="mt-3 flex items-center gap-4 text-xs text-zinc-500">
                        @if($activity->scheduled_at)
                            <span class="flex items-center gap-1">
                                <flux:icon name="calendar" class="size-3.5" />
                                {{ $activity->scheduled_at->format('M d, Y H:i') }}
                            </span>
                        @endif
                        @if($activity->assignedTo)
                            <span class="flex items-center gap-1">
                                <flux:icon name="user" class="size-3.5" />
                                {{ $activity->assignedTo->name }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    @if($activity->status === 'planned')
                        <button wire:click="markAsCompleted({{ $activity->id }})" class="rounded p-1.5 text-zinc-400 hover:bg-emerald-100 hover:text-emerald-600 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400" title="Mark as completed">
                            <flux:icon name="check" class="size-4" />
                        </button>
                    @endif
                    <button wire:click="openModal({{ $activity->id }})" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="pencil" class="size-4" />
                    </button>
                    <button wire:click="delete({{ $activity->id }})" wire:confirm="Delete this activity?" class="rounded p-1.5 text-zinc-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400">
                        <flux:icon name="trash" class="size-4" />
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-800 dark:bg-zinc-900">
                <flux:icon name="calendar" class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                <p class="mt-2 text-sm text-zinc-500">No activities found</p>
            </div>
        @endforelse
    </div>

    @if($activities->hasPages())
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    @endif

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $isEditing ? 'Edit Activity' : 'New Activity' }}</h3>
                <form wire:submit="save" class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Type *</label>
                            <select wire:model="activityType" class="mt-1 w-full rounded-lg border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                            <select wire:model="activityStatus" class="mt-1 w-full rounded-lg border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <option value="planned">Planned</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject *</label>
                        <input type="text" wire:model="subject" class="mt-1 w-full rounded-lg border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                        @error('subject')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Related To</label>
                            <select wire:model.live="relatedType" class="mt-1 w-full rounded-lg border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <option value="">None</option>
                                <option value="lead">Lead</option>
                                <option value="opportunity">Opportunity</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Select</label>
                            <select wire:model="relatedId" class="mt-1 w-full rounded-lg border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800" @if(!$relatedType) disabled @endif>
                                <option value="">Select...</option>
                                @if($relatedType === 'lead')
                                    @foreach($leads as $lead)
                                        <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                                    @endforeach
                                @elseif($relatedType === 'opportunity')
                                    @foreach($opportunities as $opp)
                                        <option value="{{ $opp->id }}">{{ $opp->name }}</option>
                                    @endforeach
                                @elseif($relatedType === 'customer')
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Scheduled At</label>
                            <input type="datetime-local" wire:model="scheduledAt" class="mt-1 w-full rounded-lg border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Assigned To</label>
                            <select wire:model="assignedTo" class="mt-1 w-full rounded-lg border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
                        <textarea wire:model="description" rows="3" class="mt-1 w-full rounded-lg border-zinc-200 text-sm dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">Cancel</button>
                        <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">{{ $isEditing ? 'Update' : 'Create' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
