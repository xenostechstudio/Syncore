<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-normal text-zinc-900 dark:text-zinc-100">Localization</h1>
        <flux:button wire:click="save" variant="primary">
            Save Changes
        </flux:button>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Timezone & Language --}}
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Regional Settings</h2>
            </div>
            <div class="space-y-5 p-5">
                {{-- Timezone --}}
                <div>
                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Timezone</label>
                    <select 
                        wire:model="timezone"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        @foreach($timezones as $tz)
                            <option value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Language --}}
                <div>
                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Language</label>
                    <select 
                        wire:model="language"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        @foreach($languages as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Currency --}}
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Currency</h2>
            </div>
            <div class="space-y-5 p-5">
                {{-- Currency --}}
                <div>
                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Default Currency</label>
                    <select 
                        wire:model="currency"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        @foreach($currencies as $code => $info)
                            <option value="{{ $code }}">{{ $info['symbol'] }} - {{ $info['name'] }} ({{ $code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Currency Symbol --}}
                <div>
                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Currency Symbol</label>
                    <input 
                        type="text"
                        wire:model="currency_symbol"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                </div>
            </div>
        </div>

        {{-- Date & Time Format --}}
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-2">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h2 class="text-sm font-normal text-zinc-900 dark:text-zinc-100">Date & Time Format</h2>
            </div>
            <div class="grid gap-5 p-5 sm:grid-cols-2">
                {{-- Date Format --}}
                <div>
                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Date Format</label>
                    <select 
                        wire:model="date_format"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        @foreach($dateFormats as $format => $example)
                            <option value="{{ $format }}">{{ $example }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Time Format --}}
                <div>
                    <label class="mb-2 block text-sm font-light text-zinc-600 dark:text-zinc-400">Time Format</label>
                    <select 
                        wire:model="time_format"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition-colors focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        <option value="H:i">24-hour (14:30)</option>
                        <option value="h:i A">12-hour (02:30 PM)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
