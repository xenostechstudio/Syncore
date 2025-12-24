@php
    $companyProfile = \App\Models\Settings\CompanyProfile::getProfile();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-zinc-50">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
        <div class="flex min-h-screen flex-col">
            <header class="border-b border-zinc-200 bg-white/90 backdrop-blur-sm">
                <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-3">
                        <x-app-logo-icon class="h-8 w-8 text-zinc-900" />
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">{{ $companyProfile->company_name ?? config('app.name', 'Syncore') }}</p>
                            @if(! empty($companyProfile->company_website))
                                <p class="text-xs text-zinc-500">{{ $companyProfile->company_website }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right text-xs text-zinc-500">
                        <p>{{ $companyProfile->company_email ?? 'support@example.com' }}</p>
                        @if(! empty($companyProfile->company_phone))
                            <p>{{ $companyProfile->company_phone }}</p>
                        @endif
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl flex-1 px-6 py-10">
                {{ $slot }}
            </main>

            <footer class="border-t border-zinc-200 bg-white/80">
                <div class="mx-auto w-full max-w-7xl px-6 py-6 text-center text-xs text-zinc-500">
                    © {{ now()->year }} {{ $companyProfile->company_name ?? config('app.name', 'Syncore') }}. All rights reserved.
                </div>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
