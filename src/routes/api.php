<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Molitor\Unas\Http\Controllers\Api\UnasProductCategoryController;
use Molitor\Unas\Http\Controllers\Api\UnasProductController;
use Molitor\Unas\Http\Controllers\Api\UnasProductParameterController;
use Molitor\Unas\Http\Controllers\Api\UnasShopController;

Route::prefix('api/unas')->middleware(['api', 'auth:sanctum'])->group(function (): void {
    Route::get('shops/options', [UnasShopController::class, 'options']);
    Route::apiResource('shops', UnasShopController::class);
    Route::apiResource('products', UnasProductController::class)->only(['index', 'show', 'destroy']);
    Route::apiResource('categories', UnasProductCategoryController::class)->only(['index', 'show', 'destroy']);
    Route::apiResource('parameters', UnasProductParameterController::class)->only(['index', 'show', 'destroy']);
});
