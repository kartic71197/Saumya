<!DOCTYPE html>
<html>

<head>
    <title>Pending Acknowledgment Orders</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
            /* border-radius: 5px; */
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .table-container {
            background-color: #fff;
            /* border-radius: 8px; */
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
            text-align: left;
            font-size: 16px;
            letter-spacing: 0.5px;
        }

        .section-header.email {
            background-color: #3498db;
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
            text-align: center;
            display: inline-block;
            min-width: 80px;
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

        .empty-message {
            padding: 20px;
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
        }

        .no-orders {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            text-align: center;
            color: #7f8c8d;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <h2>Pending Acknowledgment Orders</h2>

    @if($orders->isEmpty())
        <div class="no-orders">
            <p>No pending acknowledgment orders found.</p>
        </div>
    @else

        @php
            /**
             * Split the orders into two separate collections for our use case:
             * 1. EDI Orders -> orders where the supplier uses EDI (Electronic Data Interchange)
             * 2. Email Orders -> orders where the supplier uses email communication
             */
            $ediOrders = $orders->filter(function ($order) {
                return isset($order->purchaseSupplier->is_edi) && $order->purchaseSupplier->is_edi;
            });

            $emailOrders = $orders->filter(function ($order) {
                return !isset($order->purchaseSupplier->is_edi) || !$order->purchaseSupplier->is_edi;
            });

            /**
             * Email orders case
             * Calculate priority based on order age
             * 1–2 days = Low (Green)
             * 3–4 days = Medium (Yellow)
             * 5+ days  = High (Red)
             */
            /**
             * EDI orders case
             * Calculate priority based on order age
             * 1 day = Low (Green)
             * 2 days = Medium (Yellow)
             * 3+ days = High (Red)
             */
            function getPriority($createdAt, ?string $integrationType = null)
            {
                $now = now();
                $created = \Carbon\Carbon::parse($createdAt);

                //changed diffInDays to diffInHours for EDI priority calculation
                // for crucial time-sensitive orders
                // 🟠 EDI logic (24-hour slabs, starts AFTER 12h filter)
                if (strtolower($integrationType ?? '') === 'edi') {
                    $hours = $created->diffInHours($now);

                    if ($hours >= 48) {
                        return ['class' => 'priority-high', 'label' => 'High'];   // 3rd day+
                    } elseif ($hours >= 24) {
                        return ['class' => 'priority-medium', 'label' => 'Medium']; // 2nd day
                    } else {
                        return ['class' => 'priority-low', 'label' => 'Low'];     // 1st day
                    }
                }
                //  Earlier it was giving Negative value of DiffInDays, and resulting in wrong priority
                $daysDiff = $created->diffInDays($now);

                // Old logic for Email / None
                if ($daysDiff >= 5) {
                    return ['class' => 'priority-high', 'label' => 'High'];
                } elseif ($daysDiff >= 3) {
                    return ['class' => 'priority-medium', 'label' => 'Medium'];
                } else {
                    return ['class' => 'priority-low', 'label' => 'Low'];
                }
            }
        @endphp

        <div class="table-container">
            <table>
                {{-- EDI Orders Section --}}
                @if(!$ediOrders->isEmpty())
                    <thead>
                        <tr>
                            <th colspan="5" class="section-header">EDI Orders</th>
                        </tr>
                        <tr>
                            <th>Purchase Order</th>
                            <th>Created At</th>
                            <th>Supplier</th>
                            <th>Location (Practice)</th>
                            <th>Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ediOrders as $order)
                            @php
                                // Calculate priority for EDI orders
                                // fetch integration type from supplier
                                $priority = getPriority(
                                    $order->created_at,
                                    $order->purchaseSupplier->int_type ?? 'NONE'
                                );
                            @endphp
                            <tr>
                                <td>{{ $order->purchase_order_number }}</td>
                                <td>{{ $order->created_at }}</td>
                                <td>{{ $order->purchaseSupplier->supplier_name ?? '-' }}</td>
                                <td>{{ ($order->shippingLocation->name ?? '-') . ' (' . ($order->organization->name ?? '-') . ')' }}</td>
                                <td>
                                    <span class="priority-badge {{ $priority['class'] }}">
                                        {{ $priority['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif

                {{-- Email Orders Section --}}
                @if(!$emailOrders->isEmpty())
                    <thead>
                        <tr>
                            <th colspan="5" class="section-header email">Email Orders</th>
                        </tr>
                        <tr>
                            <th>Purchase Order</th>
                            <th>Created At</th>
                            <th>Supplier</th>
                            <th>Location (Practice)</th>
                            <th>Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($emailOrders as $order)
                            @php
                                $priority = getPriority($order->created_at);
                            @endphp
                            <tr>
                                <td>{{ $order->purchase_order_number }}</td>
                                <td>{{ $order->created_at }}</td>
                                <td>{{ $order->purchaseSupplier->supplier_name ?? '-' }}</td>
                                <td>{{ ($order->shippingLocation->name ?? '-') . ' (' . ($order->organization->name ?? '-') . ')' }}</td>
                                <td>
                                    <span class="priority-badge {{ $priority['class'] }}">
                                        {{ $priority['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            </table>
        </div>
    @endif
</body>
</html>