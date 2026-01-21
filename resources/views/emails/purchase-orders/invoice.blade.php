<!DOCTYPE html>
<html>

<head>
    <title>Invoice {{ $invoice->number }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #565656;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .purchase-order-info,
        .company-info {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #565656;
            background-color: #f9f9f9;
            /* border-radius: 5px; */
        }

        .address-columns {
            display: flex;
            gap: 5px;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .address-column {
            width: 48%;
            padding: 2px 10px;
            border: 1px solid #565656;
            background-color: #f9f9f9;
            /* border-radius: 5px; */
        }

        .card-header {
            border-bottom: 1px solid #565656;
            padding-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #565656;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #ddd;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #565656;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>INVOICE #{{ $invoice->number }}</h2>
        </div>

        <div class="purchase-order-info">
            <p><strong>Supplier :</strong> {{ $purchaseOrder->purchaseSupplier->supplier_name }}</p>
            <p><strong>PO Number :</strong> {{ $purchaseOrder->purchase_order_number }}</p>
            <p><strong>Date due :</strong> {{  $invoice->due_date ? date('Y-m-d', $invoice->due_date) : null;}}</p>
            <p><strong>Total :</strong> {{ strtoupper($invoice->currency) }}
                {{ number_format($purchaseOrder->total, 2) }}
            </p>
        </div>
        <div>
            <div align="center" style="padding:0 40px 40px 40px;">
                <a href="{{ $paymentUrl }}" style="display:inline-block;
                                  padding:16px 48px;
                                  background:#1a1a1a;
                                  border-radius:6px;
                                  text-decoration:none;
                                  color:#ffffff;
                                  font-weight:600;
                                  font-size:15px;
                                  letter-spacing:0.3px;
                                  transition:background 0.2s;">
                    Pay Invoice
                </a>
                <p style="margin:16px 0 0 0;font-size:13px;color:#9ca3af;">
                    🔒 Secure payment powered by Stripe
                </p>
            </div>
        </div>

        <div class="address-columns">
            <div class="address-column">
                <h3 class="card-header">Bill From</h3>
                <p><strong>Account :</strong> {{$billFrom['name'] ?? 'N/A' }}</p>
                <p><strong>Email :</strong> {{ $billFrom['email'] }}</p>
                <p><strong>Address :</strong>
                    @if(!empty($billFrom['address']))
                        {{ $billFrom['address']['line1'] ?? '' }},
                        {{ $billFrom['address']['city'] ?? '' }},
                        {{ $billFrom['address']['state'] ?? '' }},
                        {{ $billFrom['address']['country'] ?? '' }},
                        {{ $billFrom['address']['postal_code'] ?? '' }}
                    @else
                        N/A
                    @endif
            </div>

            <div class="address-column">
                <h3 class="card-header">Bill to</h3>
                <p><strong>Account :</strong>{{ $purchaseOrder->organization->name }}</p>
                <p><strong>Email :</strong> {{$purchaseOrder->organization->email }}</p>
                <p><strong>Address:</strong>

                    @php
                        $addressParts = array_filter([
                            $purchaseOrder->organization->location->address ?? null,
                            $purchaseOrder->organization->city ?? null,
                            $purchaseOrder->organization->state ?? null,
                            $purchaseOrder->organization->country ?? null,
                            $purchaseOrder->organization->pin ?? null,
                        ]);
                        echo !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
                    @endphp
                </p>
            </div>
        </div>

        <h3>Order Details</h3>
        <table>
            <thead>
                <tr>
                    <th>S no.</th>
                    <th>Product Code #</th>
                    <th>Product Name</th>
                    <th>Mfr #</th>
                    <th>UoM</th>
                    <th>Order Qty</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->purchasedProducts as $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->product_code ?? '' }}</td>
                        <td>{{$item->product->product_name ?? '' }}</td>
                        <td>{{ $item->product->manufacture_code ?? 'N/A' }}</td>
                        <td>{{ $item->unit->unit_code ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->sub_total }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- PAY BUTTON -->
        {{-- <div>
            <div align="center" style="padding:0 40px 40px 40px;">
                <a href="{{ $paymentUrl }}" style="display:inline-block;
                                  padding:16px 48px;
                                  background:#1a1a1a;
                                  border-radius:6px;
                                  text-decoration:none;
                                  color:#ffffff;
                                  font-weight:600;
                                  font-size:15px;
                                  letter-spacing:0.3px;
                                  transition:background 0.2s;">
                    Pay Invoice
                </a>
                <p style="margin:16px 0 0 0;font-size:13px;color:#9ca3af;">
                    🔒 Secure payment powered by Stripe
                </p>
            </div>
        </div> --}}

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>If you have any questions regarding this purchase order, please contact us.</p>
        </div>
    </div>
</body>

</html>