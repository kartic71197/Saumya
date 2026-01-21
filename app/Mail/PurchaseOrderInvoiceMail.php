<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Stripe\Invoice;

class PurchaseOrderInvoiceMail extends Mailable
{
    // REMOVED: use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public Invoice $invoice,
        public array $billFrom
    ) {
        // Constructor body can be empty
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf(
                'Invoice %s for Purchase Order #%s',
                $this->invoice->number,
                $this->purchaseOrder->purchase_order_number
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-orders.invoice',
            with: [
                'purchaseOrder' => $this->purchaseOrder,
                'invoice'       => $this->invoice,
                'billFrom'      => $this->billFrom,
                'paymentUrl'    => $this->invoice->hosted_invoice_url,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}