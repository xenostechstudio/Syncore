<div 
    x-show="showShareModal" 
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
        class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl ring-1 ring-black/5 dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10"
        x-show="showShareModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showShareModal = false"
        x-data="{ activeTab: 'link' }"
    >
        {{-- Header --}}
        <div class="border-b border-zinc-100 bg-zinc-50 px-6 pt-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Send Invoice</h3>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Share the invoice with your customer.</p>
                </div>

                <button 
                    type="button"
                    @click="showShareModal = false"
                    class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    aria-label="Close"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex items-center gap-6">
                <button 
                    type="button"
                    @click="activeTab = 'link'"
                    class="relative pb-3 text-sm font-medium transition-colors"
                    :class="activeTab === 'link' ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                >
                    Public Link
                    <div x-show="activeTab === 'link'" class="absolute bottom-0 left-0 h-0.5 w-full bg-zinc-900 dark:bg-zinc-100" layoutId="underline"></div>
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'email'"
                    class="relative pb-3 text-sm font-medium transition-colors"
                    :class="activeTab === 'email' ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300'"
                >
                    Send Email
                    <div x-show="activeTab === 'email'" class="absolute bottom-0 left-0 h-0.5 w-full bg-zinc-900 dark:bg-zinc-100" layoutId="underline"></div>
                </button>
            </div>
        </div>

        <div class="px-6 py-5">
            {{-- Tab: Public Link --}}
            <div x-show="activeTab === 'link'" class="space-y-4">
                @if($shareLink)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Public link</label>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-stretch">
                            <input type="text" readonly value="{{ $shareLink }}" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100" />
                            <button type="button" x-data x-on:click="navigator.clipboard.writeText('{{ $shareLink }}')" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 sm:w-auto dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="clipboard" class="size-4" />
                                Copy
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Link expires {{ optional(optional($invoice)->share_token_expires_at)->diffForHumans() ?? 'in 30 days' }}.</p>
                    </div>
                @else
                    <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                        <flux:icon name="exclamation-triangle" class="size-5 flex-shrink-0 text-amber-500 dark:text-amber-400" />
                        <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Please generate a link to share this invoice.</p>
                    </div>
                @endif

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/50 dark:text-zinc-300">
                    Your customer can view invoice details and choose payment method.
                </div>
            </div>

            {{-- Tab: Email --}}
            <div x-show="activeTab === 'email'" class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">To</label>
                    <input 
                        type="email" 
                        wire:model="emailTo"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100" 
                        placeholder="customer@example.com"
                    />
                    @error('emailTo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</label>
                    <input 
                        type="text" 
                        wire:model="emailSubject"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100" 
                    />
                    @error('emailSubject') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Message</label>
                    <textarea 
                        wire:model="emailMessage"
                        rows="6"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                    ></textarea>
                    @error('emailMessage') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <flux:icon name="document-text" class="size-5 text-zinc-400" />
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">PDF Invoice will be attached automatically.</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
            {{-- Footer for Link Tab --}}
            <template x-if="activeTab === 'link'">
                <div class="flex items-center gap-3">
                    <button 
                        type="button"
                        wire:click="regenerateShareLink"
                        wire:loading.attr="disabled"
                        wire:target="regenerateShareLink"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="arrow-path" wire:loading.remove wire:target="regenerateShareLink" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="regenerateShareLink" class="size-4 animate-spin" />
                        Regenerate Link
                    </button>

                    @if($shareLink)
                        <button 
                            type="button"
                            onclick="window.open('{{ $shareLink }}', '_blank')"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            View as Customer
                        </button>
                    @endif
                </div>
            </template>

            {{-- Footer for Email Tab --}}
            <template x-if="activeTab === 'email'">
                <div class="flex items-center gap-3">
                    <button 
                        type="button"
                        @click="showShareModal = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>
                    
                    <button 
                        type="button"
                        wire:click="sendInvoiceEmail"
                        wire:loading.attr="disabled"
                        wire:target="sendInvoiceEmail"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <flux:icon name="paper-airplane" wire:loading.remove wire:target="sendInvoiceEmail" class="size-4" />
                        <flux:icon name="arrow-path" wire:loading wire:target="sendInvoiceEmail" class="size-4 animate-spin" />
                        <span>Send Email</span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>