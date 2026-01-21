<?php

namespace App\Services\Edi;

use App\Models\Edi855;
use App\Models\FlaggedPo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Facades\Log;

class Edi855Service
{
    /**
     * Process an 855 EDI file
     *
     * @param string $filePath
     * @return bool
     */
    public function process(string $filePath): bool
    {
        Log::info("📄 Processing 855 file: $filePath");

        try {
            $content = file_get_contents($filePath);
            if (!$content) {
                throw new \Exception("Empty or unreadable file: $filePath");
            }

            //TODO: Replace this with your actual 855 parsing logic
            // Example: parse invoice and save to database
            Log::debug("855 Content Preview: " . substr($content, 0, 200));

            $success = $this->processEDI($filePath);

            if (!$success) {
                throw new \Exception("Failed to parse file: $filePath");
            }

            // If success
            Log::info("✅ Successfully processed 855 file: $filePath");
            return true;

        } catch (\Exception $e) {
            Log::error("❌ Failed to process 855 file: $filePath. Error: " . $e->getMessage());
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

            foreach ($segments as $segment) {
                // Log::info("Processing segment: $segment");
                $segment = str_replace(' ', '', $segment);

                $elements = explode('*', $segment);
                if (count($elements) > 0) {
                    $segmentIdentifier = $elements[0];
                    switch ($segmentIdentifier) {
                        case "GS":
                            if ($elements[1] == 'PR') {
                                try {
                                    Log::info('EDI is PR');
                                    $this->process855($segments, $filePath);
                                } catch (\Exception $e) {
                                    Log::error("Failed to Parse $filePath at process855 function" . $e->getMessage());
                                    return;
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to Parse $filePath at process855 function" . $e->getMessage());
            return false;
        }
    }
    public function process855($segments, $filePath)
    {
        try {
            $ack855Data = [];

            $currentSTIndex = -1;

            foreach ($segments as $segment) {
                $elements = explode('*', $segment);

                if (count($elements) > 0) {
                    $segmentIdentifier = $elements[0];

                    switch ($segmentIdentifier) {

                        case "ST":
                            // Log::info("ST segment entered ");

                            $currentSTIndex++;

                            // Log::info("curretn index =>" . $currentSTIndex);

                            $ack855Data[$currentSTIndex] = [
                                'purchaseOrderNumber' => null,
                                'acknowledgmentTypes' => [],
                                'date' => null,
                                'billToParty' => 'N/A',
                                'shipTo' => 'N/A',
                                'quantityOrderedValues' => [],
                                'unitOrBasisForMeasurementValues' => [],
                                'unitPriceValues' => [],
                                'lineItemStatusCodeValues' => [],
                                'acknowledgedQuantityValues' => [],
                                'acknowledgedUnit' => [],
                                'descriptionValues' => [],
                                'product_code' => [],
                            ];
                            break;

                        case "BAK":
                            if (count($elements) > 1) {
                                $ack855Data[$currentSTIndex]['purchaseOrderNumber'] = $elements[3] ?? null;
                                $rawDate = $elements[4] ?? null;
                                if (strlen($rawDate) == 8) {
                                    $ack855Data[$currentSTIndex]['date'] = substr($rawDate, 0, 4) . '-' . substr($rawDate, 4, 2) . '-' . substr($rawDate, 6, 2);
                                }
                            }
                            break;

                        case "N1":
                            if (count($elements) > 2) {
                                $n101 = $elements[1] ?? null;
                                $n102 = $elements[2] ?? null;
                                if ($n101 == "BT") {
                                    $ack855Data[$currentSTIndex]['billToParty'] = $n102;
                                } elseif ($n101 == "ST") {
                                    $ack855Data[$currentSTIndex]['shipTo'] = $n102;
                                }
                            }
                            break;

                        case "PO1":
                            if (count($elements) > 1) {
                                $ack855Data[$currentSTIndex]['quantityOrderedValues'][] = $elements[2] ?? null;
                                $ack855Data[$currentSTIndex]['unitOrBasisForMeasurementValues'][] = $elements[3] ?? null;
                                $ack855Data[$currentSTIndex]['unitPriceValues'][] = $elements[4] ?? null;
                                $ack855Data[$currentSTIndex]['lineItemStatusCodeValues'][] = $elements[6] ?? null;
                                $ack855Data[$currentSTIndex]['product_code'][] = $elements[7] ?? null;
                            }
                            break;

                        case "PID":
                            if (count($elements) > 5) {
                                $ack855Data[$currentSTIndex]['descriptionValues'][] = $elements[5] ?? null;
                            }
                            break;

                        case "ACK":
                            if (count($elements) > 1) {
                                $acknowledgmentCode = $elements[1] ?? null;
                                $acknowledgmentType = $this->getAcknowledgmentType($acknowledgmentCode);
                                logger('ack code added to variable: ' . $acknowledgmentType);
                                $acknowledgedQuantity = $elements[2] ?? null;
                                $ack855Data[$currentSTIndex]['acknowledgmentTypes'][] = $acknowledgmentType ?? null;
                                Log::info('ACK type array', ['data' => $ack855Data[$currentSTIndex]['acknowledgmentTypes']]);
                                $ack855Data[$currentSTIndex]['acknowledgedQuantityValues'][] = $acknowledgedQuantity ?? null;
                                $ack855Data[$currentSTIndex]['acknowledgedUnit'][] = $elements[3] ?? null;
                            }
                            break;

                        default:
                            break;
                    }
                }
            }

            Log::info("Insert the data into the Ack855 table for each ST segment");
            foreach ($ack855Data as $data) {
                // Log::info($data);
                $this->insertIntoAck855(
                    $data['purchaseOrderNumber'],
                    $data['acknowledgmentTypes'],
                    $data['date'],
                    $data['billToParty'],
                    $data['shipTo'],
                    $data['quantityOrderedValues'],
                    $data['unitOrBasisForMeasurementValues'],
                    $data['unitPriceValues'],
                    $data['lineItemStatusCodeValues'],
                    $data['acknowledgedQuantityValues'],
                    $data['descriptionValues'],
                    $data['acknowledgedUnit'],
                    $data['product_code'],
                    $filePath
                );
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Error in Process855: " . $e->getMessage());
            return false;
        }
    }
    public function getAcknowledgmentType($acknowledgmentCode)
    {
        logger('ack code received: ' . $acknowledgmentCode);
        if ($acknowledgmentCode == "AA") {
            return "(AA) Item Accepted, Order Forwarded to Alternate Supplier";
        } elseif ($acknowledgmentCode == "AC") {
            return "(AC) Item Accepted and Shipped";
        } elseif ($acknowledgmentCode == "BP") {
            return "(BP) Item Accepted and Partial Shipment, Balance Background";
        } elseif ($acknowledgmentCode == "IA") {
            return "(IA) Item Accepted";
        } elseif ($acknowledgmentCode == "IB") {
            return "(IB) Item Backordered";
        } elseif ($acknowledgmentCode == "IP") {
            return "(IP) Item Accepted, Price changed";
        } elseif ($acknowledgmentCode == "IQ") {
            return "(IQ) Item Accepted, Quant Changed";
        } elseif ($acknowledgmentCode == "IR") {
            return "(IR) Item Rejected";
        } else {
            return "Unknown";
        }
    }

    public function insertIntoAck855(
        $purchaseOrderNumber,
        $acknowledgmentTypes,
        $date,
        $billToParty,
        $shipTo,
        $quantityOrderedValues,
        $unitOrBasisForMeasurementValues,
        $unitPriceValues,
        $lineItemStatusCodeValues,
        $acknowledgedQuantityValues,
        $descriptionValues,
        $acknowledgedUnit,
        $product_code,
        $filePath
    ) {
        try {
            $flaggedPO = FlaggedPo::where('purchase_order', $purchaseOrderNumber)->first();
            if ($flaggedPO) {
                $flaggedPO->delete();
            }

            $po = PurchaseOrder::where('purchase_order_number', $purchaseOrderNumber)->first();

            if (!$po) {
                Log::error("❌ PurchaseOrder not found for PO#: {$purchaseOrderNumber}");
                return false; // or continue / throw based on your flow
            }
            for ($key = 0; $key < count($acknowledgmentTypes); $key++) {
                logger('Looping line item for PO#: ' . $purchaseOrderNumber . ', Item#: ' . ($key + 1) . ' ack: ' . $acknowledgmentTypes[$key]);
                try {
                    Log::info("Inserting line item for PO#: $purchaseOrderNumber, Item#: " . ($key + 1));
                    Edi855::create([
                        'purchase_order' => $purchaseOrderNumber,
                        'ack_date' => $date ?? date('H:i:s'),
                        'bill_to' => $billToParty,
                        'ship_to' => $shipTo,
                        'product_name' => (string) ($descriptionValues[$key] ?? null),
                        'product_code' => (string) ($product_code[$key] ?? null),
                        'ordered_qty' => (int) ($quantityOrderedValues[$key] ?? null),
                        'ordered_unit' => (string) ($unitOrBasisForMeasurementValues[$key] ?? null),
                        'unit_price' => (int) ($unitPriceValues[$key] ?? null),
                        'ack_qty' => (int) ($acknowledgedQuantityValues[$key] ?? null),
                        'ack_unit' => (string) ($acknowledgedUnit[$key] ?? null),
                        'ack' => $acknowledgmentTypes[$key] ?? null,
                        'ack_type' => (string) ($acknowledgmentTypes[$key] ?? null), // new column
                        'file_name' => basename($filePath)
                    ]);

                    PurchaseOrder::where('purchase_order_number', $purchaseOrderNumber)
                        ->update(['note' => 'Purchase order is Acknowledged by the Supplier.']);

                    $updateData = [];

                    if (isset($acknowledgmentTypes[$key])) {
                        $updateData['product_status'] = (string) $acknowledgmentTypes[$key];
                    }
                    logger('$po->id: ' . $po->id);
                    if (!empty($updateData)) {
                        PurchaseOrderDetail::where('purchase_order_id', $po->id)
                            ->whereHas('product', function ($query) use ($product_code, $key) {
                                $query->where('product_code', $product_code[$key] ?? null);
                            })
                            ->update($updateData);
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to insert line item for PO#: $purchaseOrderNumber, Item#: " . ($key + 1) . " on line " . $e->getLine());
                    Log::error($e->getMessage());
                    continue;
                }
            }
            return true;

        } catch (\Exception $e) {
            Log::error("Error inserting into Ack855 table for PO#: $purchaseOrderNumber");
            Log::error("Error: " . $e->getMessage());
            return false;
        }
    }
}
