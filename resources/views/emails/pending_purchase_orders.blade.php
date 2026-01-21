<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Purchase Orders</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #333;
            line-height: 1.6;
        }

        .email-container {
            max-width: 700px;
            margin: 20px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .email-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .email-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .summary-info {
            padding: 20px 30px;
            text-align: center;
            font-size: 15px;
            color: #1f2937;
        }

        .order-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin: 20px 30px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .order-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            color: #1f2937;
        }

        .order-card h3 .icon {
            margin-right: 10px;
            font-size: 22px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .info-item {
            background: #fff;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background-color: #e5e7eb;
            font-weight: 600;
        }

        .footer {
            background: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 600px) {
            .order-card, .email-container {
                margin: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>Pending Purchase Orders Reminder</h1>
            <p>Automated reminder for orders pending more than 36 hours</p>
        </div>

        <!-- Summary Info -->
        <div class="summary-info">
            <p><strong>Total pending orders older than 3 days:</strong> {{ $total }}</p>
            <p><strong>Generated at:</strong> {{ $generatedAt->format('m-d-Y H:i') }}</p>
        </div>

        <!-- Orders List -->
        @foreach($pendingOrders as $order)
        <div class="order-card">
            <h3><span class="icon">📦</span> PO Number: {{ $order->purchase_order_number ?? $order->id }}</h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Supplier</div>
                    <div class="info-value">{{ $order->purchaseSupplier->supplier_name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Location</div>
                    <div class="info-value">{{ $order->shippingLocation?->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Items</div>
                    <div class="info-value">{{ $order->purchasedProducts->count() ?? 0 }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date Created</div>
                    <div class="info-value">{{ $order->created_at->format('m-d-Y') }}</div>
                </div>
            </div>

            @if($order->purchasedProducts && $order->purchasedProducts->count() > 0)
            <h4>Items</h4>
            <table>
                <thead>
                    <tr>
                        <th>Code #</th>
                        <th>Product Name</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->purchasedProducts as $detail)
                    <tr>
                        <td>{{ $detail->product->product_code ?? '' }}</td>
                        <td>{{ $detail->product->product_name ?? '' }}</td>
                        <td>{{ $detail->quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        @endforeach

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated reminder. Please review and take necessary action on pending orders.</p>
        </div>
    </div>
</body>
</html>
