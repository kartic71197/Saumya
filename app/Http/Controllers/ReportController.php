<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
     public function downloadPdf(PurchaseOrder $po)
    {
        // load invoice data
        $po->load('edi810s');
        $supplier = Supplier::where('id',$po->supplier_id)->first();
        $organization = Organization::where('id',$po->organization_id)->first();

        // render view into PDF
        $pdf = Pdf::loadView('invoices.pdf', compact('po', 'supplier','organization'));

        // return download response
        return $pdf->download("Invoice-{$po->purchase_order_number}.pdf");
    }
    public function index()
    {
        if (auth()->user()->role_id == '3') {
            abort(403, 'Unauthorized.');
        }
        return view("reports.index");
    }
    public function purchaseOrderReport()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('purchase_order_report') || $user->role_id <= 2) {
            return view("reports.purchase_order_report");
        }
        return redirect()->back()->with('error', 'Not authorized access to purchase order report.');
    }
    public function PickingReport()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('picking_report') || $user->role_id <= 2) {
            return view("reports.picking_report");
        }
        return redirect()->back()->with('error', 'Not authorized access to purchase order report.');

    }
    public function AuditReport()
    {

        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('audit_report') || $user->role_id <= 2) {
            return view("reports.audit_report");
        }
        return redirect()->back()->with('error', 'Not authorized access to purchase order report.');

    }
    public function inventoryAdjust()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('inventory_adjust_report') || $user->role_id <= 2) {
            return view("reports.inventory_adjust");
        }
        return redirect()->back()->with('error', 'Not authorized access to purchase order report.');

    }

    public function inventoryTransfer()
    {

        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('inventory_transfer_report') || $user->role_id <= 2) {
            return view("reports.inventory_transfer");
        }
        return redirect()->back()->with('error', 'Not authorized access to purchase order report.');


    }

    public function productReport()
    {
        $user = auth()->user();
        $role = $user->role;

        // if ($role?->hasPermission('product_report') || $user->role_id <= 2) {
        //     $locations = [];
        //     $organizations = Organization::where('is_active', true)->get();

        //     if(auth()->user()->role_id > 1){
        //         $locations =  Location::where('id', auth()->user()->location_id)->where('is_active', true);
        //     }

        //     return view("reports.product_report", compact('organizations', 'locations'));
        // }

        $locations = [];
        $organizations = Organization::where('is_active', true)
            ->where('is_deleted', 0)
            ->where('is_rep_org', 0)
            ->get();
        if (auth()->user()->role_id > 1) {
            $locations = Location::where('org_id', auth()->user()->organization_id)->where('is_active', true)->get();
        }

        return view("reports.product_report", compact('organizations', 'locations'));

    }

    public function CycleCount()
    {
        return view("reports.cycle_count_report");
    }

    public function lotPickingReport()
    {
        $user = auth()->user();
        $role = $user->role;

        // Check permission or specific role ID
        if ($role?->hasPermission('picking_report') || $user->role_id <= 2) {
            return view("reports.lot_picking_report");
        }
        return redirect()->back()->with('error', 'Not authorized access to lot picking report.');
    }

    public function invoiceReport()
    {
        return view("reports.invoices");
    }

    public function showInvoiceModal($id)
    {
        // $po = PurchaseOrder::query()
        //     ->where('purchase_orders.organization_id', auth()->user()->organization_id)
        //     ->whereHas('edi810s')
        //     ->with('edi810s')
        //     ->findOrFail($id);
        // logger($po);

        $po = PurchaseOrder::query()
            ->whereHas('edi810s')
            ->with('edi810s')
            ->findOrFail($id);

        // Load the supplier and organization data
        $supplier = Supplier::find($po->supplier_id);
        $organization = Organization::find($po->organization_id);
        logger($po);
        logger('Supplier found: ' . ($supplier ? 'Yes' : 'No'));

        // You now have the purchase order + all its invoices
        // passing supplier and organization to the view
        return view('invoices.show', compact('po', 'supplier', 'organization'));
    }

    public function priceHistory()
    {
        $user = auth()->user();
        $role = $user->role;

        return view("reports.price_history_report");

        // Check permission or specific role ID
        // if ($role?->hasPermission('price_history_report') || $user->role_id <= 2) {
        //     return view("reports.price_history_report");
        // }
        // return redirect()->back()->with('error', 'Not authorized access to price history report.');
    }
}