<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

use App\Livewire\Inventory\Index as InventoryIndex;
use App\Livewire\Inventory\ItemForm as InventoryItemForm;
use App\Livewire\Inventory\Items\Index as ItemsIndex;
use App\Livewire\Inventory\Warehouses\Index as WarehousesIndex;

use App\Livewire\Sales\Index as SalesIndex;
use App\Livewire\Sales\Orders\Index as SalesOrdersIndex;
use App\Livewire\Sales\Orders\Form as SalesOrderForm;
use App\Livewire\Sales\Customers\Index as CustomersIndex;

use App\Livewire\Delivery\Index as DeliveryIndex;
use App\Livewire\Delivery\Orders\Index as DeliveryOrdersIndex;

use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Settings\Users\Index as SettingsUsersIndex;
use App\Livewire\Settings\Roles\Index as SettingsRolesIndex;
use App\Livewire\Settings\Localization\Index as SettingsLocalizationIndex;
use App\Livewire\Settings\Company\Index as SettingsCompanyIndex;

Route::post('/locale', function (Request $request) {
    $locale = $request->input('locale');

    if (! in_array($locale, ['en', 'id'])) {
        abort(400);
    }

    $request->session()->put('locale', $locale);

    return back();
})->name('locale.switch');

Route::view('/', 'home')
    ->middleware(['auth', 'verified'])
    ->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', InventoryIndex::class)->name('index');
    
    // Items
    Route::get('/items', ItemsIndex::class)->name('items.index');
    Route::get('/items/create', InventoryItemForm::class)->name('items.create');
    Route::get('/items/{id}/edit', InventoryItemForm::class)->name('items.edit');
    
    // Warehouses
    Route::get('/warehouses', WarehousesIndex::class)->name('warehouses.index');
    Route::get('/warehouses/create', \App\Livewire\Inventory\WarehouseForm::class)->name('warehouses.create');
    Route::get('/warehouses/{id}/edit', \App\Livewire\Inventory\WarehouseForm::class)->name('warehouses.edit');
});

Route::middleware(['auth', 'verified'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/', SalesIndex::class)->name('index');
    
    // Orders
    Route::get('/orders', SalesOrdersIndex::class)->name('orders.index');
    Route::get('/orders/create', SalesOrderForm::class)->name('orders.create');
    Route::get('/orders/{id}/edit', SalesOrderForm::class)->name('orders.edit');
    
    // Customers
    Route::get('/customers', CustomersIndex::class)->name('customers.index');
});

Route::middleware(['auth', 'verified'])->prefix('delivery')->name('delivery.')->group(function () {
    Route::get('/', DeliveryIndex::class)->name('index');
    
    // Delivery Orders
    Route::get('/orders', DeliveryOrdersIndex::class)->name('orders.index');
});

// General Setup Module
Route::middleware(['auth', 'verified'])->prefix('setup')->name('settings.')->group(function () {
    Route::get('/', SettingsIndex::class)->name('index');
    
    // Users
    Route::get('/users', SettingsUsersIndex::class)->name('users.index');
    Route::get('/users/create', SettingsUsersIndex::class)->name('users.create');
    Route::get('/users/{id}/edit', SettingsUsersIndex::class)->name('users.edit');
    
    // Roles & Permissions
    Route::get('/roles', SettingsRolesIndex::class)->name('roles.index');
    
    // Localization
    Route::get('/localization', SettingsLocalizationIndex::class)->name('localization.index');
    
    // Company
    Route::get('/company', SettingsCompanyIndex::class)->name('company.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
