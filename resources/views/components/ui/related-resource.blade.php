@props([
    'resource' => null,
    'id' => null,
    'href' => null,
    'icon' => null,
    'label' => '',
    'title' => null,
    'tone' => null,
])

{{--
    Pill referencing another resource — e.g. on a Sales Order page,
    show the linked Delivery Order and Invoice. Used both in form
    headers and inside chatter activity entries.

    Pass `resource="sales_order"` (or any ResourceType case) to inherit
    that resource's identity color + default icon from the enum. To
    override either, pass `:tone="..."` or `:icon="..."` directly.

    Usage:
      <x-ui.related-resource
          resource="invoice"
          :href="route('invoicing.invoices.edit', $invoice->id)"
          :label="$invoice->invoice_number"
      >
          <x-ui.status-badge :status="$invoice->state" />
      </x-ui.related-resource>
--}}

@php
    use App\Enums\ResourceType;

    $resourceEnum = $resource instanceof ResourceType
        ? $resource
        : ($resource ? ResourceType::tryFrom($resource) : null);

    $iconName = $icon ?? $resourceEnum?->icon() ?? 'document';
    $toneName = $tone ?? $resourceEnum?->tone() ?? 'zinc';

    // If `:id` is passed without an explicit `:href`, derive the route from
    // the enum. Saves callers from hand-rolling route() in five places per
    // page; one source of truth in ResourceType::route().
    if ($href === null && $id !== null && $resourceEnum !== null) {
        $href = $resourceEnum->route($id);
    }

    // Same idea for label: if no label was passed, fall back to the enum's
    // human name ("Sales Order"). Most callers pass a record number, so
    // this only fires for chips that are pure type references.
    if ($label === '' && $resourceEnum !== null) {
        $label = $resourceEnum->label();
    }

    // Tailwind JIT only ships classes whose names appear as literal
    // strings somewhere in the source — interpolated `bg-{$tone}-50`
    // would silently produce an unstyled chip in production. Enumerate
    // every supported tone here.
    $toneClasses = match ($toneName) {
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50',
        'blue'    => 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50',
        'amber'   => 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50',
        'violet'  => 'border-violet-200 bg-violet-50 text-violet-700 hover:bg-violet-100 dark:border-violet-800 dark:bg-violet-900/30 dark:text-violet-400 dark:hover:bg-violet-900/50',
        'indigo'  => 'border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50',
        'teal'    => 'border-teal-200 bg-teal-50 text-teal-700 hover:bg-teal-100 dark:border-teal-800 dark:bg-teal-900/30 dark:text-teal-400 dark:hover:bg-teal-900/50',
        'sky'     => 'border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-900/30 dark:text-sky-400 dark:hover:bg-sky-900/50',
        default   => 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
    };

    $baseClasses = 'inline-flex flex-shrink-0 items-center gap-2 rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors';
@endphp

@if($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => "{$baseClasses} {$toneClasses}"]) }} @if($title) title="{{ $title }}" @endif>
        <flux:icon name="{{ $iconName }}" class="size-4" />
        <span>{{ $label }}</span>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->merge(['class' => "{$baseClasses} {$toneClasses}"]) }} @if($title) title="{{ $title }}" @endif>
        <flux:icon name="{{ $iconName }}" class="size-4" />
        <span>{{ $label }}</span>
        {{ $slot }}
    </div>
@endif
