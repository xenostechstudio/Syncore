<div>
    <x-slot:header>
        <div class="flex items-center gap-2">
            <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Localization</h1>
            <span class="hidden text-xs text-zinc-500 dark:text-zinc-400 sm:inline">Also editable on Company Profile → Localization</span>
        </div>
        <button
            type="button"
            x-data="{ saving: false }"
            x-on:click="saving = true; Livewire.dispatch('saveLocalizationSettings')"
            x-on:localization-settings-saved.window="saving = false"
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
    </div>

    @php
        $preview = \Carbon\Carbon::now()->setTimezone($timezone ?: 'UTC');
    @endphp

    <div>
        {{-- Regional --}}
        <x-ui.section-bar title="Regional" :first="true" />
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Timezone and language applied across the platform.</p>
        <div class="mb-8 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Timezone</label>
                <x-ui.select wire:model="timezone">
                    @foreach($timezones as $tz)
                        <option value="{{ $tz }}">{{ $tz }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Language</label>
                <x-ui.select wire:model="language">
                    @foreach($languages as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        {{-- Currency --}}
        <x-ui.section-bar title="Currency" />
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Currency code and display symbol — used on every invoice, sales order, and report.</p>
        <div class="mb-8 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Default currency</label>
                <x-ui.select wire:model="currency">
                    @foreach($currencies as $code => $info)
                        <option value="{{ $code }}">{{ $info['symbol'] }} — {{ $info['name'] }} ({{ $code }})</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Symbol</label>
                <input type="text" wire:model="currency_symbol" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-zinc-500 dark:focus:ring-zinc-500" />
            </div>
        </div>

        {{-- Date & Time --}}
        <x-ui.section-bar title="Date & Time" />
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Used in documents, emails, and analytics.</p>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Date format</label>
                <x-ui.select wire:model="date_format">
                    @foreach($dateFormats as $format => $example)
                        <option value="{{ $format }}">{{ $example }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Time format</label>
                <x-ui.select wire:model="time_format">
                    <option value="H:i">24-hour (14:30)</option>
                    <option value="h:i A">12-hour (02:30 PM)</option>
                </x-ui.select>
            </div>
            <div class="md:col-span-2 rounded-lg border border-dashed border-zinc-200 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                <span class="font-medium text-zinc-700 dark:text-zinc-200">Preview:</span>
                <span class="ml-2 text-zinc-900 dark:text-zinc-100">{{ $preview->format($date_format) }} · {{ $preview->format($time_format) }} · {{ $currency_symbol }} 125,000.00</span>
            </div>
        </div>
    </div>
</div>
