<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InvoiceDownloadService;
use App\Models\PurchaseOrder;


class NotificationController extends Controller
{
    /**
     * Show all notifications for logged-in user
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return back();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()
            ->unreadNotifications
            ->markAsRead();

        return back();
    }

     /**
     * Download invoice PDF file associated with a notification.
     * 
     * Handles invoice downloads triggered from notification actions.
     * Route: GET /notifications/invoices/{po}/{source}/download
     * Flow:
     * 1. User clicks "Download Invoice" in notification
     * 2. Route passes PO ID and source type
     * 3. InvoiceDownloadService handles the specific download logic
     * 4. Returns PDF file to browser
     *  * 
     * @see InvoiceDownloadService::downloadByPurchaseOrderId()
     */
    public function downloadInvoice($po, $source)
    {
        return app(InvoiceDownloadService::class)
            ->downloadByPurchaseOrderId($po, $source);
    }

}
