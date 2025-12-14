<div>
    {{-- Flash Messages --}}
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if(session('success'))
            <x-ui.alert type="success" :duration="5000">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert type="error" :duration="7000">
                {{ session('error') }}
            </x-ui.alert>
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

    {{-- Header & Actions --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            {{-- Left group (button, title, gear) --}}
            <div class="flex items-center gap-3">
                <button 
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Company Profile</span>
                
                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <button type="button" wire:click="removeLogo" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="photo" class="size-4" />
                            <span>Remove logo</span>
                        </button>
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>Export profile</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center group placeholder --}}
            <div class="flex flex-1 items-center justify-center">
                {{-- Reserved for filters/search if needed --}}
            </div>

            {{-- Right group: metadata --}}
            <div class="flex items-center gap-3 text-xs text-zinc-400 dark:text-zinc-500">
                <flux:icon name="clock" class="size-4" />
                <span>Last updated {{ optional(\App\Models\Settings\CompanyProfile::first())->updated_at?->diffForHumans() ?? 'recently' }}</span>
            </div>
        </div>
    </div>

    {{-- Overview --}}
    <div class="-mx-4 -mt-6 mb-8 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Registered Name</p>
                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">{{ $company_name ?: 'Syncore' }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Display name across all modules</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Primary Email</p>
                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">{{ $company_email ?: '—' }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Used for invoices & notifications</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Phone</p>
                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">{{ $company_phone ?: '—' }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Customer-facing contact</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Website</p>
                <p class="mt-2 text-base font-medium text-blue-600 dark:text-blue-400">{{ $company_website ?: '—' }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Shown on outgoing documents</p>
            </div>
        </div>
    </div>

    {{-- Layout --}}
    <div class="grid gap-6 lg:grid-cols-12">
        <div class="space-y-6 lg:col-span-4">
            {{-- Logo Card --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-b from-zinc-50 to-white dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Brand Identity</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Logo appears on invoices, emails, and pdf exports</p>
                </div>
                <div class="flex flex-col items-center gap-4 px-5 py-6">
                    @if($logo)
                        <div class="relative h-28 w-28 border border-dashed border-zinc-300 bg-white/70 dark:border-zinc-700 dark:bg-zinc-900/70">
                            <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview" class="h-full w-full object-contain p-3" />
                        </div>
                    @elseif($logo_path)
                        <div class="relative h-28 w-28 border border-dashed border-zinc-300 bg-white/70 dark:border-zinc-700 dark:bg-zinc-900/70">
                            <img src="{{ Storage::url($logo_path) }}" alt="Company logo" class="h-full w-full object-contain p-3" />
                            <button 
                                type="button"
                                wire:click="removeLogo"
                                class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-sm hover:bg-red-600"
                            >
                                <flux:icon name="x-mark" class="size-4" />
                            </button>
                        </div>
                    @else
                        <div class="flex h-28 w-28 items-center justify-center border border-dashed border-zinc-300 bg-white/70 dark:border-zinc-700 dark:bg-zinc-900/70">
                            <flux:icon name="photo" class="size-8 text-zinc-400" />
                        </div>
                    @endif

                    <div class="flex flex-col items-center gap-2">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                            <flux:icon name="arrow-up-tray" class="size-4" />
                            <span>{{ $logo_path ? 'Replace Logo' : 'Upload Logo' }}</span>
                            <input type="file" wire:model="logo" accept="image/*" class="hidden" />
                        </label>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">Recommended: PNG / SVG, 240 × 240px</p>
                        @error('logo') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Snapshot --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Company Snapshot</p>
                    <flux:icon name="ellipsis-horizontal" class="size-5 text-zinc-400" />
                </div>
                <dl class="mt-4 space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Address</dt>
                        <dd class="mt-1">
                            {{ $company_address ?: 'No address provided' }}<br>
                            {{ $company_city }} {{ $company_country }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Tax ID</dt>
                        <dd class="mt-1">{{ $tax_id ?: 'Not configured' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Forms --}}
        <div class="space-y-6 lg:col-span-8">
            {{-- Identity --}}
            <section class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Company Identity</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Core identifiers that appear across modules</p>
                    </div>
                </div>
                <div class="grid gap-5 px-6 py-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Registered Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="company_name" placeholder="Your legal business name" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500">
                        @error('company_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Primary Email</label>
                        <input type="email" wire:model="company_email" placeholder="company@example.com" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500">
                        @error('company_email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Phone</label>
                        <input type="text" wire:model="company_phone" placeholder="+62 812 345 678" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500">
                        @error('company_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Website</label>
                        <input type="url" wire:model="company_website" placeholder="https://syncore.id" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500">
                        @error('company_website') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Address --}}
            <section class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Headquarters</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Physical location for operations and documentation</p>
                    </div>
                </div>
                <div class="grid gap-5 px-6 py-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Street Address</label>
                        <textarea 
                            wire:model="company_address"
                            rows="2"
                            placeholder="Street, district, building"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                        ></textarea>
                        @error('company_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">City</label>
                        <input type="text" wire:model="company_city" placeholder="Jakarta" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500">
                        @error('company_city') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Country</label>
                        <input type="text" wire:model="company_country" placeholder="Indonesia" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500">
                        @error('company_country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Compliance --}}
            <section class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Compliance</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Identifiers for invoices, tax records, and contracts</p>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Tax ID / VAT Number</label>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input 
                            type="text"
                            wire:model="tax_id"
                            placeholder="XX-XXXXXXX"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500 sm:flex-1"
                        >
                        <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-zinc-600">
                            <flux:icon name="document-magnifying-glass" class="size-4" />
                            Validate
                        </button>
                    </div>
                    @error('tax_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </section>
        </div>
    </div>
</div>
