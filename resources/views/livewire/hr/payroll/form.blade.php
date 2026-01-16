<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false, showApproveModal: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.payroll.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Payroll Run</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $periodId ? $name : 'New Payroll Run' }}</span>
                        @if($periodId)
                            <flux:dropdown position="bottom" align="start">
                                <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                    <flux:icon name="cog-6-tooth" class="size-4" />
                                </button>
                                <flux:menu class="w-40">
                                    <button type="button" wire:click="delete" wire:confirm="Delete this payroll period?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
                                    </button>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot:header>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">Please fix the following errors:</span>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>

    {{-- Action Buttons Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$periodId || $status === 'draft')
                        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="document-check" wire:loading.remove wire:target="save" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="save" class="size-4 animate-spin" />
                            <span wire:loading.remove wire:target="save">Save</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    @endif
                    @if($periodId && $status === 'draft')
                        <button type="button" wire:click="generatePayroll" wire:loading.attr="disabled" wire:target="generatePayroll" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="calculator" wire:loading.remove wire:target="generatePayroll" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="generatePayroll" class="size-4 animate-spin" />
                            <span wire:loading.remove wire:target="generatePayroll">Generate</span>
                            <span wire:loading wire:target="generatePayroll">Generating...</span>
                        </button>
                        <button type="button" @click="showApproveModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                            <flux:icon name="check" class="size-4" />
                            Approve
                        </button>
                    @endif
                    @if($periodId && $status === 'approved')
                        <button type="button" wire:click="startProcessing" wire:loading.attr="disabled" wire:target="startProcessing" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-amber-600 disabled:opacity-50">
                            <flux:icon name="play" wire:loading.remove wire:target="startProcessing" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="startProcessing" class="size-4 animate-spin" />
                            <span wire:loading.remove wire:target="startProcessing">Start Processing</span>
                            <span wire:loading wire:target="startProcessing">Processing...</span>
                        </button>
                        <button type="button" wire:click="resetToDraft" wire:loading.attr="disabled" wire:target="resetToDraft" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <flux:icon name="arrow-uturn-left" wire:loading.remove wire:target="resetToDraft" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="resetToDraft" class="size-4 animate-spin" />
                            Back to Draft
                        </button>
                    @endif
                    @if($periodId && $status === 'processing')
                        <button type="button" wire:click="markAsPaid" wire:loading.attr="disabled" wire:target="markAsPaid" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-600 disabled:opacity-50">
                            <flux:icon name="banknotes" wire:loading.remove wire:target="markAsPaid" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="markAsPaid" class="size-4 animate-spin" />
                            <span wire:loading.remove wire:target="markAsPaid">Mark as Paid</span>
                            <span wire:loading wire:target="markAsPaid">Processing...</span>
                        </button>
                    @endif
                    @if($periodId && $status === 'paid')
                        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="printer" class="size-4" />
                            Print
                        </button>
                    @endif
                    @if($periodId && $status === 'cancelled')
                        <button type="button" wire:click="resetToDraft" wire:loading.attr="disabled" wire:target="resetToDraft" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="arrow-uturn-left" wire:loading.remove wire:target="resetToDraft" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="resetToDraft" class="size-4 animate-spin" />
                            <span wire:loading.remove wire:target="resetToDraft">Reopen</span>
                            <span wire:loading wire:target="resetToDraft">Reopening...</span>
                        </button>
                    @endif
                    @if($periodId && !in_array($status, ['paid', 'cancelled']))
                        <button type="button" wire:click="cancel" wire:confirm="Cancel this payroll run?" wire:loading.attr="disabled" wire:target="cancel" class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-50 disabled:opacity-50 dark:border-red-700 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <flux:icon name="x-mark" wire:loading.remove wire:target="cancel" class="size-4" />
                            <flux:icon name="arrow-path" wire:loading wire:target="cancel" class="size-4 animate-spin" />
                            Cancel
                        </button>
                    @endif
                    <a href="{{ route('hr.payroll.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="arrow-left" class="size-4" />
                        Back
                    </a>
                </div>

                {{-- Status Stepper: Draft → Approved → Processing → Paid --}}
                @php
                    $steps = [
                        ['key' => 'draft', 'label' => 'Draft', 'icon' => 'pencil'],
                        ['key' => 'approved', 'label' => 'Approved', 'icon' => 'check'],
                        ['key' => 'processing', 'label' => 'Processing', 'icon' => 'cog-6-tooth'],
                        ['key' => 'paid', 'label' => 'Paid', 'icon' => 'banknotes'],
                    ];
                    $isCancelled = $status === 'cancelled';
                    $currentIndex = $isCancelled ? -1 : collect($steps)->search(fn($s) => $s['key'] === $status);
                    if ($currentIndex === false) $currentIndex = 0;
                @endphp
                <div class="hidden items-center lg:flex">
                    @if($isCancelled)
                        <div class="flex h-[38px] items-center rounded-lg bg-red-100 px-4 text-sm font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                            <flux:icon name="x-circle" class="mr-1.5 size-4" />
                            Cancelled
                        </div>
                    @else
                        @foreach($steps as $index => $step)
                            @php
                                $isActive = $index === $currentIndex;
                                $isCompleted = $index < $currentIndex;
                                $isPending = $index > $currentIndex;
                                $isFirst = $index === 0;
                            @endphp
                            <div class="relative flex items-center {{ !$isFirst ? '-ml-2' : '' }}" style="z-index: {{ count($steps) - $index }};">
                                <div class="relative flex h-[38px] items-center px-4 {{ $isActive ? 'bg-violet-600 text-white' : '' }} {{ $isCompleted ? 'bg-emerald-500 text-white' : '' }} {{ $isPending ? 'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' : '' }}" style="clip-path: polygon({{ $isFirst ? '0 0' : '10px 0' }}, calc(100% - 10px) 0, 100% 50%, calc(100% - 10px) 100%, {{ $isFirst ? '0 100%' : '10px 100%' }}, {{ $isFirst ? '0 50%' : '0 100%, 10px 50%, 0 0' }});">
                                    <span class="flex items-center gap-1 text-sm font-medium whitespace-nowrap">
                                        @if($isCompleted)<flux:icon name="check" class="size-4" />@endif
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="col-span-3 flex items-center justify-end gap-1">
                <x-ui.chatter-buttons :showMessage="false" />
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9 space-y-6">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Header Section --}}
                    <div class="p-5">
                        <h1 class="mb-5 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $periodId ? $name : 'New Payroll Run' }}</h1>
                        <div class="grid gap-6 sm:grid-cols-2">
                            {{-- Left: Period Name --}}
                            <div>
                                <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Period Name <span class="text-red-500">*</span></label>
                                @if($status !== 'draft' && $periodId)
                                    <div class="rounded-lg bg-zinc-50 px-4 py-2.5 text-sm text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">{{ $name }}</div>
                                @else
                                    <input type="text" wire:model.live="name" placeholder="e.g., January 2025" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                @endif
                                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            {{-- Right: Inline Fields --}}
                            <div class="space-y-3">
                                <div class="flex items-center gap-4">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Period <span class="text-red-500">*</span></label>
                                    @if($status !== 'draft' && $periodId)
                                        <div class="flex-1 px-3 py-1.5 text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                                            <span class="text-zinc-400 mx-1">—</span>
                                            {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                        </div>
                                    @else
                                        <div class="flex flex-1 items-center gap-2">
                                            <input type="date" wire:model="startDate" class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700" />
                                            <span class="text-zinc-400">—</span>
                                            <input type="date" wire:model="endDate" class="w-full rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700" />
                                        </div>
                                    @endif
                                </div>
                                @error('startDate')<p class="ml-32 text-xs text-red-500">{{ $message }}</p>@enderror
                                @error('endDate')<p class="ml-32 text-xs text-red-500">{{ $message }}</p>@enderror

                                <div class="flex items-center gap-4">
                                    <label class="w-28 flex-shrink-0 text-sm font-light text-zinc-600 dark:text-zinc-400">Payment Date</label>
                                    @if($status !== 'draft' && $periodId)
                                        <div class="flex-1 px-3 py-1.5 text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $paymentDate ? \Carbon\Carbon::parse($paymentDate)->format('M d, Y') : '-' }}
                                        </div>
                                    @else
                                        <input type="date" wire:model="paymentDate" class="flex-1 rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm text-zinc-900 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:hover:border-zinc-700" />
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="border-t border-zinc-200 p-5 dark:border-zinc-800">
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Notes</label>
                        @if($status !== 'draft' && $periodId)
                            <div class="rounded-lg bg-zinc-50 px-3 py-2 text-sm text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">{{ $notes ?: 'No notes' }}</div>
                        @else
                            <textarea wire:model="notes" rows="2" placeholder="Additional notes..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                        @endif
                    </div>
                </div>

                {{-- Payroll Items --}}
                @if($periodId)
                    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center justify-between border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Employee Payslips ({{ $totalItemsCount }})</h3>
                            <div class="flex items-center gap-3">
                                {{-- Pagination --}}
                                @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages())
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $items->firstItem() }}-{{ $items->lastItem() }}/{{ $items->total() }}
                                        </span>
                                        <div class="flex items-center gap-0.5">
                                            <button type="button" wire:click="previousPage" @disabled($items->onFirstPage()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                                <flux:icon name="chevron-left" class="size-4" />
                                            </button>
                                            <button type="button" wire:click="nextPage" @disabled(!$items->hasMorePages()) class="flex h-7 w-7 items-center justify-center rounded text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                                <flux:icon name="chevron-right" class="size-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                {{-- Search --}}
                                <div class="relative">
                                    <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                                    <input 
                                        type="text" 
                                        wire:model.live.debounce.300ms="payslipSearch" 
                                        placeholder="Search employee..." 
                                        class="w-48 rounded-lg border border-zinc-200 bg-white py-1.5 pl-9 pr-3 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    />
                                    @if($payslipSearch)
                                        <button type="button" wire:click="$set('payslipSearch', '')" class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                                            <flux:icon name="x-mark" class="size-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Employee</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Department</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Basic</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Earnings</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Deductions</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Net</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @forelse($items as $item)
                                        <tr class="group cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50" wire:click="$dispatch('navigate', { url: '{{ route('hr.payroll.item', ['periodId' => $periodId, 'itemId' => $item->id]) }}' })" onclick="Livewire.navigate('{{ route('hr.payroll.item', ['periodId' => $periodId, 'itemId' => $item->id]) }}')">
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->employee->name }}</span>
                                                    <flux:icon name="chevron-right" class="size-3.5 text-zinc-400 opacity-0 transition-opacity group-hover:opacity-100" />
                                                </div>
                                                <span class="block text-xs text-zinc-500">{{ $item->employee->email }}</span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $item->employee->department?->name ?? '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-zinc-900 dark:text-zinc-100">
                                                Rp {{ number_format($item->basic_salary, 0, ',', '.') }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-emerald-600 dark:text-emerald-400">
                                                +Rp {{ number_format($item->total_earnings, 0, ',', '.') }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-red-600 dark:text-red-400">
                                                -Rp {{ number_format($item->total_deductions, 0, ',', '.') }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                Rp {{ number_format($item->net_salary, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-400">
                                                @if($payslipSearch)
                                                    No payslips found matching "{{ $payslipSearch }}"
                                                @else
                                                    No payslips generated. Click "Generate" to create payslips.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($totalItemsCount > 0)
                                    <tfoot class="bg-zinc-50 dark:bg-zinc-900/50">
                                        <tr>
                                            <td colspan="2" class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">Total (all {{ $totalItemsCount }} employees)</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                Rp {{ number_format($totalBasicSalary, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-emerald-600 dark:text-emerald-400">
                                                +Rp {{ number_format($totalEarnings, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-red-600 dark:text-red-400">
                                                -Rp {{ number_format($totalDeductions, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                Rp {{ number_format($totalNetSalary, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                <x-ui.chatter-forms :showMessage="false" />

                @if($periodId)
                    <x-ui.activity-timeline 
                        :activities="$activities" 
                        emptyMessage="Payroll period created"
                        :createdAt="$periodCreatedAt"
                    />
                @else
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Activity</span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>
                    <div class="py-8 text-center">
                        <flux:icon name="clock" class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-zinc-900/60" @click="showApproveModal = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.outside="showApproveModal = false">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                    <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Approve Payroll</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">Are you sure you want to approve this payroll run? Once approved, the payroll data will be locked and ready for payment processing.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showApproveModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Cancel</button>
                <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700 disabled:opacity-50">
                    <flux:icon name="check" wire:loading.remove wire:target="approve" class="size-4" />
                    <flux:icon name="arrow-path" wire:loading wire:target="approve" class="size-4 animate-spin" />
                    <span wire:loading.remove wire:target="approve">Approve</span>
                    <span wire:loading wire:target="approve">Approving...</span>
                </button>
            </div>
        </div>
    </div>
</div>
