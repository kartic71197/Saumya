<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrganizationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $organization;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $organization)
    {
        $this->user = $user;
        $this->organization = $organization;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Practice Created: {$this->organization->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-organization',
            with: [
                'userName' => $this->user->name,
                'practiceEmail' => $this->organization->email,
                'organizationName' => $this->organization->name,
                'createdAt' => $this->organization->created_at->format('M d, Y h:i A'),
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
