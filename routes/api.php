<?php

use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Webhooks\XenditWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Xendit Webhook (no CSRF, no auth)
Route::post('/webhooks/xendit/invoice', XenditWebhookController::class)->name('webhooks.xendit');

// Health Check (no auth required)
Route::get('/health', HealthController::class);

// Determine API auth middleware (use sanctum if available, otherwise web)
$apiAuthMiddleware = config('auth.guards.sanctum') ? 'auth:sanctum' : 'auth:web';

// API v1 Routes (requires authentication)
Route::prefix('v1')->middleware([$apiAuthMiddleware])->group(function () {
    
    // Health Check (detailed, requires auth)
    Route::get('/health/detailed', [HealthController::class, 'detailed']);
    
    // Dashboard & KPIs
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardApiController::class, 'index']);
        Route::get('/kpi', [DashboardApiController::class, 'kpi']);
        Route::get('/sales', [DashboardApiController::class, 'sales']);
        Route::get('/inventory', [DashboardApiController::class, 'inventory']);
        Route::get('/invoicing', [DashboardApiController::class, 'invoicing']);
        Route::get('/hr', [DashboardApiController::class, 'hr']);
        Route::get('/crm', [DashboardApiController::class, 'crm']);
        Route::get('/purchase', [DashboardApiController::class, 'purchase']);
    });

    // Customers
    Route::apiResource('customers', CustomerApiController::class);

    // Products
    Route::get('products/{id}/stock', [ProductApiController::class, 'stock']);
    Route::apiResource('products', ProductApiController::class);

    // Invoices
    Route::get('invoices/summary', [InvoiceApiController::class, 'summary']);
    Route::apiResource('invoices', InvoiceApiController::class)->only(['index', 'show']);
});
