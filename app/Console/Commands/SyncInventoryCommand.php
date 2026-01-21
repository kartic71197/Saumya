<?php

namespace App\Console\Commands;

use App\Models\Mycatalog;
use App\Models\Product;
use App\Models\StockCount;
use Illuminate\Console\Command;

class SyncInventoryCommand extends Command
{
    protected $signature = 'app:sync-inventory';
    protected $description = 'Sync inventory totals from stock_counts into mycatalogs';

    public function handle()
    {
        $products = Product::all();
        $count = 0;

        foreach ($products as $product) {
            $stockByLocation = StockCount::where('product_id', $product->id)
                ->selectRaw('location_id, SUM(on_hand_quantity) as total_qty')
                ->groupBy('location_id')
                ->get();

            foreach ($stockByLocation as $stock) {
                // only create/update if qty > 0
                if ($stock->total_qty > 0) {
                    Mycatalog::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'location_id' => $stock->location_id,
                        ],
                        [
                            'total_quantity' => $stock->total_qty,
                        ]
                    );
                    $count++;
                }
            }
        }

        logger("✅ Inventory sync complete. Created/updated {$count} mycatalog records where qty > 0.");
    }
}
