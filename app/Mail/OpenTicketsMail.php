<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OpenTicketsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tickets;
    public $noTickets;

    /**
     * Create a new message instance.
     */
    public function __construct(Collection $tickets, bool $noTickets = false)
    {
        $this->tickets = $tickets;
        $this->noTickets = $noTickets;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->noTickets
                ? '🎉 Great Work Team! No Open Tickets This Week'
                : '📋 Weekly Open Tickets Report',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.open-tickets-summary',
            with: [
                'tickets' => $this->tickets,
                'noTickets' => $this->noTickets,
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
