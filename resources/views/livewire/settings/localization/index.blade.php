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
            {{-- Left group --}}
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
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Localization</span>

                <flux:dropdown position="bottom" align="start">
                    <button class="flex items-center justify-center rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon name="cog-6-tooth" class="size-5" />
                    </button>

                    <flux:menu class="w-48">
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="arrow-path" class="size-4" />
                            <span>Reset to defaults</span>
                        </button>
                        <button type="button" class="flex w-full items-center gap-2 px-2 py-1.5 text-sm text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800">
                            <flux:icon name="document-arrow-down" class="size-4" />
                            <span>Export config</span>
                        </button>
                    </flux:menu>
                </flux:dropdown>
            </div>

            {{-- Center placeholder --}}
            <div class="flex flex-1 items-center justify-center text-xs text-zinc-400 dark:text-zinc-500">
                {{-- Reserved for future filters --}}
            </div>

            {{-- Right group --}}
            <div class="flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:icon name="globe-alt" class="size-4" />
                <span>{{ strtoupper($language) }} · {{ $timezone }}</span>
            </div>
        </div>
    </div>

    {{-- Overview --}}
    <div class="-mx-4 -mt-6 mb-8 border-b border-zinc-200 bg-white px-4 py-4 sm:-mx-6 lg:-mx-8 lg:px-8 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Timezone</p>
                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">{{ $timezone }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Calendar & reminder schedule</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Language</p>
                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">{{ strtoupper($language) }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">UI & PDF translations</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Currency</p>
                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">{{ $currency_symbol }} • {{ $currency }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Documents & reports</p>
            </div>
            @php
                $preview = \Carbon\Carbon::now()->setTimezone($timezone);
            @endphp
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Preview</p>
                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">
                    {{ $preview->format($date_format) }} · {{ $preview->format($time_format) }}
                </p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">How dates & times render</p>
            </div>
        </div>
    </div>

    {{-- Layout --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left column --}}
        <div class="space-y-6 lg:col-span-4">
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-b from-zinc-50 to-white dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Regional presets</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Quickly align locale, currency, and format</p>
                </div>
                <div class="space-y-3 px-5 py-5">
                    <button type="button" class="flex w-full items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 text-left text-sm font-medium text-zinc-700 transition hover:border-zinc-300 hover:bg-white dark:border-zinc-700 dark:text-zinc-200 dark:hover:border-zinc-600">
                        <span>Indonesia</span>
                        <span class="text-xs text-zinc-400">Coming soon</span>
                    </button>
                    <button type="button" class="flex w-full items-center justify-between rounded-xl border border-dashed border-zinc-300 px-4 py-3 text-left text-sm font-medium text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                        Create preset
                        <flux:icon name="plus" class="size-4" />
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Formatting preview</p>
                    <flux:icon name="sparkles" class="size-4 text-zinc-400" />
                </div>
                <dl class="mt-4 space-y-3 text-sm text-zinc-600 dark:text-zinc-300">
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Invoice total</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $currency_symbol }} 125,000.00</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Date example</dt>
                        <dd class="mt-1">{{ $preview->format($date_format) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Time example</dt>
                        <dd class="mt-1">{{ $preview->format($time_format) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-6 lg:col-span-8">
            {{-- Regional settings --}}
            <section class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Regional settings</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Language and timezone applied across the platform</p>
                    </div>
                </div>
                <div class="grid gap-5 px-6 py-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Timezone</label>
                        <select 
                            wire:model="timezone"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                        >
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}">{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Language</label>
                        <select 
                            wire:model="language"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                        >
                            @foreach($languages as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            {{-- Currency --}}
            <section class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Currency & numbering</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Impact invoices, reports, and numeric parsing</p>
                    </div>
                </div>
                <div class="grid gap-5 px-6 py-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Default currency</label>
                        <select 
                            wire:model="currency"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                        >
                            @foreach($currencies as $code => $info)
                                <option value="{{ $code }}">{{ $info['symbol'] }} — {{ $info['name'] }} ({{ $code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Currency symbol</label>
                        <input 
                            type="text"
                            wire:model="currency_symbol"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                        />
                    </div>
                </div>
            </section>

            {{-- Date & Time --}}
            <section class="rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Date & time formats</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Used in documents, emails, and analytics</p>
                    </div>
                </div>
                <div class="grid gap-5 px-6 py-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Date format</label>
                        <select 
                            wire:model="date_format"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                        >
                            @foreach($dateFormats as $format => $example)
                                <option value="{{ $format }}">{{ $example }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Time format</label>
                        <select 
                            wire:model="time_format"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500"
                        >
                            <option value="H:i">24-hour (14:30)</option>
                            <option value="h:i A">12-hour (02:30 PM)</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 rounded-xl border border-dashed border-zinc-200 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                        <span class="font-medium text-zinc-700 dark:text-zinc-200">Live preview:</span>
                        <span class="ml-2 text-zinc-900 dark:text-zinc-100">{{ $preview->format($date_format) }} · {{ $preview->format($time_format) }}</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
