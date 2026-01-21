<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CloseTicketNotification extends Notification
{
    use Queueable;

    public $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
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

        // admin@demo -> null
        // drd -> null
        $ccEmails = User::where('location_id', $notifiable->location_id)
            ->where('location_id', '!=', null)
            ->where('id', '!=', $notifiable->id)
            ->where('is_active', 1)
            ->pluck('email')
            ->filter()
            ->unique()
            ->toArray();
        $ccEmails[] = config('app.email');
        return (new MailMessage)
            ->subject('✅ Your Support Ticket Has Been Resolved - #' . $this->ticket->id)
            ->cc($ccEmails)
            ->view('emails.ticket-closed', [
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
            'closed_at' => $this->ticket->updated_at,
        ];
    }
}