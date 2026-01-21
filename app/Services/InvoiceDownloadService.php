<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class InvoiceDownloadService
{
    /**
     * Download invoice file by Purchase Order ID and source type
     * 
     * This is the main entry point for invoice downloads.
     * Based on the source type, it calls the appropriate download method.
     * 
     * @param int $purchaseOrderId The ID of the purchase order
     * @param string $source Where the invoice came from: 'manual' | 'edi' | 'stripe'
     * @return mixed PDF file download response
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If PO not found
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException If source invalid
     */
    public function downloadByPurchaseOrderId(int $purchaseOrderId, string $source)
    {
        $po = PurchaseOrder::with(['edi810s', 'purchaseSupplier', 'organization'])
            ->findOrFail($purchaseOrderId);

        return match ($source) {
            'manual' => $this->downloadManualInvoice($po),
            'edi' => $this->downloadEdiInvoice($po),
            'stripe' => $this->downloadStripeInvoice($po),
            default => abort(404, 'Invalid invoice source'),
        };
    }

    /**
     * Download a manually uploaded invoice PDF
     * 
     * For invoices that users upload as PDF files directly.
     * Files are stored in the public storage disk.
     * 
     * @param PurchaseOrder $po The purchase order
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse PDF download
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException If file not found
     */

    protected function downloadManualInvoice(PurchaseOrder $po)
    {
        if (!$po->invoice_path || !Storage::disk('public')->exists($po->invoice_path)) {
            abort(404, 'Manual invoice file not found');
        }

        return Storage::disk('public')->download(
            $po->invoice_path,
            "Invoice-{$po->purchase_order_number}.pdf"
        );
    }

    /* ============================================
       EDI INVOICES (EDI 810 documents)
       ============================================ */

    /**
     * Download an EDI-generated invoice
     * 
     * For invoices received via EDI 810 documents.
     * Generates a PDF from the EDI data using a template.
     * 
     * @param PurchaseOrder $po The purchase order
     * @return \Illuminate\Http\Response PDF download
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException If no EDI data
     */

    protected function downloadEdiInvoice(PurchaseOrder $po)
    {
        if ($po->edi810s->isEmpty()) {
            abort(404, 'EDI invoice data not found');
        }

        $supplier = $po->purchaseSupplier;
        $organization = $po->organization;

        $pdf = Pdf::loadView('invoices.pdf', [
            'po' => $po,
            'supplier' => $supplier,
            'organization' => $organization
        ]);

        return $pdf->download("Invoice-{$po->purchase_order_number}.pdf");
    }

    /* ============================================
       STRIPE INVOICES (Stripe hosted)
       ============================================ */

    /**
     * Download a Stripe-hosted invoice PDF
     * 
     * For invoices created through Stripe payment system.
     * Downloads the PDF directly from Stripe's servers.
     * 
     * @param PurchaseOrder $po The purchase order
     * @return \Illuminate\Http\Response PDF download or JSON error
     */

    /**
     * Download a Stripe-hosted invoice PDF
     * 
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function downloadStripeInvoice(PurchaseOrder $po)
    {
        try {
            // Get Stripe service instance
            $stripeService = app(\App\Services\Stripe\StripeInvoiceService::class);

            // Get the Stripe invoice for this purchase order
            $invoice = $stripeService->getInvoice($po);
            // Check if invoice exists
            if (!$invoice) {
                return response()->json([
                    'error' => 'No Stripe invoice found',
                    'message' => 'This purchase order does not have a Stripe invoice.'
                ], 404);
            }

            // Check if PDF is available from Stripe
            if (!$invoice->invoice_pdf) {
                return response()->json([
                    'error' => 'PDF not available',
                    'message' => 'The invoice PDF is not yet available from Stripe.'
                ], 404);
            }
            // Download PDF from Stripe's CDN
            $response = Http::timeout(30)
                ->withOptions(['verify' => false])  // DEV ONLY - remove in production!
                ->get($invoice->invoice_pdf);

            // Check if download was successful
            if (!$response->successful()) {
                throw new \Exception('Stripe returned status: ' . $response->status());
            }

            // Return PDF to browser
            return response($response->body(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf(
                    'attachment; filename="Invoice-%s-%s.pdf"',
                    $po->purchase_order_number,
                    $invoice->number
                ),
            ]);
            } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Stripe invoice download failed', [
                'po_id' => $po->id,
                'po_number' => $po->purchase_order_number,
                'error' => $e->getMessage(),
            ]);

            // Return error response
            return response()->json([
                'error' => 'Download failed',
                'message' => 'Failed to download invoice: ' . $e->getMessage(),
            ], 500);
        }
    }
}
