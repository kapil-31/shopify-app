<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductSyncController;
use App\Http\Controllers\Webhooks\ProductWebhookController;
use Illuminate\Support\Facades\Route;



Route::middleware(['api.shopify.auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'summary']);
    Route::get('/products', [ProductController::class, 'index']);


    // Sync product with shopify
    Route::get('/sync/products', [ProductSyncController::class, 'sync']);
});



// Webhook 
Route::get('/webhooks/register', [ProductWebhookController::class, 'registerWebhooks']);
Route::post('/webhooks/products', [ProductWebhookController::class, 'handle']);
