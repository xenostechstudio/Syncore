@props([
    'status',
    'type' => 'default', // kept for backward compat: invoice | delivery | order
])

@php
    use App\Enums\Contracts\HasDisplayMetadata;

    // If $status implements HasDisplayMetadata, consume the interface directly.
    if ($status instanceof HasDisplayMetadata) {
        $label = $status->label();
        $color = $status->color();
    } else {
        // Fall back to the legacy string-keyed table.
        $commonStatuses = [
            'active' => ['color' => 'emerald', 'label' => 'Active'],
            'inactive' => ['color' => 'zinc', 'label' => 'Inactive'],
        ];

        if (isset($commonStatuses[$status])) {
            $label = $commonStatuses[$status]['label'];
            $color = $commonStatuses[$status]['color'];
        } else {
            $legacy = match ($type) {
                'invoice' => match ($status) {
                    'draft' => ['color' => 'zinc', 'label' => 'Draft'],
                    'sent' => ['color' => 'blue', 'label' => 'Sent'],
                    'partial' => ['color' => 'amber', 'label' => 'Partial'],
                    'paid' => ['color' => 'emerald', 'label' => 'Paid'],
                    'overdue' => ['color' => 'red', 'label' => 'Overdue'],
                    'cancelled' => ['color' => 'red', 'label' => 'Cancelled'],
                    default => null,
                },
                'delivery' => match ($status) {
                    'pending' => ['color' => 'zinc', 'label' => 'Pending'],
                    'picked' => ['color' => 'blue', 'label' => 'Picked'],
                    'in_transit' => ['color' => 'violet', 'label' => 'In Transit'],
                    'delivered' => ['color' => 'emerald', 'label' => 'Delivered'],
                    'failed' => ['color' => 'red', 'label' => 'Failed'],
                    'returned' => ['color' => 'amber', 'label' => 'Returned'],
                    'cancelled' => ['color' => 'red', 'label' => 'Cancelled'],
                    default => null,
                },
                'order' => match ($status) {
                    'draft', 'quotation' => ['color' => 'zinc', 'label' => 'Quotation'],
                    'confirmed' => ['color' => 'blue', 'label' => 'Confirmed'],
                    'sales_order' => ['color' => 'emerald', 'label' => 'Sales Order'],
                    'cancelled' => ['color' => 'red', 'label' => 'Cancelled'],
                    default => null,
                },
                default => null,
            };

            $label = $legacy['label'] ?? ucfirst(str_replace('_', ' ', (string) $status));
            $color = $legacy['color'] ?? 'zinc';
        }
    }

    // Map Tailwind color names to bg/text classes. Tailwind JIT needs literal
    // strings to include these in the build — so we enumerate every color
    // the enums are known to return.
    $classes = match ($color) {
        'emerald', 'green' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'violet', 'purple' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
        'amber', 'yellow' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'gray', 'zinc' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400',
        default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {$classes}"]) }}>
    {{ $label }}
</span>
