<?php

namespace App\Services\Edi;

use App\Models\FlaggedPo;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\Edi810;
use App\Models\Unit;
use Auth;
use Illuminate\Support\Facades\Log;
use App\Services\Pricing\PriceHistoryService;
use Monolog\LogRecord;

class Edi810Service
{
    /**
     * Process an 810 EDI file
     *
     * @param string $filePath
     * @return bool
     */
    public function process(string $filePath): bool
    {
        Log::info("📄 Processing 810 file: $filePath");

        try {
            $content = file_get_contents($filePath);
            if (!$content) {
                logger("Empty or unreadable file: $filePath");
                throw new \Exception("Empty or unreadable file: $filePath");
            }
            Log::debug("810 Content Preview: " . substr($content, 0, 200));
            $this->processEDI($filePath);
            // If success
            Log::info("✅ Successfully processed 810 file: $filePath");
            return true;

        } catch (\Exception $e) {
            Log::error("❌ Failed to process 810 file: $filePath. Error: " . $e->getMessage());
            return false;
        }
    }
    private function processEDI($filePath)
    {
        try {
            Log::info('Entered ProcessEDI');

            $fileContents = file_get_contents($filePath);

            // Log hex preview for debugging
            Log::debug('File hex preview: ' . bin2hex(substr($fileContents, 0, 500)));

            // Check for all possible segment terminators
            $has85 = strpos($fileContents, chr(133)) !== false;  // 0x85
            $has1E = strpos($fileContents, chr(30)) !== false;   // 0x1E
            $hasTilde = strpos($fileContents, "~") !== false;    // 0x7E

            Log::debug("Detection results - Has 0x85: " . ($has85 ? 'YES' : 'NO') .
                ", Has 0x1E: " . ($has1E ? 'YES' : 'NO') .
                ", Has ~: " . ($hasTilde ? 'YES' : 'NO'));

            // Determine segment terminator - check 0x85 first (Cardinal)
            if ($has85) {
                $segmentTerminator = chr(133);  // 0x85
            } elseif ($has1E) {
                $segmentTerminator = chr(30);   // 0x1E
            } else {
                $segmentTerminator = "~";       // 0x7E
            }

            Log::debug('Using Segment Terminator: 0x' . bin2hex($segmentTerminator));

            // Split by segment terminator
            $segments = explode($segmentTerminator, $fileContents);

            Log::info('Total segments found: ' . count($segments));

            foreach ($segments as $index => $segment) {
                $segment = trim($segment);

                if (empty($segment)) {
                    continue;
                }

                // Debug first few segments
                if ($index < 5) {
                    Log::info("Segment #$index: " . substr($segment, 0, 100));
                }

                $elements = explode('*', $segment);

                if (count($elements) > 0) {
                    $segmentIdentifier = trim($elements[0]);

                    if ($index < 5) {
                        Log::info("Segment #$index Identifier: '$segmentIdentifier'");
                    }

                    switch ($segmentIdentifier) {
                        case "GS":
                            if (isset($elements[1]) && trim($elements[1]) == 'IN') {
                                Log::info("Found GS*IN segment, calling process810");
                                try {
                                    $this->process810($segments, $filePath);
                                    return true;
                                } catch (\Exception $e) {
                                    Log::error("Failed to Parse $filePath at process810 function: " . $e->getMessage());
                                    return false;
                                }
                            }
                            break;
                    }
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to Parse $filePath: " . $e->getMessage());
            return false;
        }
    }
    public function process810($segments, $filePath)
    {

        Log::info('810 processing started');

        // Initialize variables
        $poNumbers = [];
        $invoiceNumbers = [];
        $invoiceDates = [];
        $shippedDates = [];
        $times = [];
        $productCodes = [];
        $productDescriptions = [];
        $units = [];
        $prices = [];
        $taxes = [];
        $scacs = [];
        $carrierInfo = [];
        $transportationMethod = [];
        $referenceQualifier = [];
        $referenceId = [];
        $totalAmountDue = [];
        $currentSTIndex = -1;
        $currentProductIndex = 0;

        foreach ($segments as $segment) {
            $elements = explode('*', $segment);
            if (count($elements) > 0) {
                $segmentIdentifier = $elements[0];

                switch ($segmentIdentifier) {
                    case "ST":
                        $currentSTIndex++;
                        $currentProductIndex = 0;
                        $poNumbers[$currentSTIndex] = null;
                        $invoiceNumbers[$currentSTIndex] = null;
                        $invoiceDates[$currentSTIndex] = null;
                        $shippedDates[$currentSTIndex] = null;
                        $times[$currentSTIndex] = null;
                        $productCodes[$currentSTIndex] = [];
                        $productDescriptions[$currentSTIndex] = [];
                        $units[$currentSTIndex] = [];
                        $qty[$currentSTIndex] = [];
                        $prices[$currentSTIndex] = [];
                        $taxes[$currentSTIndex] = [];
                        $taxPercent[$currentSTIndex] = [];
                        $scacs[$currentSTIndex] = null;
                        $carrierInfo[$currentSTIndex] = null;
                        $transportationMethod[$currentSTIndex] = null;
                        $referenceQualifier[$currentSTIndex] = null;
                        $referenceId[$currentSTIndex] = null;

                        break;

                    case "BIG":
                        if (count($elements) > 1) {
                            $invoiceNumbers[$currentSTIndex] = $elements[2] ?? null;
                            $poNumbers[$currentSTIndex] = $elements[4] ?? null;
                            $rawInvoiceDate = $elements[1] ?? null;
                            if (strlen($rawInvoiceDate) == 8) {
                                $invoiceDates[$currentSTIndex] = substr($rawInvoiceDate, 0, 4) . '-' . substr($rawInvoiceDate, 4, 2) . '-' . substr($rawInvoiceDate, 6, 2);
                            }
                            Log::info('BIG segment data', [
                                'poNumber' => $poNumbers[$currentSTIndex],
                            ]);
                        }
                        break;

                    case "DTM":
                        if (count($elements) > 1 && $elements[1] == '011') {
                            $rawShippedDate = $elements[2] ?? null;
                            if (strlen($rawShippedDate) == 8) {
                                $shippedDates[$currentSTIndex] = substr($rawShippedDate, 0, 4) . '-' . substr($rawShippedDate, 4, 2) . '-' . substr($rawShippedDate, 6, 2);
                            }
                            $rawTime = $elements[3] ?? null;
                            $times[$currentSTIndex] = date('H:i:s', strtotime($rawTime));
                        }
                        break;

                    case "IT1":
                        if (count($elements) > 1) {
                            $productCodes[$currentSTIndex][$currentProductIndex] = $elements[7] ?? null;
                            $qty[$currentSTIndex][$currentProductIndex] = $elements[2] ?? null;
                            $units[$currentSTIndex][$currentProductIndex] = $elements[3] ?? null;
                            $prices[$currentSTIndex][$currentProductIndex] = $elements[4] ?? null;
                            $currentProductIndex++;
                        }
                        break;

                    case "TXI":
                        if (count($elements) > 1 && $currentProductIndex > 0) {
                            $taxes[$currentSTIndex][$currentProductIndex - 1] = $elements[2] ?? 0; // Tax amount
                            $taxPercent[$currentSTIndex][$currentProductIndex - 1] = $elements[3] ?? 0; // Tax percentage 
                        }
                        break;


                    case "PID":
                        if (count($elements) > 1 && $currentProductIndex > 0) {
                            $productDescriptions[$currentSTIndex][$currentProductIndex - 1] = $elements[5] ?? null;
                        }
                        break;

                    case "TDS":
                        if (count($elements) > 1) {
                            $totalAmountDue[$currentSTIndex] = $elements[1] ?? null; // TDS01
                        }
                        break;


                    case "CAD":
                        if (count($elements) > 1) {
                            $scacs[$currentSTIndex] = $elements[4] ?? null;
                            $carrierInfo[$currentSTIndex] = $elements[5] ?? null;
                            $transportationMethod[$currentSTIndex] = $elements[1] ?? null;
                            $referenceQualifier[$currentSTIndex] = $elements[7] ?? null;
                            $referenceId[$currentSTIndex] = $elements[8] ?? null;

                        }
                        break;

                    default:
                        Log::info('Unhandled segment', ['segment' => $segment]);
                        break;
                }
            }
        }

        // Save to the unified table
        foreach ($poNumbers as $index => $poNumber) {
            foreach ($productCodes[$index] as $productIndex => $productCode) {
                try {
                    // Check if data already exists to avoid duplication
                    $existingRecord = Edi810::where('po_number', $poNumber)
                        ->where('invoice_number', $invoiceNumbers[$index])
                        ->where('product_code', $productCode)
                        ->first();

                    if (!$existingRecord) {

                        // Price update 
                        $incomingUnit = $units[$index][$productIndex] ?? null;
                        $incomingPrice = $prices[$index][$productIndex] ?? null;

                        $this->updateCatalogPrice($productCode, $incomingUnit, $incomingPrice, $poNumber);

                        // Price update ended 

                        $ack810 = new Edi810();
                        $ack810->po_number = $poNumber;
                        $ack810->invoice_number = $invoiceNumbers[$index];
                        $ack810->invoice_date = $invoiceDates[$index];
                        $ack810->shipped_date = $shippedDates[$index] ?? $invoiceDates[$index] ?? date('Y-m-d');
                        $ack810->time = $times[$index] ?? date('H:i:s');
                        $ack810->scac = $scacs[$index] ?? 'N/A';
                        $ack810->carrier_info = $carrierInfo[$index] ?? 'N/A';
                        $ack810->transportation_method = $transportationMethod[$index] ?? 'N/A';
                        $ack810->reference_qualifier = $referenceQualifier[$index] ?? 'N/A';
                        $ack810->reference_id = $referenceId[$index] ?? 'N/A';
                        $ack810->product_code = $productCode;
                        $ack810->product_description = $productDescriptions[$index][$productIndex] ?? null;
                        $ack810->unit = $units[$index][$productIndex] ?? null;
                        $ack810->qty = $qty[$index][$productIndex] ?? null;
                        $ack810->price = $prices[$index][$productIndex] ?? null;
                        $ack810->tax = $taxes[$index][$productIndex] ?? 0;
                        $ack810->taxPercent = $taxPercent[$index][$productIndex] ?? 0;
                        $ack810->total_amount_due = $totalAmountDue[$index] ?? null;
                        $ack810->file_name = basename($filePath);


                        $ack810->save();

                        Log::info('Saved record', ['poNumber' => $poNumber, 'productCode' => $productCode]);

                        $flaggedPO = FlaggedPo::where('purchase_order', $poNumber)->first();
                        if ($flaggedPO) {
                            $flaggedPO->delete();
                        }

                        PurchaseOrder::where('purchase_order_number', $poNumber)
                            ->update(['note' => 'Invoice received for this Purchase .']);
                    } else {
                        Log::info('Duplicate record skipped', ['poNumber' => $poNumber, 'productCode' => $productCode]);
                    }

                } catch (\Exception $e) {
                    Log::error('Error processing 810: ' . $e->getMessage());
                    continue;
                }
            }
        }

        Log::info('810 processing completed');
    }
    protected function updateCatalogPrice($productCode, $unitCode, $price, $poNumber)
    {

        try {

            $basePrice = $this->convertToBaseUnitPrice($productCode, $unitCode, $price, $poNumber);

            if (!$basePrice) {
                return false;
            }

            $purchaseOrder = PurchaseOrder::where('purchase_order_number', $poNumber)->first();
            if (!$purchaseOrder) {
                return false;
            }

            $organizationId = $purchaseOrder->organization_id;

            $product = Product::where('product_code', $productCode)
                ->where('organization_id', $organizationId)
                ->first();

            if (!$product) {
                return false;
            }

            if ((float) $product->cost !== (float) $basePrice) {

                app(PriceHistoryService::class)->changePrice(
                    $product,
                    $product->price ?? 0,   // existing selling price
                    $basePrice,             // new cost
                    Auth::id() ?? null       // fallback for system jobs
                );

                // Update current product snapshot
                $product->update([
                    'cost' => $basePrice,
                ]);
            }
            Log::info('Updated catalog price', [
                'productCode' => $productCode,
                'organization_id' => $organizationId,
                'basePrice' => $basePrice,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error updating catalog price: ' . $e->getMessage());
            return false;
        }

    }

    protected function convertToBaseUnitPrice($productCode, $unitCode, $price, $poNumber)
    {
        if (!$price || !$unitCode) {
            return $price;
        }

        $unit = Unit::where('unit_code', $unitCode)->first();
        $purchaseOrder = PurchaseOrder::where('purchase_order_number', $poNumber)->first();

        if (!$unit || !$purchaseOrder) {
            return $price;
        }

        $organization = $purchaseOrder->organization;

        $product = Product::where('product_code', $productCode)
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return $price;
        }

        $productUnits = ProductUnit::where('product_id', $product->id)->get();
        $targetUnit = $productUnits->firstWhere('unit_id', $unit->id);

        if (!$targetUnit) {
            Log::warning("Unit mapping not found", [
                'productCode' => $productCode,
                'unitCode' => $unitCode,
                'poNumber' => $poNumber
            ]);
            return $price;
        }

        return $price * $targetUnit->conversion_factor;
    }

}
