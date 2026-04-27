<div
    x-show="showChangePasswordModal"
    x-cloak
    x-data="{ showPassword: false, showConfirmPassword: false }"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="absolute inset-0 bg-zinc-900/60" @click="showChangePasswordModal = false"></div>

    <div
        class="relative z-10 w-full max-w-md overflow-hidden rounded-xl bg-white shadow-xl dark:bg-zinc-900"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="showChangePasswordModal = false"
    >
        <div class="px-5 pb-4 pt-5">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $userId ? 'Change Password' : 'Set Password' }}</h3>
                <button type="button" @click="showChangePasswordModal = false" class="rounded-md p-1 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <div>
                    <label class="mb-1 block text-sm text-zinc-600 dark:text-zinc-400">{{ $userId ? 'New Password' : 'Password' }}</label>
                    <div class="relative">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            wire:model="password"
                            placeholder="Minimum 8 characters"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-10 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                        >
                            <flux:icon x-show="!showPassword" name="eye" class="size-4" />
                            <flux:icon x-show="showPassword" name="eye-slash" class="size-4" x-cloak />
                        </button>
                    </div>
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm text-zinc-600 dark:text-zinc-400">Confirm Password</label>
                    <div class="relative">
                        <input
                            :type="showConfirmPassword ? 'text' : 'password'"
                            wire:model="password_confirmation"
                            placeholder="Confirm password"
                            class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 pr-10 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                        >
                            <flux:icon x-show="!showConfirmPassword" name="eye" class="size-4" />
                            <flux:icon x-show="showConfirmPassword" name="eye-slash" class="size-4" x-cloak />
                        </button>
                    </div>
                    @error('password_confirmation') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-zinc-100 bg-zinc-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
            <button
                type="button"
                @click="showChangePasswordModal = false; $wire.set('password', ''); $wire.set('password_confirmation', '')"
                class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            >
                Cancel
            </button>

            <button
                type="button"
                wire:click="save"
                @click="showChangePasswordModal = false"
                class="rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                {{ $userId ? 'Update Password' : 'Create User' }}
            </button>
        </div>
    </div>
</div>
