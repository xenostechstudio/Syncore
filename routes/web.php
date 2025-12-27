<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

use App\Livewire\Inventory\Index as InventoryIndex;
use App\Livewire\Inventory\ItemForm as InventoryItemForm;
use App\Livewire\Inventory\Items\Index as ItemsIndex;
use App\Livewire\Inventory\Products\Form as InventoryProductForm;
use App\Livewire\Inventory\Products\Pricelists\Index as InventoryProductPricelistsIndex;
use App\Livewire\Inventory\Products\Pricelists\Form as InventoryProductPricelistForm;
use App\Livewire\Inventory\Warehouses\Index as WarehousesIndex;
use App\Livewire\Inventory\Categories\Index as CategoriesIndex;
use App\Livewire\Inventory\Categories\Form as CategoryForm;

use App\Livewire\Sales\Index as SalesIndex;
use App\Livewire\Sales\Orders\Index as SalesOrdersIndex;
use App\Livewire\Sales\Orders\Form as SalesOrderForm;
use App\Livewire\Sales\Customers\Index as CustomersIndex;
use App\Livewire\Sales\Teams\Index as SalesTeamsIndex;
use App\Livewire\Sales\Teams\Form as SalesTeamForm;
use App\Livewire\Sales\Customers\Form as CustomerForm;
use App\Livewire\Sales\Products\Index as SalesProductsIndex;
use App\Livewire\Sales\Products\Form as SalesProductForm;

use App\Livewire\Sales\Configuration\Taxes\Index as TaxesIndex;
use App\Livewire\Sales\Configuration\Taxes\Form as TaxForm;
use App\Livewire\Sales\Configuration\PaymentTerms\Index as PaymentTermsIndex;
use App\Livewire\Sales\Configuration\PaymentTerms\Form as PaymentTermForm;
use App\Livewire\Sales\Configuration\Pricelists\Index as PricelistsIndex;
use App\Livewire\Sales\Configuration\Pricelists\Form as PricelistForm;
use App\Livewire\Sales\Invoices\OrdersToInvoice as SalesOrdersToInvoice;

use App\Livewire\Delivery\Index as DeliveryIndex;
use App\Livewire\Delivery\Orders\Index as DeliveryOrdersIndex;
use App\Livewire\Delivery\Orders\Form as DeliveryOrderForm;

use App\Livewire\Invoicing\Index as InvoicingIndex;
use App\Livewire\Invoicing\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoicing\Invoices\Form as InvoiceForm;
use App\Livewire\Invoicing\Payments\Index as PaymentsIndex;

use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Settings\Users\Index as SettingsUsersIndex;
use App\Livewire\Settings\Users\Form as SettingsUsersForm;
use App\Livewire\Settings\Roles\Index as SettingsRolesIndex;
use App\Livewire\Settings\Roles\Form as SettingsRolesForm;
use App\Livewire\Settings\Localization\Index as SettingsLocalizationIndex;
use App\Livewire\Settings\Company\Index as SettingsCompanyIndex;
use App\Livewire\Public\Invoices\Show as PublicInvoiceShow;

use App\Livewire\Purchase\Rfq\Index as PurchaseRfqIndex;
use App\Livewire\Purchase\Rfq\Form as PurchaseRfqForm;
use App\Livewire\Purchase\Orders\Index as PurchaseOrdersIndex;
use App\Livewire\Purchase\Orders\Form as PurchaseOrdersForm;
use App\Livewire\Purchase\Suppliers\Index as PurchaseSuppliersIndex;
use App\Livewire\Purchase\Suppliers\Form as PurchaseSuppliersForm;

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

Route::get('/public/invoices/{token}', PublicInvoiceShow::class)
    ->middleware('signed')
    ->name('public.invoices.show');

Route::middleware(['auth', 'verified', 'permission:access.inventory'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', InventoryIndex::class)->name('index');
    
    // Operations - Transfers
    Route::get('/transfers', \App\Livewire\Inventory\Transfers\Index::class)->name('transfers.index');
    Route::get('/transfers/create', \App\Livewire\Inventory\Transfers\Form::class)->name('transfers.create');
    Route::get('/transfers/{id}/edit', \App\Livewire\Inventory\Transfers\Form::class)->name('transfers.edit');
    
    // Operations - Adjustments
    Route::get('/adjustments', \App\Livewire\Inventory\Adjustments\Index::class)->name('adjustments.index');
    Route::get('/adjustments/create', \App\Livewire\Inventory\Adjustments\Form::class)->name('adjustments.create');
    Route::get('/adjustments/{id}/edit', \App\Livewire\Inventory\Adjustments\Form::class)->name('adjustments.edit');

    // Operations - Warehouse IN / OUT
    Route::get('/warehouse-in', \App\Livewire\Inventory\Adjustments\Index::class)->name('warehouse-in.index');
    Route::get('/warehouse-in/create', \App\Livewire\Inventory\Adjustments\Form::class)->name('warehouse-in.create');
    Route::get('/warehouse-in/{id}/edit', \App\Livewire\Inventory\Adjustments\Form::class)->name('warehouse-in.edit');

    Route::get('/warehouse-out', \App\Livewire\Inventory\Adjustments\Index::class)->name('warehouse-out.index');
    Route::get('/warehouse-out/create', \App\Livewire\Inventory\Adjustments\Form::class)->name('warehouse-out.create');
    Route::get('/warehouse-out/{id}/edit', \App\Livewire\Inventory\Adjustments\Form::class)->name('warehouse-out.edit');
    
    // Products
    Route::get('/products', ItemsIndex::class)->name('products.index');
    Route::get('/products/create', InventoryProductForm::class)->name('products.create');
    Route::get('/products/{id}/edit', InventoryProductForm::class)->name('products.edit');

    // Products - Pricelists
    Route::get('/products/pricelists', InventoryProductPricelistsIndex::class)->name('products.pricelists.index');
    Route::get('/products/pricelists/create', InventoryProductPricelistForm::class)->name('products.pricelists.create');
    Route::get('/products/pricelists/{id}/edit', InventoryProductPricelistForm::class)->name('products.pricelists.edit');
    
    // Categories
    Route::get('/categories', CategoriesIndex::class)->name('categories.index');
    Route::get('/categories/create', CategoryForm::class)->name('categories.create');
    Route::get('/categories/{id}/edit', CategoryForm::class)->name('categories.edit');
    
    // Warehouses
    Route::get('/warehouses', WarehousesIndex::class)->name('warehouses.index');
    Route::get('/warehouses/create', \App\Livewire\Inventory\Warehouses\Form::class)->name('warehouses.create');
    Route::get('/warehouses/{id}/edit', \App\Livewire\Inventory\Warehouses\Form::class)->name('warehouses.edit');
});

Route::middleware(['auth', 'verified', 'permission:access.sales'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/', SalesIndex::class)->name('index');
    
    // Orders
    Route::get('/orders', SalesOrdersIndex::class)->name('orders.index');
    Route::get('/orders/all', SalesOrdersIndex::class)->name('orders.all');
    Route::get('/orders/create', SalesOrderForm::class)->name('orders.create');
    Route::get('/orders/{id}/edit', SalesOrderForm::class)->name('orders.edit');
    Route::get('/orders/{id}/print', \App\Http\Controllers\Sales\SalesOrderPrintController::class)->name('orders.print');
    
    // Orders to Invoice
    Route::get('/invoices/orders', SalesOrdersToInvoice::class)->name('invoices.pending');
    
    // Customers
    Route::get('/customers', CustomersIndex::class)->name('customers.index');
    Route::get('/customers/create', CustomerForm::class)->name('customers.create');
    Route::get('/customers/{id}/edit', CustomerForm::class)->name('customers.edit');
    
    // Sales Teams
    Route::get('/teams', SalesTeamsIndex::class)->name('teams.index');
    Route::get('/teams/create', SalesTeamForm::class)->name('teams.create');
    Route::get('/teams/{id}/edit', SalesTeamForm::class)->name('teams.edit');

    // Products (Sales view of Inventory Items)
    Route::get('/products', SalesProductsIndex::class)->name('products.index');
    Route::get('/products/create', SalesProductForm::class)->name('products.create');
    Route::get('/products/{id}/edit', SalesProductForm::class)->name('products.edit');
    
    // Configuration - Taxes
    Route::get('/configuration/taxes', TaxesIndex::class)->name('configuration.taxes.index');
    Route::get('/configuration/taxes/create', TaxForm::class)->name('configuration.taxes.create');
    Route::get('/configuration/taxes/{id}/edit', TaxForm::class)->name('configuration.taxes.edit');
    
    // Configuration - Payment Terms
    Route::get('/configuration/payment-terms', PaymentTermsIndex::class)->name('configuration.payment-terms.index');
    Route::get('/configuration/payment-terms/create', PaymentTermForm::class)->name('configuration.payment-terms.create');
    Route::get('/configuration/payment-terms/{id}/edit', PaymentTermForm::class)->name('configuration.payment-terms.edit');
    
    // Configuration - Pricelists
    Route::get('/configuration/pricelists', PricelistsIndex::class)->name('configuration.pricelists.index');
    Route::get('/configuration/pricelists/create', PricelistForm::class)->name('configuration.pricelists.create');
    Route::get('/configuration/pricelists/{id}/edit', PricelistForm::class)->name('configuration.pricelists.edit');
});

Route::middleware(['auth', 'verified', 'permission:access.delivery'])->prefix('delivery')->name('delivery.')->group(function () {
    Route::get('/', DeliveryIndex::class)->name('index');
    
    // Delivery Orders
    Route::get('/orders', DeliveryOrdersIndex::class)->name('orders.index');
    Route::get('/orders/create', DeliveryOrderForm::class)->name('orders.create');
    Route::get('/orders/{id}/edit', DeliveryOrderForm::class)->name('orders.edit');
});

Route::middleware(['auth', 'verified', 'permission:access.invoicing'])->prefix('invoicing')->name('invoicing.')->group(function () {
    Route::get('/', InvoicingIndex::class)->name('index');
    
    // Invoices
    Route::get('/invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('/invoices/create', InvoiceForm::class)->name('invoices.create');
    Route::get('/invoices/{id}/edit', InvoiceForm::class)->name('invoices.edit');
    
    // Payments
    Route::get('/payments', PaymentsIndex::class)->name('payments.index');
    
    // Reports
    Route::get('/reports', \App\Livewire\Invoicing\Reports\Index::class)->name('reports');
    
    // Configuration - Payment Gateway
    Route::get('/configuration/payment-gateway', \App\Livewire\Invoicing\Configuration\PaymentGateway\Index::class)->name('configuration.payment-gateway.index');
});

Route::middleware(['auth', 'verified', 'permission:access.purchase'])->prefix('purchase')->name('purchase.')->group(function () {
    Route::get('/', \App\Livewire\Purchase\Index::class)->name('index');
    
    // Request for Quotation
    Route::get('/rfq', PurchaseRfqIndex::class)->name('rfq.index');
    Route::get('/rfq/create', PurchaseRfqForm::class)->name('rfq.create');
    Route::get('/rfq/{id}/edit', PurchaseRfqForm::class)->name('rfq.edit');
    
    // Purchase Orders
    Route::get('/orders', PurchaseOrdersIndex::class)->name('orders.index');
    Route::get('/orders/create', PurchaseOrdersForm::class)->name('orders.create');
    Route::get('/orders/{id}/edit', PurchaseOrdersForm::class)->name('orders.edit');
    
    // Suppliers
    Route::get('/suppliers', PurchaseSuppliersIndex::class)->name('suppliers.index');
    Route::get('/suppliers/create', PurchaseSuppliersForm::class)->name('suppliers.create');
    Route::get('/suppliers/{id}/edit', PurchaseSuppliersForm::class)->name('suppliers.edit');
});

// General Setup Module
Route::middleware(['auth', 'verified', 'permission:access.settings'])->prefix('setup')->name('settings.')->group(function () {
    Route::get('/', SettingsIndex::class)->name('index');
    
    // Module Configuration
    Route::get('/modules/sales-order', \App\Livewire\Settings\Modules\SalesOrder::class)->name('modules.sales-order');
    Route::get('/modules/purchase-order', \App\Livewire\Settings\Modules\PurchaseOrder::class)->name('modules.purchase-order');
    Route::get('/modules/invoice', \App\Livewire\Settings\Modules\Invoice::class)->name('modules.invoice');
    
    // Users
    Route::get('/users', SettingsUsersIndex::class)->name('users.index');
    Route::get('/users/create', SettingsUsersForm::class)->name('users.create');
    Route::get('/users/{id}/edit', SettingsUsersForm::class)->name('users.edit');
    
    // Roles & Permissions
    Route::get('/roles', SettingsRolesIndex::class)->name('roles.index');
    Route::get('/roles/create', SettingsRolesForm::class)->name('roles.create');
    Route::get('/roles/{id}/edit', SettingsRolesForm::class)->name('roles.edit');
    
    // Localization
    Route::get('/localization', SettingsLocalizationIndex::class)->name('localization.index');
    
    // Company
    Route::get('/company', SettingsCompanyIndex::class)->name('company.index');
    
    // Email Configuration
    Route::get('/email', \App\Livewire\Settings\Email\Index::class)->name('email.index');
    
    // Audit Trail
    Route::get('/audit-trail', \App\Livewire\Settings\AuditTrail\Index::class)->name('audit-trail.index');
});

// Export Routes
Route::middleware(['auth', 'verified'])->prefix('export')->name('export.')->group(function () {
    Route::get('/sales-orders', [\App\Http\Controllers\ExportController::class, 'salesOrders'])->name('sales-orders');
    Route::get('/invoices', [\App\Http\Controllers\ExportController::class, 'invoices'])->name('invoices');
    Route::get('/delivery-orders', [\App\Http\Controllers\ExportController::class, 'deliveryOrders'])->name('delivery-orders');
    Route::get('/customers', [\App\Http\Controllers\ExportController::class, 'customers'])->name('customers');
    Route::get('/products', [\App\Http\Controllers\ExportController::class, 'products'])->name('products');
    Route::get('/purchase-orders', [\App\Http\Controllers\ExportController::class, 'purchaseOrders'])->name('purchase-orders');
    Route::get('/suppliers', [\App\Http\Controllers\ExportController::class, 'suppliers'])->name('suppliers');
    Route::get('/warehouses', [\App\Http\Controllers\ExportController::class, 'warehouses'])->name('warehouses');
    Route::get('/categories', [\App\Http\Controllers\ExportController::class, 'categories'])->name('categories');
    Route::get('/users', [\App\Http\Controllers\ExportController::class, 'users'])->name('users');
});

// PDF Routes
Route::middleware(['auth', 'verified'])->prefix('pdf')->name('pdf.')->group(function () {
    Route::get('/invoice/{invoice}', [\App\Http\Controllers\PdfController::class, 'invoice'])->name('invoice');
    Route::get('/sales-order/{salesOrder}', [\App\Http\Controllers\PdfController::class, 'salesOrder'])->name('sales-order');
    Route::get('/delivery-order/{deliveryOrder}', [\App\Http\Controllers\PdfController::class, 'deliveryOrder'])->name('delivery-order');
});

