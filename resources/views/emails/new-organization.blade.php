<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Organization Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .icon-badge {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }

        .intro-card {
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            padding: 20px 25px;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
            margin: 25px 0;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
        }

        .intro-card p {
            margin: 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
        }

        .intro-card strong {
            color: #2563eb;
        }

        .info-card {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #e2e8f0;
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #64748b;
            min-width: 140px;
            font-size: 14px;
        }

        .info-value {
            color: #1f2937;
            flex: 1;
            font-size: 14px;
        }

        .org-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 8px;
        }

        .alert-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .alert-box strong {
            color: #d97706;
        }

        .success-badge {
            background-color: #d1fae5;
            color: #065f46;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        .action-required {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            padding: 15px 30px;
            text-align: center;
            border-radius: 8px;
            margin: 25px 0;
            font-weight: 600;
        }

        @media only screen and (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                margin-bottom: 5px;
            }

            .container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                @if($logoUrl ?? false)
                    <img src="{{ $logoUrl }}" alt="Healthshade"
                        style="max-width: 200px; height: auto; margin-bottom: 10px;">
                @else
                    <div style="font-size: 24px; font-weight: bold; color: #2563eb;">Dear, Healthshade Team</div>
                @endif
            </div>
            <h1 style="color: #1f2937; margin: 0;">New Practice Created</h1>
        </div>

        <div class="intro-card">
            <p>A new practice has been successfully registered in the <strong>Healthshade</strong> system. Please review the details below:</p>
        </div>

        <div class="org-name">{{ $organizationName }}</div>

        <div class="info-card">
            <div class="info-row">
                <div class="info-label">Practice Name:</div>
                <div class="info-value"><strong>{{ $organizationName }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Created By:</div>
                <div class="info-value">{{ $userName }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Practice Email:</div>
                <div class="info-value">{{ $practiceEmail }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Created At:</div>
                <div class="info-value">{{ $createdAt }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="success-badge">✓ Created</span>
                </div>
            </div>
        </div>

        <div class="action-required">
            <div style="font-size: 18px; margin-bottom: 5px;">⚡ Action Required</div>
            <div style="font-size: 14px; font-weight: normal;">Please review and verify this practice in the admin panel</div>
        </div>

        <div class="alert-box">
            <strong>🔒 Security Notice:</strong> This is an automated notification for new practice registrations. Please verify the legitimacy of this practice and take appropriate action if needed.
        </div>

        <p style="color: #64748b; font-size: 14px;">If you have any questions or need to take action regarding this practice, please log in to the admin dashboard.</p>

        <div class="footer">
            <p>This is an automated notification. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} Healthshade. All rights reserved.</p>
        </div>
    </div>
</body>

</html>