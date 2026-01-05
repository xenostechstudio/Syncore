<div x-data="{ showLogNote: false, showSendMessage: false, showScheduleActivity: false, showDeleteModal: false, deleteIndex: null }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.payroll.edit', $period->id) }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Payslip</span>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->employee->name }}</span>
                </div>
            </div>
        </div>
    </x-slot:header>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
    </div>

    {{-- Action Bar --}}
    <div class="-mx-4 -mt-6 bg-zinc-50 px-4 py-3 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 dark:bg-zinc-900/50">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-9 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($this->isEditable)
                        <button type="button" wire:click="openAddModal" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <flux:icon name="plus" class="size-4" />
                            Add Adjustment
                        </button>
                    @endif
                    <a href="{{ route('hr.payroll.edit', $period->id) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="arrow-left" class="size-4" />
                        Back
                    </a>
                </div>

                {{-- Status Stepper: Draft → Approved → Processing → Paid --}}
                @php
                    $steps = [
                        ['key' => 'draft', 'label' => 'Draft'],
                        ['key' => 'approved', 'label' => 'Approved'],
                        ['key' => 'processing', 'label' => 'Processing'],
                        ['key' => 'paid', 'label' => 'Paid'],
                    ];
                    $isCancelled = $period->status === 'cancelled';
                    $currentIndex = $isCancelled ? -1 : collect($steps)->search(fn($s) => $s['key'] === $period->status);
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
            {{-- Left: Employee Info & Payslip Breakdown --}}
            <div class="lg:col-span-9 space-y-6">
                {{-- Employee Card --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 text-xl font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $item->employee->initials }}
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $item->employee->name }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $item->employee->position?->name ?? 'No position' }} · {{ $item->employee->department?->name ?? 'No department' }}</p>
                            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ $item->employee->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Period</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $period->name }}</p>
                            <p class="text-xs text-zinc-400">{{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Payslip Breakdown with Summary --}}
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Payslip Breakdown</h3>
                        <div class="flex items-center gap-3">
                            {{-- Legend --}}
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-xs text-zinc-500">
                                    <span class="inline-flex h-4 items-center rounded bg-zinc-100 px-1 text-[9px] font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">REC</span>
                                    Recurring
                                </span>
                                <span class="inline-flex items-center gap-1 text-xs text-zinc-500">
                                    <span class="inline-flex h-4 items-center rounded bg-amber-100 px-1 text-[9px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">ADJ</span>
                                    Adjustment
                                </span>
                            </div>
                            @if(!$this->isEditable)
                                <span class="inline-flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="lock-closed" class="size-3" />
                                    Locked
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Basic Salary --}}
                    <div class="border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Basic Salary</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Monthly base salary</p>
                            </div>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->basic_salary, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Earnings Section --}}
                    @php $earnings = collect($details)->where('type', 'earning'); @endphp
                    @if($earnings->count() > 0)
                        <div class="border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between bg-emerald-50/50 px-4 py-2 dark:bg-emerald-900/10">
                                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Earnings</p>
                                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($this->totalEarnings, 0, ',', '.') }}</p>
                            </div>
                            <div class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                @foreach($earnings as $index => $detail)
                                    <div class="group flex items-center justify-between px-4 py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/30" wire:key="detail-{{ $detail['id'] }}">
                                        <div class="flex items-center gap-3">
                                            @if($detail['source'] === 'adjustment')
                                                <span class="inline-flex h-5 items-center rounded bg-amber-100 px-1.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">ADJ</span>
                                            @else
                                                <span class="inline-flex h-5 items-center rounded bg-zinc-100 px-1.5 text-[10px] font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">REC</span>
                                            @endif
                                            <div>
                                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $detail['component_name'] }}</p>
                                                @if($detail['notes'])
                                                    <p class="text-xs text-zinc-400">{{ $detail['notes'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($this->isEditable && $detail['source'] === 'adjustment')
                                                <input 
                                                    type="text" 
                                                    wire:model.blur="details.{{ $index }}.amount"
                                                    wire:change="updateDetailAmount({{ $index }})"
                                                    class="w-32 rounded border-0 bg-transparent py-1 text-right text-sm text-emerald-600 focus:ring-1 focus:ring-emerald-500 dark:text-emerald-400 [appearance:textfield]"
                                                />
                                            @else
                                                <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">+Rp {{ number_format($detail['amount'], 0, ',', '.') }}</span>
                                            @endif
                                            @if($this->isEditable && $detail['source'] === 'adjustment')
                                                <div class="flex items-center gap-0.5 opacity-0 transition-opacity group-hover:opacity-100">
                                                    <button type="button" wire:click="editDetail({{ $index }})" class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700">
                                                        <flux:icon name="pencil" class="size-3.5" />
                                                    </button>
                                                    <button type="button" @click="deleteIndex = {{ $index }}; showDeleteModal = true" class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                                        <flux:icon name="trash" class="size-3.5" />
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Deductions Section --}}
                    @php $deductions = collect($details)->where('type', 'deduction'); @endphp
                    @if($deductions->count() > 0)
                        <div class="border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between bg-red-50/50 px-4 py-2 dark:bg-red-900/10">
                                <p class="text-xs font-semibold uppercase tracking-wider text-red-700 dark:text-red-400">Deductions</p>
                                <p class="text-xs font-medium text-red-600 dark:text-red-400">-Rp {{ number_format($this->totalDeductions, 0, ',', '.') }}</p>
                            </div>
                            <div class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                @foreach($deductions as $index => $detail)
                                    <div class="group flex items-center justify-between px-4 py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/30" wire:key="detail-{{ $detail['id'] }}">
                                        <div class="flex items-center gap-3">
                                            @if($detail['source'] === 'adjustment')
                                                <span class="inline-flex h-5 items-center rounded bg-amber-100 px-1.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">ADJ</span>
                                            @else
                                                <span class="inline-flex h-5 items-center rounded bg-zinc-100 px-1.5 text-[10px] font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">REC</span>
                                            @endif
                                            <div>
                                                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $detail['component_name'] }}</p>
                                                @if($detail['notes'])
                                                    <p class="text-xs text-zinc-400">{{ $detail['notes'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($this->isEditable && $detail['source'] === 'adjustment')
                                                <input 
                                                    type="text" 
                                                    wire:model.blur="details.{{ $index }}.amount"
                                                    wire:change="updateDetailAmount({{ $index }})"
                                                    class="w-32 rounded border-0 bg-transparent py-1 text-right text-sm text-red-600 focus:ring-1 focus:ring-red-500 dark:text-red-400 [appearance:textfield]"
                                                />
                                            @else
                                                <span class="text-sm font-medium text-red-600 dark:text-red-400">-Rp {{ number_format($detail['amount'], 0, ',', '.') }}</span>
                                            @endif
                                            @if($this->isEditable && $detail['source'] === 'adjustment')
                                                <div class="flex items-center gap-0.5 opacity-0 transition-opacity group-hover:opacity-100">
                                                    <button type="button" wire:click="editDetail({{ $index }})" class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700">
                                                        <flux:icon name="pencil" class="size-3.5" />
                                                    </button>
                                                    <button type="button" @click="deleteIndex = {{ $index }}; showDeleteModal = true" class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                                        <flux:icon name="trash" class="size-3.5" />
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Empty State --}}
                    @if($earnings->count() === 0 && $deductions->count() === 0)
                        <div class="border-b border-zinc-100 px-4 py-8 text-center dark:border-zinc-800">
                            <flux:icon name="document-text" class="mx-auto size-8 text-zinc-300 dark:text-zinc-600" />
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No additional components</p>
                            @if($this->isEditable)
                                <button type="button" wire:click="openAddModal" class="mt-2 text-sm font-medium text-zinc-900 hover:underline dark:text-zinc-100">
                                    Add adjustment
                                </button>
                            @endif
                        </div>
                    @endif

                    {{-- Add Adjustment Button (inline) --}}
                    @if($this->isEditable && ($earnings->count() > 0 || $deductions->count() > 0))
                        <div class="border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                            <button type="button" wire:click="openAddModal" class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                                <flux:icon name="plus" class="size-4" />
                                Add adjustment
                            </button>
                        </div>
                    @endif

                    {{-- Summary Footer --}}
                    <div class="bg-zinc-50 px-4 py-4 dark:bg-zinc-900/50">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <div class="flex items-center gap-6 text-sm">
                                    <span class="text-zinc-500 dark:text-zinc-400">Basic: <span class="font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($item->basic_salary, 0, ',', '.') }}</span></span>
                                    <span class="text-emerald-600 dark:text-emerald-400">+ Earnings: Rp {{ number_format($this->totalEarnings, 0, ',', '.') }}</span>
                                    <span class="text-red-600 dark:text-red-400">- Deductions: Rp {{ number_format($this->totalDeductions, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Net Salary</p>
                                <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->netSalary, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Activity Timeline --}}
            <div class="lg:col-span-3">
                <x-ui.chatter-forms :showMessage="false" />

                <x-ui.activity-timeline 
                    :activities="$activities" 
                    emptyMessage="Payslip generated"
                    :createdAt="$itemCreatedAt"
                />
            </div>
        </div>
    </div>

    {{-- Add/Edit Adjustment Modal --}}
    @if($showAddModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center overflow-y-auto bg-black/50 p-4" x-data @keydown.escape.window="$wire.set('showAddModal', false)">
        <div class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900" @click.outside="$wire.set('showAddModal', false)">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $editingIndex !== null ? 'Edit' : 'Add' }} Adjustment
                </h3>
                <button type="button" wire:click="$set('showAddModal', false)" class="rounded-lg p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Quick Select --}}
                @if($editingIndex === null)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Quick Select</label>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($this->commonAdjustments as $adj)
                                <button 
                                    type="button" 
                                    wire:click="selectCommonAdjustment('{{ $adj['name'] }}', '{{ $adj['type'] }}')"
                                    class="rounded-full px-2.5 py-1 text-xs font-medium transition-colors {{ $adj['type'] === 'earning' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400' }}"
                                >
                                    {{ $adj['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="adjustmentName" placeholder="e.g., Overtime, Bonus, Late Deduction" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    @error('adjustmentName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type <span class="text-red-500">*</span></label>
                    <div class="flex gap-3">
                        <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 transition-colors {{ $adjustmentType === 'earning' ? 'border-emerald-500 bg-emerald-50 dark:border-emerald-600 dark:bg-emerald-900/20' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700' }}">
                            <input type="radio" wire:model="adjustmentType" value="earning" class="sr-only">
                            <flux:icon name="arrow-trending-up" class="size-5 {{ $adjustmentType === 'earning' ? 'text-emerald-600' : 'text-zinc-400' }}" />
                            <span class="text-sm font-medium {{ $adjustmentType === 'earning' ? 'text-emerald-700 dark:text-emerald-400' : 'text-zinc-600 dark:text-zinc-400' }}">Earning</span>
                        </label>
                        <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 transition-colors {{ $adjustmentType === 'deduction' ? 'border-red-500 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700' }}">
                            <input type="radio" wire:model="adjustmentType" value="deduction" class="sr-only">
                            <flux:icon name="arrow-trending-down" class="size-5 {{ $adjustmentType === 'deduction' ? 'text-red-600' : 'text-zinc-400' }}" />
                            <span class="text-sm font-medium {{ $adjustmentType === 'deduction' ? 'text-red-700 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}">Deduction</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Amount <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-500">Rp</span>
                        <input type="text" wire:model="adjustmentAmount" class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 [appearance:textfield]">
                    </div>
                    @error('adjustmentAmount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes</label>
                    <textarea wire:model="adjustmentNotes" rows="2" placeholder="Optional notes..." class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <button type="button" wire:click="$set('showAddModal', false)" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    Cancel
                </button>
                <button type="button" wire:click="saveAdjustment" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    {{ $editingIndex !== null ? 'Update' : 'Add' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
        <div class="relative w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.outside="showDeleteModal = false">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <flux:icon name="trash" class="size-5 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Remove Adjustment</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">Are you sure you want to remove this adjustment from the payslip?</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="showDeleteModal = false" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">Cancel</button>
                <button type="button" @click="$wire.removeDetail(deleteIndex); showDeleteModal = false" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">Remove</button>
            </div>
        </div>
    </div>
</div>
