<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\TravelOrder\ListTravelOrdersController;
use App\Http\Controllers\Api\V1\TravelOrder\ShowTravelOrderController;
use App\Http\Controllers\Api\V1\TravelOrder\StoreTravelOrderController;
use App\Http\Controllers\Api\V1\TravelOrder\UpdateTravelOrderStatusController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', RegisterController::class);
        Route::post('login', LoginController::class);
        Route::post('logout', LogoutController::class)->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('travel-orders', StoreTravelOrderController::class);
        Route::get('travel-orders', ListTravelOrdersController::class);
        Route::get('travel-orders/{id}', ShowTravelOrderController::class)->whereUuid('id');
        Route::patch('travel-orders/{id}/status', UpdateTravelOrderStatusController::class)
            ->whereUuid('id');
    });
});
