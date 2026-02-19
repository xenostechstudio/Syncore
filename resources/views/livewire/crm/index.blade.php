<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left Group --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('crm.leads.index') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    {{ __('crm.new_lead') }}
                </a>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">{{ __('crm.overview') }}</span>
            </div>

            {{-- Right Group --}}
            <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:icon name="calendar" class="size-4" />
                <span>{{ now()->format('F Y') }}</span>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Two Column Layout --}}
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column --}}
            <div class="space-y-6 lg:col-span-4 lg:sticky lg:top-20 lg:h-fit">
                {{-- Lead Stats --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Lead Statistics</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Total Leads</span>
                            <span class="text-sm font-normal text-zinc-900 dark:text-zinc-100">{{ number_format($totalLeads) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">New</span>
                            <span class="text-sm font-normal text-blue-600 dark:text-blue-400">{{ number_format($newLeads) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Qualified</span>
                            <span class="text-sm font-normal text-emerald-600 dark:text-emerald-400">{{ number_format($qualifiedLeads) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-sm font-light text-zinc-500 dark:text-zinc-400">Converted</span>
                            <span class="text-sm font-normal text-violet-600 dark:text-violet-400">{{ number_format($convertedLeads ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Quick Actions</h3>
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <a href="{{ route('crm.leads.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="user-plus" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Manage Leads</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                        <a href="{{ route('crm.opportunities.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="currency-dollar" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">View Pipeline</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                        <a href="{{ route('crm.activities.index') }}" wire:navigate class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="flex items-center gap-3">
                                <flux:icon name="calendar" class="size-4 text-zinc-400" />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Activities</span>
                            </div>
                            <flux:icon name="chevron-right" class="size-4 text-zinc-400" />
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Summary Cards --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="user-plus" class="size-4 text-blue-500 dark:text-blue-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Total Leads</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($totalLeads) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $newLeads }} new, {{ $qualifiedLeads }} qualified</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="currency-dollar" class="size-4 text-violet-500 dark:text-violet-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Open Opportunities</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($openOpportunities) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">In pipeline</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="banknotes" class="size-4 text-emerald-500 dark:text-emerald-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Expected Revenue</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($expectedRevenue / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Weighted: Rp {{ number_format($weightedRevenue / 1000000, 1) }}M</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="trophy" class="size-4 text-amber-500 dark:text-amber-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Won This Month</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($wonThisMonth / 1000000, 1) }}M</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Closed deals</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="calendar" class="size-4 text-cyan-500 dark:text-cyan-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Activities</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($upcomingActivities->count()) }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Upcoming tasks</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2">
                            <flux:icon name="chart-bar" class="size-4 text-pink-500 dark:text-pink-400" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Conversion Rate</p>
                        </div>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $totalLeads > 0 ? number_format(($convertedLeads ?? 0) / $totalLeads * 100, 0) : 0 }}%</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Lead to customer</p>
                    </div>
                </div>

                {{-- Recent Leads --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Leads</h2>
                        <a href="{{ route('crm.leads.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                            View all →
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentLeads as $lead)
                            <div class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $lead->name }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $lead->company_name ?? $lead->email ?? '-' }}</p>
                                </div>
                                @php
                                    $statusColors = ['new' => 'blue', 'contacted' => 'amber', 'qualified' => 'emerald', 'converted' => 'violet', 'lost' => 'red'];
                                    $color = $statusColors[$lead->status] ?? 'zinc';
                                @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-zinc-400">No leads yet</div>
                        @endforelse
                    </div>
                </div>

                {{-- Upcoming Activities --}}
                <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Upcoming Activities</h2>
                        <a href="{{ route('crm.activities.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                            View all →
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($upcomingActivities as $activity)
                            <div class="flex items-start gap-3 px-5 py-3">
                                @php
                                    $typeColors = ['call' => 'blue', 'meeting' => 'violet', 'email' => 'emerald', 'task' => 'amber'];
                                    $typeIcons = ['call' => 'phone', 'meeting' => 'users', 'email' => 'envelope', 'task' => 'clipboard-document-check'];
                                    $color = $typeColors[$activity->type] ?? 'zinc';
                                    $icon = $typeIcons[$activity->type] ?? 'calendar';
                                @endphp
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30">
                                    <flux:icon name="{{ $icon }}" class="size-4 text-{{ $color }}-600 dark:text-{{ $color }}-400" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity->subject }}</p>
                                    <p class="text-xs text-zinc-500">{{ $activity->scheduled_at?->format('M d, H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-zinc-400">No upcoming activities</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
