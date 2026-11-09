<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ManagerController;

// Root redirect berdasarkan role user yang sedang login
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->role === 'kasir') return redirect()->route('pos.index');
        if ($user->role === 'gudang') return redirect()->route('inventory.index');
        return redirect()->route('manager.dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Harus Login)
Route::middleware('auth')->group(function () {

    // Kasir Module (Bisa diakses oleh Kasir & Manager)
    Route::middleware('role:kasir,manager')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
        Route::post('/pos/shift/open', [PosController::class, 'openShift'])->name('pos.shift.open');
        Route::post('/pos/shift/close', [PosController::class, 'closeShift'])->name('pos.shift.close');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/receipt/{id}', [PosController::class, 'receipt'])->name('pos.receipt');
    });

    // Gudang Module (Bisa diakses oleh Gudang & Manager)
    Route::middleware('role:gudang,manager')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/products', [InventoryController::class, 'products'])->name('inventory.products');
        Route::post('/inventory/products', [InventoryController::class, 'storeProduct'])->name('inventory.products.store');
        Route::put('/inventory/products/{id}', [InventoryController::class, 'updateProduct'])->name('inventory.products.update');
        Route::delete('/inventory/products/{id}', [InventoryController::class, 'destroyProduct'])->name('inventory.products.destroy');
        Route::get('/inventory/restock', [InventoryController::class, 'restockIndex'])->name('inventory.restock');
        Route::post('/inventory/restock', [InventoryController::class, 'storeRestock'])->name('inventory.restock.store');
        Route::get('/inventory/adjustments', [InventoryController::class, 'adjustmentsIndex'])->name('inventory.adjustments');
        Route::post('/inventory/adjustments', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustments.store');
    });

    // Manager Module (Khusus Manager saja)
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
        
        // Reports
        Route::get('/reports/sales', [ManagerController::class, 'reports'])->name('manager.reports.sales');
        Route::get('/reports/sales/export', [ManagerController::class, 'exportCsv'])->name('manager.reports.export');
        Route::get('/reports/shifts', [ManagerController::class, 'shiftReports'])->name('manager.reports.shifts');
        Route::get('/reports/stock', [ManagerController::class, 'stockReports'])->name('manager.reports.stock');
        
        // User Management
        Route::get('/manager/users', [ManagerController::class, 'users'])->name('manager.users');
        Route::post('/manager/users', [ManagerController::class, 'storeUser'])->name('manager.users.store');
        Route::put('/manager/users/{id}', [ManagerController::class, 'updateUser'])->name('manager.users.update');
        Route::post('/manager/users/{id}/toggle', [ManagerController::class, 'toggleUserStatus'])->name('manager.users.toggle');
        
        // Promotions Management
        Route::get('/manager/promotions', [ManagerController::class, 'promotions'])->name('manager.promotions');
        Route::post('/manager/promotions', [ManagerController::class, 'storePromotion'])->name('manager.promotions.store');
        Route::delete('/manager/promotions/{id}', [ManagerController::class, 'destroyPromotion'])->name('manager.promotions.destroy');
    });

});