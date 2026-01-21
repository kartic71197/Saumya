<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $po->purchase_order_number }}</title>
    <style>
        /* --- Base --- */
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #000;
            margin: 30px;
        }

        /* --- Header: centered logo, then invoice row --- */
        .doc-header {
            position: relative;
            margin-bottom: 18px;
        }

        .logo-wrap {
            text-align: center;
            margin-bottom: 14px;
        }
        .logo-wrap img { height: 70px; width: auto; }

        .invoice-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px 0;
        }
        .invoice-left {
            font-size: 12px;
            font-weight: 500;
            letter-spacing: .5px;
        }
        .invoice-left .sep { padding: 0 6px; font-weight: 300; }
        .invoice-right {
            text-align: right;
            font-size: 10px;          /* smaller font on top-right corner */
            line-height: 1.3;
        }

        /* --- Parties (Supplier & Customer in a single row) --- */
        .parties {
            display: table;
            width: 100%;
            margin: 16px 0 22px;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .party {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px 12px;
        }
        .party + .party { border-left: 1px solid #000; }

        .block-title {
            margin: 0 0 8px 0;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }
        .info-row { margin: 4px 0; }
        .label {
            display: inline-block;
            min-width: 92px;
            font-weight: 700;
        }

        /* --- Meta (optional details like PO, Due) --- */
        .meta {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }
        .meta-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }
        .meta-col:last-child { padding-right: 0; }
        .meta-box {
            border: 1px solid #000;
            padding: 10px 12px;
        }
        .meta-box .row { display: flex; justify-content: space-between; margin: 4px 0; }
        .meta-box .row .k { font-weight: 700; }

        /* --- Items table --- */
        table.items {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 10px;
        }
        .items th, .items td {
            border: 1px solid #000;
            padding: 8px 6px;
            vertical-align: top;
        }
        .items th {
            background: #f2f2f2;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: .3px;
        }
        .w-code { width: 15%; }
        .w-desc { width: 35%; }
        .w-unit { width: 8%; }
        .w-qty  { width: 8%; }
        .w-price{ width: 12%; }
        .w-total{ width: 12%; }
        .w-tax  { width: 10%; }

        .number { text-align: right; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
        .wrap { word-wrap: break-word; word-break: break-word; }

        /* --- Totals --- */
        .totals tfoot td {
            font-weight: 700;
            background: #f9f9f9;
        }

        /* --- Signatures & Footer --- */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 36px;
        }
        .sig {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 12px;
        }
        .sig-line {
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 6px;
            font-size: 10px;
        }

        .footer {
            margin-top: 28px;
            font-size: 10px;
            color: #444;
            text-align: center;
        }

        /* --- Print --- */
        @media print {
            body { margin: 0; }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="doc-header">
        <div class="logo-wrap">
            <img src="{{ public_path('logos/logo.png') }}" alt="Company Logo">
        </div>

        <div class="invoice-row">
            <!-- Left: INVOICE and number in a single row -->
            <div class="invoice-left">
                INVOICE <span class="sep">—</span> #{{ $po->edi810s->first()->invoice_number }}
            </div>

            <!-- Right: smaller font invoice date on top-right -->
            <div class="invoice-right">
                Invoice Date:
                {{ \Carbon\Carbon::parse($po->edi810s->first()->invoice_date)->format('F j, Y') }}
            </div>
        </div>
    </div>

    <!-- Parties: Supplier & Customer in a single row -->
    <div class="parties">
        <div class="party">
            <h3 class="block-title">Supplier</h3>
            <div class="info-row"><span class="label">Company:</span> {{ $supplier->supplier_name }}</div>
            <div class="info-row"><span class="label">Address:</span> {{ $supplier->supplier_address ?? 'N/A' }}</div>
            <div class="info-row"><span class="label">City:</span> {{ $supplier->supplier_city ?? 'N/A' }}</div>
            <div class="info-row"><span class="label">Country:</span> {{ $supplier->supplier_state.' '.$supplier->supplier_country }}</div>
            <div class="info-row"><span class="label">Phone:</span> {{ $supplier->supplier_phone }}</div>
            <div class="info-row"><span class="label">Email:</span> {{ $supplier->supplier_email }}</div>
        </div>
        <div class="party">
            <h3 class="block-title">Customer</h3>
            <div class="info-row"><span class="label">Company:</span> {{ $organization->name }}</div>
            <div class="info-row"><span class="label">Address:</span> {{ $organization->address ?? 'Billing Address' }}</div>
            <div class="info-row"><span class="label">City:</span> {{ $organization->city ?? 'N/A' }}</div>
            <div class="info-row"><span class="label">Country:</span> {{ $organization->state.' '.$organization->country }}</div>
            <div class="info-row"><span class="label">Phone:</span> {{ $organization->phone }}</div>
            <div class="info-row"><span class="label">Email:</span> {{ $organization->email }}</div>
        </div>
    </div>

    <!-- Optional meta row (kept compact & formal) -->
    <div class="meta">
        <div class="meta-col">
            <div class="meta-box">
                <div class="row"><span class="k">PO Number</span><span>{{ $po->purchase_order_number }}</span></div>
            </div>
        </div>
        <div class="meta-col">
            <div class="meta-box">
                <div class="row"><span class="k">Total Amount Due</span>
                    <span class="number">${{ number_format($po->edi810s->first()->total_amount_due / 100, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items">
        <thead>
            <tr>
                <th class="w-code">Product Code</th>
                <th class="w-desc">Description</th>
                <th class="w-unit">Unit</th>
                <th class="w-qty">Qty</th>
                <th class="w-price">Unit Price</th>
                <th class="w-total">Line Total</th>
                <th class="w-tax">Tax</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->edi810s as $item)
                <tr>
                    <td>{{ $item->product_code }}</td>
                    <td class="wrap">{{ $item->product_description }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="number">{{ number_format($item->qty, 0) }}</td>
                    <td class="number">${{ number_format($item->price, 2) }}</td>
                    <td class="number">${{ number_format($item->price * $item->qty, 2) }}</td>
                    <td class="number">
                        ${{ number_format($item->tax, 2) }} <span style="font-size:9px;">({{ $item->taxPercent }}%)</span>
                    </td>
                </tr>
            @endforeach

            <!-- Summary Rows -->
            <tr>
                <td colspan="5" class="number" style="font-weight:700;">Subtotal</td>
                <td class="number" style="font-weight:700;">
                    ${{ number_format($po->edi810s->sum(function ($it) { return $it->price * $it->qty; }), 2) }}
                </td>
                <td></td>
            </tr>
            <tr>
                <td colspan="5" class="number" style="font-weight:700;">Total Tax</td>
                <td class="number" style="font-weight:700;">
                    ${{ number_format($po->edi810s->sum('tax'), 2) }}
                </td>
                <td></td>
            </tr>
            <tr>
                <td colspan="5" class="number" style="font-weight:700; font-size:12px;">TOTAL DUE</td>
                <td class="number" style="font-weight:700; font-size:12px;">
                    ${{ number_format($po->edi810s->first()->total_amount_due / 100, 2) }}
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig">
            <div class="sig-line">Authorized Signature</div>
        </div>
        <div class="sig">
            <div class="sig-line">Customer Signature</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your business. Payment is due within 30 days of the invoice date.</p>
        <p>For questions regarding this invoice, please contact our billing department.</p>
        <p><small>Generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t g:i A') }}</small></p>
    </div>
</body>

</html>
