<div
    x-show="showEmailModal"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-zinc-900/60 p-4"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div
        class="relative w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-zinc-900 dark:ring-1 dark:ring-white/10"
        x-show="showEmailModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showEmailModal = false"
    >
        {{-- Modal Header --}}
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Compose Email</h3>
            <button 
                type="button"
                @click="showEmailModal = false"
                class="rounded-lg p-1.5 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
            >
                <flux:icon name="x-mark" class="size-5" />
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="px-5 py-3">
            {{-- Recipients Row --}}
            <div class="flex items-start gap-3 py-2">
                <label class="w-20 shrink-0 pt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Recipients</label>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach($emailRecipients as $index => $recipient)
                            <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-1 text-sm text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $recipient }}
                                <button 
                                    type="button" 
                                    wire:click="removeEmailRecipient({{ $index }})"
                                    class="rounded-full p-0.5 text-zinc-400 transition-colors hover:bg-zinc-200 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                                >
                                    <flux:icon name="x-mark" class="size-3.5" />
                                </button>
                            </span>
                        @endforeach
                        <input 
                            type="email" 
                            wire:model="emailRecipientInput"
                            wire:keydown.enter.prevent="addEmailRecipient"
                            wire:keydown.tab.prevent="addEmailRecipient"
                            placeholder="{{ empty($emailRecipients) ? 'Add recipient email...' : 'Add more...' }}"
                            class="min-w-[150px] flex-1 border-0 bg-transparent px-0 py-1 text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-500"
                        />
                    </div>
                    @if($emailRecipientError)
                        <p class="mt-1 text-xs text-red-500">{{ $emailRecipientError }}</p>
                    @endif
                    @error('emailRecipients')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Subject Row --}}
            <div class="flex items-center gap-3 border-t border-zinc-100 py-2 dark:border-zinc-800">
                <label class="w-20 shrink-0 text-sm text-zinc-500 dark:text-zinc-400">Subject</label>
                <input 
                    type="text" 
                    wire:model="emailSubject"
                    placeholder="Email subject"
                    class="flex-1 border-0 bg-transparent px-0 py-1 text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-500"
                />
            </div>
            @error('emailSubject')
                <p class="ml-[92px] text-xs text-red-500">{{ $message }}</p>
            @enderror

            {{-- Message Body --}}
            <div class="border-t border-zinc-100 pt-3 dark:border-zinc-800">
                <textarea 
                    wire:model="emailBody"
                    rows="12"
                    placeholder="Write your message here..."
                    class="w-full resize-none border-0 bg-transparent px-0 py-1 text-sm leading-relaxed text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-100 dark:placeholder-zinc-500"
                ></textarea>
            </div>
            @error('emailBody')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror

            {{-- Attachments --}}
            <div class="flex items-center gap-3 border-t border-zinc-100 py-3 dark:border-zinc-800">
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800/50 dark:hover:bg-zinc-800">
                    <input 
                        type="checkbox" 
                        wire:model="emailAttachPdf"
                        class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-600 dark:bg-zinc-700"
                    />
                    <flux:icon name="paper-clip" class="size-4 text-zinc-500 dark:text-zinc-400" />
                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ in_array($status, ['draft', 'confirmed']) ? 'Quotation' : 'Sales Order' }} - {{ $orderNumber ?? 'Order' }}.pdf</span>
                </label>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-end gap-2 border-t border-zinc-100 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <button 
                type="button"
                @click="showEmailModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
            >
                Discard
            </button>

            <button 
                type="button"
                wire:click="sendEmail"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <flux:icon name="paper-airplane" class="size-4" wire:loading.remove wire:target="sendEmail" />
                <flux:icon name="arrow-path" class="size-4 animate-spin" wire:loading wire:target="sendEmail" />
                <span>Send</span>
            </button>
        </div>
    </div>
</div>