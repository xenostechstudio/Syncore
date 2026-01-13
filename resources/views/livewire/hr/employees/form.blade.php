<div x-data="{ activeTab: 'work', showLogNote: false }">
    <x-slot:header>
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.employees.index') }}" wire:navigate class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="arrow-left" class="size-5" />
                </a>
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Employee</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $employeeId ? $name : 'New Employee' }}
                        </span>
                        <flux:dropdown position="bottom" align="start">
                            <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                                <flux:icon name="cog-6-tooth" class="size-4" />
                            </button>
                            <flux:menu class="w-40">
                                @if($employeeId)
                                    <button type="button" wire:click="delete" wire:confirm="Delete this employee?" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        <flux:icon name="trash" class="size-4" />
                                        <span>Delete</span>
                                    </button>
                                @else
                                    <div class="px-2 py-1.5 text-sm text-zinc-500 dark:text-zinc-400">No actions</div>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
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
                    <button type="button" wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <flux:icon name="document-check" class="size-4" />
                        Save
                    </button>
                    <a href="{{ route('hr.employees.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <flux:icon name="x-mark" class="size-4" />
                        Cancel
                    </a>
                </div>
                <div class="hidden items-center lg:flex">
                    @php
                        $statusConfig = match($status) {
                            'active' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'icon' => 'check-circle'],
                            'inactive' => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'icon' => null],
                            'terminated' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'icon' => 'x-circle'],
                            'resigned' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'icon' => 'arrow-right-start-on-rectangle'],
                            default => ['bg' => 'bg-zinc-100 dark:bg-zinc-800', 'text' => 'text-zinc-600 dark:text-zinc-400', 'icon' => null],
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                        @if($statusConfig['icon'])
                            <flux:icon name="{{ $statusConfig['icon'] }}" class="mr-1 size-3" />
                        @endif
                        {{ ucfirst($status) }}
                    </span>
                </div>
            </div>
            <div class="col-span-3">
                <x-ui.chatter-buttons :showMessage="false" :showActivity="false" />
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="-mx-4 px-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Left Column: Main Form --}}
            <div class="lg:col-span-9">
                <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Profile Header Section --}}
                    <div class="p-5">
                        <div class="flex items-start gap-6">
                            {{-- Profile Image Placeholder --}}
                            <div class="relative flex-shrink-0">
                                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                    @if($employeeId && $name)
                                        <span class="text-3xl font-medium text-zinc-400 dark:text-zinc-500">
                                            {{ strtoupper(substr($name, 0, 2)) }}
                                        </span>
                                    @else
                                        <flux:icon name="user" class="size-10 text-zinc-300 dark:text-zinc-600" />
                                    @endif
                                </div>
                                <button type="button" class="absolute -bottom-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-zinc-100 text-zinc-500 transition-colors hover:bg-zinc-200 dark:border-zinc-900 dark:bg-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-600" title="Change photo">
                                    <flux:icon name="camera" class="size-3.5" />
                                </button>
                            </div>

                            {{-- Name, Email, Phone --}}
                            <div class="flex-1 space-y-1">
                                {{-- Full Name (Big Input) --}}
                                <div>
                                    <input type="text" wire:model="name" placeholder="Full Name" class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-2xl font-bold text-zinc-900 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-200 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-700 dark:focus:border-zinc-700" />
                                    @error('name') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Email --}}
                                <div class="flex items-center gap-2 pl-2">
                                    <flux:icon name="envelope" class="size-4 flex-shrink-0 text-zinc-400" />
                                    <input type="email" wire:model="email" placeholder="Email address" class="flex-1 border-0 border-b border-transparent bg-transparent px-0 py-0.5 text-sm text-zinc-700 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500 dark:hover:border-zinc-700" />
                                </div>
                                @error('email') <p class="ml-8 text-xs text-red-500">{{ $message }}</p> @enderror

                                {{-- Phone --}}
                                <div class="flex items-center gap-2 pl-2">
                                    <flux:icon name="phone" class="size-4 flex-shrink-0 text-zinc-400" />
                                    <input type="tel" wire:model="phone" placeholder="Phone number" class="flex-1 border-0 border-b border-transparent bg-transparent px-0 py-0.5 text-sm text-zinc-700 placeholder-zinc-400 transition-colors hover:border-zinc-200 focus:border-zinc-400 focus:outline-none focus:ring-0 dark:text-zinc-300 dark:placeholder-zinc-500 dark:hover:border-zinc-700" />
                                </div>
                                @error('phone') <p class="ml-8 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Tab Headers --}}
                    <div class="mx-5 mb-4 border-b border-zinc-200 dark:border-zinc-800">
                        <nav class="-mb-px flex space-x-4 text-sm">
                            <button type="button" @click="activeTab = 'work'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1" :class="activeTab === 'work' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                                Work
                            </button>
                            <button type="button" @click="activeTab = 'personal'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1" :class="activeTab === 'personal' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                                Personal
                            </button>
                            <button type="button" @click="activeTab = 'payroll'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1" :class="activeTab === 'payroll' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                                Payroll
                            </button>
                            <button type="button" @click="activeTab = 'settings'" class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1" :class="activeTab === 'settings' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                                Settings
                            </button>
                        </nav>
                    </div>

                    {{-- Tab Content: Work --}}
                    <div x-show="activeTab === 'work'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-8">
                                {{-- Position --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Position</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Department and job position.</p>
                                    </div>
                                    <div class="flex-1 space-y-4">
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Department</label>
                                                <div class="relative">
                                                    <select wire:model.live="departmentId" class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-9 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                        <option value="">Select department...</option>
                                                        @foreach($departments as $dept)
                                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Position</label>
                                                <div class="relative">
                                                    <select wire:model="positionId" class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-9 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                        <option value="">Select position...</option>
                                                        @foreach($this->positions as $pos)
                                                            <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Manager</label>
                                            {{-- Searchable Manager Dropdown --}}
                                            <div class="relative" x-data="{ open: false, search: '' }">
                                                <button 
                                                    type="button"
                                                    @click="open = !open; $nextTick(() => { if(open) $refs.managerSearch.focus() })"
                                                    class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-2 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                                >
                                                    @php $selectedManager = $employees->firstWhere('id', $managerId); @endphp
                                                    @if($selectedManager)
                                                        <div class="flex items-center gap-2">
                                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                                {{ $selectedManager->initials }}
                                                            </div>
                                                            <span class="text-zinc-900 dark:text-zinc-100">{{ $selectedManager->name }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-zinc-400">No manager</span>
                                                    @endif
                                                    <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                </button>
                                                <div 
                                                    x-show="open" 
                                                    @click.outside="open = false; search = ''"
                                                    x-transition
                                                    class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                                >
                                                    <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                        <input 
                                                            type="text"
                                                            x-ref="managerSearch"
                                                            x-model="search"
                                                            placeholder="Search employees..."
                                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                            @keydown.escape="open = false; search = ''"
                                                        />
                                                    </div>
                                                    <div class="max-h-48 overflow-auto py-1">
                                                        <button 
                                                            type="button"
                                                            wire:click="$set('managerId', null)"
                                                            @click="open = false; search = ''"
                                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-500 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                        >
                                                            <span>No manager</span>
                                                        </button>
                                                        @foreach($employees as $emp)
                                                            <button 
                                                                type="button"
                                                                x-show="'{{ strtolower($emp->name) }}'.includes(search.toLowerCase()) || search === ''"
                                                                wire:click="$set('managerId', {{ $emp->id }})"
                                                                @click="open = false; search = ''"
                                                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $managerId == $emp->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                                                            >
                                                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                                    {{ $emp->initials }}
                                                                </div>
                                                                <span class="text-zinc-900 dark:text-zinc-100">{{ $emp->name }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Employment --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Employment</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Employment type and status.</p>
                                    </div>
                                    <div class="flex-1 space-y-4">
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Employment Type</label>
                                                <div class="relative">
                                                    <select wire:model="employmentType" class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-9 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                        <option value="permanent">Permanent</option>
                                                        <option value="contract">Contract</option>
                                                        <option value="probation">Probation</option>
                                                        <option value="intern">Intern</option>
                                                        <option value="freelance">Freelance</option>
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Status</label>
                                                <div class="relative">
                                                    <select wire:model="status" class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-9 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                        <option value="terminated">Terminated</option>
                                                        <option value="resigned">Resigned</option>
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Hire Date</label>
                                                <input type="date" wire:model="hireDate" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Contract End Date</label>
                                                <input type="date" wire:model="contractEndDate" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Contact --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Contact</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Additional contact information.</p>
                                    </div>
                                    <div class="flex-1">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Mobile</label>
                                            <input type="text" wire:model="mobile" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Personal --}}
                    <div x-show="activeTab === 'personal'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-8">
                                {{-- Personal Information --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Personal Information</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Basic personal details.</p>
                                    </div>
                                    <div class="flex-1 space-y-4">
                                        <div class="grid gap-4 sm:grid-cols-3">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Birth Date</label>
                                                <input type="date" wire:model="birthDate" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Gender</label>
                                                <div class="relative">
                                                    <select wire:model="gender" class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-9 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                        <option value="">Select...</option>
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Marital Status</label>
                                                <div class="relative">
                                                    <select wire:model="maritalStatus" class="w-full appearance-none rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-9 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                        <option value="">Select...</option>
                                                        <option value="single">Single</option>
                                                        <option value="married">Married</option>
                                                        <option value="divorced">Divorced</option>
                                                        <option value="widowed">Widowed</option>
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">ID Number (KTP/Passport)</label>
                                                <input type="text" wire:model="idNumber" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Nationality</label>
                                                <input type="text" wire:model="nationality" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Address --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Address</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Home address information.</p>
                                    </div>
                                    <div class="flex-1 space-y-4">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Address</label>
                                            <textarea wire:model="address" rows="2" class="w-full resize-none rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                                        </div>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">City</label>
                                                <input type="text" wire:model="city" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Postal Code</label>
                                                <input type="text" wire:model="postalCode" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Emergency Contact --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Emergency Contact</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Person to contact in case of emergency.</p>
                                    </div>
                                    <div class="flex-1 space-y-4">
                                        <div class="grid gap-4 sm:grid-cols-3">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Contact Name</label>
                                                <input type="text" wire:model="emergencyContactName" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Contact Phone</label>
                                                <input type="text" wire:model="emergencyContactPhone" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Relationship</label>
                                                <input type="text" wire:model="emergencyContactRelation" placeholder="e.g., Spouse, Parent" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Payroll --}}
                    <div x-show="activeTab === 'payroll'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-8">
                                {{-- Bank Information --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Bank Information</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Bank account for salary transfer.</p>
                                    </div>
                                    <div class="flex-1 space-y-4">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Bank Name</label>
                                            <input type="text" wire:model="bankName" placeholder="e.g., BCA, Mandiri, BNI" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        </div>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Account Number</label>
                                                <input type="text" wire:model="bankAccountNumber" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Account Name</label>
                                                <input type="text" wire:model="bankAccountName" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tax Information --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Tax Information</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Tax identification number.</p>
                                    </div>
                                    <div class="flex-1">
                                        <div>
                                            <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">NPWP</label>
                                            <input type="text" wire:model="taxId" placeholder="XX.XXX.XXX.X-XXX.XXX" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        </div>
                                    </div>
                                </div>

                                {{-- Basic Salary --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Basic Salary</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Monthly base salary.</p>
                                    </div>
                                    <div class="flex-1">
                                        <div class="relative max-w-xs">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-500">Rp</span>
                                            <input type="number" wire:model="basicSalary" step="1000" class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-10 pr-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        </div>
                                    </div>
                                </div>

                                {{-- Salary Components Table --}}
                                <div>
                                    <div class="mb-3">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Salary Components</h3>
                                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Allowances, deductions, and other components.</p>
                                    </div>

                                    <div class="overflow-visible rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <table class="w-full">
                                            <thead>
                                                <tr class="border-b border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-900/50">
                                                    <th class="px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Component</th>
                                                    <th class="w-28 px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Type</th>
                                                    <th class="w-32 px-3 py-2.5 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Effective</th>
                                                    <th class="w-44 px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Amount</th>
                                                    <th class="w-10 px-2 py-2.5"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/50">
                                                @foreach($employeeSalaryComponents as $index => $component)
                                                    @php
                                                        $isEarning = $component['component_type'] === 'earning';
                                                    @endphp
                                                    <tr class="group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30" wire:key="comp-{{ $index }}">
                                                        {{-- Component Selection --}}
                                                        <td class="px-3 py-2 overflow-visible">
                                                            <div x-data="{ open: false, search: '' }" class="relative">
                                                                @if($component['salary_component_id'])
                                                                    <button 
                                                                        type="button"
                                                                        @click="open = true; $nextTick(() => $refs.compSearch{{ $index }}.focus())"
                                                                        class="flex w-full items-center gap-2 text-left"
                                                                    >
                                                                        <div>
                                                                            <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $component['component_name'] }}</p>
                                                                            <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $component['component_code'] }}</p>
                                                                        </div>
                                                                    </button>
                                                                @else
                                                                    <button 
                                                                        type="button"
                                                                        @click="open = true; $nextTick(() => $refs.compSearch{{ $index }}.focus())"
                                                                        class="text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                                                                    >
                                                                        Select component...
                                                                    </button>
                                                                @endif

                                                                {{-- Component Dropdown --}}
                                                                <div 
                                                                    x-show="open" 
                                                                    @click.outside="open = false; search = ''"
                                                                    x-transition
                                                                    class="absolute left-0 top-full z-[200] mt-1 w-80 rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
                                                                >
                                                                    <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                                        <input 
                                                                            type="text"
                                                                            x-ref="compSearch{{ $index }}"
                                                                            x-model="search"
                                                                            placeholder="Search components..."
                                                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                                            @keydown.escape="open = false; search = ''"
                                                                        />
                                                                    </div>
                                                                    <div class="max-h-48 overflow-auto py-1">
                                                                        @foreach($this->availableSalaryComponents as $comp)
                                                                            @php $compType = $comp->type; @endphp
                                                                            <button 
                                                                                type="button"
                                                                                x-show="'{{ strtolower($comp->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($comp->code) }}'.includes(search.toLowerCase()) || search === ''"
                                                                                wire:click="selectComponent({{ $index }}, {{ $comp->id }})"
                                                                                @click="open = false; search = ''"
                                                                                class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                                                            >
                                                                                <div class="flex-1">
                                                                                    <p class="text-zinc-900 dark:text-zinc-100">{{ $comp->name }}</p>
                                                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $comp->code }} · {{ ucfirst($compType) }}</p>
                                                                                </div>
                                                                                @if($comp->default_amount)
                                                                                    <span class="text-xs text-zinc-400">Rp {{ number_format($comp->default_amount, 0, ',', '.') }}</span>
                                                                                @endif
                                                                            </button>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        {{-- Type Badge --}}
                                                        <td class="px-3 py-2">
                                                            @if($component['salary_component_id'])
                                                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $isEarning ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                                                    {{ $isEarning ? 'Earning' : 'Deduction' }}
                                                                </span>
                                                            @endif
                                                        </td>

                                                        {{-- Effective From --}}
                                                        <td class="px-3 py-2">
                                                            <input 
                                                                type="date"
                                                                wire:model="employeeSalaryComponents.{{ $index }}.effective_from"
                                                                class="w-full bg-transparent text-sm text-zinc-900 focus:outline-none dark:text-zinc-100"
                                                            />
                                                        </td>

                                                        {{-- Amount --}}
                                                        <td class="px-3 py-2">
                                                            <input 
                                                                type="text"
                                                                wire:model.live="employeeSalaryComponents.{{ $index }}.amount"
                                                                class="w-full bg-transparent text-right text-sm {{ $isEarning ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} focus:outline-none [appearance:textfield]"
                                                            />
                                                        </td>

                                                        {{-- Remove --}}
                                                        <td class="px-2 py-2 text-right">
                                                            <button 
                                                                type="button"
                                                                wire:click="removeComponent({{ $index }})"
                                                                class="rounded p-1 text-zinc-300 opacity-0 transition-all hover:text-red-500 group-hover:opacity-100 dark:text-zinc-600 dark:hover:text-red-400"
                                                            >
                                                                <flux:icon name="trash" class="size-4" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        {{-- Add Line Button --}}
                                        <div class="border-t border-zinc-100 px-3 py-2.5 dark:border-zinc-800">
                                            <button 
                                                type="button"
                                                wire:click="addComponent"
                                                class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                                            >
                                                <flux:icon name="plus" class="size-4" />
                                                Add a line
                                            </button>
                                        </div>

                                        {{-- Totals --}}
                                        @if(count($employeeSalaryComponents) > 0)
                                            <div class="border-t border-zinc-200 bg-zinc-50/50 px-3 py-3 dark:border-zinc-700 dark:bg-zinc-900/50">
                                                <div class="flex justify-end">
                                                    <div class="w-72 space-y-1.5">
                                                        <div class="flex items-center justify-between text-sm">
                                                            <span class="text-zinc-500 dark:text-zinc-400">Basic Salary</span>
                                                            <span class="text-zinc-900 dark:text-zinc-100">Rp {{ number_format($basicSalary, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between text-sm">
                                                            <span class="text-zinc-500 dark:text-zinc-400">Total Earnings</span>
                                                            <span class="font-medium text-emerald-600 dark:text-emerald-400">+ Rp {{ number_format($this->totalEarnings, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between text-sm">
                                                            <span class="text-zinc-500 dark:text-zinc-400">Total Deductions</span>
                                                            <span class="font-medium text-red-600 dark:text-red-400">- Rp {{ number_format($this->totalDeductions, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between border-t border-zinc-200 pt-1.5 text-sm dark:border-zinc-700">
                                                            <span class="font-medium text-zinc-900 dark:text-zinc-100">Estimated Total</span>
                                                            <span class="font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($this->estimatedTotalSalary, 0, ',', '.') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Content: Settings --}}
                    <div x-show="activeTab === 'settings'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="px-5 pb-5">
                            <div class="space-y-8">
                                {{-- Link to User --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Link to User</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Connect this employee to a system user account.</p>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            {{-- Searchable User Dropdown --}}
                                            <div class="relative flex-1" x-data="{ open: false, search: '' }">
                                                <button 
                                                    type="button"
                                                    @click="open = !open; $nextTick(() => { if(open) $refs.userSearch.focus() })"
                                                    class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-2 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                                >
                                                    @php $selectedUser = $users->firstWhere('id', $userId); @endphp
                                                    @if($selectedUser)
                                                        <div class="flex items-center gap-2">
                                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                                {{ strtoupper(substr($selectedUser->name, 0, 2)) }}
                                                            </div>
                                                            <span class="text-zinc-900 dark:text-zinc-100">{{ $selectedUser->name }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-zinc-400">No linked user</span>
                                                    @endif
                                                    <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                                </button>
                                                <div 
                                                    x-show="open" 
                                                    @click.outside="open = false; search = ''"
                                                    x-transition
                                                    class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                                >
                                                    <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                        <input 
                                                            type="text"
                                                            x-ref="userSearch"
                                                            x-model="search"
                                                            placeholder="Search users..."
                                                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                            @keydown.escape="open = false; search = ''"
                                                        />
                                                    </div>
                                                    <div class="max-h-48 overflow-auto py-1">
                                                        <button 
                                                            type="button"
                                                            wire:click="$set('userId', null)"
                                                            @click="open = false; search = ''"
                                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-500 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                        >
                                                            <span>No linked user</span>
                                                        </button>
                                                        @foreach($users as $user)
                                                            <button 
                                                                type="button"
                                                                x-show="'{{ strtolower($user->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($user->email) }}'.includes(search.toLowerCase()) || search === ''"
                                                                wire:click="$set('userId', {{ $user->id }})"
                                                                @click="open = false; search = ''"
                                                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $userId == $user->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                                                            >
                                                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                                </div>
                                                                <div>
                                                                    <p class="text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                                                                </div>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Add New User Button --}}
                                            <button type="button" wire:click="openCreateUserModal" class="inline-flex h-[38px] flex-shrink-0 items-center gap-1.5 rounded-lg bg-zinc-900 px-3 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                                <flux:icon name="plus" class="size-4" />
                                                <span>New</span>
                                            </button>
                                        </div>
                                        <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">Linking allows the employee to access the system with their user credentials.</p>
                                    </div>
                                </div>

                                {{-- HR Responsible --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">HR Responsible</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">HR person responsible for this employee.</p>
                                    </div>
                                    <div class="flex-1">
                                        {{-- Searchable HR Responsible Dropdown --}}
                                        <div class="relative" x-data="{ open: false, search: '' }">
                                            <button 
                                                type="button"
                                                @click="open = !open; $nextTick(() => { if(open) $refs.hrSearch.focus() })"
                                                class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-2 text-left text-sm transition-colors hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
                                            >
                                                @php $selectedHr = $employees->firstWhere('id', $hrResponsibleId); @endphp
                                                @if($selectedHr)
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                            {{ $selectedHr->initials }}
                                                        </div>
                                                        <span class="text-zinc-900 dark:text-zinc-100">{{ $selectedHr->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-zinc-400">No HR responsible assigned</span>
                                                @endif
                                                <flux:icon name="chevron-down" class="size-4 text-zinc-400" />
                                            </button>
                                            <div 
                                                x-show="open" 
                                                @click.outside="open = false; search = ''"
                                                x-transition
                                                class="absolute left-0 top-full z-[100] mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
                                            >
                                                <div class="border-b border-zinc-100 p-2 dark:border-zinc-800">
                                                    <input 
                                                        type="text"
                                                        x-ref="hrSearch"
                                                        x-model="search"
                                                        placeholder="Search employees..."
                                                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                        @keydown.escape="open = false; search = ''"
                                                    />
                                                </div>
                                                <div class="max-h-48 overflow-auto py-1">
                                                    <button 
                                                        type="button"
                                                        wire:click="$set('hrResponsibleId', null)"
                                                        @click="open = false; search = ''"
                                                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-zinc-500 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                    >
                                                        <span>No HR responsible assigned</span>
                                                    </button>
                                                    @foreach($employees as $emp)
                                                        <button 
                                                            type="button"
                                                            x-show="'{{ strtolower($emp->name) }}'.includes(search.toLowerCase()) || search === ''"
                                                            wire:click="$set('hrResponsibleId', {{ $emp->id }})"
                                                            @click="open = false; search = ''"
                                                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $hrResponsibleId == $emp->id ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                                                        >
                                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                                                {{ $emp->initials }}
                                                            </div>
                                                            <span class="text-zinc-900 dark:text-zinc-100">{{ $emp->name }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- PIN Code --}}
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                                    <div class="lg:w-72">
                                        <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">PIN Code</h3>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">PIN for attendance system integration.</p>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" wire:model="pinCode" maxlength="10" placeholder="Enter PIN code" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                        <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">Used for clock-in/clock-out on attendance devices.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity Timeline --}}
            <div class="lg:col-span-3">
                {{-- Chatter Forms --}}
                <x-ui.chatter-forms :showMessage="false" :showActivity="false" />

                {{-- Activity Timeline --}}
                @if($employeeId)
                    {{-- Date Separator --}}
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            @if($activities->isNotEmpty() && $activities->first()['created_at']->isToday())
                                Today
                            @else
                                Activity
                            @endif
                        </span>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    </div>

                    {{-- Activity Items --}}
                    <div class="space-y-3">
                        @forelse($activities as $item)
                            @if($item['type'] === 'note')
                                {{-- Note Item - Compact --}}
                                <x-ui.note-item :note="$item['data']" />
                            @else
                                {{-- Activity Log Item --}}
                                <x-ui.activity-item :activity="$item['data']" emptyMessage="Employee record created" />
                            @endif
                        @empty
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <x-ui.user-avatar :user="auth()->user()" size="md" :showPopup="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-ui.user-name :user="auth()->user()" />
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $createdAt ?? now()->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Employee record created</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State for New Employee --}}
                    <div class="py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <flux:icon name="chat-bubble-left-right" class="size-6 text-zinc-400" />
                        </div>
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No activity yet</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Activity will appear here once you save</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create User Modal --}}
    @if($showCreateUserModal)
    <div class="fixed inset-0 z-[200] flex items-center justify-center overflow-y-auto bg-black/50 p-4" x-data x-on:keydown.escape.window="$wire.set('showCreateUserModal', false)">
        <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900" @click.outside="$wire.set('showCreateUserModal', false)">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Create New User</h3>
                <button type="button" wire:click="$set('showCreateUserModal', false)" class="rounded-lg p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="space-y-4 p-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
                    <input type="text" wire:model="newUserName" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Full name">
                    @error('newUserName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                    <input type="email" wire:model="newUserEmail" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="email@example.com">
                    @error('newUserEmail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password</label>
                    <input type="password" wire:model="newUserPassword" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" placeholder="Minimum 8 characters">
                    @error('newUserPassword') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <button type="button" wire:click="$set('showCreateUserModal', false)" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    Cancel
                </button>
                <button type="button" wire:click="createUser" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                    Create & Link
                </button>
            </div>
        </div>
    </div>
    @endif
</div>