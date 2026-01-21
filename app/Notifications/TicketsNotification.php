<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketsNotification extends Notification
{
    use Queueable;

    public $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ccEmails = User::where('location_id', $notifiable->location_id)
            ->where('id', '!=', $notifiable->id)
            ->where('organization_id',auth()->user()->organization_id)
            ->where('is_active', 1)
            ->pluck('email')
            ->filter()
            ->unique()
            ->toArray();
        $ccEmails[] = config('app.email');
        return (new MailMessage)
            ->subject('🎫 New Support Ticket Created - #' . $this->ticket->id)
            ->cc($ccEmails)
            ->view('emails.ticket-created', [
                'ticket' => $this->ticket,
                'user' => $notifiable
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_type' => $this->ticket->type,
            'ticket_priority' => $this->ticket->priority,
            'created_at' => $this->ticket->created_at,
            'user_name' => $notifiable->creatorUser->name ?? 'N/A',
            'organization' => $notifiable->organization->name ?? 'N/A',
        ];
    }
}