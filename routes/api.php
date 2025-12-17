<?php

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
