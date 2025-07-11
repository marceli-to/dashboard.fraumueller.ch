<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UploadController;
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
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::post('/upload', [UploadController::class, 'store']);
    Route::post('/upload/process', [UploadController::class, 'process']);
});
