<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Edi810;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;


/**
 * Invoice Alert Notification
 * 
 * This notification is triggered when an invoice is uploaded/created for a Purchase Order.
 * It supports three sources: manual upload, EDI (Edi810), and Stripe invoices.
 * 
 * Usage:
 * - Manual: When user manually uploads an invoice file
 * - EDI: When an Edi810 document is received
 * - Stripe: When a Stripe invoice is created via PaymentObserver
 * 
 * The notification is stored in the database and displayed in the notifications UI.
 */

class InvoiceAlertNotification extends Notification
{
    use Queueable;

    protected PurchaseOrder $purchaseOrder;
    protected ?Edi810 $edi810;
    protected string $source;
    protected ?User $actor;


    /**
     * @param string $source manual | edi | stripe
     */

     /**
     * Create a new notification instance.
     * 
     * @param PurchaseOrder $purchaseOrder The purchase order receiving the invoice
     * @param string $source Source type: 'manual' | 'edi' | 'stripe'
     * @param Edi810|null $edi810 EDI document object (required when source is 'edi')
     * @param User|null $actor User who performed the action (defaults to authenticated user)
     */
    public function __construct(
        PurchaseOrder $purchaseOrder,
        string $source = 'manual',
        ?Edi810 $edi810 = null,
        ?User $actor = null
    ) {
        $this->purchaseOrder = $purchaseOrder;
        $this->source = $source;
        $this->edi810 = $edi810;
        $this->actor = $actor ?? auth()->user();

         Log::info('📦 InvoiceAlertNotification constructed', [
            'po_id' => $this->purchaseOrder->id,
            'source' => $this->source,
            'has_edi' => (bool) $this->edi810,
            'edi_id' => $this->edi810?->id,
            'actor_id' => $this->actor?->id,
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }


    /**
     * Get the mail representation of the notification.
     */
     /**
     * Get the array representation of the notification for database storage.
     * 
     * This method defines the data structure stored in the notifications table.
     * The data is later used to render the notification in the UI.
     * 
     * @param mixed $notifiable The entity being notified (typically a User model)
     * @return array<string, mixed> Notification data structure
     */
    public function toDatabase($notifiable): array
    {
        Log::info('🧾 InvoiceAlertNotification@toDatabase ENTERED', [
            'notifiable_id' => $notifiable->id,
            'source' => $this->source,
            'po_id' => $this->purchaseOrder->id,
            'edi_id' => $this->edi810?->id,
        ]);


        /** ==========================
         * MESSAGE BUILDING
         * ========================== */
        if ($this->source === 'edi' && $this->edi810) {
            $message = sprintf(
                'EDI Invoice %s received for Purchase Order %s',
                $this->edi810->invoice_number,
                number_format($this->edi810->total_amount_due / 100, 2),
                $this->purchaseOrder->purchase_order_number
            );
        } else {
            // MANUAL / STRIPE
            $message = sprintf(
                'Invoice uploaded for Purchase Order %s at %s',
                $this->purchaseOrder->purchase_order_number,
                optional($this->purchaseOrder->invoice_uploaded_at)?->format('d M Y H:i')
            );
        }

        return [
            'type' => 'invoice_uploaded',
            'source' => $this->source, // manual | edi | stripe
            'title' => 'Invoice Uploaded',
            'message' => $message,

            // PO info
            'purchase_order_id' => $this->purchaseOrder->id,
            'purchase_order_number' => $this->purchaseOrder->purchase_order_number,

            // EDI info (ONLY when exists)
            'invoice_number' => $this->edi810?->invoice_number,
            'invoice_amount' => $this->edi810
                ? round($this->edi810->total_amount_due / 100, 2)
                : null,
            'invoice_date' => $this->edi810?->invoice_date,

            // Org badge (superadmin)
            'organization_name' =>
                $notifiable->role_id == 1
                ? $this->purchaseOrder->organization?->name
                : null,
            'organization_id' =>
                $notifiable->role_id == 1
                ? $this->purchaseOrder->organization_id
                : null,

            'actor_name' => $this->actor?->name ?? 'System',

            \Log::info('✅ InvoiceAlertNotification@toDatabase COMPLETED', [
                'notifiable_id' => $notifiable->id,
                'po_id' => $this->purchaseOrder->id,
            ])
        ];



    }

}

