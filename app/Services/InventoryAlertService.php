<?php

namespace App\Services;

use App\Models\Mycatalog;
use App\Models\User;
use App\Notifications\LowInventoryAlertNotification;
use Illuminate\Support\Facades\Log;

class InventoryAlertService
{
    /**
     * WHY THIS SERVICE WAS ADDED:
     * - To handle low-stock alert logic in one place
     * - Checks when stock falls below alert level
     * - Prevents sending duplicate notifications
     * - Sends alerts to superadmins and the user who changed stock
     */

    public function handleLowStockFromCatalog(Mycatalog $catalog, $previousQty = null): void
    {
        Log::info('LOW_STOCK_DEBUG: Checking catalog stock', [
            'product_id' => $catalog->product_id,
            'location_id' => $catalog->location_id,
            'previous_qty' => $previousQty,
            'current_qty' => $catalog->total_quantity,
            'alert_qty' => $catalog->alert_quantity,
        ]);

        // No alert quantity
        if ($catalog->alert_quantity === null) {
            return;
        }

        // Still above alert
        if ($catalog->total_quantity > $catalog->alert_quantity) {
            return;
        }

        // Prevent duplicate alerts
        if ($previousQty !== null && $previousQty <= $catalog->alert_quantity) {
            return;
        }

        // Get the actor (user who triggered the inventory change)
        $actor = auth()->user();

        // Superadmins
        User::where('role_id', 1)->each(function ($admin) use ($catalog, $actor) {
            $admin->notify(new LowInventoryAlertNotification($catalog, $actor));
        });

        //  Notify the actor (same as PurchaseOrderObserver)
        // $orgUser = auth()->user();

        // if ($orgUser && $orgUser->role_id != 1) {
        //     $orgUser->notify(new LowInventoryAlertNotification($catalog));
        // }

        /*
     |--------------------------------------------------------------------------
     | 2️ Notify ALL Admin users (role_id = 2) of SAME organization
     |--------------------------------------------------------------------------
     | Earlier: only the logged-in user (actor) was notified
     | Now: every admin under the same organization receives the alert
     */
        User::where('role_id', 2)
            ->where('organization_id', $catalog->organization_id)
            ->each(function ($orgUser) use ($catalog, $actor) {
                $orgUser->notify(new LowInventoryAlertNotification($catalog, $actor));
            });
    }
}
