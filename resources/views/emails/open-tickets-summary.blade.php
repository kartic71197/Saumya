<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Open Tickets Summary</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            margin: 0;
            color: #333;
        }

        .email-container {
            max-width: 700px;
            margin: 20px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .email-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .email-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        /* Ticket Card */
        .ticket-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 30px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            position: relative;
        }

        .ticket-card.low {
            border-left: 5px solid #16a34a;
        }

        .ticket-card.medium {
            border-left: 5px solid #eab308;
        }

        .ticket-card.high {
            border-left: 5px solid #dc2626;
        }

        .ticket-card.critical {
            border-left: 5px solid #991b1b;
        }

        .ticket-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1f2937;
            display: flex;
            align-items: center;
        }

        .ticket-card h3 span {
            margin-right: 8px;
            font-size: 18px;
        }

        .ticket-info {
            margin: 8px 0;
            font-size: 14px;
            color: #374151;
        }

        .ticket-info strong {
            color: #111827;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-open {
            background: #dbeafe;
            color: #1e40af;
        }

        .priority-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .priority-low {
            background: #dcfce7;
            color: #166534;
        }

        .priority-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-high {
            background: #fee2e2;
            color: #b91c1c;
        }

        .priority-critical {
            background: #fecaca;
            color: #7f1d1d;
        }

        /* Summary Card */
        .summary-card {
            padding: 20px;
            background: #eff6ff;
            border: 1px solid #93c5fd;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 30px;
            /* same horizontal margin as ticket cards */
        }

        .summary-card .icon {
            font-size: 22px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e40af;
        }

        .summary-card .text {
            font-size: 15px;
            color: #1f2937;
            line-height: 1.5;
        }

        .summary-card .text strong {
            display: block;
            margin-bottom: 4px;
            color: #1e3a8a;
        }

        /* Footer */
        .email-footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 600px) {
            .ticket-card {
                margin: 15px;
            }

            .summary-card {
                margin: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="ticket-icon">
                🎫
            </div>
            <h1>Weekly Open Tickets Summary</h1>
            @if ($tickets->count() > 0)
                <p>Here’s the list of all currently open tickets</p>
            @endif
        </div>

        <!-- Body -->
        <div class="email-body">
            <!-- Greeting -->
            <div class="greeting"
                style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 30px 20px 30px;">
                Hello HealthShade Team!
            </div>

            @if ($tickets->count() > 0)
                <!-- Summary / Intro Message -->
                <div class="summary-card">
                    <div class="icon">📋</div>
                    <div class="text">
                        <strong> Open Tickets Summary</strong>
                        Below is a summary of all currently open tickets in the system. Please review and resolve them at
                        the earliest.
                    </div>
                </div>

                <!-- Tickets List -->
                @foreach ($tickets as $ticket)
                    <div class="ticket-card {{ strtolower($ticket->priority) }}">
                        <h3><span>🎫</span> Ticket #{{ $ticket->id }} - {{ $ticket->module ?? 'General' }}</h3>
                        <div class="ticket-info">
                            <strong>Priority:</strong>
                            <span class="priority-badge priority-{{ strtolower($ticket->priority) }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                        <div class="ticket-info">
                            <strong>Status:</strong>
                            <span class="status-badge status-open">{{ ucfirst($ticket->status) }}</span>
                        </div>

                        <div class="ticket-info">
                            <strong>Description:</strong>
                            {{ Str::limit($ticket->description, 100) ?? 'No Description' }}
                        </div>

                        <div class="ticket-info">
                            <strong>Created At:</strong>
                            {{ $ticket->created_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                @endforeach
            @else

                <div class="summary-card">
                    <div class="icon">📋</div>
                    <div class="text">
                        <strong> Open Tickets Summary</strong>
                        There are not open tickets for this week!
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="email-footer">
            Weekly Ticket Summary • This is an automated email
        </div>
    </div>
</body>

</html>