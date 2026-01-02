@props([
    'showMessage' => true,
    'showNote' => true,
    'showActivity' => true,
])

<div class="flex items-center justify-end gap-1">
    @if($showMessage)
        <button 
            @click="showSendMessage = !showSendMessage; showLogNote = false; showScheduleActivity = false" 
            :class="showSendMessage ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
            class="rounded-lg p-2 transition-colors" 
            title="Send message"
        >
            <flux:icon name="chat-bubble-left" class="size-5" />
        </button>
    @endif
    @if($showNote)
        <button 
            @click="showLogNote = !showLogNote; showSendMessage = false; showScheduleActivity = false" 
            :class="showLogNote ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
            class="rounded-lg p-2 transition-colors" 
            title="Log note"
        >
            <flux:icon name="pencil-square" class="size-5" />
        </button>
    @endif
    @if($showActivity)
        <button 
            @click="showScheduleActivity = !showScheduleActivity; showSendMessage = false; showLogNote = false" 
            :class="showScheduleActivity ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'"
            class="rounded-lg p-2 transition-colors" 
            title="Schedule activity"
        >
            <flux:icon name="clock" class="size-5" />
        </button>
    @endif
</div>
