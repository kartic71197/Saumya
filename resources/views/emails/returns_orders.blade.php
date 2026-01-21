<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Purchase Order Placement Failed</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      color: #333;
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #f4f6f8;
    }
    .email-container {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }
    .header {
      text-align: center;
      margin-bottom: 25px;
    }
    .alert-badge {
      background-color: #e74c3c;
      color: #fff;
      padding: 8px 18px;
      border-radius: 50px;
      font-size: 14px;
      font-weight: bold;
      display: inline-block;
      margin-bottom: 12px;
      box-shadow: 0 2px 6px rgba(231,76,60,0.3);
    }
    h1 {
      color: #e74c3c;
      font-size: 22px;
      margin: 0;
    }
    .content p {
      margin: 15px 0;
      color: #555;
    }
    .order-details {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin: 20px 0;
      border: 1px solid #e0e0e0;
    }
    .order-item {
      background: #fff;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      padding: 12px 15px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: background 0.2s ease;
    }
    .order-item:hover {
      background: #fef7f7;
    }
    .order-number {
      font-weight: bold;
      color: #007bff;
    }
    .action-required {
      background-color: #fff8e1;
      border-left: 5px solid #ffc107;
      padding: 15px;
      margin: 25px 0;
      border-radius: 6px;
    }
    .action-title {
      font-weight: bold;
      color: #856404;
      margin-bottom: 6px;
    }
    .footer {
      text-align: center;
      margin-top: 25px;
      padding-top: 15px;
      border-top: 1px solid #eee;
      font-size: 13px;
      color: #666;
    }
    .footer p {
      margin: 6px 0;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="header">
      <div class="alert-badge">⚠️ FAILURE ALERT</div>
      <h1>Purchase Order Placement Failed</h1>
    </div>

    <div class="content">
      <p>The following purchase orders require <strong>immediate attention</strong>. They were rejected by the suppliers.</p>

      <div class="order-details">
        @foreach($orders as $order)
        <div class="order-item">
          <span class="order-number">📦 PO #{{ $order }}</span>
        </div>
        @endforeach
      </div>

      <div class="action-required">
        <div class="action-title">⚡ Action Required</div>
        <p>The affected purchase orders remain in <strong>"ordered"</strong> status and need manual intervention. Please review and resolve them as soon as possible.</p>
      </div>

      <p style="font-size: 14px; color:#777;">This is an automated notification. Do not reply directly to this email.</p>
    </div>

    <div class="footer">
      <p><strong>HealthShade Support Team</strong></p>
      <p>Email: <a href="mailto:support@healthshade.com" style="color:#007bff;">support@healthshade.com</a></p>
      <p style="color:#aaa; font-size:12px;">This email was sent automatically by the Purchase Order Management System.</p>
    </div>
  </div>
</body>
</html>
