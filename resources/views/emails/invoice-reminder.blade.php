<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
</head>

<body
    style="margin:0;padding:0;background-color:#f6f9fc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#333;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f9fc;padding:40px 0;">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.05);overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="padding:40px;border-bottom:1px solid #e6ebf1;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="left" valign="top" style="width:60%;">
                                        <h2 style="margin:0 0 20px 0;font-size:28px;font-weight:700;color:#111827;">
                                            Invoice
                                        </h2>

                                        <table cellpadding="0" cellspacing="0"
                                            style="font-size:13px;line-height:1.8;color:#4b5563;">
                                            <tr>
                                                <td style="padding-right:12px;"><strong>Invoice number</strong></td>
                                                <td>{{ $invoiceNumber }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-right:12px;"><strong>Date of issue</strong></td>
                                                <td>{{ $invoiceDate }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-right:12px;"><strong>Date due</strong></td>
                                                <td>{{ $dueDate }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right" valign="top">
                                        <img src="https://healthshade.com/logos/logo.png" alt="HealthShade" height="36"
                                            style="display:block;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Biller Details -->
                    <tr>
                        <td style="padding:32px 40px 24px;">
                            <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:#111827;">
                                {{ $billerName }}
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#6b7280;">
                                {!! $billerAddress !!}<br>
                                @if($billerPhone)
                                    {{ $billerPhone }}<br>
                                @endif
                                {{ $billerEmail }}
                            </p>
                        </td>
                    </tr>

                    <!-- Amount Due -->
                    <tr>
                        <td style="padding:0 40px 36px;">
                            <p style="margin:0;font-size:34px;font-weight:700;color:#111827;">
                                ${{ number_format($invoiceAmount, 2) }} USD
                            </p>
                            <p style="margin:6px 0 0;font-size:14px;color:#6b7280;">
                                Due by {{ $dueDate }}
                            </p>

                            <a href="{{ $invoiceUrl }}" style="display:inline-block;margin-top:18px;padding:12px 20px;
                background:#635bff;color:#ffffff;text-decoration:none;
                font-size:14px;font-weight:600;border-radius:6px;">
                                Pay online →
                            </a>
                        </td>
                    </tr>

                    <!-- Thank You -->
                    <tr>
                        <td style="padding:0 40px 28px;">
                            <p style="margin:0;font-size:14px;color:#4b5563;">
                                Thank you for your business!
                            </p>
                        </td>
                    </tr>

                    <!-- Invoice Items -->
                    <tr>
                        <td style="padding:0 40px 36px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <thead>
                                    <tr style="border-bottom:2px solid #111827;">
                                        <th align="left" style="padding:14px 0;font-size:13px;font-weight:600;">
                                            Description</th>
                                        <th align="right"
                                            style="padding:14px 0;font-size:13px;font-weight:600;width:60px;">Qty</th>
                                        <th align="right"
                                            style="padding:14px 0;font-size:13px;font-weight:600;width:100px;">Unit</th>
                                        <th align="right"
                                            style="padding:14px 0;font-size:13px;font-weight:600;width:100px;">Amount
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lineItems as $item)
                                        <tr style="border-bottom:1px solid #e6ebf1;">
                                            <td style="padding:18px 0;font-size:14px;color:#4b5563;">
                                                <strong>{{ $item['description'] }}</strong><br>
                                                <span style="font-size:13px;color:#6b7280;">
                                                    {{ $item['period'] }}
                                                </span>
                                            </td>
                                            <td align="right" style="padding:18px 0;">{{ $item['quantity'] }}</td>
                                            <td align="right" style="padding:18px 0;">
                                                ${{ number_format($item['unit_price'], 2) }}
                                            </td>
                                            <td align="right" style="padding:18px 0;">
                                                ${{ number_format($item['amount'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <!-- Totals -->
                    <tr>
                        <td style="padding:0 40px 36px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:60%;"></td>
                                    <td align="right" style="padding:8px 0;color:#6b7280;">Subtotal</td>
                                    <td align="right" style="padding-left:20px;width:100px;">
                                        ${{ number_format($invoiceAmount, 2) }}
                                    </td>
                                </tr>
                                <tr style="border-top:2px solid #111827;">
                                    <td></td>
                                    <td align="right" style="padding:14px 0;font-weight:700;color:#111827;">
                                        Amount due
                                    </td>
                                    <td align="right" style="padding-left:20px;font-weight:700;color:#111827;">
                                        ${{ number_format($invoiceAmount, 2) }} USD
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:32px 40px;border-top:1px solid #e6ebf1;
        font-size:12px;color:#6b7280;line-height:1.7;">
                            <p style="margin:0;">
                                If payment has already been completed, you may safely ignore this email.
                            </p>
                            <p style="margin:14px 0 0;">
                                Questions? Contact us at
                                <a href="mailto:support@healthshade.com" style="color:#635bff;text-decoration:none;">
                                    support@healthshade.com
                                </a>
                            </p>
                            <p style="margin:14px 0 0;">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
    </table>
    </td>
    </tr>
    </table>



</body>

</html>