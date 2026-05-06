@props([
    'status',
])

@php
    use App\Enums\Contracts\HasDisplayMetadata;

    // Preferred path: pass an enum that implements HasDisplayMetadata
    // (every state enum in app/Enums does). Strings still work for the
    // common 'active'/'inactive' booleans used by config-style models.
    if ($status instanceof HasDisplayMetadata) {
        $label = $status->label();
        $color = $status->color();
    } else {
        $status = (string) $status;
        $common = [
            'active'   => ['color' => 'emerald', 'label' => 'Active'],
            'inactive' => ['color' => 'zinc',    'label' => 'Inactive'],
        ];
        $label = $common[$status]['label'] ?? ucfirst(str_replace('_', ' ', $status));
        $color = $common[$status]['color'] ?? 'zinc';
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
