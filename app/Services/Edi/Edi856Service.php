<?php

namespace App\Services\Edi;

use App\Models\Edi856;
use App\Models\FlaggedPo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Facades\Log;

class Edi856Service
{
    /**
     * Process an 856 EDI file
     *
     * @param string $filePath
     * @return bool
     */
    public function process(string $filePath): bool
    {
        Log::info("📄 Processing 856 file: $filePath");

        try {
            $content = file_get_contents($filePath);
            if (!$content) {
                throw new \Exception("Empty or unreadable file: $filePath");
            }

            $success = $this->processEDI($filePath);

            if (!$success) {
                throw new \Exception("Failed to parse file: $filePath");
            }
            Log::debug("856 Content Preview: " . substr($content, 0, 200));

            // If success
            Log::info("✅ Successfully processed 856 file: $filePath");
            return true;

        } catch (\Exception $e) {
            Log::error("❌ Failed to process 856 file: $filePath. Error: " . $e->getMessage());
            return false;
        }
    }
    private function processEDI($filePath)
    {
        try {

            Log::info('Entered ProcessEDI');
            $fileContents = file_get_contents($filePath);
            $fileContents = preg_replace('/\s+/', '', $fileContents);

            $segments = explode("~", $fileContents);
            Log::info('Entered Segments');


            foreach ($segments as $segment) {
                $segment = str_replace(' ', '', $segment);

                $elements = explode('*', $segment);
                if (count($elements) > 0) {
                    $segmentIdentifier = $elements[0];


                    switch ($segmentIdentifier) {

                        case "GS":
                            if ($elements[1] == 'SH') {
                                try {
                                    Log::info('EDI is SH');
                                    $success = $this->process856($segments, $filePath);
                                    if (!$success) {
                                        Log::error("Failed to process 856 file: $filePath");
                                        return false;
                                    }
                                } catch (\Exception $e) {
                                    Log::error("Failed to Parse $filePath at process856 function" . $e->getMessage());
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
    public function process856($segments, $filePath)
    {
        // Log::info(' SH is hitted');

        $poNumber = [];
        $internalRefNumber = [];
        $date = [];
        $time = [];
        $SCAC = [];
        $carrier = [];
        $invoiceNumber = [];
        $product_code = [];
        $product_des = [];
        $unitShipped = [];
        $units = [];
        $status = [];
        $shippedDate = [];
        foreach ($segments as $segment) {
            $elements = explode('*', $segment);

            if (count($elements) > 0) {
                $segmentIdentifier = $elements[0];

                switch ($segmentIdentifier) {

                    case "PRF":
                        Log::info('PRF is hitted');

                        if (count($elements) > 1) {
                            $poNumber[] = $elements[1];
                            Log::info('PO Number: ' . $elements[1]);
                        }
                        break;

                    case "BSN":
                        if (count($elements) > 1) {
                            $internalRefNumber[] = $elements[2] ?? null;
                            $rawDate = $elements[3] ?? null;
                            if (strlen($rawDate) == 8) {
                                $date[] = substr($rawDate, 0, 4) . '-' . substr($rawDate, 4, 2) . '-' . substr($rawDate, 6, 2);
                            }
                            $rawtime = $elements[4] ?? null;
                            $time[] = date('H:i:s', strtotime($rawtime));
                        }
                        break;

                    case "TD5":
                        if (count($elements) > 1) {
                            $SCAC[] = $elements[3] ?? null;
                            $carrier[] = $elements[5] ?? null;
                        }
                        break;

                    case "REF":
                        if (count($elements) > 1) {
                            $ref01 = $elements[1];
                            if ($ref01 == 'CN') {
                                $invoiceNumber[] = $elements[2] ?? null;
                            }
                        }
                        break;
                    case "LIN":
                        if (count($elements) > 1) {
                            $product_code[] = $elements[5];
                        }
                        break;
                    case "PID":
                        if (count($elements) > 1) {
                            $product_des[] = $elements[5];
                        }
                        break;

                    case "SN1":
                        if (count($elements) > 1) {
                            $unitShipped[] = $elements[2];
                            $units[] = $elements[3];
                        }
                        break;

                    case "DTM":
                        if (count($elements) > 1) {
                            if ($elements[1] == '011') {
                                $status[] = 'Shipped';
                            }
                            $rawDate = $elements[2];
                            if (strlen($rawDate) == 8) {
                                $shippedDate[] = substr($rawDate, 0, 4) . '-' . substr($rawDate, 4, 2) . '-' . substr($rawDate, 6, 2);
                            }
                        }
                        break;
                    default:
                        break;
                }

            }
        }

        foreach ($internalRefNumber as $key => $number) {
            try {

                Log::info('Inserting EDI856 data into database for PO: ' . $poNumber[0]);
                $ack856 = new Edi856();
                $ack856->poNumber = $poNumber[$key];
                $ack856->internalRefNumber = $number;
                $ack856->date = $date[$key];
                $ack856->time = $time[$key];
                $ack856->SCAC = $SCAC[$key];
                $ack856->carrier = $carrier[$key];
                $ack856->invoiceNumber = $invoiceNumber[$key];
                $ack856->product_code = $product_code[$key];
                $ack856->product_desc = $product_des[$key];
                $ack856->unitShipped = $unitShipped[$key];
                $ack856->units = $units[$key];
                $ack856->status = $status[$key];
                $ack856->shippedDate = $shippedDate[$key];
                $ack856->file_name = basename($filePath);
                $ack856->save();

                // ---- Update purchase_orders with tracking link ----
                if (!empty($invoiceNumber[$key])) {
                    $trackingNumber = $invoiceNumber[$key];

                    // Example: UPS tracking link (adjust carrier logic if needed)
                    $trackingLink = "https://www.ups.com/track?track=yes&trackNums={$trackingNumber}&loc=en_US&requester=ST/trackdetails";
                    //https://www.ups.com/track?track=yes&trackNums=1Z7311000312332299&loc=en_US&requester=ST/trackdetails

                    $po = PurchaseOrder::where('purchase_order_number', $poNumber[$key])->first();

                    if ($po) {

                        $updatePoData = [];
                        // keep existing values if already set
                        if (empty($po->tracking_link)) {
                            $updatePoData['tracking_link'] = $trackingLink;
                        }
                        $updatePoData['note'] = 'Order is Shipped.';
                        if (!empty($updatePoData)) {
                            $po->update($updatePoData);
                        }
                        PurchaseOrderDetail::where('purchase_order_id', $po->id)
                            ->update([
                                'tracking_link' => $trackingLink
                            ]);

                        Log::info("Tracking link saved for PO {$poNumber[$key]}");
                    }
                }

                $flaggedPO = FlaggedPo::where('purchase_order', $poNumber[$key])->first();

                if ($flaggedPO) {
                    $flaggedPO->delete();
                    Log::info("FlaggedPO with purchase_order {$poNumber[$key]} deleted successfully.");
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                return false;

            }
        }
        return true;
    }
}
