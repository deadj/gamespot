<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->name('products')->group(function () {
    Route::get('', [ProductController::class, 'getAll'])->name('.get.all');
    Route::get('stock', [ProductController::class, 'getStock'])->name('.get.stock');
    Route::get('{sku}', [ProductController::class, 'getBySku'])->name('.get.sku');
});


Route::prefix('orders')->name('orders')->group(function () {
    Route::get('strange', [OrderController::class, 'showStrangeOrders'])->name('.show.strange');
    Route::get('{id}', [OrderController::class, 'show'])->name('.show');
    Route::post('', [OrderController::class, 'store'])->name('.store');
});

Route::post('webhooks/payment', PaymentWebhookController::class)->name('webhook.payment');