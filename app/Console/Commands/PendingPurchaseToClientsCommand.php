<?php

namespace App\Console\Commands;

use App\Mail\PendingOrderToClientsMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Mail\PendingPurchaseOrdersMail;

class PendingPurchaseToClientsCommand extends Command
{
    protected $signature = 'app:pending-orders-for-practice';

    protected $description = 'Notify each practice about their pending purchase orders older than 48 hours';

    public function handle()
    {
        $pendingOrders = PurchaseOrder::query()
            ->where('status', 'ordered')
            ->where('created_at', '<', now()->subHours(120))
            ->whereNotIn('purchase_order_number', function ($q) {
                $q->select('purchase_order')->from('edi855s');
            })
            ->whereNotIn('purchase_order_number', function ($q) {
                $q->select('po_number')->from('edi810s');
            })
            ->whereHas('organization', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['purchaseSupplier', 'organization', 'shippingLocation'])
            ->get()
            ->groupBy('organization_id');

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending purchase orders older than 48 hours.');
            return;
        }

        logger()->info('Sending pending purchase orders emails practice-wise.');

        foreach ($pendingOrders as $organizationId => $orders) {

            // Fetch users of that practice
            $users = User::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->where('role_id', '2')
                ->whereNotNull('email')
                ->pluck('email')
                ->toArray();

            logger()->info("Pending PO email will be sent to users of org {$organizationId}: " . implode(', ', $users));

            if (empty($users)) {
                continue;
            }

            try {
                Mail::to($users)->send(
                    new PendingOrderToClientsMail($orders)
                );
            } catch (\Exception $e) {
                Log::error(
                    "Failed sending pending PO email for org {$organizationId}: " . $e->getMessage()
                );
            }
        }

        $this->info('Pending purchase orders emails sent practice-wise successfully.');
    }
}
