<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Company Profile</h1>
        <flux:button wire:click="save" variant="primary">
            Save Changes
        </flux:button>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left: Company Logo --}}
        <div class="lg:col-span-4">
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Company Logo</h2>
                </div>
                <div class="p-5">
                    <div class="flex flex-col items-center">
                        <div class="flex h-24 w-24 items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                            <svg class="size-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <button class="mt-4 rounded-lg border border-zinc-200 px-4 py-2 text-sm font-light text-zinc-600 transition-colors hover:border-zinc-300 hover:text-zinc-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-100">
                            Upload Logo
                        </button>
                        <p class="mt-2 text-xs font-light text-zinc-400 dark:text-zinc-500">PNG, JPG up to 2MB</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Company Details --}}
        <div class="space-y-6 lg:col-span-8">
            {{-- Basic Info --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Basic Information</h2>
                </div>
                <div class="grid gap-5 p-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Company Name <span class="text-red-500">*</span></label>
                        <input 
                            type="text"
                            wire:model="company_name"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('company_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Email</label>
                        <input 
                            type="email"
                            wire:model="company_email"
                            placeholder="company@example.com"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Phone</label>
                        <input 
                            type="text"
                            wire:model="company_phone"
                            placeholder="+1 234 567 890"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Website</label>
                        <input 
                            type="url"
                            wire:model="company_website"
                            placeholder="https://example.com"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Address</h2>
                </div>
                <div class="grid gap-5 p-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Street Address</label>
                        <textarea 
                            wire:model="company_address"
                            rows="2"
                            placeholder="123 Business Street"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        ></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">City</label>
                        <input 
                            type="text"
                            wire:model="company_city"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Country</label>
                        <input 
                            type="text"
                            wire:model="company_country"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                    </div>
                </div>
            </div>

            {{-- Tax Info --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Tax Information</h2>
                </div>
                <div class="p-5">
                    <div>
                        <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Tax ID / VAT Number</label>
                        <input 
                            type="text"
                            wire:model="tax_id"
                            placeholder="XX-XXXXXXX"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 sm:max-w-xs"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
