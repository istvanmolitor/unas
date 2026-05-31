<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Molitor\Unas\Http\Controllers\Api\UnasOrderController;
use Molitor\Unas\Http\Controllers\Api\UnasProductCategoryController;
use Molitor\Unas\Http\Controllers\Api\UnasProductController;
use Molitor\Unas\Http\Controllers\Api\UnasProductImageController;
use Molitor\Unas\Http\Controllers\Api\UnasProductParameterController;
use Molitor\Unas\Http\Controllers\Api\UnasShopController;

Route::prefix('api/unas')->middleware(['api', 'auth:sanctum', 'permission:unas'])->group(function (): void {
    Route::get('shops/options', [UnasShopController::class, 'options']);
    Route::apiResource('shops', UnasShopController::class)
        ->parameters(['shops' => 'unasShop']);
    Route::apiResource('products', UnasProductController::class)
        ->parameters(['products' => 'unasProduct'])
        ->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::prefix('products/{unasProduct}')->group(function (): void {
        Route::get('images', [UnasProductImageController::class, 'index']);
        Route::post('images', [UnasProductImageController::class, 'store']);
        Route::put('images/{image}', [UnasProductImageController::class, 'update']);
        Route::delete('images/{image}', [UnasProductImageController::class, 'destroy']);
    });
    Route::apiResource('categories', UnasProductCategoryController::class)
        ->parameters(['categories' => 'unasProductCategory'])
        ->only(['index', 'show', 'destroy']);
    Route::apiResource('parameters', UnasProductParameterController::class)
        ->parameters(['parameters' => 'unasProductParameter'])
        ->only(['index', 'show', 'destroy']);
    Route::apiResource('orders', UnasOrderController::class)
        ->parameters(['orders' => 'unasOrder'])
        ->only(['index', 'show', 'destroy']);
});
