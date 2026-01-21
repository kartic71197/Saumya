<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

/**
 * Class InvoiceReminderMail
 *
 * Renders a Stripe-style invoice email
 * with dynamic Bill-From & Bill-To information.
 */
class InvoiceReminderMail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $organization,
        public float  $invoiceAmount,
        public string $invoiceUrl,
        public string $invoiceNumber,
        public string $invoiceDate,
        public string $dueDate,
        public string $customerName,
        public string $customerEmail,
        public string $customerAddress,
        public array  $lineItems,

        // Bill From
        public string $billerName,
        public string $billerAddress,
        public string $billerEmail,
        public ?string $billerPhone,
    ) {}

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Monthly Plan Invoice')
            ->view('emails.invoice-reminder');
    }
}
