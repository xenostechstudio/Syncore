@props([
    'status',
    'type' => 'default', // invoice, delivery, order
])

@php
    $config = match($type) {
        'invoice' => match($status) {
            'draft' => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300', 'label' => 'Draft'],
            'sent' => ['bg' => 'bg-blue-200 dark:bg-blue-800', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'Sent'],
            'partial' => ['bg' => 'bg-amber-200 dark:bg-amber-800', 'text' => 'text-amber-700 dark:text-amber-300', 'label' => 'Partial'],
            'paid' => ['bg' => 'bg-emerald-200 dark:bg-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300', 'label' => 'Paid'],
            'overdue' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300', 'label' => 'Overdue'],
            'cancelled' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300', 'label' => 'Cancelled'],
            default => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300', 'label' => ucfirst($status)],
        },
        'delivery' => match($status) {
            'pending' => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300', 'label' => 'Pending'],
            'picked' => ['bg' => 'bg-blue-200 dark:bg-blue-800', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'Picked'],
            'in_transit' => ['bg' => 'bg-violet-200 dark:bg-violet-800', 'text' => 'text-violet-700 dark:text-violet-300', 'label' => 'In Transit'],
            'delivered' => ['bg' => 'bg-emerald-200 dark:bg-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300', 'label' => 'Delivered'],
            'failed' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300', 'label' => 'Failed'],
            'returned' => ['bg' => 'bg-amber-200 dark:bg-amber-800', 'text' => 'text-amber-700 dark:text-amber-300', 'label' => 'Returned'],
            'cancelled' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300', 'label' => 'Cancelled'],
            default => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300', 'label' => ucfirst(str_replace('_', ' ', $status))],
        },
        'order' => match($status) {
            'draft', 'quotation' => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300', 'label' => 'Quotation'],
            'confirmed' => ['bg' => 'bg-blue-200 dark:bg-blue-800', 'text' => 'text-blue-700 dark:text-blue-300', 'label' => 'Confirmed'],
            'sales_order' => ['bg' => 'bg-emerald-200 dark:bg-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300', 'label' => 'Sales Order'],
            'cancelled' => ['bg' => 'bg-red-200 dark:bg-red-800', 'text' => 'text-red-700 dark:text-red-300', 'label' => 'Cancelled'],
            default => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300', 'label' => ucfirst(str_replace('_', ' ', $status))],
        },
        default => ['bg' => 'bg-zinc-200 dark:bg-zinc-700', 'text' => 'text-zinc-600 dark:text-zinc-300', 'label' => ucfirst(str_replace('_', ' ', $status))],
    };
@endphp

<span {{ $attributes->merge(['class' => "rounded px-1.5 py-0.5 text-xs font-medium {$config['bg']} {$config['text']}"]) }}>
    {{ $config['label'] }}
</span>
