<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\LowStockAlertController;
use App\Http\Controllers\Api\V1\StockTransferController;
use App\Http\Controllers\Api\V1\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Auth (public) ─────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    });

    // ── Protected (Sanctum) ───────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

        // Warehouses
        Route::apiResource('warehouses', WarehouseController::class);

        // Items
        Route::apiResource('items', ItemController::class);

        // Inventory (read-only)
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');

        // Stock Transfers
        Route::get('transfers', [StockTransferController::class, 'index'])->name('transfers.index');
        Route::post('transfers', [StockTransferController::class, 'store'])->name('transfers.store');
        Route::get('transfers/{transfer}', [StockTransferController::class, 'show'])->name('transfers.show');

        // Low Stock Alerts
        Route::get('alerts/low-stock', [LowStockAlertController::class, 'index'])->name('alerts.low-stock');
    });
});
