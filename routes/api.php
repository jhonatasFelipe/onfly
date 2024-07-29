<?php

use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(UserController::class)->prefix('/user')->group(function () {
    Route::post('/', 'create')->name('user.create');
});

Route::controller(LoginController::class)->prefix('/login')->group(function () {
    Route::post('/', 'index')->name('login.token');
});

Route::middleware('auth:sanctum')->group(function(){
    Route::controller(ExpensesController::class)->prefix('/expenses')->group(function () {
        Route::get('/','index')
            ->name('user.getUser');
        Route::get('/{expenses}','getById')
            ->name('user.getById')
            ->middleware('can:toManage,expenses');
        Route::post('/','create')
            ->name('user.create');
        Route::put('/{expenses}','update')
            ->name('user.update')
            ->middleware('can:toManage,expenses');
        Route::delete('/{expenses}','destroy')
            ->name('user.destroy')
            ->middleware('can:toManage,expenses');
    });
});

