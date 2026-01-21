<?php

namespace App\Services\Edi;

use App\Models\FlaggedPo;
use Illuminate\Support\Facades\Log;

class EdiFlaggedService
{
    /**
     * Process an Flagged EDI file
     *
     * @param string $filePath
     * @return bool
     */
    public function process(string $filePath): bool
    {
        Log::info("📄 Processing Flagged file: $filePath");

        try {
            $content = file_get_contents($filePath);
            if (!$content) {
                throw new \Exception("Empty or unreadable file: $filePath");
            }

            $success = $this->processInboundSaveFiles($filePath);

            if (!$success) {
                throw new \Exception("Failed to parse file: $filePath");
            }
            Log::debug("Flagged Content Preview: " . substr($content, 0, 200));

            // If success
            Log::info("✅ Successfully processed Flagged file: $filePath");
            return true;

        } catch (\Exception $e) {
            Log::error("❌ Failed to process Flagged file: $filePath. Error: " . $e->getMessage());
            return false;
        }
    }
    public function processInboundSaveFiles($filePath)
    {
        Log::info('Entered processInboundSaveFiles');

        try {
            $fileContents = file_get_contents($filePath);
            $fileContents = preg_replace('/\s+/', '', $fileContents);
            $segments = explode("~", $fileContents);

            Log::info('Processing Segments');
            $poNumber = null; // Initialize variable

            foreach ($segments as $segment) {
                $segment = trim($segment); // Clean up segment
                $elements = explode('*', $segment);

                if (count($elements) > 0) {
                    $segmentIdentifier = $elements[0];

                    switch ($segmentIdentifier) {
                        case "BEG":
                            $poNumber = $elements[3] ?? null; // Safely access element
                            Log::info("Purchase order number: $poNumber");

                            // if ($poNumber) {
                            //     try {
                            //         // This will only create if a record with the same purchase_order doesn't exist
                            //         FlaggedPo::firstOrCreate(
                            //             ['purchase_order' => $poNumber], // attributes to check for existence
                            //             ['is_inbound_save' => true]      // attributes to set if creating
                            //         );
                            //     } catch (\Exception $e) {
                            //         Log::error("Failed to create flagged PO: " . $e->getMessage());
                            //     }
                            // }
                            break;
                    }

                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to process $filePath in processInboundSaveFiles: " . $e->getMessage());
        }
    }
}
