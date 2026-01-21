<?php

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Admin\EdiController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\organization\PosController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SettingController;


Route::middleware(['auth'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/update_dashboard/{organization_id}', [SuperAdminController::class, 'updateDashboard'])->name('admin.update_dashboard');
        Route::get('/apex-bar-chart/{organization_id}', [SuperAdminController::class, 'barCharData']);
        Route::get('/top-pickups/filter', [SuperAdminController::class, 'filterTopPickups'])->name('admin.top-pickups.filter');
        Route::get('/plans', [PlanController::class, 'index'])->name('admin.plans.index');
        Route::get('/units', [UnitController::class, 'index'])->name('admin.units.index');
        Route::get('/organization', [OrganizationController::class, 'index'])->name('admin.organization.index');
        Route::get('/medrep', [OrganizationController::class, 'medrepIndex'])->name('admin.medrep.organization.index');
        Route::get('/inventory', [InventoryController::class, 'admininventory'])->name('admin.inventory.index');
        Route::get('/purchase', [PurchaseController::class, 'adminPurchase'])->name('admin.purchase.index');
        Route::get('/users', [RegisteredUserController::class, 'usersindex'])->name('admin.users.index');
        Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
        Route::post('/impersonate/{admin}', [ImpersonationController::class, 'start'])->name('impersonate.start');
        Route::get('/settings', [SettingController::class, 'adminSetting'])->name('admin.settings.index');
        Route::get('/blogs', [BlogController::class, 'index'])->name('admin.blogs.index');
        Route::get('/blogs/create', [BlogController::class, 'create'])->name('admin.blogs.create');
        Route::post('/blogs', [BlogController::class, 'store'])->name('admin.blogs.store');
        Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('admin.blogs.show');
        Route::get('/blogs/{blog}/edit', [BlogController::class, 'edit'])->name('admin.blogs.edit');
        Route::put('/blogs/{blog}', [BlogController::class, 'update'])->name('admin.blogs.update');
        Route::delete('/blogs/{blog}', [BlogController::class, 'destroy'])->name('admin.blogs.destroy');
        Route::get('/edi-report', [EdiController::class, 'index'])->name('admin.report.edi-report');
        Route::prefix('pos')->group(function () {
            Route::get('/', [PosController::class, 'index'])->name('admin.pos.sales.index');
            Route::post('/store', [PosController::class, 'store'])->name('admin.pos.sales.store');
        });

    });
});




