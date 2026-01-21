<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Invoice;
use Stripe\Customer;
use Stripe\Account;
use App\Models\Organization;
use App\Mail\InvoiceReminderMail;

/**
 * Class SendMonthlyInvoiceEmails
 *
 * Sends monthly Stripe invoice payment links to active organizations.
 * - Fetches OPEN invoices first
 * - Falls back to UPCOMING invoices
 * - Pulls Bill-From details dynamically from Stripe Account
 */
class SendMonthlyInvoiceEmails extends Command
{
    /**
     * Console command signature
     */
    protected $signature = 'billing:send-monthly-invoices';

    /**
     * Console command description
     */
    protected $description = 'Send monthly Stripe invoice payment links to organizations';

    /**
     * Execute the console command
     */
    public function handle()
    {
        // Set Stripe secret key
        Stripe::setApiKey(config('services.stripe.secret'));

        // Fetch active organizations with Stripe customers
        $organizations = Organization::whereNotNull('stripe_id')
            ->where('is_active', true)
            ->get();

        $this->info("Processing {$organizations->count()} organizations");

        /**
         * ----------------------------------------------------
         * BILL FROM (Seller) INFO — fetched ONCE from Stripe
         * ----------------------------------------------------
         */
        $account = Account::retrieve();

        $companyAddress = $account->company->address ?? null;

        $billerAddress = collect([
            $companyAddress->line1 ?? null,
            $companyAddress->line2 ?? null,
            implode(', ', array_filter([
                $companyAddress->city ?? null,
                $companyAddress->state ?? null,
                $companyAddress->postal_code ?? null,
            ])),
            $companyAddress->country ?? null,
        ])->filter()->implode('<br>');

        $billerName = $account->business_profile->name ?? config('app.name');
        $billerEmail = config('app.email');
        $billerPhone = $account->business_profile->support_phone ?? null;

        foreach ($organizations as $org) {
            try {
                /**
                 * ----------------------------------------------------
                 * 1️⃣ Get OPEN invoice (unpaid / failed)
                 * ----------------------------------------------------
                 */
                $openInvoices = Invoice::all([
                    'customer' => $org->stripe_id,
                    'status' => 'open',
                    'limit' => 1,
                ]);

                $invoice = $openInvoices->data[0] ?? null;

                /**
                 * ----------------------------------------------------
                 * 2️⃣ Fallback to UPCOMING invoice
                 * ----------------------------------------------------
                 */
                if (!$invoice) {
                    $invoice = Invoice::upcoming([
                        'customer' => $org->stripe_id,
                    ]);
                }

                // Safety check
                if (
                    !$invoice ||
                    !isset($invoice->hosted_invoice_url) ||
                    !$invoice->hosted_invoice_url
                ) {
                    $this->warn("Invoice not finalized yet for Org ID {$org->id}");
                    continue;
                }

                // Skip already paid invoices
                if ($invoice->status === 'paid') {
                    continue;
                }

                /**
                 * ----------------------------------------------------
                 * CUSTOMER INFO (from Stripe)
                 * ----------------------------------------------------
                 */
                $customer = Customer::retrieve($invoice->customer);

                $address = $customer->address;
                $customerAddress = collect([
                    $address->line1 ?? null,
                    $address->line2 ?? null,
                    implode(', ', array_filter([
                        $address->city ?? null,
                        $address->state ?? null,
                        $address->postal_code ?? null,
                    ])),
                    $address->country ?? null,
                ])->filter()->implode('<br>');

                /**
                 * ----------------------------------------------------
                 * FORMAT DATES
                 * ----------------------------------------------------
                 */
                $invoiceDate = date('F j, Y', $invoice->created);
                $dueDate = date('F j, Y', $invoice->due_date);

                /**
                 * ----------------------------------------------------
                 * LINE ITEMS
                 * ----------------------------------------------------
                 */
                $lineItems = [];

                foreach ($invoice->lines->data as $line) {
                    $lineItems[] = [
                        'description' => $line->description ?? 'N/A',
                        'period' => date('M j, Y', $line->period->start)
                            . ' – ' .
                            date('M j, Y', $line->period->end),
                        'quantity' => $line->quantity ?? 1,
                        'unit_price' => ($line->price->unit_amount ?? 0) / 100,
                        'amount' => $line->amount / 100,
                    ];
                }

                /**
                 * ----------------------------------------------------
                 * SEND EMAIL
                 * ----------------------------------------------------
                 */
                Mail::to($org->email)
                    ->cc(array_filter([
                        $billerEmail,
                        $customer->email,
                    ]))
                    ->send(
                        new InvoiceReminderMail(
                            organization: $customer->name,
                            invoiceAmount: $invoice->amount_due / 100,
                            invoiceUrl: $invoice->hosted_invoice_url,
                            invoiceNumber: $invoice->number,
                            invoiceDate: $invoiceDate,
                            dueDate: $dueDate,
                            customerName: $customer->name,
                            customerEmail: $customer->email,
                            customerAddress: $customerAddress,
                            lineItems: $lineItems,

                            // Bill From
                            billerName: $billerName,
                            billerAddress: $billerAddress,
                            billerEmail: $billerEmail,
                            billerPhone: $billerPhone,
                        )
                    );

                $this->info("Invoice email sent to {$org->billing_email}");

            } catch (\Throwable $e) {
                $this->error("Org ID {$org->id}: {$e->getMessage()}");
            }
        }

        $this->info('Monthly invoice emails completed');
    }
}
