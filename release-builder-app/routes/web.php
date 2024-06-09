<?php

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

Route::get('/login', [\App\Http\Controllers\LoginController::class, 'show'])
    ->name('login');
Route::post('/login', [\App\Http\Controllers\LoginController::class, 'store']);

Route::get('/sign-up', [\App\Http\Controllers\SignupController::class, 'show'])
    ->name('sign-up');
Route::post('/sign-up', [\App\Http\Controllers\SignupController::class, 'store']);

Route::get('/', function () {
    return view('welcome');
});
