<div>
    <x-slot:header>
        <div class="flex items-center gap-3">
            <button 
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
            <h1 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Email Configuration</h1>
        </div>
        <div class="flex items-center gap-4">
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
    </x-slot:header>

    <div class="space-y-6">
        @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
        @endif

        {{-- Enable Database Configuration --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Use Database Configuration</h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        When enabled, email settings from this page will override environment variables.
                    </p>
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
        </div>

        {{-- SMTP Configuration --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">SMTP Settings</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    Configure your mail server connection details.
                </p>
            </div>
            <div class="grid gap-5 px-6 py-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Mail Driver</label>
                    <select 
                        wire:model="mailer"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        <option value="smtp">SMTP</option>
                        <option value="sendmail">Sendmail</option>
                        <option value="mailgun">Mailgun</option>
                        <option value="ses">Amazon SES</option>
                        <option value="postmark">Postmark</option>
                        <option value="log">Log (Testing)</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">SMTP Host</label>
                    <input 
                        type="text"
                        wire:model="host"
                        placeholder="smtp.example.com"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('host') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">SMTP Port</label>
                    <input 
                        type="number"
                        wire:model="port"
                        placeholder="587"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('port') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Encryption</label>
                    <select 
                        wire:model="encryption"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="">None</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Username</label>
                    <input 
                        type="text"
                        wire:model="username"
                        placeholder="your-username"
                        autocomplete="off"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('username') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Password</label>
                    <input 
                        type="password"
                        wire:model="password"
                        placeholder="••••••••"
                        autocomplete="new-password"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Leave empty to keep existing password</p>
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Sender Information --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Sender Information</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    Default "From" address and name for outgoing emails.
                </p>
            </div>
            <div class="grid gap-5 px-6 py-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">From Address</label>
                    <input 
                        type="email"
                        wire:model="fromAddress"
                        placeholder="noreply@example.com"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('fromAddress') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">From Name</label>
                    <input 
                        type="text"
                        wire:model="fromName"
                        placeholder="Your Company Name"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    @error('fromName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Test Email --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Test Configuration</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    Send a test email to verify your configuration is working.
                </p>
            </div>
            <div class="px-6 py-5">
                <div class="flex gap-3">
                    <input 
                        type="email"
                        wire:model="testEmail"
                        placeholder="test@example.com"
                        class="flex-1 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 transition focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    <button 
                        type="button"
                        wire:click="sendTestEmail"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        <flux:icon name="paper-airplane" class="size-4" />
                        <span wire:loading.remove wire:target="sendTestEmail">Send Test</span>
                        <span wire:loading wire:target="sendTestEmail">Sending...</span>
                    </button>
                </div>
                @error('testEmail') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Current Effective Configuration --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Active Configuration</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    The configuration currently being used for sending emails.
                </p>
            </div>
            <div class="px-6 py-5">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Source</p>
                        <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $isActive ? 'Database' : 'Environment' }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">SMTP Host</p>
                        <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $effectiveHost ?: 'Not configured' }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">From Address</p>
                        <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $effectiveFromAddress ?: 'Not configured' }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Driver</p>
                        <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ config('mail.default', 'smtp') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Environment Variables Reference --}}
        <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900/50">
            <h3 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Environment Variables (Fallback)</h3>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                When database configuration is disabled, these environment variables are used:
            </p>
            <pre class="mt-3 overflow-x-auto rounded-lg bg-zinc-900 p-4 text-xs text-zinc-100 dark:bg-zinc-950">MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"</pre>
        </div>
    </div>
</div>
