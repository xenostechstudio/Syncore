<x-layouts.auth>
    <div class="syncore-anim flex flex-col gap-7">

        <x-auth-header
            :title="__('Welcome back.')"
            :description="__('Pick up where you left off — your operations are ready.')"
        />

        {{-- Session status (e.g. "we sent you an email") --}}
        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            {{-- Email --}}
            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@company.com"
            />

            {{-- Password + forgot link --}}
            <div class="flex flex-col gap-1.5">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate
                       class="self-end font-mono text-[11px] uppercase tracking-[0.18em] text-zinc-500 transition hover:text-zinc-300">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            {{-- Remember me --}}
            <flux:checkbox name="remember" :label="__('Keep me signed in')" :checked="old('remember')" />

            {{-- Submit --}}
            <button type="submit" data-test="login-button"
                    class="group relative mt-1 flex w-full items-center justify-center gap-2 rounded-md bg-zinc-100 px-4 py-2.5 text-sm font-medium text-zinc-950 shadow-[0_1px_0_rgba(255,255,255,0.04)_inset] transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-300 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-900">
                <span>{{ __('Sign in') }}</span>
                <svg class="size-4 transition-transform duration-200 group-hover:translate-x-0.5"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 5l7 7-7 7"/>
                </svg>
            </button>
        </form>

        @if (Route::has('register'))
            <div class="flex items-center gap-3 pt-2">
                <span class="h-px flex-1 bg-zinc-800"></span>
                <span class="font-mono text-[10px] uppercase tracking-[0.28em] text-zinc-600">or</span>
                <span class="h-px flex-1 bg-zinc-800"></span>
            </div>

            <p class="text-center text-sm text-zinc-500">
                {{ __("New to Syncore?") }}
                <a href="{{ route('register') }}" wire:navigate
                   class="font-medium text-zinc-100 underline-offset-4 transition hover:underline">
                    {{ __('Request an account') }}
                </a>
            </p>
        @endif
    </div>
</x-layouts.auth>
