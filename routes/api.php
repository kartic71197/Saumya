<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CyclecountController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/purchase_list', [PurchaseController::class, 'getPurchaseList']);
    Route::post('/purchase_order', [PurchaseController::class, 'getPurchaseOrder']);
    Route::post('/update_purchase', [PurchaseController::class, 'updatePurchaseOrder']);
    Route::post('/location/all', [LocationController::class, 'getLocations']);
    Route::post('/inventory', [InventoryController::class, 'inventoryStatus']);
    Route::post('/pick/code', [InventoryController::class, 'pickByCode']);
    Route::post('/pick/update', [InventoryController::class, 'pickUpdate']);
    Route::post('/products/{id}/upload-image', [InventoryController::class, 'uploadImage']);
    Route::post('/pick/batch-update', [InventoryController::class, 'batchUpdate']);

    /*
    |--------------------------------------------------------------------------
    | Cart API's
    |--------------------------------------------------------------------------
    */
    Route::post('/cart/update', [CartController::class, 'updateCart']);
    Route::post('/cart/delete-item', [CartController::class, 'deleteCartItem']);

    // Cycle count
    Route::get('/cycle_count/list', [CyclecountController::class, 'index']);
    Route::post('/cycle_count/data', [CyclecountController::class, 'cycledata']);
    Route::put('/cycle_count/update/{id}', [CycleCountController::class, 'update']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/sale/create', [SaleController::class, 'create']);

    Route::post('/purchase_order/{id}/upload', [PurchaseController::class, 'uploadDocument']);

    Route::get('/cart/products', [CartController::class, 'getCart']);

});
