<x-layouts.app title="Syncore ERP">
    <div class="flex h-full w-full flex-1 flex-col gap-8 rounded-xl">
        
        <div>
            <flux:heading size="xl" level="1">Welcome to Syncore</flux:heading>
            <flux:subheading size="lg" class="mb-6">Select a module to get started.</flux:subheading>
            
            <div class="grid auto-rows-min gap-4 md:grid-cols-3 lg:grid-cols-4">
                <!-- Inventory Module -->
                <a href="{{ route('inventory.index') }}" class="group relative flex flex-col gap-2 overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 transition-all hover:border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:hover:border-neutral-600">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <flux:icon.archive-box class="size-6" />
                    </div>
                    <div>
                        <h3 class="font-medium text-neutral-900 dark:text-white">Inventory</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Multi-warehouse inventory management.</p>
                    </div>
                </a>

                <!-- Sales Order Module -->
                <div class="group relative flex flex-col gap-2 overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 opacity-60 transition-all dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                        <flux:icon.shopping-cart class="size-6" />
                    </div>
                    <div>
                        <h3 class="font-medium text-neutral-900 dark:text-white">Sales Order</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage customer orders and sales.</p>
                    </div>
                    <span class="absolute top-4 right-4 text-xs font-medium text-neutral-400 dark:text-neutral-600">Coming Soon</span>
                </div>

                <!-- Purchase Order Module -->
                <div class="group relative flex flex-col gap-2 overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 opacity-60 transition-all dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                        <flux:icon.document-text class="size-6" />
                    </div>
                    <div>
                        <h3 class="font-medium text-neutral-900 dark:text-white">Purchase Order</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Procurement and vendor management.</p>
                    </div>
                    <span class="absolute top-4 right-4 text-xs font-medium text-neutral-400 dark:text-neutral-600">Coming Soon</span>
                </div>

                <!-- Delivery Order Module -->
                <div class="group relative flex flex-col gap-2 overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 opacity-60 transition-all dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                        <flux:icon.truck class="size-6" />
                    </div>
                    <div>
                        <h3 class="font-medium text-neutral-900 dark:text-white">Delivery Order</h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Shipment tracking and logistics.</p>
                    </div>
                    <span class="absolute top-4 right-4 text-xs font-medium text-neutral-400 dark:text-neutral-600">Coming Soon</span>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
