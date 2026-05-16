@props([
    // Any attributes (wire:model, name, etc.) are forwarded to the native
    // <select>. Pass `<option>` children as the slot content.
])

{{--
    Styled to mirror the project's standard text input — no shadow, same
    border / bg / text / focus treatment — with a manually positioned
    chevron icon so the visual is consistent across forms. Use this
    instead of <flux:select> on flat settings/form layouts; flux:select
    carries `shadow-xs` plus its own color palette that stands out next
    to plain Tailwind inputs.
--}}
<div class="relative">
    <select
        {{ $attributes->class([
            'w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-9 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400',
            'dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500',
        ]) }}
    >
        {{ $slot }}
    </select>
    <flux:icon name="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400 dark:text-zinc-500" />
</div>
