<?php

use App\Http\Controllers\Appointment\AppoinmentCategoryController;
use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Appointment\ClientAppointmentController;

Route::middleware(['auth'])->group(function () {
    Route::get(
        '/appointment-categories',
        [AppoinmentCategoryController::class, 'index']
    )->name('appointments.categories');
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/get', [AppointmentController::class, 'getAppointments'])->name('appointments.get');
        Route::get('/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
        Route::post('/{id}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    });
});

// Public booking routes
Route::get('/book-appointment', [ClientAppointmentController::class, 'index'])->name('appointments.client.index');
Route::get('/book/{organization:slug}', [ClientAppointmentController::class, 'create'])->name('appointments.client.create');
Route::post('/book/{organization:slug}', [ClientAppointmentController::class, 'store'])->name('appointments.client.store');
Route::get('/appointment/confirmation/{appointment}', [ClientAppointmentController::class, 'confirmation'])->name('appointments.client.confirmation');