<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Stripe\Stripe;
use Stripe\Invoice;
use Carbon\Carbon;

class InvoiceController extends Controller
{

    public function invoiceList(Request $request)
    {
        $user = auth()->user();

        Stripe::setApiKey(config('cashier.secret'));

        /**
         * Base Stripe query params
         */
        $params = [
            'limit' => 100,
        ];

        /**
         * Status filter
         */
        if ($request->filled('status')) {
            $params['status'] = $request->status;
        }

        /**
         * Date filters
         */
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $params['created'] = [];

            if ($request->filled('date_from')) {
                $params['created']['gte'] =
                    Carbon::parse($request->date_from)->startOfDay()->timestamp;
            }

            if ($request->filled('date_to')) {
                $params['created']['lte'] =
                    Carbon::parse($request->date_to)->endOfDay()->timestamp;
            }
        }

        /**
         * ORG USER → restrict invoices to organization
         */
        if ($user->role_id != 1) {
            $organization = $user->organization;

            if (!$organization || !$organization->stripe_id) {
                return view('stripe.index', [
                    'invoices' => collect(),
                    'stats' => $this->emptyStats(),
                ]);
            }

            $params['customer'] = $organization->stripe_id;
        }

        /**
         * Fetch invoices from Stripe
         */
        $stripeInvoices = Invoice::all($params);
        $invoices = collect($stripeInvoices->data);

        /**
         * Search filter (invoice number)
         */
        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $invoices = $invoices->filter(function ($invoice) use ($search) {
                return isset($invoice->number)
                    && str_contains(strtolower($invoice->number), $search);
            });
        }

        /**
         * Calculate statistics
         */
        $stats = $this->calculateStats($invoices);

        if ($user->role_id != 1) {
            $invoices = $invoices->filter(function ($invoice) {
                return in_array($invoice->status, ['paid', 'open']);
            });
        }

        logger('Invoice list viewed', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'invoice_count' => $invoices->count(),
        ]);

        return view('stripe.index', compact('invoices', 'stats'));
    }

    public function sendReminder(string $invoiceId)
    {
        Stripe::setApiKey(config('cashier.secret'));

        $invoice = Invoice::retrieve($invoiceId);

        if (!in_array($invoice->status, ['open', 'past_due'])) {
            return back()->with('error', 'Invoice cannot be reminded.');
        }
        $invoice->sendInvoice();

        return back()->with('success', 'Invoice reminder sent successfully.');
    }


    /**
     * Apply filters to invoice collection
     */
    protected function applyFilters($invoices, Request $request)
    {
        // Filter by status
        if ($request->filled('status')) {
            $invoices = $invoices->filter(function ($invoice) use ($request) {
                return $invoice->status === $request->status;
            });
        }

        // Filter by search (invoice number)
        if ($request->filled('search')) {
            $invoices = $invoices->filter(function ($invoice) use ($request) {
                $number = $invoice->number ?? '';
                return stripos($number, $request->search) !== false;
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $invoices = $invoices->filter(function ($invoice) use ($dateFrom) {
                $invoiceDate = $this->getInvoiceDate($invoice);
                return $invoiceDate && $invoiceDate->gte($dateFrom);
            });
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->endOfDay();
            $invoices = $invoices->filter(function ($invoice) use ($dateTo) {
                $invoiceDate = $this->getInvoiceDate($invoice);
                return $invoiceDate && $invoiceDate->lte($dateTo);
            });
        }

        return $invoices;
    }

    /**
     * Get invoice date (handles both Cashier and Stripe invoices)
     */
    protected function getInvoiceDate($invoice)
    {
        $isCashier = method_exists($invoice, 'date');

        if ($isCashier) {
            return $invoice->date();
        }

        return isset($invoice->created)
            ? Carbon::createFromTimestamp($invoice->created)
            : null;
    }

    /**
     * Calculate invoice statistics
     */
    protected function calculateStats($invoices)
    {
        $stats = [
            'total' => $invoices->count(),
            'paid' => 0,
            'unpaid' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'unpaid_amount' => 0,
        ];

        foreach ($invoices as $invoice) {
            $isCashier = method_exists($invoice, 'total');
            $amount = $isCashier
                ? (float) str_replace(['$', ','], '', $invoice->total())
                : ($invoice->amount_due ?? 0) / 100;

            $stats['total_amount'] += $amount;

            if ($invoice->status === 'paid') {
                $stats['paid']++;
                $stats['paid_amount'] += $amount;
            } else {
                $stats['unpaid']++;
                $stats['unpaid_amount'] += $amount;
            }
        }

        return $stats;
    }

    /**
     * Download invoice PDF
     */
    public function download($invoiceId)
    {
        $user = auth()->user();

        if ($user->role_id == 1) {
            // Super admin can download any invoice
            Stripe::setApiKey(config('cashier.secret'));
            $invoice = Invoice::retrieve($invoiceId);

            return redirect($invoice->invoice_pdf);
        }

        // Regular user - check if invoice belongs to their organization
        $organization = $user->organization;

        if (!$organization || !$organization->stripe_id) {
            abort(403, 'Unauthorized access');
        }

        $invoices = collect($organization->invoices());
        $invoice = $invoices->firstWhere('id', $invoiceId);

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        return redirect($invoice->invoice_pdf);
    }

    /**
     * View invoice details
     */
    public function show($invoiceId)
    {
        $user = auth()->user();

        if ($user->role_id == 1) {
            // Super admin can view any invoice
            Stripe::setApiKey(config('cashier.secret'));
            $invoice = Invoice::retrieve($invoiceId);
        } else {
            // Regular user - check if invoice belongs to their organization
            $organization = $user->organization;

            if (!$organization || !$organization->stripe_id) {
                abort(403, 'Unauthorized access');
            }

            $invoices = collect($organization->invoices());
            $invoice = $invoices->firstWhere('id', $invoiceId);

            if (!$invoice) {
                abort(404, 'Invoice not found');
            }
        }

        return view('stripe.show', compact('invoice'));
    }

    /**
     * Export invoices to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        // Get filtered invoices
        if ($user->role_id == 1) {
            Stripe::setApiKey(config('cashier.secret'));

            $params = ['limit' => 100];

            if ($request->filled('status')) {
                $params['status'] = $request->status;
            }

            if ($request->filled('date_from')) {
                $params['created']['gte'] = Carbon::parse($request->date_from)->startOfDay()->timestamp;
            }

            if ($request->filled('date_to')) {
                $params['created']['lte'] = Carbon::parse($request->date_to)->endOfDay()->timestamp;
            }

            $stripeInvoices = Invoice::all($params);
            $invoices = collect($stripeInvoices->data);

            if ($request->filled('search')) {
                $invoices = $invoices->filter(function ($invoice) use ($request) {
                    return stripos($invoice->number, $request->search) !== false;
                });
            }
        } else {
            $organization = $user->organization;

            if (!$organization || !$organization->stripe_id) {
                return redirect()->back()->with('error', 'No organization found');
            }

            $invoices = collect($organization->invoices());
            $invoices = $this->applyFilters($invoices, $request);
        }

        // Generate CSV
        $filename = 'invoices_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, ['Invoice Number', 'Amount', 'Status', 'Due Date', 'Created Date', 'Customer Email']);

            // Add data
            foreach ($invoices as $invoice) {
                $isCashier = method_exists($invoice, 'total');

                $amount = $isCashier
                    ? $invoice->total()
                    : number_format(($invoice->amount_due ?? 0) / 100, 2);

                $created = $isCashier
                    ? $invoice->date()->toDateString()
                    : Carbon::createFromTimestamp($invoice->created)->toDateString();

                $dueDate = null;
                if ($isCashier && $invoice->due_date) {
                    $dueDate = Carbon::createFromTimestamp($invoice->due_date)->toDateString();
                } elseif (!$isCashier && $invoice->due_date) {
                    $dueDate = Carbon::createFromTimestamp($invoice->due_date)->toDateString();
                }

                $email = $invoice->customer_email ?? 'N/A';

                fputcsv($file, [
                    $invoice->number ?? 'N/A',
                    $amount,
                    strtoupper($invoice->status),
                    $dueDate ?? 'N/A',
                    $created,
                    $email,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Resend invoice email
     */
    public function resend($invoiceId)
    {
        $user = auth()->user();

        if ($user->role_id != 1) {
            abort(403, 'Only administrators can resend invoices');
        }

        Stripe::setApiKey(config('cashier.secret'));

        try {
            $invoice = Invoice::retrieve($invoiceId);
            $invoice->sendInvoice();

            return redirect()->back()->with('success', 'Invoice email sent successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }

    /**
     * Mark invoice as paid (admin only)
     */
    public function markAsPaid($invoiceId)
    {
        $user = auth()->user();

        if ($user->role_id != 1) {
            abort(403, 'Only administrators can mark invoices as paid');
        }

        Stripe::setApiKey(config('cashier.secret'));

        try {
            $invoice = Invoice::retrieve($invoiceId);
            $invoice->pay();

            return redirect()->back()->with('success', 'Invoice marked as paid');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to mark invoice as paid: ' . $e->getMessage());
        }
    }

    /**
     * Void invoice (admin only)
     */
    public function void($invoiceId)
    {
        $user = auth()->user();

        if ($user->role_id != 1) {
            abort(403, 'Only administrators can void invoices');
        }

        Stripe::setApiKey(config('cashier.secret'));

        try {
            $invoice = Invoice::retrieve($invoiceId);
            $invoice->voidInvoice();

            return redirect()->back()->with('success', 'Invoice voided successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to void invoice: ' . $e->getMessage());
        }
    }
}