<div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50" x-data="{ showSubmitModal: false, showApproveModal: false, showRejectModal: false, showCancelModal: false, showEmailModal: false }">
    <div class="grid grid-cols-12 items-center gap-6">
        <div class="col-span-9 flex items-center justify-between">
            <div class="flex flex-wrap items-center gap-2">
                @if(!$requestId)
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                @elseif($status === 'draft')
                    <button type="button" @click="showSubmitModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="paper-airplane" class="size-4" />
                        Submit
                    </button>
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                @elseif($status === 'pending')
                    <button type="button" @click="showApproveModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="check" class="size-4" />
                        Approve
                    </button>
                    <button type="button" @click="showRejectModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-700 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                        <flux:icon name="x-mark" class="size-4" />
                        Reject
                    </button>
                    <button type="button" @click="showEmailModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="envelope" class="size-4" />
                        Send
                    </button>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="printer" class="size-4" />
                        Print
                    </button>
                @elseif(in_array($status, ['approved', 'rejected']))
                    <button type="button" @click="showEmailModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="envelope" class="size-4" />
                        Send
                    </button>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="printer" class="size-4" />
                        Print
                    </button>
                @endif
                @if($requestId && in_array($status, ['draft', 'pending']))
                    <button type="button" @click="showCancelModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                        <flux:icon name="x-mark" class="size-4" />
                        Cancel
                    </button>
                @endif
            </div>

            {{-- Stepper --}}
            @php
                $steps = \App\Enums\LeaveRequestState::steps();
                $currentIndex = collect($steps)->search(fn($s) => $s['key'] === $status);
                if ($currentIndex === false) $currentIndex = 0;
            @endphp
            @if($status === 'rejected')
                <span class="inline-flex h-[38px] items-center rounded-lg bg-red-100 px-4 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                    <flux:icon name="x-circle" class="mr-1.5 size-4" />Rejected
                </span>
            @elseif($status === 'cancelled')
                <span class="inline-flex h-[38px] items-center rounded-lg bg-zinc-100 px-4 text-sm font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Cancelled</span>
            @else
                <div class="flex items-center">
                    @foreach($steps as $index => $step)
                        @php
                            $isActive = $index === $currentIndex;
                            $isCompleted = $index < $currentIndex;
                            $isPending = $index > $currentIndex;
                            $isFirst = $index === 0;
                        @endphp
                        <div class="relative flex items-center {{ !$isFirst ? '-ml-2' : '' }}" style="z-index: {{ count($steps) - $index }};">
                            <div class="relative flex h-[38px] items-center px-4 {{ $isActive ? 'bg-violet-600 text-white' : '' }} {{ $isCompleted ? 'bg-emerald-500 text-white' : '' }} {{ $isPending ? 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' : '' }}" style="clip-path: polygon({{ $isFirst ? '0 0' : '10px 0' }}, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, {{ $isFirst ? '0 100%' : '10px 100%' }}, {{ $isFirst ? '0 50%' : '0 100%, 10px 50%, 0 0' }});">
                                <span class="flex items-center gap-1 text-sm font-medium whitespace-nowrap">
                                    @if($isCompleted)<flux:icon name="check" class="size-4" />@endif
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="col-span-3 flex items-center justify-end gap-1">
            <x-ui.chatter-buttons :showMessage="false" />
        </div>
    </div>

    {{-- Submit Modal --}}
    <div x-show="showSubmitModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50" @click="showSubmitModal = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.outside="showSubmitModal = false">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-900/30">
                    <flux:icon name="paper-airplane" class="size-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Submit Leave Request</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This will send the request for approval.</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">Are you sure you want to submit this leave request? Once submitted, it will be sent to your manager for approval.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showSubmitModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Cancel</button>
                <button type="button" wire:click="submit" @click="showSubmitModal = false" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">Submit Request</button>
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50" @click="showApproveModal = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.outside="showApproveModal = false">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                    <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Approve Leave Request</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">Are you sure you want to approve this leave request? The employee will be notified and their leave balance will be updated.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showApproveModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Cancel</button>
                <button type="button" wire:click="approve" @click="showApproveModal = false" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700">Approve</button>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50" @click="showRejectModal = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.outside="showRejectModal = false">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="x-circle" class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Reject Leave Request</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">Are you sure you want to reject this leave request? The employee will be notified of the rejection.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showRejectModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Cancel</button>
                <button type="button" wire:click="reject" @click="showRejectModal = false" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">Reject</button>
            </div>
        </div>
    </div>

    {{-- Cancel Modal --}}
    <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50" @click="showCancelModal = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.outside="showCancelModal = false">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="x-mark" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Cancel Leave Request</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">Are you sure you want to cancel this leave request?</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showCancelModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">No, Keep It</button>
                <button type="button" wire:click="cancel" @click="showCancelModal = false" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">Yes, Cancel</button>
            </div>
        </div>
    </div>

    {{-- Email Modal --}}
    <div x-show="showEmailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50" @click="showEmailModal = false"></div>
        <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl dark:bg-zinc-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.outside="showEmailModal = false">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                        <flux:icon name="envelope" class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Send Leave Request</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Send notification via email</p>
                    </div>
                </div>
                <button type="button" @click="showEmailModal = false" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">To</label>
                    <input type="email" value="{{ $this->selectedEmployee?->email ?? '' }}" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="recipient@example.com" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</label>
                    <input type="text" value="Leave Request {{ $requestId ? 'LR-' . str_pad($requestId, 5, '0', STR_PAD_LEFT) : '' }} - {{ ucfirst($status) }}" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Message</label>
                    <textarea rows="4" class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Add a message..."></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="attachPdf" checked class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-600 dark:bg-zinc-800" />
                    <label for="attachPdf" class="text-sm text-zinc-600 dark:text-zinc-400">Attach PDF document</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <button type="button" @click="showEmailModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Cancel</button>
                <button type="button" @click="showEmailModal = false" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    <flux:icon name="paper-airplane" class="mr-1.5 inline size-4" />
                    Send Email
                </button>
            </div>
        </div>
    </div>
</div>
