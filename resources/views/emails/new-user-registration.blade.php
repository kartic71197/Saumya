<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Registered</title>
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
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .logo {
            font-size: 18px;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 10px;
        }

        h1 {
            color: #1f2937;
            margin: 10px 0 0 0;
            font-size: 24px;
        }

        .intro-text {
            background-color: #f8fafc;
            padding: 15px 20px;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
            margin: 25px 0;
            font-size: 14px;
            color: #475569;
        }

        .intro-text strong {
            color: #2563eb;
        }

        .user-name-section {
            text-align: center;
            margin: 25px 0;
            padding: 20px;
            background-color: #eff6ff;
            border-radius: 8px;
            border: 2px solid #bfdbfe;
        }

        .user-name-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .user-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin: 0;
        }

        .info-table {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #e2e8f0;
        }

        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #64748b;
            font-size: 13px;
            display: block;
            margin-bottom: 4px;
        }

        .info-value {
            color: #1f2937;
            font-size: 14px;
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

        .action-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: center;
        }

        .action-box strong {
            color: #d97706;
            font-size: 15px;
        }

        .security-note {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 13px;
        }

        .security-note strong {
            color: #dc2626;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        @media only screen and (max-width: 600px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">Dear Healthshade Team</div>
            <h1>New User Registered</h1>
        </div>

        <div class="intro-text">
            A new user has been successfully registered in the <strong>Healthshade</strong> system. Please review the details below.
        </div>
        <div class="info-table">
            <div class="info-row">
                <span class="info-label">User Name:</span>
                <span class="info-value">{{ $userName }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">User Email:</span>
                <span class="info-value">{{ $userEmail }}</span>
            </div>
            {{-- <div class="info-row">
                <span class="info-label">Location:</span>
                <span class="info-value">{{ $location }}</span>
            </div> --}}
            <div class="info-row">
                <span class="info-label">Created At:</span>
                <span class="info-value">{{ $createdAt }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="success-badge">✓ Created</span>
                </span>
            </div>
        </div>
        <div class="security-note">
            <strong>🔒 Security Notice:</strong> Please verify this user and take appropriate action if needed.
        </div>

        <p style="color: #64748b; font-size: 14px;">If you have any questions, please log in to the admin dashboard.</p>

        <div class="footer">
            <p>This is an automated notification. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} Healthshade. All rights reserved.</p>
        </div>
    </div>
</body>

</html>