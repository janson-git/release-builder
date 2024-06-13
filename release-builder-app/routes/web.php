<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ReleasesController;
use App\Http\Controllers\SignupController;
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

Route::get('/logout', LogoutController::class);

Route::group(['middleware' => ['guest']], function () {
    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/sign-up', [SignupController::class, 'show'])
        ->name('sign-up');
    Route::post('/sign-up', [SignupController::class, 'store']);
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/dashboard', function() {
        return redirect()->intended('/releases');
    });
    Route::get('/releases', [ReleasesController::class, 'index']);
    Route::get('/releases/{id}', [ReleasesController::class, 'show']);
});

Route::get('/', function () {
    return redirect()->intended('login');
});
