<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderLogController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\ExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Dashboard
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'get']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/bulk-update', [OrderController::class, 'bulkUpdate']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    Route::get('/order-logs', [OrderLogController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/products/{product}/send-test-notification', [ProductController::class, 'sendTestNotification']);
    Route::post('/upload', [UploadController::class, 'store']);
    Route::post('/upload/process', [UploadController::class, 'process']);
    Route::post('/export/orders/csv', [ExportController::class, 'exportOrdersCsv']);
});
