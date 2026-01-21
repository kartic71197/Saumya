<?php

namespace App\Observers;

use App\Models\Edi810;
use App\Models\User;
use App\Notifications\InvoiceAlertNotification;
use Illuminate\Support\Facades\Log;


/**
 * EDI810 Observer
 * 
 * Watches for new EDI 810 invoice documents and sends notifications.
 * When an EDI 810 (invoice) is received, it alerts relevant users.
 */
class Edi810Observer
{
    /**
     * When an EDI 810 invoice is created
     * 
     * Sends notifications to:
     * 1. Organization users (role 2+) - they see their own company's invoices
     * 2. Superadmins (role 1) - they see all invoices from all companies
     */
    public function created(Edi810 $edi810): void
    {
        Log::info('🧾 EDI810Observer@created ENTERED', [
            'edi810_id' => $edi810->id,
            'po_id' => $edi810->purchase_order_id,
        ]);

        $purchaseOrder = $edi810->purchaseOrder;
        if (!$purchaseOrder) {
            Log::warning('⚠️ EDI810 has NO purchase order', [
                'edi810_id' => $edi810->id,
            ]);
            return;
        }

        Log::info('🔗 EDI810 linked to PO', [
            'edi810_id' => $edi810->id,
            'po_id' => $purchaseOrder->id,
            'org_id' => $purchaseOrder->organization_id,
        ]);

        // SYSTEM ACTOR
        $actor = null;

        // Org users
        User::where('organization_id', $purchaseOrder->organization_id)
            ->where('role_id', '>=', 2)
            ->each(function ($user) use ($purchaseOrder, $edi810, $actor) {
                Log::info('➡️ Sending EDI InvoiceAlertNotification to org user', [
                    'user_id' => $user->id,
                    'edi810_id' => $edi810->id,
                    'po_id' => $purchaseOrder->id,
                ]);

                $user->notify(
                    new InvoiceAlertNotification(
                        $purchaseOrder,
                        'edi',
                        $edi810,
                        $actor
                    )
                );
            });

        // Superadmins
        User::where('role_id', 1)
            ->each(function ($admin) use ($purchaseOrder, $edi810, $actor) {
                Log::info('➡️ Sending EDI InvoiceAlertNotification to superadmin', [
                    'admin_id' => $admin->id,
                    'edi810_id' => $edi810->id,
                ]);

                $admin->notify(
                    new InvoiceAlertNotification(
                        $purchaseOrder,
                        'edi',
                        $edi810,
                        $actor
                    )
                );
            });

        Log::info('✅ EDI810Observer@created COMPLETED', [
            'edi810_id' => $edi810->id,
        ]);
    }


    /**
     * Handle the Edi810 "updated" event.
     */
    public function updated(Edi810 $edi810): void
    {
        //
    }

    /**
     * Handle the Edi810 "deleted" event.
     */
    public function deleted(Edi810 $edi810): void
    {
        //
    }

    /**
     * Handle the Edi810 "restored" event.
     */
    public function restored(Edi810 $edi810): void
    {
        //
    }

    /**
     * Handle the Edi810 "force deleted" event.
     */
    public function forceDeleted(Edi810 $edi810): void
    {
        //
    }
}
