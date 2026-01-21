<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Medrep\MedicalRepOrganizationController;
use App\Http\Controllers\MedicalRepSalesController;
use App\Http\Controllers\Medrep\SamplesController;

/*
|--------------------------------------------------------------------------
| Medical Representative Routes
|--------------------------------------------------------------------------
| These routes handle all actions related to medical representatives,
| including dashboard access, organization management, sample handling,
| and sales tracking.
|--------------------------------------------------------------------------
*/

// -------------------------------
//  Dashboard Routes
// -------------------------------
Route::get('/medical_rep/dashboard', [HomeController::class, 'repDashboard'])
    ->middleware(['verified'])
    ->name('medical_rep.dashboard');

// -------------------------------
// Organization Management Routes
// -------------------------------

// View list of organizations accessible to the medical rep
Route::get('/medical_rep/organizations', [MedicalRepOrganizationController::class, 'index'])
    ->middleware(['verified'])
    ->name('medical_rep.organizations');

// View details of a specific organization
Route::get('/medical-rep/organization/{id}', [MedicalRepOrganizationController::class, 'viewOrganization'])
    ->name('medical_rep.organization.view');

// Send a request to access an organization
Route::post('/medical-rep/organization/{org}/request-access', [MedicalRepOrganizationController::class, 'requestAccess'])
    ->name('medical_rep.organization.request_access');



// -------------------------------
// Samples Management Routes
// -------------------------------

// View and send medical samples
Route::get('/medrep/send-samples', [SamplesController::class, 'index'])
    ->name('medrep.send_samples');

// View list of sent or available samples (can be customized later)
Route::get('/medical_rep/sample_list', [SamplesController::class, 'viewSampleList'])
    ->middleware(['verified'])
    ->name('medical_rep.sample_list');

// -------------------------------
//  Sales Routes
// -------------------------------

// View medical representative sales data
Route::get('/medical-rep/sales', [MedicalRepSalesController::class, 'index'])
    ->name('medical_rep.sales');
