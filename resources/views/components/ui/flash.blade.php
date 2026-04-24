@props([
    'successDuration' => 5000,
    'errorDuration' => 7000,
])

@if (session('success') || session('error'))
    <div class="fixed right-4 top-20 z-[300] w-96 space-y-2">
        @if (session('success'))
            <x-ui.alert type="success" :duration="$successDuration">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error" :duration="$errorDuration">
                {{ session('error') }}
            </x-ui.alert>
        @endif
    </div>
@endif
