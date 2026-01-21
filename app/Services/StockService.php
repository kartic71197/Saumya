<?php

namespace App\Services;

use App\Models\Mycatalog;
use App\Models\ProductUnit;
use App\Models\StockCount;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Convert given quantity to base unit quantity
     */
    public function convertToBaseUnit($productId, $quantity, $unit)
    {
        // Get the product's base unit and conversion
        $productUnit = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $unit)
            ->first();

        if (!$productUnit) {
            throw new \Exception('Unit conversion not found for product ' . $productId . 'and unit is ' . $unit);
        }

        if ($productUnit->is_base_unit) {
            return $quantity;
        }

        return $quantity * $productUnit->conversion_factor;

    }

    /*
     * Update or create stock count for a given product + location
     */
    public function updateStock($productId, $locationId, $data = [])
    {

        logger("update stock services" . json_encode($data));
        logger('product ' . $productId);
        $quantity = $data['quantity'] ?? 0;

        if (isset($data['unit'])) {
            $quantity = $this->convertToBaseUnit($productId, $quantity, $data['unit']);
        }

        StockCount::updateOrCreate(
            [
                'product_id' => $productId,
                'location_id' => $locationId,
                'batch_number' => $data['batch_number'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
            ],
            [
                'on_hand_quantity' => $quantity,
            ]
        );

        $this->updateAvailableStock($productId, $locationId);

        return true;

    }

    /**
     * Add stock for a batch (purchase order, receiving, etc.)
     */
    public function addStock($productId, $locationId, $batchNumber, $expiryDate, $quantity, $unit = null)
    {
        return DB::transaction(function () use ($productId, $locationId, $batchNumber, $expiryDate, $quantity, $unit) {
            logger('Adding stock', [$productId, $locationId, $batchNumber, $expiryDate, $quantity, $unit]);

            // Convert to base units
            if ($unit) {
                $quantity = $this->convertToBaseUnit($productId, $quantity, $unit);
            }

            $existingBatch = StockCount::where('product_id', $productId)
                ->where('batch_number', $batchNumber)
                ->where('expiry_date', $expiryDate)
                ->where('location_id', $locationId)
                ->first();

            if ($existingBatch) {
                $existingBatch->on_hand_quantity += $quantity;
                $existingBatch->save();
            } else {
                $existingBatch = StockCount::create([
                    'product_id' => $productId,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                    'on_hand_quantity' => $quantity,
                    'location_id' => $locationId,
                ]);
            }

            // Recalculate total stock at this location
            $totalQty = StockCount::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->sum('on_hand_quantity');

            $this->updateAvailableStock($productId, $locationId, $totalQty);

            return $existingBatch;
        });
    }

    /**
     * Update alert and par quantities
     */
    public function updateAlertPar($productId, $locationId, $alertQty, $parQty)
    {
        return StockCount::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->update([
                'alert_quantity' => $alertQty,
                'par_quantity' => $parQty,
            ]);
    }

    /**
     * Update total available stock for a product at a location
     * 
     
     */
    // InventoryAlertService IS CALLED HERE:
    // - Runs AFTER stock is fully updated in Mycatalog
    // - Compares previous quantity with current quantity
    // - Triggers a low-stock notification only when stock
    //   crosses the alert threshold (avoids duplicate alerts)
    // - Notifies superadmins and the user who caused the stock change

    public function updateAvailableStock($productId, $locationId, $quantity = null)
    {
        // 🔹 Get previous total quantity
        $previousQty = Mycatalog::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->value('total_quantity');
        logger("QTY before", [$quantity]);
        if (is_null($quantity)) {
            $quantity = StockCount::where('product_id', $productId)
                ->where('location_id', $locationId)
                ->sum('on_hand_quantity');
        }
        logger("QTY after", [$quantity]);
        // 🔹 Update total quantity
        $catalog = Mycatalog::updateOrCreate(
            [
                'product_id' => $productId,
                'location_id' => $locationId,
            ],
            [
                'total_quantity' => $quantity,
            ]
        );

        //  Fire low stock alert AFTER update
        if ($catalog) {
            app(\App\Services\InventoryAlertService::class)
                ->handleLowStockFromCatalog($catalog, $previousQty);
        }

        return $catalog;
    }

    /**
     * Decrement stock when POS sale happens (FEFO based)
     */
    public function decrementStock($productId, $locationId, $qtyToDeduct, $batchNumber = null, $expiryDate = null)
    {
        return DB::transaction(function () use ($productId, $locationId, $qtyToDeduct, $batchNumber, $expiryDate) {

            // If batch & expiry are provided (POS item came with batch)
            if ($batchNumber || $expiryDate) {
                $batch = StockCount::where('product_id', $productId)
                    ->where('location_id', $locationId)
                    ->when($batchNumber, fn($q) => $q->where('batch_number', $batchNumber))
                    ->when($expiryDate, fn($q) => $q->where('expiry_date', $expiryDate))
                    ->first();

                if (!$batch) {
                    throw new \Exception("Stock batch not found for product: $productId");
                }

                if ($batch->on_hand_quantity < $qtyToDeduct) {
                    throw new \Exception("Not enough stock for batch {$batch->batch_number}");
                }

                $batch->on_hand_quantity -= $qtyToDeduct;
                $batch->save();

            } else {

                // FEFO = first expiry first out
                $batches = StockCount::where('product_id', $productId)
                    ->where('location_id', $locationId)
                    ->where('on_hand_quantity', '>', 0)
                    ->orderBy('expiry_date', 'ASC')
                    ->get();

                if ($batches->sum('on_hand_quantity') < $qtyToDeduct) {
                    throw new \Exception("Not enough stock for product: $productId");
                }

                foreach ($batches as $batch) {
                    if ($qtyToDeduct <= 0)
                        break;

                    $deduct = min($batch->on_hand_quantity, $qtyToDeduct);

                    $batch->on_hand_quantity -= $deduct;
                    $batch->save();

                    $qtyToDeduct -= $deduct;
                }
            }

            // update total available stock
            $this->updateAvailableStock($productId, $locationId);

            return true;
        });
    }

}

