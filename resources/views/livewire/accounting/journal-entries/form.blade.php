<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            {{-- Left Group: Back Button, Title, Gear Dropdown --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('accounting.journal-entries.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        Journal Entry
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $entryId ? ($entry->entry_number ?? 'Edit Entry') : 'New Entry' }}
                        </span>

                        @if($entryId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>

                                <flux:menu class="w-40">
                                    <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                                        <flux:icon name="document-duplicate" class="size-4" />
                                        <span>Duplicate</span>
                                    </button>
                                    @if($entry && $entry->status === 'draft')
                                        <flux:menu.separator />
                                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                            <flux:icon name="trash" class="size-4" />
                                            <span>Delete</span>
                                        </button>
                                    @endif
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
            {{-- Left: Action Buttons (col-span-9) --}}
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$entryId || ($entry && $entry->status === 'draft'))
                        <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="document-check" class="size-4" />
                            Save
                        </button>
                    @endif
                </div>

                {{-- Status Badge --}}
                @if($entryId && $entry)
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $entry->status === 'posted' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                        {{ ucfirst($entry->status) }}
                    </span>
                @endif
            </div>

            {{-- Right: Chatter Icons (col-span-3) --}}
            <div class="col-span-3 flex items-center justify-end gap-1">
                <button @click="showSendMessage = !showSendMessage; showLogNote = false; showScheduleActivity = false" :class="showSendMessage ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Send message">
                    <flux:icon name="chat-bubble-left" class="size-5" />
                </button>
                <button @click="showLogNote = !showLogNote; showSendMessage = false; showScheduleActivity = false" :class="showLogNote ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Log note">
                    <flux:icon name="pencil-square" class="size-5" />
                </button>
                <button @click="showScheduleActivity = !showScheduleActivity; showSendMessage = false; showLogNote = false" :class="showScheduleActivity ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'" class="rounded-lg p-2 transition-colors" title="Schedule activity">
                    <flux:icon name="clock" class="size-5" />
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Entry Details Section --}}
                    <div class="p-5">
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $entryId ? ($entry->entry_number ?? 'Entry') : 'New' }}
                        </h1>
                        <div class="grid gap-6 sm:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Date <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="entryDate" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                @error('entryDate')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Reference</label>
                                <input type="text" wire:model="reference" placeholder="e.g., INV-001" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Description</label>
                                <input type="text" wire:model="description" placeholder="Entry description..." class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                        </div>
                    </div>

                    {{-- Journal Lines Section --}}
                    <div class="border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Journal Lines</span>
                        </div>
                        <div class="overflow-visible">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                        <th class="w-10 px-2 py-2.5"></th>
                                        <th class="w-[20rem] px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Account</th>
                                        <th class="px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Description</th>
                                        <th class="w-36 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Debit</th>
                                        <th class="w-36 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Credit</th>
                                        <th class="w-10 px-2 py-2.5"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                    @foreach($lines as $index => $line)
                                        <tr wire:key="line-{{ $index }}" class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30">
                                            {{-- Drag Handle --}}
                                            <td class="px-2 py-2">
                                                <div class="flex cursor-grab items-center justify-center text-zinc-300 transition-opacity hover:text-zinc-500 dark:text-zinc-600 dark:hover:text-zinc-400">
                                                    <svg class="size-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                                    </svg>
                                                </div>
                                            </td>

                                            {{-- Account Selection (Searchable) --}}
                                            <td class="w-[20rem] px-3 py-2 overflow-visible">
                                                <div x-data="{ open: false, search: '' }" class="relative">
                                                    @php $selectedAccount = $accounts->firstWhere('id', $line['account_id']); @endphp
                                                    @if($selectedAccount)
                                                        <button type="button" @click="open = true; $nextTick(() => $refs.accountSearch{{ $index }}.focus())" class="flex w-full items-center gap-2 text-left">
                                                            <div>
                                                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $selectedAccount->name }}</p>
                                                                <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $selectedAccount->code }}</p>
                                                            </div>
                                                        </button>
                                                    @else
                                                        <button type="button" @click="open = true; $nextTick(() => $refs.accountSearch{{ $index }}.focus())" class="text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                                                            Select an account...
                                                        </button>
                                                    @endif

                                                    {{-- Account Dropdown --}}
                                                    <div x-show="open" @click.outside="open = false; search = ''" x-transition class="absolute left-0 top-full z-[200] mt-1 w-80 rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                                                        <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                            <input type="text" x-ref="accountSearch{{ $index }}" x-model="search" placeholder="Search accounts..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" @keydown.escape="open = false; search = ''">
                                                        </div>
                                                        <div class="max-h-48 overflow-auto py-1">
                                                            @foreach($accounts as $account)
                                                                <button type="button" x-show="'{{ strtolower($account->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($account->code) }}'.includes(search.toLowerCase()) || search === ''" wire:click="$set('lines.{{ $index }}.account_id', {{ $account->id }})" @click="open = false; search = ''" class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                                                    <div class="flex-1">
                                                                        <p class="text-zinc-900 dark:text-zinc-100">{{ $account->name }}</p>
                                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $account->code }} · {{ ucfirst($account->type) }}</p>
                                                                    </div>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Description (borderless) --}}
                                            <td class="px-3 py-2">
                                                <input type="text" wire:model="lines.{{ $index }}.description" placeholder="Add description..." class="w-full bg-transparent text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none dark:text-zinc-100">
                                            </td>

                                            {{-- Debit (borderless, no spinners) --}}
                                            <td class="w-36 px-3 py-2">
                                                <input type="text" wire:model.live="lines.{{ $index }}.debit" inputmode="decimal" placeholder="0" class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                            </td>

                                            {{-- Credit (borderless, no spinners) --}}
                                            <td class="w-36 px-3 py-2">
                                                <input type="text" wire:model.live="lines.{{ $index }}.credit" inputmode="decimal" placeholder="0" class="w-full bg-transparent text-right text-sm text-zinc-900 focus:outline-none dark:text-zinc-100 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                            </td>

                                            {{-- Remove --}}
                                            <td class="px-2 py-2 text-right">
                                                @if(count($lines) > 2)
                                                    <button type="button" wire:click="removeLine({{ $index }})" class="rounded p-1 text-zinc-300 opacity-0 transition-all hover:text-red-500 group-hover:opacity-100 dark:text-zinc-600 dark:hover:text-red-400">
                                                        <flux:icon name="trash" class="size-4" />
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Add Line Button --}}
                        <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                            <button type="button" wire:click="addLine" class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                                <flux:icon name="plus" class="size-4" />
                                Add a line
                            </button>
                        </div>

                        {{-- Totals Row --}}
                        <div class="border-t border-zinc-100 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-900/30">
                            <div class="flex justify-end">
                                <div class="w-72 space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-500 dark:text-zinc-400">Total Debit</span>
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($totalDebit, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-500 dark:text-zinc-400">Total Credit</span>
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($totalCredit, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="border-t border-zinc-200 pt-2 dark:border-zinc-700">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="font-medium text-zinc-700 dark:text-zinc-300">Difference</span>
                                            <span class="font-bold {{ $balanced ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                                Rp {{ number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Sidebar --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- Balance Status Card --}}
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Balance Status</h3>
                    </div>
                    <div class="p-4">
                        @if($balanced)
                            <div class="flex items-center gap-2 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                                <flux:icon name="check-circle" class="size-5" />
                                <span class="font-medium">Balanced</span>
                            </div>
                        @else
                            <div class="flex flex-col gap-2 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="exclamation-circle" class="size-5" />
                                    <span class="font-medium">Not Balanced</span>
                                </div>
                                <p class="text-xs">Difference: Rp {{ number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Entry Info (only for existing entries) --}}
                @if($entryId && $entry)
                    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Entry Info</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Entry #</span>
                                <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ $entry->entry_number }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">Created</span>
                                <span class="text-zinc-900 dark:text-zinc-100">{{ $entry->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($entry->createdBy)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Created By</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $entry->createdBy->name }}</span>
                                </div>
                            @endif
                            @if($entry->posted_at)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Posted</span>
                                    <span class="text-zinc-900 dark:text-zinc-100">{{ $entry->posted_at->format('M d, Y · H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Log Note Panel --}}
                <div x-show="showLogNote" x-transition class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Log Note</h3>
                    </div>
                    <div class="p-4">
                        <textarea rows="3" placeholder="Write a note..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                        <div class="mt-3 flex justify-end">
                            <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">Log</button>
                        </div>
                    </div>
                </div>

                {{-- Send Message Panel --}}
                <div x-show="showSendMessage" x-transition class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Send Message</h3>
                    </div>
                    <div class="p-4">
                        <textarea rows="3" placeholder="Write a message..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                        <div class="mt-3 flex justify-end">
                            <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">Send</button>
                        </div>
                    </div>
                </div>

                {{-- Schedule Activity Panel --}}
                <div x-show="showScheduleActivity" x-transition class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Schedule Activity</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Activity Type</label>
                            <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <option>To Do</option>
                                <option>Call</option>
                                <option>Meeting</option>
                                <option>Email</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Due Date</label>
                            <input type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Summary</label>
                            <textarea rows="2" placeholder="Activity summary..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">Schedule</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
