<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Notifications\InvoiceAlertNotification;
use App\Models\User;


class PurchaseOrderObserver
{
    /**
     * Handle the PurchaseOrder "created" event.
     * Runs automatically when a new PurchaseOrder is created
     * Sends notification for NEW orders
     * Notifies:
     *   • All superadmins
     *   • The user who created the order (if not superadmin)
     */
    public function created(PurchaseOrder $purchaseOrder): void
    {
        $oldStatus = null; // No previous status
        $newStatus = $purchaseOrder->status;
        $actor = auth()->user(); // Get the user who created the order

        // Notify superadmins (role_id 1) about NEW order
        User::where('role_id', 1)
            // ->where('organization_id', $purchaseOrder->organization_id)
            ->each(function ($admin) use ($purchaseOrder, $oldStatus, $newStatus, $actor) {
                $admin->notify(
                    new OrderStatusUpdatedNotification(
                        $purchaseOrder,
                        $oldStatus,
                        $newStatus,
                        $actor // Pass the actor
                    )
                );
            });
        //  notify the creator 
        // if ($purchaseOrder->createdUser && $purchaseOrder->createdUser->role_id != 1) {
        //     $purchaseOrder->createdUser->notify(
        //         new OrderStatusUpdatedNotification(
        //             $purchaseOrder,
        //             $oldStatus,
        //             $newStatus
        //         )
        //     );
        // }

        // 2️⃣ Notify ALL admins (role_id = 2) of SAME organization
        User::where('role_id', 2)
            ->where('organization_id', $purchaseOrder->organization_id)
            ->each(function ($orgUser) use ($purchaseOrder, $oldStatus, $newStatus, $actor) {
                $orgUser->notify(
                    new OrderStatusUpdatedNotification(
                        $purchaseOrder,
                        $oldStatus,
                        $newStatus,
                        $actor // Pass the actor
                    )
                );
            });
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     *  Runs automatically when a PurchaseOrder is updated
     * We only care about STATUS changes
     *  Sends notification when order status changes
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        $actor = auth()->user() ?? null; // Get the user who updated the status
        /* ==============================
        | INVOICE UPLOADED
        |==============================*/
        // Check if the invoice_path field changed and now has a value
        if (
            $purchaseOrder->wasChanged('invoice_path') &&
            !is_null($purchaseOrder->invoice_path)
        ) {
            \Log::info('🔔 Invoice observer triggered', [
                'po_id' => $purchaseOrder->id,
                'invoice_path' => $purchaseOrder->invoice_path,
                'actor_id' => $actor?->id,
            ]);

            /* ==============================
            | 1. NOTIFY COMPANY USERS
            |    Users in the same company (role 2+)
            | ==============================*/
            User::where('organization_id', $purchaseOrder->organization_id)
                ->where('role_id', '>=', 2)
                ->each(function ($user) use ($purchaseOrder, $actor) {

                    \Log::info('➡️ Sending InvoiceAlertNotification to org user', [
                        'user_id' => $user->id,
                        'po_id' => $purchaseOrder->id,
                    ]);

                    $user->notify(
                        new InvoiceAlertNotification(
                            $purchaseOrder,
                            'manual',
                            null,
                            $actor
                        )
                    );
                });

            /* ==============================
            | 2. NOTIFY SUPERADMINS
            |    Superadmins (role 1) get all notifications
            | ==============================*/
            User::where('role_id', 1)->each(function ($admin) use ($purchaseOrder, $actor) {

                \Log::info('➡️ Sending InvoiceAlertNotification to superadmin', [
                    'admin_id' => $admin->id,
                    'po_id' => $purchaseOrder->id,
                ]);

                $admin->notify(
                    new InvoiceAlertNotification(
                        $purchaseOrder,
                        'manual',
                        null,
                        $actor
                    )
                );
            });
        }



        /* ==============================
        | STATUS CHANGED
        |==============================*/

        // Check if status actually changed
        if (!$purchaseOrder->wasChanged('status')) {
            return;
        }

        $oldStatus = $purchaseOrder->getOriginal('status');
        $newStatus = $purchaseOrder->status;
        $actor = auth()->user(); // Get the user who updated the status

        //  Notify order creator (User side - role_id 2)
        // if (
        //     $purchaseOrder->createdUser &&
        //     $purchaseOrder->createdUser->role_id != 1 &&
        //     $purchaseOrder->createdUser->organization_id === $purchaseOrder->organization_id
        // ) {
        //     $purchaseOrder->createdUser->notify(
        //         new OrderStatusUpdatedNotification(
        //             $purchaseOrder,
        //             $oldStatus,
        //             $newStatus
        //         )
        //     );
        // }

        /*
         | WHY THIS CHANGE:
         | - Previously, only the user who performed the action was notified
         | - Business requirement: all admins of the same organization
         |   must be aware of low inventory events
         | - Super admins continue to receive global notifications
         | - Keeps notification logic centralized inside the service
         */

        //  Notify ALL admins (role_id = 2) of SAME organization
        User::where('role_id', 2)
            ->where('organization_id', $purchaseOrder->organization_id)
            ->each(function ($orgUser) use ($purchaseOrder, $oldStatus, $newStatus, $actor) {
                $orgUser->notify(
                    new OrderStatusUpdatedNotification(
                        $purchaseOrder,
                        $oldStatus,
                        $newStatus,
                        $actor // Pass the actor
                    )
                );
            });
        // 2️⃣ Notify superadmins (role_id 1)
        User::where('role_id', 1)->each(function ($admin) use ($purchaseOrder, $oldStatus, $newStatus, $actor) {
            $admin->notify(
                new OrderStatusUpdatedNotification(
                    $purchaseOrder,
                    $oldStatus,
                    $newStatus,
                    $actor // Pass the actor
                )
            );
        });

    }

    /**
     * Handle the PurchaseOrder "deleted" event.
     */
    public function deleted(PurchaseOrder $purchaseOrder): void
    {
        //
    }

    /**
     * Handle the PurchaseOrder "restored" event.
     */
    public function restored(PurchaseOrder $purchaseOrder): void
    {
        //
    }

    /**
     * Handle the PurchaseOrder "force deleted" event.
     */
    public function forceDeleted(PurchaseOrder $purchaseOrder): void
    {
        //
    }
}
