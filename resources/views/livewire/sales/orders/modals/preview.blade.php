<div
    x-show="showPreviewModal"
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
        class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-zinc-900 dark:ring-1 dark:ring-white/10"
        x-show="showPreviewModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showPreviewModal = false"
    >
        <div class="flex items-start justify-between gap-4 border-b border-zinc-100 bg-zinc-50/50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Customer Preview</h3>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Share this link with your customer to view and confirm the order.</p>
            </div>

            <button 
                type="button"
                @click="showPreviewModal = false"
                class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                aria-label="Close"
            >
                <flux:icon name="x-mark" class="size-5" />
            </button>
        </div>

        <div class="px-6 py-5">
            <div class="space-y-4">
                @if($previewLink)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Public link</label>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-stretch">
                            <input type="text" readonly value="{{ $previewLink }}" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100" />
                            <button type="button" x-data x-on:click="navigator.clipboard.writeText('{{ $previewLink }}')" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 sm:w-auto dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                <flux:icon name="clipboard" class="size-4" />
                                Copy
                            </button>
                        </div>
                        @if($orderId)
                            @php
                                $orderForExpiry = \App\Models\Sales\SalesOrder::find($orderId);
                            @endphp
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Link expires {{ optional($orderForExpiry?->share_token_expires_at)->diffForHumans() ?? 'in 30 days' }}.</p>
                        @endif
                    </div>
                @else
                    <div class="flex items-center justify-center py-4">
                        <flux:icon name="arrow-path" class="size-5 animate-spin text-zinc-400" />
                        <span class="ml-2 text-sm text-zinc-500">Generating link...</span>
                    </div>
                @endif

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-300">
                    Your customer can view order details and confirm the quotation from this link.
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-zinc-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
            <button 
                type="button"
                wire:click="refreshPreviewLink"
                wire:loading.attr="disabled"
                wire:target="refreshPreviewLink"
                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
            >
                <flux:icon name="arrow-path" wire:loading.remove wire:target="refreshPreviewLink" class="size-4" />
                <flux:icon name="arrow-path" wire:loading wire:target="refreshPreviewLink" class="size-4 animate-spin" />
                Regenerate Link
            </button>

            <button 
                type="button"
                @click="showPreviewModal = false"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
            >
                Close
            </button>

            @if($previewLink)
                <button 
                    type="button"
                    onclick="window.open('{{ $previewLink }}', '_blank')"
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    View as Customer
                </button>
            @else
                <button 
                    type="button"
                    disabled
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white opacity-50"
                >
                    View as Customer
                </button>
            @endif
        </div>
    </div>
</div>