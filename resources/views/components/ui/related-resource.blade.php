@props([
    'href' => null,
    'icon' => 'document',
    'label' => '',
    'title' => null,
])

{{--
    Header pill for a "related resource" — e.g. on a Sales Order page,
    show the linked Delivery Order and Invoice. Renders a zinc-colored
    chip with icon + label + optional status badge slot.

    Background is intentionally neutral (zinc) across every resource type.
    The interior <x-ui.status-badge> carries the enum-driven color, so the
    chip stays consistent while the status pops against the gray.

    Usage:
      <x-ui.related-resource
          :href="route('sales.orders.edit', $order->id)"
          icon="shopping-cart"
          :label="$order->order_number"
      >
          <x-ui.status-badge :status="$order->state" />
      </x-ui.related-resource>
--}}

@php
    $classes = 'inline-flex flex-shrink-0 items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700';
@endphp

@if($href)
    <a href="{{ $href }}" wire:navigate class="{{ $classes }}" @if($title) title="{{ $title }}" @endif>
        <flux:icon name="{{ $icon }}" class="size-4" />
        <span>{{ $label }}</span>
        {{ $slot }}
    </a>
@else
    <div class="{{ $classes }}" @if($title) title="{{ $title }}" @endif>
        <flux:icon name="{{ $icon }}" class="size-4" />
        <span>{{ $label }}</span>
        {{ $slot }}
    </div>
@endif
