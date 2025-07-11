<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/**
 * Auth Routes
 */
require __DIR__.'/auth.php';

/**
 * Frontend Routes
 */
Route::get('/', [HomeController::class, 'index'])->name('page.home');

/**
 * Backend Routes
 */
Route::get('/dashboard/{any?}', function () {
    return view('pages.dashboard');
})->where('any', '.*')->middleware(['auth', 'verified'])->name('page.dashboard');

Route::get('/error/{any?}', function () {
    return view('pages.dashboard');
})->where('any', '.*')->middleware(['auth', 'verified'])->name('page.dashboard');
