<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\CommentController;

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
  Route::get('/votes', [VoteController::class, 'get']);
  Route::get('/comments', [CommentController::class, 'get']);
  Route::put('/comments/update/{comment}', [CommentController::class, 'update']);
  Route::put('/comments/restore/{id}', [CommentController::class, 'restore']);
  Route::put('/comments/toggle/{comment}', [CommentController::class, 'toggle']);
  Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});