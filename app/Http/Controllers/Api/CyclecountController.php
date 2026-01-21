<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use App\Models\CycleCount;
use Illuminate\Http\Request;

class CyclecountController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get cycles that have at least one pending or non-completed cycle count for this user
        $latestPendingCycle = Cycle::whereHas('cycleCounts', function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->where('status', '!=', 'completed'); // only counts not completed
        })
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['message' => 'Success', 'cycle' => $latestPendingCycle], 200);
    }


    public function cycledata(Request $request)
    {
        $user = auth()->user();

        $query = CycleCount::with(['product', 'cycle', 'product.category', 'location'])
            ->where('user_id', $user->id)
            ->where('cycle_id', $request->id)
            ->where('status', 'pending')
            ->whereHas('cycle', function ($q) {
                $q->whereDate('schedule_date', '<=', now());
            });

        // search (by product code, name, or category)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('product', function ($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('category_name', 'like', "%{$search}%");
                    });
            });
        }

        //  pagination (default 10 per page)
        $perPage = $request->get('per_page', 10);
        $cycles = $query->paginate($perPage);

        if ($cycles->isEmpty()) {
            return response()->json(['message' => 'No cycles found for this user', 'cycles' => $cycles], 200);
        }

        return response()->json([
            'message' => 'Success',
            'cycles' => $cycles
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'counted_qty' => 'required|integer|min:0',
            'status' => 'required|string|in:counted,pending,skipped',
        ]);

        $item = CycleCount::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $item->counted_qty = $request->counted_qty;
        $item->status = 'completed';
        $item->variance = $item->counted_qty - $item->expected_qty;
        $item->notes = $request->note ?? null;
        $item->counted_at = now();
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated successfully',
            'data' => $item
        ]);
    }

}
