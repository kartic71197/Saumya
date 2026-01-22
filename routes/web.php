<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrainingController;
use App\Mail\PurchaseOrderMail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;

use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\HomeController;
use App\Livewire\User\Inventory\InventoryAdjustComponent;
use App\Http\Controllers\CartController;
use App\Livewire\Organization\Inventory\CycleCountDetails;
use App\Livewire\Organization\Inventory\UserTasks;
use App\Http\Controllers\NotificationController;




Route::view('/', 'website.main')->name('home');
// Route::view('/privacy-policies', 'website.privacypolicies')->name('privacy.policies');
// Route::view('/contact', 'website.contact')->name('contact');
// Route::view('/help_center', 'website.helpCenter')->name('helpCenter');
Route::get('/supplier-costs', [SupplierController::class, 'getSupplierCosts']);
Route::get('/purchase-orders/{id}/details', [PurchaseController::class, 'details']);
Route::post('/purchase-orders/{id}/reorder', [PurchaseController::class, 'reorder']);
// Route::get('/blogs', [BlogController::class, 'userView'])->name('blogs.index');
// Route::get('/blogs/{slug}', [BlogController::class, 'userShow'])->name('blogs.show');






Route::get('/purchase-orders/{id}/details', [PurchaseController::class, 'details']);
Route::post('/purchase-orders/{id}/reorder', [PurchaseController::class, 'reorder']);
// Route::get('/dashboard/top-pickups', [App\Http\Controllers\HomeController::class, 'getTopPickups']);
Route::get('/top-pickups/filter', [HomeController::class, 'filterTopPickups'])->name('top-pickups.filter');
Route::get('/user/inventory/adjust', InventoryAdjustComponent::class)->name('user.inventory.adjust');

Route::post('/cart/add-multiple', [CartController::class, 'addMultipleFromPO']);
Route::post('/cart/check-existing', [CartController::class, 'checkExisting']);


Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
Route::post('/training/validate-chart', [TrainingController::class, 'validateChartId'])->name('training.validate-chart');

Route::get('/training/note/{noteId}/download', [TrainingController::class, 'downloadNote'])->name('training.download-note');
Route::get('/training/logout', [TrainingController::class, 'logout'])->name('training.logout');

Route::view('/our-story', 'website.ourStory')->name('ourStory');

// Test route to check if requests reach the server
Route::post('/training/test-upload', function () {
    \Log::info('Test upload route hit');
    return response()->json(['message' => 'Test route working']);
});


// Training Admin Routes
Route::middleware(['auth'])->group(function () {

    Route::get('/training/admin', [TrainingController::class, 'admin'])->name('training.admin');
    // Chapter CRUD
    Route::post('/training/chapters', [TrainingController::class, 'storeChapter'])->name('training.store-chapter');
    Route::put('/training/chapters/{id}', [TrainingController::class, 'updateChapter'])->name('training.update-chapter');
    Route::delete('/training/chapters/{id}', [TrainingController::class, 'deleteChapter'])->name('training.delete-chapter');

    // Note CRUD
    Route::post('/training/notes', [TrainingController::class, 'storeNote'])->name('training.store-note');
    Route::put('/training/notes/{id}', [TrainingController::class, 'updateNote'])->name('training.update-note');
    Route::delete('/training/notes/{id}', [TrainingController::class, 'deleteNote'])->name('training.delete-note');

    // Debug route (remove in production)
    Route::get('/training/debug-s3', [TrainingController::class, 'debugS3'])->name('training.debug-s3');

    Route::post('/training/quizzes', [TrainingController::class, 'storeQuiz'])->name('training.store-quiz');
    Route::post('/training/questions', [TrainingController::class, 'storeQuestion'])->name('training.store-question');
    Route::delete('/training/quizzes/{id}', [TrainingController::class, 'deleteQuiz'])->name('training.delete-quiz');
    Route::delete('/training/questions/{id}', [TrainingController::class, 'deleteQuestion'])->name('training.delete-question');

    Route::get('/training/quizzes/{id}/attempt', [TrainingController::class, 'attemptQuiz'])->name('training.attempt-quiz');
    Route::post('/training/quizzes/{id}/submit', [TrainingController::class, 'submitQuiz'])->name('training.submit-quiz');

    Route::get('/training/score-card', [TrainingController::class, 'scoreCard'])->name('training.score-card');
    Route::get('/training/score-board', [TrainingController::class, 'scoreBoard'])->name('training.score-board');

    // - To display user notifications on a dedicated page
    // - To allow users to mark individual notifications as read
    // - To provide a single action to mark all notifications as read
    // - Supports both dropdown notifications and full notifications page
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::get('/notifications/invoices/{po}/{source}',[NotificationController::class, 'downloadInvoice'])->name('notifications.invoices.download');
});

Route::get('/organization/cycle-counts/{cycle}', CycleCountDetails::class)
    ->name('organization.cycle-counts.details');

Route::middleware(['auth'])->group(function () {
    Route::get('/cycles/user-tasks', \App\Livewire\Organization\Inventory\UserTasks::class)->name('cycles.user-tasks');
});

// use App\Mail\SendSingleOrderMail;
// use App\Models\PurchaseOrder;
// use App\Models\Organization;
// use App\Models\Supplier;

// Route::get('/preview-single-order', function () {
//     $purchaseOrder = PurchaseOrder::where('id', 348)->first();
//     $purchaseOrderDetails = $purchaseOrder->purchasedProducts; // adjust if needed
//     $organization = Organization::first();
//     $supplier = Supplier::first();

//     return new SendSingleOrderMail(
//         $organization,
//         $purchaseOrder,
//         $supplier,
//         $purchaseOrderDetails
//     );
// });

// Route::get('/preview-order', function () {
//     $purchaseOrder = PurchaseOrder::where('id', 348)->first();
//     $purchaseOrderDetails = $purchaseOrder->purchasedProducts; // adjust if needed
//     $organization = Organization::first();
//     $supplier = Supplier::first();

//     return new PurchaseOrderMail(
//          $purchaseOrder->created_at,
//         $organization,
//         $purchaseOrder->purchase_order_number,
//         $supplier,
//         $purchaseOrderDetails,
//         17,
//         17
//     );
// });





require __DIR__ . '/auth.php';
require __DIR__ . '/super-admin.php';
require __DIR__ . '/medrep.php';   
require __DIR__ . '/appointments.php';        

