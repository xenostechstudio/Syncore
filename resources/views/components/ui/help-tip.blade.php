@props([
    'position' => 'top',
    'align' => 'center',
    'label' => 'Show help',
])

{{--
    Inline help affordance — a small question-mark icon that reveals a
    short help popover on hover. Place immediately after the label text
    (no extra whitespace) so it reads as part of the label, not a
    separate control.

    Usage:
      <span class="inline-flex items-center gap-1 whitespace-nowrap">
          <span>Expiration</span>
          <x-ui.help-tip>
              The date your quotation expires. After this date the
              customer must request a fresh quote.
          </x-ui.help-tip>
      </span>
--}}

<flux:tooltip position="{{ $position }} {{ $align }}">
    <span
        class="inline-flex -translate-y-px cursor-help items-center justify-center text-zinc-400 transition-colors hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300"
        role="img"
        aria-label="{{ $label }}"
    >
        <flux:icon name="question-mark-circle" class="size-3.5" />
    </span>

    <flux:tooltip.content class="max-w-xs whitespace-normal text-left text-[13px] leading-relaxed font-normal">
        {{ $slot }}
    </flux:tooltip.content>
</flux:tooltip>
