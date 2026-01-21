<?php

namespace App\Services\Pricing;

use App\Models\Product;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\DB;

class PriceHistoryService
{
    /**
     * Change product price and create a versioned price history entry.
     *
     * @param Product $product
     * @param float   $newPrice
     * @param float   $newCost
     * @param int     $userId
     * @return void
     */
    public function changePrice(
        Product $product,
        float $newPrice,
        float $newCost,
        ?int $userId
    ): void {
        DB::transaction(function () use (
            $product,
            $newPrice,
            $newCost,
            $userId,
        ) {
            //  Close current price version
            PriceHistory::where('product_id', $product->id)
                ->whereNull('effective_to')
                ->update([
                    'effective_to' => now(),
                ]);

            //  Create new price version
            PriceHistory::create([
                'product_id'     => $product->id,
                'cost'     => $newCost,
                'price'          => $newPrice,
                'effective_from' => now(),
                'changed_by'     => $userId
            ]);
        });
    }
}
