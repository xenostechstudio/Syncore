@props([
    'showMessage' => true,
    'showNote' => true,
    'showActivity' => true,
])

{{-- Send Message Form --}}
@if($showMessage)
<div x-show="showSendMessage" x-collapse class="mb-4">
    <div class="flex gap-3">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
            <flux:icon name="chat-bubble-left" class="size-4" />
        </div>
        <div class="flex-1">
            <textarea 
                rows="3"
                placeholder="Write a message..."
                class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-blue-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
            ></textarea>
            <div class="mt-2 flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                        <flux:icon name="paper-clip" class="size-4" />
                    </button>
                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                        <flux:icon name="at-symbol" class="size-4" />
                    </button>
                </div>
                <button type="button" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-blue-700">
                    Send
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Log Note Form --}}
@if($showNote)
<div x-show="showLogNote" x-collapse class="mb-4">
    <div class="flex gap-3">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
            <flux:icon name="pencil-square" class="size-4" />
        </div>
        <div class="flex-1">
            <textarea 
                wire:model="noteContent"
                rows="3"
                placeholder="Log an internal note..."
                class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-amber-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500"
            ></textarea>
            <div class="mt-2 flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Attach file">
                        <flux:icon name="paper-clip" class="size-4" />
                    </button>
                    <button type="button" class="rounded p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" title="Mention">
                        <flux:icon name="at-symbol" class="size-4" />
                    </button>
                </div>
                <button type="button" wire:click="addNote" @click="showLogNote = false" class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-amber-700">
                    Log Note
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Schedule Activity Form --}}
@if($showActivity)
<div x-show="showScheduleActivity" x-collapse class="mb-4">
    <div class="flex gap-3">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
            <flux:icon name="clock" class="size-4" />
        </div>
        <div class="flex-1 space-y-3">
            <div>
                <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Activity Type</label>
                <select class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="">Select activity type...</option>
                    <option value="call">Call</option>
                    <option value="meeting">Meeting</option>
                    <option value="todo">To-Do</option>
                    <option value="email">Email</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Due Date</label>
                <input type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
            </div>
            <div>
                <label class="mb-1 block text-xs text-zinc-500 dark:text-zinc-400">Summary</label>
                <input type="text" placeholder="Activity summary..." class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
            </div>
            <div class="flex justify-end">
                <button type="button" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-violet-700">
                    Schedule
                </button>
            </div>
        </div>
    </div>
</div>
@endif
