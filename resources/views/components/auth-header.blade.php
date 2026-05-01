@props([
    'title',
    'description',
])

<div class="space-y-2">
    <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.28em] text-zinc-600">
        <span class="h-px w-5 bg-zinc-700"></span>
        <span>Sign in</span>
    </div>
    <h2 class="font-display text-[34px] font-semibold leading-[1.05] tracking-[-0.02em] text-zinc-50"
        style="font-variation-settings: 'opsz' 48, 'SOFT' 30;">
        {{ $title }}
    </h2>
    <p class="text-sm text-zinc-400">
        {{ $description }}
    </p>
</div>
