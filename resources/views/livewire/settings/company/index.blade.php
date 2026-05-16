<div x-data="{ activeTab: 'identity' }">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Company Profile</h1>
        </div>
        <button
            type="button"
            x-data="{ saving: false }"
            x-on:click="saving = true; Livewire.dispatch('saveCompanyProfile')"
            x-on:company-profile-saved.window="saving = false"
            x-bind:disabled="saving"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
            <svg x-show="saving" class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-show="!saving">Save</span>
            <span x-show="saving">Saving…</span>
        </button>
    </x-slot:header>

    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">{{ session('error') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error" :duration="10000">
                <span class="font-medium">Please fix the following errors:</span>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>

    {{-- Card container --}}
    <div class="overflow-visible rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="p-5">
            {{-- Head: logo LEFT, label + inline name on the right --}}
            <div class="mb-5 flex items-start gap-4">
                <label class="group relative flex h-20 w-20 cursor-pointer items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                    @if($logo)
                        <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview" class="h-full w-full rounded-lg object-contain p-1.5" />
                    @elseif($logo_path)
                        <img src="{{ Storage::url($logo_path) }}" alt="Company logo" class="h-full w-full rounded-lg object-contain p-1.5" />
                    @else
                        <flux:icon name="photo" class="size-8 text-zinc-300 dark:text-zinc-600" />
                    @endif
                    <input type="file" wire:model="logo" accept="image/*" class="hidden" />
                    <span class="absolute inset-0 hidden items-center justify-center rounded-lg bg-zinc-900/60 text-xs font-medium text-white group-hover:flex">
                        <flux:icon name="arrow-up-tray" class="size-4" />
                    </span>
                    @if($logo_path && !$logo)
                        <button
                            type="button"
                            wire:click.stop="removeLogo"
                            @click.stop
                            class="absolute -right-2 -top-2 hidden h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white shadow-sm hover:bg-red-600 group-hover:flex"
                            title="Remove logo"
                        >
                            <flux:icon name="x-mark" class="size-3" />
                        </button>
                    @endif
                </label>

                <div class="flex-1">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Company</p>
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="Company name..."
                        class="mt-2 w-full rounded-md border border-transparent bg-transparent px-2 py-1 text-2xl font-medium text-zinc-900 placeholder-zinc-400 hover:border-zinc-300 focus:border-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder-zinc-500 dark:hover:border-zinc-600 dark:focus:border-zinc-500"
                    />
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    @if($legal_name)
                        <p class="mt-1 px-2 text-xs text-zinc-500 dark:text-zinc-400">Legal name: {{ $legal_name }}</p>
                    @endif
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mb-4 border-b border-zinc-200 dark:border-zinc-800">
                <nav class="-mb-px flex space-x-4 text-sm">
                    <button
                        type="button"
                        @click="activeTab = 'identity'"
                        class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                        :class="activeTab === 'identity'
                            ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    >
                        Identity
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'address'"
                        class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                        :class="activeTab === 'address'
                            ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    >
                        Address
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'brand'"
                        class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                        :class="activeTab === 'brand'
                            ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    >
                        Brand
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'localization'"
                        class="whitespace-nowrap border-b-2 px-3 pb-2 pt-1"
                        :class="activeTab === 'localization'
                            ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    >
                        Localization
                    </button>
                </nav>
            </div>

            {{-- Identity --}}
            <div x-show="activeTab === 'identity'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="space-y-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Legal name</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Registered name on contracts and tax documents.</p>
                        </div>
                        <div class="flex-1">
                            <input type="text" wire:model="legal_name" placeholder="PT Syncore Indonesia" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            @error('legal_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Email</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Shown on invoices and customer share pages.</p>
                        </div>
                        <div class="flex-1">
                            <input type="email" wire:model="email" placeholder="hello@company.com" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Phone</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Include the country code.</p>
                        </div>
                        <div class="flex-1">
                            <input type="text" wire:model="phone" placeholder="+62 812 345 678" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Website</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Linked from PDF headers. Include https://.</p>
                        </div>
                        <div class="flex-1">
                            <input type="url" wire:model="website" placeholder="https://syncore.id" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            @error('website') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Tax ID</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">NPWP or VAT. Printed on every invoice.</p>
                        </div>
                        <div class="flex-1">
                            <input type="text" wire:model="tax_id" placeholder="XX-XXXXXXX" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            @error('tax_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div x-show="activeTab === 'address'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="space-y-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Street</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Return address on PDFs.</p>
                        </div>
                        <div class="flex-1">
                            <textarea wire:model="address" rows="2" placeholder="Jalan ABC No. 12, Kel. XYZ" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                            @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">City, state &amp; postal</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Appears in the address block on documents.</p>
                        </div>
                        <div class="grid flex-1 gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">City</label>
                                <input type="text" wire:model="city" placeholder="Jakarta" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                @error('city') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">State</label>
                                <input type="text" wire:model="state" placeholder="DKI Jakarta" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                @error('state') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Postal</label>
                                <input type="text" wire:model="postal_code" placeholder="12190" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                @error('postal_code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Country</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Drives currency and tax defaults.</p>
                        </div>
                        <div class="flex-1">
                            <input type="text" wire:model="country" placeholder="Indonesia" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                            @error('country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Brand --}}
            <div x-show="activeTab === 'brand'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="space-y-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Logo</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Shown on invoices and PDFs. PNG/SVG, transparent background. Max 2 MB.</p>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                                @if($logo)
                                    <div class="h-24 w-24 flex-shrink-0 rounded-lg border border-dashed border-zinc-300 bg-white/70 dark:border-zinc-700 dark:bg-zinc-900/70">
                                        <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview" class="h-full w-full object-contain p-2" />
                                    </div>
                                @elseif($logo_path)
                                    <div class="h-24 w-24 flex-shrink-0 rounded-lg border border-dashed border-zinc-300 bg-white/70 dark:border-zinc-700 dark:bg-zinc-900/70">
                                        <img src="{{ Storage::url($logo_path) }}" alt="Company logo" class="h-full w-full object-contain p-2" />
                                    </div>
                                @else
                                    <div class="flex h-24 w-24 flex-shrink-0 items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-white/70 dark:border-zinc-700 dark:bg-zinc-900/70">
                                        <flux:icon name="photo" class="size-8 text-zinc-400" />
                                    </div>
                                @endif
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                        <flux:icon name="arrow-up-tray" class="size-3.5" />
                                        <span>{{ $logo_path ? 'Replace logo' : 'Upload logo' }}</span>
                                        <input type="file" wire:model="logo" accept="image/*" class="hidden" />
                                    </label>
                                    @if($logo_path)
                                        <button type="button" wire:click="removeLogo" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-900/30 dark:bg-zinc-800 dark:text-red-400 dark:hover:bg-red-900/20">
                                            <flux:icon name="trash" class="size-3.5" />
                                            <span>Remove</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @error('logo') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Localization --}}
            <div x-show="activeTab === 'localization'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @php
                    $preview = \Carbon\Carbon::now()->setTimezone($timezone ?: 'UTC');
                @endphp
                <div class="space-y-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Currency</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Code and symbol used across the platform.</p>
                        </div>
                        <div class="grid flex-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Code</label>
                                <x-ui.select wire:model="currency">
                                    <option value="IDR">IDR — Indonesian Rupiah</option>
                                    <option value="USD">USD — US Dollar</option>
                                    <option value="EUR">EUR — Euro</option>
                                    <option value="GBP">GBP — British Pound</option>
                                    <option value="SGD">SGD — Singapore Dollar</option>
                                    <option value="MYR">MYR — Malaysian Ringgit</option>
                                    <option value="JPY">JPY — Japanese Yen</option>
                                    <option value="CNY">CNY — Chinese Yuan</option>
                                </x-ui.select>
                                @error('currency') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Symbol</label>
                                <input type="text" wire:model="currency_symbol" placeholder="Rp" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                @error('currency_symbol') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Timezone</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Used for due dates and timestamps.</p>
                        </div>
                        <div class="flex-1">
                            <x-ui.select wire:model="timezone">
                                @foreach(\DateTimeZone::listIdentifiers() as $tz)
                                    <option value="{{ $tz }}">{{ $tz }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('timezone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Language</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Default UI language. Users can override per-account.</p>
                        </div>
                        <div class="flex-1">
                            <x-ui.select wire:model="language">
                                <option value="en">English</option>
                                <option value="id">Bahasa Indonesia</option>
                            </x-ui.select>
                            @error('language') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Date &amp; time</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">How dates and times appear in the UI and PDFs.</p>
                        </div>
                        <div class="grid flex-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Date</label>
                                <x-ui.select wire:model="date_format">
                                    <option value="Y-m-d">YYYY-MM-DD (2024-12-07)</option>
                                    <option value="d/m/Y">DD/MM/YYYY (07/12/2024)</option>
                                    <option value="m/d/Y">MM/DD/YYYY (12/07/2024)</option>
                                    <option value="d-m-Y">DD-MM-YYYY (07-12-2024)</option>
                                    <option value="d M Y">DD Mon YYYY (07 Dec 2024)</option>
                                </x-ui.select>
                                @error('date_format') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Time</label>
                                <x-ui.select wire:model="time_format">
                                    <option value="H:i">24-hour (14:30)</option>
                                    <option value="h:i A">12-hour (02:30 PM)</option>
                                </x-ui.select>
                                @error('time_format') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:gap-6">
                        <div class="lg:w-72">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Preview</h4>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Live preview of your choices.</p>
                        </div>
                        <div class="flex-1">
                            <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                                <span class="text-zinc-900 dark:text-zinc-100">{{ $preview->format($date_format ?: 'Y-m-d') }} · {{ $preview->format($time_format ?: 'H:i') }} · {{ $currency_symbol }} 125,000.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
