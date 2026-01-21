<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class LowInventoryAlertNotification extends Notification
{
    use Queueable;

    protected $stock;

    /**
     * Create a new notification instance.
     */
    public function __construct($stock)
    {
        $this->stock = $stock;
    }

    /**
     * Notification channels
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * WHY toDatabase() IS USED HERE:
     * - Defines what data gets stored in the notifications table
     * - Builds the low-inventory notification after stock is updated
     * - Pulls related product and organization data for display
     * - Ensures superadmins see the organization badge
     * - Keeps UI logic simple by preparing all data upfront
     */
    public function toDatabase($notifiable): array
    {
        // Fetch first StockCount to get organization name
        $stockCount = $this->stock->stockCounts()->first();
        $orgName = $stockCount?->organization?->name;

        $product = $this->stock->product;

        Log::info('LOW_STOCK_DEBUG: Building notification payload', [
            'user_id' => $notifiable->id,
            'role_id' => $notifiable->role_id,
            'product_id' => $product->id,
            'current_qty' => $this->stock->total_quantity,
            'alert_qty' => $this->stock->alert_quantity,
            'organization' => $orgName,
        ]);

        return [
            'type' => 'low_inventory_alert',

            // Only superadmin sees org badge
            'organization_name' => $notifiable->role_id == 1 ? $orgName : null,
            'organization_id'   => $notifiable->role_id == 1 ? $stockCount->organization_id ?? null : null,

            'title'   => 'Low Inventory Alert',
            'message' => "Product {$product->product_name} is below alert level. Available: {$this->stock->total_quantity}",

            // Keep all other fields from Mycatalog
            'product_id'       => $product->id,
            'product_name'     => $product->product_name,
            'location_id'      => $this->stock->location_id,
            'current_quantity' => $this->stock->total_quantity,
            'alert_quantity'   => $this->stock->alert_quantity,
            'par_quantity'     => $this->stock->par_quantity,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
