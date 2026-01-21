<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\FlaggedPo;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReturnsOrders;


class EDIPoAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EDI:email_alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Sending email for flagged PO");
        // Get the flagged POs where email hasn't been sent
        $flagged_po_list = FlaggedPo::where('email_sent', false)->get();

        // Split them into two groups
        $hs_returns_po = $flagged_po_list->where('is_inbound_save', true);
        $inactive_po = $flagged_po_list->where('is_inbound_save', false);

        // Pluck references for both groups
        $hs_returns_ref = $hs_returns_po->pluck('purchase_order')->toArray();
        $inactive_po_ref = $inactive_po->pluck('purchase_order')->toArray();

        Log::info("HS Returns PO: " . json_encode($hs_returns_ref));
        Log::info("Inactive PO: " . json_encode($inactive_po_ref));

        // Send emails and update email_sent to true
        // if (count($hs_returns_ref) > 0) {
        //     try {
        //         Mail::send(new ReturnsOrders($hs_returns_ref));
        //         $hs_returns_po->each(function ($po) {
        //             $po->update(['email_sent' => true]);
        //         });
        //     } catch (Exception $e) {
        //         Log::error("error while send email for inboundsave orders" . $e->getMessage());
        //     }
        // }
        // if (count($inactive_po) > 0) {
        //     try {
        //         Mail::send(new InactiveOrders($inactive_po_ref));
        //         $inactive_po->each(function ($po) {
        //             $po->update(['email_sent' => true]);
        //         });
        //     } catch (Exception $e) {
        //         Log::error("error while send email for not ack orders" . $e->getMessage());
        //     }
        // }
        return 0;
    }
}
