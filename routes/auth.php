<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MedicalRepController;
use App\Http\Controllers\MedicalRepOrganizationController;
use App\Http\Controllers\MedicalRepSalesController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PickingController;
use App\Http\Controllers\PotentialUsersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\organization\PosController;
use App\Http\Controllers\FieldRepController;





Route::post('/stripe/webhook', StripeWebhookController::class);



/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Create shipment
    Route::post('/shipping/create', [ShippingController::class, 'createShipment'])->name('shipping.create');

    // Get shipment details
    Route::get('/shipping/sale/{saleId}', [ShippingController::class, 'getShipment'])->name('shipping.get');

    // Track shipment
    Route::post('/shipping/track', [ShippingController::class, 'trackShipment'])->name('shipping.track');

    // Get all shipments
    Route::get('/shipping/all', [ShippingController::class, 'getAllShipments'])->name('shipping.all');

    // Download shipping label
    Route::get('/shipping/label/{shipmentId}', [ShippingController::class, 'downloadLabel'])->name('shipping.label');
});

Route::middleware('guest:web')->group(function () {
    // Registration Routes
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::post('check-email', [RegisteredUserController::class, 'checkEmail'])->name('check-email');
    Route::post('verify-otp', [RegisteredUserController::class, 'verifyOtp'])->name('verify-otp');

    // Login Routes
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});



Route::middleware(['auth:web'])->group(callback: function () {
    Route::get('/pricing', [PlanController::class, 'showPricing'])->name('pricing');
    Route::post('/checkout', [PlanController::class, 'checkout'])->name('checkout');
    Route::get('/billing-portal', [PlanController::class, 'billingPortal'])->name('billing.portal');

});

/**
 * Invoice Management Routes
 */
Route::middleware(['auth'])->group(function () {

    // Main invoice list with filters
    Route::get('/invoices', [InvoiceController::class, 'invoiceList'])->name('invoices.index');

    // View single invoice
    Route::get('/invoices/{invoiceId}', [InvoiceController::class, 'show'])->name('invoices.show');

    // Download invoice PDF
    Route::get('/invoices/{invoiceId}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    // Export invoices to CSV
    Route::post('/invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');

    // Admin-only routes
    // Route::middleware(['admin'])->group(function () {
    // Resend invoice email
    Route::post('/invoices/{invoiceId}/resend', [InvoiceController::class, 'resend'])->name('invoices.resend');

    // Mark invoice as paid
    Route::post('/invoices/{invoiceId}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');

    // Void invoice
    Route::post('/invoices/{invoiceId}/void', [InvoiceController::class, 'void'])->name('invoices.void');

    Route::post('/stripe/invoices/{invoiceId}/send-reminder', [InvoiceController::class, 'sendReminder'])
        ->name('stripe.invoices.reminder');

    // });
});


Route::middleware(['auth:web', 'check.organization', 'check.plan'])->group(function () {

    Route::get('/potential-users', [PotentialUsersController::class, 'index'])->name('potential-users.index');
    /*
    |--------------------------------------------------------------------------
    | Email Verification Routes
    |--------------------------------------------------------------------------
    */
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    /*
    |--------------------------------------------------------------------------
    | Password & Authentication Management Routes
    |--------------------------------------------------------------------------
    */
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [HomeController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');
    Route::get('/supplier', [SupplierController::class, 'index'])->name('admin.supplier.index');
    Route::get('/field-reps', [FieldRepController::class, 'index'])->name('admin.field-reps.index');


    //removed organization_id as not needed
    Route::get('/apex-bar-chart/{location_id}', [HomeController::class, 'barCharData']);
    Route::get('/update_dashboard/{location_id}', [HomeController::class, 'updateDashboard']);

    /*
    |--------------------------------------------------------------------------
    | Organization & Location Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('organization')->group(function () {
        Route::get('/create', [OrganizationController::class, 'create'])->name('organization.create');
        Route::post('/products', [ProductController::class, 'importProducts'])->name('import.products');
        Route::post('/import/catalog', [ProductController::class, 'importCatalog'])->name('import.catalog');
    });

    Route::get('/get-locations/{organizationId}', [OrganizationController::class, 'get_locations'])->name('get-locations');

    /*
    |--------------------------------------------------------------------------
    | Product Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('organization')->group(function () {

        Route::get('/inventory', [ProductController::class, 'OrganizationInventory'])->name('organization.inventory');
        Route::get('/catalog', [ProductController::class, 'OrganizationCatalog'])->name('organization.catalog');
    });
    Route::get('admin/products', [ProductController::class, 'adminProducts'])->name('admin.products.index');

    /*
    |--------------------------------------------------------------------------
    | Inventory & Operations Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/picking', [PickingController::class, 'index'])->name('picking.index');



    /*
    |--------------------------------------------------------------------------
    | E-commerce Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

    Route::get('/billing/{organization_id}', [BillingController::class, 'index'])->name('billing.index');
    // routes/api.php or web.php
    Route::get('/organization/{organization_id}/supplier/{supplier_id}/data', [BillingController::class, 'getSupplierData']);
    Route::post('/billing/{organization_id}', [BillingController::class, 'billingUpdate'])->name('billing.update');
    Route::post('/shipping/{organization_id}', [BillingController::class, 'shippingUpdate'])->name('shipping.update');

    Route::get('/fedex-shipping', [ShippingController::class, 'createShipment'])->name('fedex.shipping');

    Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.index');

    Route::post('/purchase-orders/{id}/reorder', [PurchaseController::class, 'reorder'])->name('purchase-orders.reorder');


    Route::get('user/tickets', [TicketController::class, 'index'])->name('ticket.index');

    Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['verified'])->name('dashboard');


    Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.index');

    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::post('delivery/{id}/calculate/shipment', [ShippingController::class, 'calculateShipment']);

    Route::get('/purchase-order/{id}/download-invoice', [PurchaseController::class, 'downloadInvoice'])
        ->name('download.invoice');

    Route::get('/purchase-order/{id}/download-acknowledgment', [PurchaseController::class, 'downloadAcknowledgment'])
        ->name('download.acknowledgment');

    Route::get('/purchase-order/{id}/preview-invoice', [PurchaseController::class, 'previewInvoice'])
        ->name('preview.invoice');

    Route::get('/purchase-order/{id}/preview-acknowledgment', [PurchaseController::class, 'previewAcknowledgment'])
        ->name('preview.acknowledgment');
    /*
    |--------------------------------------------------------------------------
    | Profile Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
    /*
    |--------------------------------------------------------------------------
    | Superadmin routes
    |--------------------------------------------------------------------------
    */
    Route::post('/users', [RegisteredUserController::class, 'importUsers'])->name('import.users');
    Route::get('/billing_shipping', [BillingController::class, 'billingShipping'])->name('billing_shipping.index');
    Route::post(
        '/locations/update-default/{organization_id}',
        [BillingController::class, 'updateDefaultLocation']
    )->name('locations.update-default');

    /*
    |--------------------------------------------------------------------------
    | Reporting Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('report.index');
        Route::get('/purchase_order', [ReportController::class, 'purchaseOrderReport'])->name('report.purchase_order');
        Route::get('/picking', [ReportController::class, 'PickingReport'])->name('report.picking');
        Route::get('/lot-picking', [ReportController::class, 'lotPickingReport'])->name('report.lot_picking');
        Route::get('/audit', [ReportController::class, 'AuditReport'])->name('report.audit');
        Route::get('/inventory_adjust', [ReportController::class, 'inventoryAdjust'])->name('report.inventoryAdjust');
        Route::get('/inventory_transfer', [ReportController::class, 'inventoryTransfer'])->name('report.inventoryTransfers');
        Route::get('/product', [ReportController::class, 'productReport'])->name('report.product');
        Route::get('/cycle-count', [ReportController::class, 'CycleCount'])->name('report.cycleCount');
        Route::get('/invoices', [ReportController::class, 'invoiceReport'])->name('report.invoices');
        Route::get('/price-history', [ReportController::class, 'priceHistory'])->name('report.priceHistory');

        Route::get('/getinvoices/{id}', [ReportController::class, 'showInvoiceModal']);
    });
    Route::get('/purchase-orders/{po}/invoice/download', [App\Http\Controllers\ReportController::class, 'downloadPdf'])
        ->name('invoice.download');


    /*
    |--------------------------------------------------------------------------
    | Barcode Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/barcode', [BarcodeController::class, 'index'])->name('barcode.index');
    Route::get('/barcode/bulk-print', [BarcodeController::class, 'bulkprint'])->name('barcode.bulk-print');


    /*
    |--------------------------------------------------------------------------
    | Organization Setting Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('organization')->name('organization.')->group(function () {
        Route::get('/settings', [SettingController::class, 'settings'])->name('settings');
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/categories', [SettingController::class, 'categories'])->name('categories');
            Route::get('/inventory-adjustment', [SettingController::class, 'inventory_adjustment'])->name('inventory_adjust');
            Route::get('/inventory-transfer', [SettingController::class, 'inventory_transfer'])->name('inventory_transfer');
            Route::get('/organization', [SettingController::class, 'organization_settings'])->name('organization_settings');
            Route::get('/manufacturer', [SettingController::class, 'manufacturer'])->name('manufacturer');
            Route::get('/roles', [SettingController::class, 'roles'])->name('roles');
            Route::get('/general', [SettingController::class, 'general_settings'])->name('general_settings');
            Route::put('/general', [SettingController::class, 'general_settings_update'])->name('general_settings.update');
            Route::get('/customer', [SettingController::class, 'customer_settings'])->name('customer');
            Route::get('/cycle-counts', [SettingController::class, 'cycle_counts'])->name('cycle_counts');
            Route::get('/field-reps', [SettingController::class, 'field_reps'])->name('field-reps');
        });
    });
    Route::post('/cycle-counts/products', [SettingController::class, 'getProducts'])->name('cycle-counts.products');

    Route::get('/patients', [PatientController::class, 'index'])->name('patient.index');
    Route::post('/patients', [PatientController::class, 'importPatients'])->name('import.patients');
    Route::get('/patients/{id}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients-data', [PatientController::class, 'getPatientsData']);
    // In your web.php routes
    Route::get('/patients/{patient}/prescribe-component', [PatientController::class, 'getPrescribeComponent'])
        ->name('patients.prescribe-component');

    // Approve an organization access request
    Route::post('/medical-rep/request/{id}/approve', [MedicalRepController::class, 'approveRequest'])
        ->name('medical_rep.organization.request.approve');

    // Reject an organization access request
    Route::post('/medical-rep/request/{id}/reject', [MedicalRepController::class, 'rejectRequest'])
        ->name('medical_rep.organization.request.reject');

    // View list of requests made by the logged-in medical rep
    Route::get('/my-requests', [MedicalRepController::class, 'myRequests'])
        ->middleware('auth')
        ->name('medical_rep.requests');


    Route::prefix('pos')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('pos.sales.index');
        Route::get('/create', [PosController::class, 'create'])->name('pos.sales.create');
        Route::post('/store', [PosController::class, 'store'])->name('pos.sales.store');
        Route::get('/product/search', [PosController::class, 'search'])->name('pos.product.search');
        Route::get('/customer/search', [PosController::class, 'customerSearch'])->name('customer.search');
        Route::get('/inventory/stock-counts', [PosController::class, 'stockCount']);
        Route::put('/inventory/stock-counts/{id}', [PosController::class, 'stockCount-update']);
        Route::get('/customers/search', [PosController::class, 'searchCustomers'])
            ->name('pos.customers.find');
    });


});
