<?php

namespace App\Console\Commands;

use App\Mail\PendingAckOrdersMail;
use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingPurchaseOrdersMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pending-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify the team about all pending purchase orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pendingOrders = PurchaseOrder::query()
            ->where('status', 'ordered')
            // ->where('created_at', '<', now()->subHours(36))
            ->whereHas(
                'organization.plan',
                fn($q) =>
                $q->whereRaw('LOWER(name) != ?', ['free trial'])

            )
            ->whereNotIn('purchase_order_number', function ($q) {
                $q->select('purchase_order')->from('edi855s');
            })
            ->whereNotIn('purchase_order_number', function ($q) {
                $q->select('po_number')->from('edi810s');
            })            
            ->with(['purchaseSupplier', 'organization', 'shippingLocation'])
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending purchase orders found.');
            return;
        }

        try {
            Mail::to(config('app.email'))->send(new PendingAckOrdersMail($pendingOrders));
            // Optionally mark a note on each PO
            // foreach ($pendingOrders as $order) {
            //     $order->note = trim(($order->note ?? '') . ' | Reminder sent on ' . now()->format('Y-m-d H:i:s'));
            //     $order->save();
            // }

            $this->info('Pending purchase orders notification sent.');

        } catch (\Exception $e) {
            \Log::error("Error sending pending orders mail: " . $e->getMessage());
        }

        return 0;
    }
}
