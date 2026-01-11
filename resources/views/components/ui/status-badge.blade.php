@props([
    'status' => 'default',
    'size' => 'sm', // sm, md, lg
])

@php
    $colors = [
        // General
        'default' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
        'active' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
        'inactive' => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400',
        
        // Document states
        'draft' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
        'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        'confirmed' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
        'processing' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-400',
        'sent' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
        'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
        'partial' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        'overdue' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
        'cancelled' => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400',
        'completed' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
        'delivered' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
        'done' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
        'failed' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
        'refunded' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        
        // CRM states
        'new' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
        'contacted' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
        'qualified' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-400',
        'proposal' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        'negotiation' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
        'won' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
        'lost' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
        
        // Delivery states
        'picked' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
        'in_transit' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-400',
        'returned' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        
        // Purchase states
        'rfq' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
        'purchase_order' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];

    $colorClass = $colors[$status] ?? $colors['default'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
    
    $label = ucfirst(str_replace('_', ' ', $status));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-medium {$colorClass} {$sizeClass}"]) }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</span>
