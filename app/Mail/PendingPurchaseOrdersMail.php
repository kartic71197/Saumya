<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingPurchaseOrdersMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendingOrders;
    public $generatedAt;


    public function __construct($pendingOrders)
    {
        $this->pendingOrders = $pendingOrders;
        $this->generatedAt = now();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: Pending Purchase Orders older than 3 days',
            to: [config('app.email')],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pending_purchase_orders',
            with: [
                'pendingOrders' => $this->pendingOrders,
                'generatedAt' => $this->generatedAt,
                'total' => $this->pendingOrders->count(),
]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
