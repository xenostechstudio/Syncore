<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false, showCancelModal: false, showDeleteModal: false }"
     x-on:open-cancel-modal.window="showCancelModal = true"
     x-on:open-delete-modal.window="showDeleteModal = true">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.leave.requests.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Leave Request</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $requestId ? 'LR-' . str_pad($requestId, 5, '0', STR_PAD_LEFT) : 'New Request' }}</span>
                        {{-- Destructive actions follow the Cancel-vs-Delete
                             taxonomy (see CLAUDE.md): a never-submitted draft
                             can be Deleted (hard); a submitted (pending)
                             request is Cancelled (state transition, record
                             kept). Mutually exclusive by state. --}}
                        @if($canDeleteLeave || $canCancelLeave)
                            <div x-data="{}">
                                <flux:dropdown position="bottom" align="start">
                                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                        <flux:icon name="cog-6-tooth" class="size-4" />
                                    </button>
                                    <flux:menu class="w-40">
                                        @if($canCancelLeave)
                                            <flux:menu.item
                                                icon="x-mark"
                                                variant="danger"
                                                x-on:click="$dispatch('open-cancel-modal')"
                                            >
                                                Cancel Request
                                            </flux:menu.item>
                                        @endif
                                        @if($canDeleteLeave)
                                            <flux:menu.item
                                                icon="trash"
                                                variant="danger"
                                                x-on:click="$dispatch('open-delete-modal')"
                                            >
                                                Delete
                                            </flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
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
    @include('livewire.hr.leave.requests._action-bar')

    {{-- Main Content --}}
    @include('livewire.hr.leave.requests._form-content')

    @if($canCancelLeave)
        <x-ui.action-confirm-modal
            show="showCancelModal"
            icon="x-mark"
            color="red"
            title="Cancel Leave Request"
            subtitle="The request stays on the record for audit."
            confirmLabel="Cancel Request"
            confirmLoadingLabel="Cancelling..."
            confirmMethod="cancel"
        >
            Cancelling this request marks it as cancelled but keeps the record for audit. Use this when a submitted request is no longer needed.
        </x-ui.action-confirm-modal>
    @endif

    @if($canDeleteLeave)
        <x-ui.action-confirm-modal
            show="showDeleteModal"
            icon="trash"
            color="red"
            title="Delete Draft Leave Request"
            subtitle="This action cannot be undone."
            confirmLabel="Delete Request"
            confirmLoadingLabel="Deleting..."
            confirmMethod="delete"
        >
            This draft has not been submitted, so there is nothing to keep. Once submitted, a leave request must be Cancelled instead.
        </x-ui.action-confirm-modal>
    @endif
</div>
