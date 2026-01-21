<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\InvoiceAlertNotification;

/**
 * Payment Observer
 * 
 * Watches for new payment records and sends notifications for Stripe invoices.
 * When a Stripe invoice is created, it alerts relevant users.
 */
class PaymentObserver
{
    /**
     * When a payment record is created
     * 
     * Only processes Stripe invoices (not manual or other payment types).
     * Sends notifications to:
     * 1. Company users (role 2+) - they see invoices for their company
     * 2. Superadmins (role 1) - they see all invoices
     */
    public function created(Payment $payment): void
    {
        // ONLY Stripe invoices
        if ($payment->provider !== 'stripe') {
            return;
        }

        $purchaseOrder = $payment->purchaseOrder;

        if (!$purchaseOrder) {
            return;
        }

        // SYSTEM actor (Stripe)
        $actor = null;

        // 1️⃣ Notify org users (role_id >= 2)
        User::where('organization_id', $purchaseOrder->organization_id)
            ->where('role_id', '>=', 2)
            ->each(
                fn($user) =>
                $user->notify(
                    new InvoiceAlertNotification(
                        $purchaseOrder,
                        'stripe',
                        null,
                        $actor
                    )
                )
            );

        // 2️⃣ Notify superadmins
        User::where('role_id', 1)
            ->each(
                fn($admin) =>
                $admin->notify(
                    new InvoiceAlertNotification(
                        $purchaseOrder,
                        'stripe',
                        null,
                        $actor
                    )
                )
            );
    }
}
