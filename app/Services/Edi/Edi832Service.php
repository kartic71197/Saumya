<?php

namespace App\Services\Edi;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Edi832Service
{
    public function process(string $filePath): bool
    {
        Log::info("📄 Processing 832 file: $filePath");

        try {
            if (!file_exists($filePath) || filesize($filePath) === 0) {
                throw new \Exception("Empty or unreadable file: $filePath");
            }

            $success = $this->parseEDI832File($filePath);

            if (!$success) {
                throw new \Exception("Failed to parse file: $filePath");
            }

            Log::info("✅ Successfully processed 832 file: $filePath");

            // if (app()->environment('production')) {
            //     unlink($filePath);
            //     Log::info("🗑️ Deleted local file after parsing: $filePath");
            // }

            return true;
        } catch (\Exception $e) {
            Log::error("❌ Failed to process 832 file: $filePath. Error: " . $e->getMessage());
            return false;
        }
    }

    private function parseEDI832File(string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if (!$content) {
            Log::warning("EDI file is empty or unreadable: $filePath");
            return false;
        }

        $segments = explode('~', $content);
        $currentProductCode = null;

        foreach ($segments as $segment) {
            $elements = explode('*', trim($segment));
            $tag = $elements[0] ?? null;

            switch ($tag) {
                case 'LIN':
                    if (($elements[2] ?? null) === 'VC') {
                        $currentProductCode = $elements[3] ?? null;
                        Log::debug("Found product code: $currentProductCode");
                    }
                    break;

                case 'REF':
                    if (($elements[1] ?? null) === 'LI' && isset($elements[3]) && str_contains($elements[3], 'http')) {
                        $imageUrl = $elements[3];
                        if ($currentProductCode) {
                            DB::table('products')
                                ->where('product_code', $currentProductCode)
                                ->update(['image' => $imageUrl]);

                            Log::info("🖼️ Updated image for product code: $currentProductCode");
                        }
                        $currentProductCode = null;
                    }
                    break;
            }
        }

        return true;
    }
}
