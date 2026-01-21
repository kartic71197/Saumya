<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
{
    use Queueable;

    protected $purchaseOrder;
    protected $oldStatus;
    protected $newStatus;
    protected $actor; // ADD THIS

    /**
     * We pass order + status info when creating notification
     */
    public function __construct($purchaseOrder, $oldStatus, $newStatus, $actor = null)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
         $this->actor = $actor ?? auth()->user(); // NEW
    }

    /**
     * We only want database notifications for now
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Data that will be stored in notifications table
     * Defines WHAT to store in the database
     * This array gets saved as JSON in notifications.data column
     *  Stores order + status change details in a structured way
     *  Builds user-friendly messages for create vs status update
     *  Ensures superadmins see the organization badge
     */
    public function toDatabase($notifiable): array
    {

        $orgName = $this->purchaseOrder->organization?->name;
        // If oldStatus is null, it's a new order
        if (is_null($this->oldStatus)) {
            $message = "New Order {$this->purchaseOrder->purchase_order_number} created with status: {$this->newStatus}";
        } else {
            $message = "Order {$this->purchaseOrder->purchase_order_number} status changed from {$this->oldStatus} to {$this->newStatus}";
        }
        return [
            'type' => 'order_status_updated',
            //  Superadmin only → used as badge/title-top
            'organization_name' => $notifiable->role_id == 1 ? $orgName : null,
            'organization_id' => $notifiable->role_id == 1
                ? $this->purchaseOrder->organization_id
                : null,
            'title' => is_null($this->oldStatus) ? 'New Order Created' : 'Order Status Updated',
            'message' => $message,
            'purchase_order_id' => $this->purchaseOrder->id,
            'purchase_order_number' => $this->purchaseOrder->purchase_order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'is_new_order' => is_null($this->oldStatus),
            // Adding actor info
            'actor_name' => $this->actor?->name ?? '',
        ];

    }
}
