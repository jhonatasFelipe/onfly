<?php

declare(strict_types=1);

use App\Http\Controllers\Web\AdminLoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:web')->group(function (): void {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::middleware(['guest', 'throttle:web-login'])->group(function (): void {
        Route::get('admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
        Route::post('admin/login', [AdminLoginController::class, 'store'])->name('admin.login.store');
    });

    Route::post('admin/logout', [AdminLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('admin.logout');
});
