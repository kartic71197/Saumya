<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PendingOrderToClientsMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $orders;

    /**
     * Create a new message instance.
     */
    public function __construct(Collection $orders)
    {
        $this->orders = $orders;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pending Orders',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        logger()->info('Building PendingOrderToClientsMail with ' . $this->orders->count() . ' orders.');
        return new Content(
            view: 'emails.pending_order_to_clients', 
            with: [
                'orders' => $this->orders,
            ],
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