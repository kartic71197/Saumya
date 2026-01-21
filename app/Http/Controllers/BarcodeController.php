<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorHTML;

class BarcodeController extends Controller
{
    public function index()
    {
        return view('organization.barcode.index');
    }

    public function bulkprint()
    {
        logger('reached at the backend');
        $products = session()->get('products_to_print', []);

        if (empty($products)) {
             logger('noproduct found for printing');
            return redirect()->back()->with('error', 'No products selected for printing.');
        }

        // Generate barcodes for each product
        $generator = new BarcodeGeneratorHTML();

        $productsWithBarcodes = collect($products)->map(function ($product) use ($generator) {

            // fetch category name from DB
           $categoryName = Product::with('category')
            ->find($product['id'])
            ?->category?->category_name ?? 'N/A';

            // Generate barcode from product code or create a numeric barcode
            $barcodeData = $this->generateBarcodeData($product['code']);

            try {
                $barcodeHtml = $generator->getBarcode($barcodeData, $generator::TYPE_CODE_128);
            } catch (\Exception $e) {
                // Fallback if barcode generation fails
                $barcodeHtml = $generator->getBarcode($product['id'], $generator::TYPE_CODE_128);
            }

            return [
                'id' => $product['id'],
                'name' => $product['name'],
                'code' => $product['code'],
                'category_name' => $categoryName,
                'barcode_data' => $barcodeData,
                'barcode_html' => $barcodeHtml
            ];
        });

        // Clear the session after getting the data
        session()->forget('products_to_print');

        return view('barcode.print', compact('productsWithBarcodes'));
    }

    private function generateBarcodeData($productCode)
    {
        // Remove any non-alphanumeric characters and convert to uppercase
        $cleaned = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $productCode));

        // If empty after cleaning, use a default pattern
        if (empty($cleaned)) {
            $cleaned = 'PROD' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        // Ensure it's not too long for barcode
        return substr($cleaned, 0, 20);
    }
}