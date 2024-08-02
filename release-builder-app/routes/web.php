<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\Release;
use App\Http\Controllers\ReleasesController;
use App\Http\Controllers\SandboxController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Users;
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
    Route::get('/releases', [ReleasesController::class, 'index'])
        ->name('releases');
    Route::post('/releases', [ReleasesController::class, 'store']);
    Route::get('/releases/create', [ReleasesController::class, 'create']);
    Route::get('/releases/{id}', [ReleasesController::class, 'show']);
    Route::get('/releases/{id}/edit', [ReleasesController::class, 'edit']);
    Route::post('/releases/{id}', [ReleasesController::class, 'update']);
    Route::delete('/releases/{id}', [ReleasesController::class, 'destroy']);

    Route::get('/releases/{id}/merge-branches', Release\MergeBranchesController::class);
    Route::get('/releases/{id}/search-conflicts', Release\SearchConflictBranchesController::class);
    Route::get('/releases/{id}/reset-release-branch', Release\ResetReleaseBranchController::class);
    Route::get('/releases/{id}/fetch-repositories', Release\FetchRepositoriesController::class);
    Route::get('/releases/{id}/push-release-branch', Release\PushReleaseBranchController::class);
    Route::post('/releases/{id}/git-create-tag', Release\GitCreateTagController::class);

    Route::get('/services', [ServicesController::class, 'index'])
        ->name('services');
    Route::get('/services/add', [ServicesController::class, 'create']);
    Route::get('/services/{id}/retry', [ServicesController::class, 'retryCloneRepository']);
    Route::post('/services', [ServicesController::class, 'store']);
    Route::get('/services/{id}/fetch', [ServicesController::class, 'fetchRepository']);

    Route::get('/sandboxes/{id}', [SandboxController::class, 'show']);
    Route::post('/sandboxes/{id}', [SandboxController::class, 'update']);
    Route::get('/sandboxes/{id}/edit', [SandboxController::class, 'edit']);

    Route::get('/user', [UsersController::class, 'show']);
    Route::get('/user/add-key', [Users\UserSshKeyController::class, 'edit']);
    Route::post('/user/add-key', [Users\UserSshKeyController::class, 'update']);
});

Route::get('/', function () {
    return redirect()->intended('login');
});
