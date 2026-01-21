<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use App\Mail\OpenTicketsMail;
use Illuminate\Support\Facades\Log;


class SendWeeklyOpenTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-weekly-open-tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a weekly email to team with the open tickets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $openTickets = Ticket::where('status', 'open')->get();
        $notificationEmail = config('app.email');
        try {
            if ($openTickets->isEmpty()) {
                // Send "no open tickets" email
                Mail::to($notificationEmail)->send(new OpenTicketsMail(collect(), true));

                $this->info("No open tickets found. Good work team email sent to {$notificationEmail}");
                Log::info("Good work team email sent successfully to {$notificationEmail}");
            } else {
                // Send open tickets email
                Mail::to($notificationEmail)->send(new OpenTicketsMail($openTickets));

                $this->info("Weekly open tickets mail sent to {$notificationEmail}");
                Log::info("Weekly open tickets mail sent successfully to {$notificationEmail}");
            }
        } catch (\Exception $e) {
            $this->error('Failed to send weekly open tickets mail. Check logs.');
            Log::error('Mail sending failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}