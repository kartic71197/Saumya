<!DOCTYPE html>
<html>

<head>
    <title>Pending Acknowledgment Orders</title>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        h2 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .intro-text {
            background-color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .table-container {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background-color: #34495e;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .section-header {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            padding: 14px 12px;
            font-size: 16px;
        }

        tbody tr {
            border-bottom: 1px solid #ecf0f1;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        tbody td {
            padding: 12px;
            font-size: 14px;
        }

        .priority-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
            min-width: 90px;
            text-align: center;
        }

        .priority-low {
            background-color: #27ae60;
            color: white;
        }

        .priority-medium {
            background-color: #f39c12;
            color: white;
        }

        .priority-high {
            background-color: #e74c3c;
            color: white;
        }

        .no-orders {
            background-color: #fff;
            padding: 40px;
            text-align: center;
            color: #7f8c8d;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <h2>Action Required: Pending Purchase Orders</h2>

    <div class="intro-text">
        The following purchase orders have been pending for some time and remain in <strong>“Ordered”</strong> status.
        If these orders have already been received, kindly update them in the system promptly to maintain accurate
        inventory and accounting records.
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th colspan="5" class="section-header">
                        Pending Purchase Orders
                    </th>
                </tr>
                <tr>
                    <th>PO Number</th>
                    <th>Created At</th>
                    <th>Supplier</th>
                    <th>Location</th>
                    {{-- <th>Priority</th> --}}
                </tr>
            </thead>

            <tbody>
                @foreach ($orders as $order)
                    @php
                        $hours = now()->diffInHours($order->created_at);

                        if ($hours >= 96) {
                            $priority = ['label' => 'High', 'class' => 'priority-high'];
                        } elseif ($hours >= 72) {
                            $priority = ['label' => 'Medium', 'class' => 'priority-medium'];
                        } else {
                            $priority = ['label' => 'Low', 'class' => 'priority-low'];
                        }
                    @endphp

                    <tr>
                        <td>{{ $order->purchase_order_number }}</td>
                        <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                        <td>{{ $order->purchaseSupplier->supplier_name ?? '-' }}</td>
                        <td>
                            {{ $order->shippingLocation->name ?? '-' }}<br>
                            {{-- <small>({{ $order->organization->name ?? '-' }})</small> --}}
                        </td>
                        {{-- <td>
                                <span class="priority-badge {{ $priority['class'] }}">
                                    {{ $priority['label'] }}
                                </span>
                            </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
