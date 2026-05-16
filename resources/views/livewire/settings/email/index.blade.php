<div>
    <x-slot:header>
        <div class="flex items-center gap-3">
            <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Email Configuration</h1>
            @if($isActive)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Database Config Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                    Using Environment
                </span>
            @endif
        </div>
        <button
            type="button"
            x-data="{ saving: false }"
            x-on:click="saving = true; Livewire.dispatch('saveEmailSettings')"
            x-on:email-settings-saved.window="saving = false"
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

    <div>
        {{-- Database vs Environment toggle --}}
        <x-ui.section-bar title="Configuration Source" :first="true" />
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
            When the toggle is on, the values on this page override the <code class="rounded bg-zinc-100 px-1 text-xs dark:bg-zinc-800">MAIL_*</code> environment variables. With it off, the env values are used and the form below is just a draft you can save without applying.
        </p>
        <div class="mb-8 flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div>
                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Use database configuration</p>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Apply the SMTP settings below at runtime instead of reading from .env.</p>
            </div>
            <button
                type="button"
                wire:click="toggleActive"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isActive ? 'bg-emerald-500' : 'bg-zinc-200 dark:bg-zinc-700' }}"
                role="switch"
                aria-checked="{{ $isActive ? 'true' : 'false' }}"
            >
                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isActive ? 'translate-x-5' : 'translate-x-0' }}"></span>
            </button>
        </div>

        {{-- SMTP --}}
        <x-ui.section-bar title="SMTP Connection" />
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Mail server connection details. Leave password empty to keep the stored value.</p>
        <div class="mb-8 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Mail driver</label>
                <x-ui.select wire:model="mailer">
                    <option value="smtp">SMTP</option>
                    <option value="sendmail">Sendmail</option>
                    <option value="mailgun">Mailgun</option>
                    <option value="ses">Amazon SES</option>
                    <option value="postmark">Postmark</option>
                    <option value="log">Log (Testing)</option>
                </x-ui.select>
                @error('mailer') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">SMTP host</label>
                <input type="text" wire:model="host" placeholder="smtp.example.com" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                @error('host') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">SMTP port</label>
                <input type="number" wire:model="port" placeholder="587" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                @error('port') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Encryption</label>
                <x-ui.select wire:model="encryption">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="">None</option>
                </x-ui.select>
                @error('encryption') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Username</label>
                <input type="text" wire:model="username" placeholder="your-username" autocomplete="off" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                @error('username') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Password</label>
                <input type="password" wire:model="password" placeholder="••••••••" autocomplete="new-password" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Leave empty to keep existing password</p>
                @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Sender --}}
        <x-ui.section-bar title="Sender Defaults" />
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">"From" address and display name on outgoing email.</p>
        <div class="mb-8 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">From address</label>
                <input type="email" wire:model="fromAddress" placeholder="noreply@example.com" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                @error('fromAddress') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">From name</label>
                <input type="text" wire:model="fromName" placeholder="Your Company Name" class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                @error('fromName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Test --}}
        <x-ui.section-bar title="Send Test Email" />
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Verify the current effective configuration by sending yourself a real message.</p>
        <div class="mb-8">
            <div class="flex gap-3">
                <input type="email" wire:model="testEmail" placeholder="test@example.com" class="flex-1 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                <button type="button" wire:click="sendTestEmail" wire:loading.attr="disabled" wire:target="sendTestEmail" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                    <flux:icon name="paper-airplane" class="size-4" />
                    <span wire:loading.remove wire:target="sendTestEmail">Send Test</span>
                    <span wire:loading wire:target="sendTestEmail">Sending…</span>
                </button>
            </div>
            @error('testEmail') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Effective configuration --}}
        <x-ui.section-bar title="Active Configuration" />
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">What Laravel is actually using right now — useful when the database/env toggle changes.</p>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Source</p>
                <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $isActive ? 'Database' : 'Environment' }}</p>
            </div>
            <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">SMTP Host</p>
                <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $effectiveHost ?: 'Not configured' }}</p>
            </div>
            <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">From Address</p>
                <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $effectiveFromAddress ?: 'Not configured' }}</p>
            </div>
            <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Driver</p>
                <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ config('mail.default', 'smtp') }}</p>
            </div>
        </div>
    </div>
</div>
