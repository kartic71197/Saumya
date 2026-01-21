<?php

namespace App\Services\Stripe;

use App\Mail\PurchaseOrderInvoiceMail;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Account;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

class StripeInvoiceService
{
    private const CURRENCY = 'usd';
    private const PROVIDER = 'stripe';

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Ensure Stripe customer exists or create a new one
     */
    private function ensureStripeCustomer($organization): Customer
    {
        // Try to retrieve existing customer
        if ($organization->stripe_id) {
            try {
                $customer = Customer::retrieve($organization->stripe_id);

                Log::info('Stripe customer retrieved', [
                    'organization_id' => $organization->id,
                    'stripe_id' => $customer->id,
                ]);

                return $customer;
            } catch (ApiErrorException $e) {
                Log::warning('Invalid Stripe customer, will recreate', [
                    'organization_id' => $organization->id,
                    'stripe_id' => $organization->stripe_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Create new customer
        try {
            $customerData = [
                'name' => $organization->name,
                'email' => $organization->billing_email ?? $organization->email,
                'metadata' => [
                    'organization_id' => $organization->id,
                ],
            ];

            $address = $this->formatAddress($organization);
            if ($address) {
                $customerData['address'] = $address;
            }

            $customer = Customer::create($customerData);

            $organization->update(['stripe_id' => $customer->id]);

            Log::info('Stripe customer created', [
                'organization_id' => $organization->id,
                'stripe_id' => $customer->id,
                'email' => $customerData['email'],
            ]);

            return $customer;
        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe customer', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create Stripe customer: ' . $e->getMessage());
        }
    }

    /**
     * Format organization address for Stripe
     */
    private function formatAddress($organization): ?array
    {
        if (!$organization->address) {
            return null;
        }

        return array_filter([
            'line1' => $organization->address,
            'line2' => $organization->address_line_2,
            'city' => $organization->city,
            'state' => $organization->state,
            'postal_code' => $organization->postal_code,
            'country' => $organization->country ?? 'US',
        ]);
    }

    /**
     * Check if invoice already exists for purchase order
     */
    private function hasExistingInvoice(PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->payments()
            ->where('provider', self::PROVIDER)
            ->whereNotIn('payment_status', ['failed', 'cancelled'])
            ->exists();
    }

    /**
     * Create and finalize Stripe invoice for a purchase order
     */
    public function createInvoice(PurchaseOrder $purchaseOrder): Invoice
    {
        $organization = $purchaseOrder->organization;

        if (!$organization) {
            Log::error('Organization not found for purchase order', [
                'purchase_order_id' => $purchaseOrder->id,
            ]);
            throw new \Exception('Organization not found for this purchase order');
        }

        // Prevent duplicate invoices
        if ($this->hasExistingInvoice($purchaseOrder)) {
            Log::warning('Attempted to create duplicate invoice', [
                'purchase_order_id' => $purchaseOrder->id,
                'organization_id' => $organization->id,
            ]);
            throw new \Exception('Invoice already exists for this purchase order');
        }

        Log::info('Starting invoice creation', [
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_order_number' => $purchaseOrder->purchase_order_number,
            'organization_id' => $organization->id,
            'amount' => $purchaseOrder->total,
        ]);

        $customer = $this->ensureStripeCustomer($organization);

        return DB::transaction(function () use ($purchaseOrder, $organization, $customer) {
            try {
                // Create draft invoice
                $invoiceData = [
                    'customer' => $customer->id,
                    'collection_method' => 'send_invoice',
                    'days_until_due' => config('services.stripe.invoice_days_until_due', 7),
                    'auto_advance' => false,
                    'metadata' => [
                        'purchase_order_id' => $purchaseOrder->id,
                        'purchase_order_number' => $purchaseOrder->purchase_order_number,
                        'organization_id' => $organization->id,
                    ],
                ];

                $invoice = Invoice::create($invoiceData);

                Log::info('Stripe invoice draft created', [
                    'invoice_id' => $invoice->id,
                    'purchase_order_id' => $purchaseOrder->id,
                ]);

                // Add invoice item
                $amountInCents = (int) round($purchaseOrder->total * 100);

                $invoiceItem = InvoiceItem::create([
                    'customer' => $customer->id,
                    'invoice' => $invoice->id,
                    'amount' => $amountInCents,
                    'currency' => self::CURRENCY,
                    'description' => "Purchase Order #{$purchaseOrder->purchase_order_number}",
                ]);

                Log::info('Invoice item created', [
                    'invoice_id' => $invoice->id,
                    'invoice_item_id' => $invoiceItem->id,
                    'amount_cents' => $amountInCents,
                    'amount_dollars' => $purchaseOrder->total,
                ]);

                // Finalize invoice to generate hosted_invoice_url
                $invoice = $invoice->finalizeInvoice();

                Log::info('Invoice finalized', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                    'hosted_invoice_url' => $invoice->hosted_invoice_url,
                    'invoice_pdf' => $invoice->invoice_pdf,
                ]);

                // Refresh to get latest data
                $invoice = Invoice::retrieve($invoice->id);

                // Create payment record
                $payment = Payment::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'organization_id' => $organization->id,
                    'amount' => $purchaseOrder->total,
                    'currency' => self::CURRENCY,
                    'payment_method' => self::PROVIDER,
                    'payment_status' => 'pending',
                    'provider' => self::PROVIDER,
                    'provider_invoice_id' => $invoice->id,
                    'provider_invoice_number' => $invoice->number,
                    'provider_payload' => $invoice->toArray(),
                ]);

                Log::info('Payment record created', [
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'amount' => $payment->amount,
                ]);

                return $invoice;

            } catch (ApiErrorException $e) {
                Log::error('Stripe API error during invoice creation', [
                    'purchase_order_id' => $purchaseOrder->id,
                    'organization_id' => $organization->id,
                    'error_type' => get_class($e),
                    'error_message' => $e->getMessage(),
                    'stripe_code' => $e->getStripeCode(),
                ]);

                throw new \Exception('Stripe API error: ' . $e->getMessage());
            } catch (\Exception $e) {
                Log::error('Unexpected error during invoice creation', [
                    'purchase_order_id' => $purchaseOrder->id,
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Send custom Laravel invoice email with detailed logging
     */
    public function sendInvoice(PurchaseOrder $purchaseOrder): void
    {
        Log::info('Starting invoice email process', [
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_order_number' => $purchaseOrder->purchase_order_number,
        ]);

        try {
            $invoice = $this->createInvoice($purchaseOrder);

            // Retrieve Stripe account details
            $stripeAccount = Account::retrieve();

            Log::info('Stripe account retrieved for invoice email', [
                'account_id' => $stripeAccount->id,
                'business_name' => $stripeAccount->business_profile?->name,
            ]);

            // Prepare bill from details
            $billFrom = [
                'name' => $stripeAccount->business_profile?->name ?? config('app.name'),
                'email' => $stripeAccount->email,
                'address' => $stripeAccount->company?->address,
            ];

            // Gather email recipients
            $recipients = array_values(array_filter([
                config('app.email'),
                $purchaseOrder->createdUser?->email,
                $purchaseOrder->organization?->email,
            ]));

            // $recipients = array_values(array_filter([
            //     config('app.email'),
            //     'kartic3289@gmail.com',
            //     'kartic.malhotra@healthshade.com',
            // ]));

            if (empty($recipients)) {
                throw new \Exception('No valid email recipients found');
            }

            // Log email details before sending
            Log::info('Preparing to send invoice email', [
                'purchase_order_id' => $purchaseOrder->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
                'recipients' => $recipients,
                'bill_from' => [
                    'name' => $billFrom['name'],
                    'email' => $billFrom['email'],
                    'address' => $billFrom['address'],
                ],
                'invoice_details' => [
                    'amount' => $purchaseOrder->total,
                    'currency' => self::CURRENCY,
                    'due_date' => $invoice->due_date ? date('Y-m-d', $invoice->due_date) : null,
                    'hosted_invoice_url' => $invoice->hosted_invoice_url,
                    'invoice_pdf' => $invoice->invoice_pdf,
                ],
            ]);

            // Send email
            Mail::to($recipients)->send(
                new PurchaseOrderInvoiceMail(
                    purchaseOrder: $purchaseOrder,
                    invoice: $invoice,
                    billFrom: $billFrom
                )
            );

            Log::info('Invoice email sent successfully', [
                'purchase_order_id' => $purchaseOrder->id,
                'invoice_id' => $invoice->id,
                'recipients_count' => count($recipients),
                'recipients' => $recipients,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'purchase_order_id' => $purchaseOrder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception('Failed to send invoice email: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve existing invoice for a purchase order
     */
    public function getInvoice(PurchaseOrder $purchaseOrder): ?Invoice
    {
        $payment = $purchaseOrder->payments()
            ->where('provider', self::PROVIDER)
            ->whereNotNull('provider_invoice_id')
            ->latest()
            ->first();

        if (!$payment) {
            return null;
        }

        try {
            return Invoice::retrieve($payment->provider_invoice_id);
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve Stripe invoice', [
                'purchase_order_id' => $purchaseOrder->id,
                'payment_id' => $payment->id,
                'invoice_id' => $payment->provider_invoice_id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}