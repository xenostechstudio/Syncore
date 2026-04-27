@props([
    'title',
    'first' => false,
])

{{-- Odoo-style gray section header. Use `:first="true"` on the first
     section in a page so it abuts the page chrome (`-mt-6`) instead of
     leaving a top border seam. --}}
<div
    @class([
        '-mx-4 mb-6 bg-zinc-100 px-4 py-2.5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-800/50',
        '-mt-6 border-b border-zinc-200 dark:border-zinc-800' => $first,
        'border-y border-zinc-200 dark:border-zinc-800' => ! $first,
    ])
>
    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h2>
</div>
