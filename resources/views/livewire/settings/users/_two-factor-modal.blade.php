<div
    x-show="showTwoFactorModal"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="absolute inset-0 bg-zinc-900/60" @click="showTwoFactorModal = false; $wire.cancelTwoFactorSetup()"></div>

    <div
        class="relative z-10 w-full max-w-md overflow-hidden rounded-xl bg-white shadow-xl dark:bg-zinc-900"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="p-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="shield-check" class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Set Up Two-Factor Authentication</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Secure your account with 2FA</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code to confirm.
                </p>

                {{-- QR Code --}}
                @if($this->getTwoFactorQrCodeUrl())
                    <div class="flex flex-col items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="rounded-lg bg-white p-3">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($this->getTwoFactorQrCodeUrl()) }}" alt="QR Code" class="h-[180px] w-[180px]" />
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Or enter this code manually:</p>
                            <p class="mt-1 select-all font-mono text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->getTwoFactorSecret() }}</p>
                        </div>
                    </div>
                @endif

                {{-- Verification Code Input --}}
                <div>
                    <label class="mb-1.5 block text-sm text-zinc-600 dark:text-zinc-400">Verification Code</label>
                    <input
                        type="text"
                        x-model="twoFactorCode"
                        placeholder="Enter 6-digit code"
                        maxlength="6"
                        class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-center font-mono text-lg tracking-widest text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <button
                type="button"
                @click="showTwoFactorModal = false; twoFactorCode = ''; $wire.cancelTwoFactorSetup()"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Cancel
            </button>

            <button
                type="button"
                @click="$wire.confirmTwoFactor(twoFactorCode).then(() => { if ($wire.twoFactorEnabled) { showTwoFactorModal = false; twoFactorCode = ''; } })"
                class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                Verify & Enable
            </button>
        </div>
    </div>
</div>
