@props([
    'show' => false,
    'title' => 'Import Data',
    'action' => '',
    'templateUrl' => '',
    'livewire' => false,
    'result' => null,
    'importErrors' => [],
])

@if($livewire)
{{-- Livewire Version --}}
<div
    x-data="{ open: @entangle($attributes->wire('model')) }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] overflow-y-auto"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="open = false"></div>

    {{-- Modal --}}
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="open = false"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                        <flux:icon name="arrow-up-tray" class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
                </div>
                <button 
                    type="button" 
                    wire:click="closeImportModal"
                    class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            {{-- Content --}}
            <div class="px-6 py-4">
                <div class="space-y-4">
                    @if($result)
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
                            <div class="flex items-center gap-2">
                                <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400" />
                                <span class="text-sm text-emerald-700 dark:text-emerald-300">{{ $result }}</span>
                            </div>
                        </div>
                    @endif

                    @if(count($importErrors) > 0)
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                            <div class="flex items-start gap-2">
                                <flux:icon name="exclamation-circle" class="mt-0.5 size-4 text-red-600 dark:text-red-400" />
                                <div class="text-sm text-red-700 dark:text-red-300">
                                    <p class="font-medium">Import Errors:</p>
                                    <ul class="mt-1 list-inside list-disc text-xs max-h-32 overflow-y-auto">
                                        @foreach($importErrors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Select File
                        </label>
                        <input 
                            type="file" 
                            wire:model="importFile"
                            accept=".xlsx,.xls,.csv"
                            class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:text-zinc-400 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700"
                        >
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Supported formats: .xlsx, .xls, .csv (max 10MB)
                        </p>
                        @error('importFile') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="flex items-center gap-2">
                            <flux:icon name="document-arrow-down" class="size-4 text-zinc-500" />
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Need a template?</span>
                            <button type="button" wire:click="downloadTemplate" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                Download Template
                            </button>
                        </div>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                        <div class="flex items-start gap-2">
                            <flux:icon name="exclamation-triangle" class="mt-0.5 size-4 text-amber-600 dark:text-amber-400" />
                            <div class="text-sm text-amber-700 dark:text-amber-300">
                                <p class="font-medium">Important:</p>
                                <ul class="mt-1 list-inside list-disc text-xs">
                                    <li>First row must contain column headers</li>
                                    <li>Existing records will be updated if matched</li>
                                    <li>Empty rows will be skipped</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        wire:click="closeImportModal"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="import"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 disabled:opacity-50 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        <span wire:loading.remove wire:target="import">Import</span>
                        <span wire:loading wire:target="import">Importing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@else
{{-- Standard Form Version --}}
<div
    x-data="{ open: @entangle($attributes->wire('model')) }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] overflow-y-auto"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="open = false"></div>

    {{-- Modal --}}
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="open = false"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                        <flux:icon name="arrow-up-tray" class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
                </div>
                <button 
                    type="button" 
                    @click="open = false"
                    class="rounded-lg p-2 text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            {{-- Content --}}
            <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="px-6 py-4">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Select File
                        </label>
                        <input 
                            type="file" 
                            name="file" 
                            accept=".xlsx,.xls,.csv"
                            required
                            class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:text-zinc-400 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700"
                        >
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Supported formats: .xlsx, .xls, .csv (max 10MB)
                        </p>
                    </div>

                    @if($templateUrl)
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center gap-2">
                                <flux:icon name="document-arrow-down" class="size-4 text-zinc-500" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Need a template?</span>
                                <a href="{{ $templateUrl }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Download Template
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                        <div class="flex items-start gap-2">
                            <flux:icon name="exclamation-triangle" class="mt-0.5 size-4 text-amber-600 dark:text-amber-400" />
                            <div class="text-sm text-amber-700 dark:text-amber-300">
                                <p class="font-medium">Important:</p>
                                <ul class="mt-1 list-inside list-disc text-xs">
                                    <li>First row must contain column headers</li>
                                    <li>Existing records will be updated if matched</li>
                                    <li>Empty rows will be skipped</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
