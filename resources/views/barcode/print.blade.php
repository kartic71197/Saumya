<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Labels</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                margin: 0;
                size: 54mm 25mm;
            }

            body {
                margin: 0;
                padding: 0;
                background: white;
                font-family: Arial, sans-serif;
            }

            .label {
                width: 54mm;
                height: 25mm;
                padding: 0;
                background: white;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                page-break-after: always;
                page-break-inside: avoid;
                border: none;
                overflow: hidden;
            }

            .label:last-child {
                page-break-after: avoid;
            }

            .product-name {
                font-size: 7pt;
                font-weight: bold;
                color: black;
                margin-bottom: 0.5mm;
                white-space: wrap;
                width: 100%;
            }

            .product-category {
                font-size: 4pt;
                color: #666;
                margin-bottom: 0.5mm;
                line-height: 1;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                width: 100%;
            }

            .barcode-container {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                max-height: 12mm;
                margin: 0.5mm 0;
            }

            .barcode-container svg {
                max-width: 50mm;
                max-height: 10mm;
                width: auto;
                height: auto;
            }

            .barcode-text {
                font-size: 4pt;
                color: black;
                font-family: monospace;
                letter-spacing: 0.2px;
                margin-top: 0.5mm;
                line-height: 1;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                width: 100%;
            }
        }

        /* Screen preview styles */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .controls {
            margin-bottom: 20px;
            text-align: center;
        }

        .print-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }

        .print-btn:hover {
            background: #2563eb;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .label {
            width: 200px;
            height: 93px;
            /* Scaled preview: 54mm x 25mm */
            padding: 4px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-name {
            font-size: 7pt;
            font-weight: bold;
            color: black;
            margin-bottom: 0.5mm;
            white-space: wrap;
            width: 100%;
        }

        .product-category {
            font-size: 8px;
            color: #666;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .barcode-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin: 2px 0;
        }

        .barcode-container svg {
            max-width: 180px;
            max-height: 40px;
            width: auto;
            height: auto;
        }

        .barcode-text {
            font-size: 8px;
            color: #333;
            font-family: monospace;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        @media screen {
            .print-only {
                display: none;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="controls no-print">
        <h1 style="margin-bottom: 20px; color: #333;">Barcode Labels Preview</h1>
        <button class="print-btn" onclick="window.print()">🖨️ Print Labels</button>
        <p style="margin-top: 10px; color: #666; font-size: 14px;">
            Labels sized for 25mm × 54mm paper
        </p>
    </div>

    <div class="preview-grid no-print">
        <!-- Sample labels for preview -->
        <div class="label">
            <div class="product-name">Sample Product Name</div>
            <div class="product-category">Electronics</div>
            <div class="barcode-container">
                <svg width="120" height="30" viewBox="0 0 120 30">
                    <g stroke="black" stroke-width="1">
                        <rect x="0" y="0" width="2" height="25" fill="black" />
                        <rect x="3" y="0" width="1" height="25" fill="black" />
                        <rect x="5" y="0" width="2" height="25" fill="black" />
                        <rect x="9" y="0" width="1" height="25" fill="black" />
                        <rect x="12" y="0" width="3" height="25" fill="black" />
                        <rect x="17" y="0" width="1" height="25" fill="black" />
                        <rect x="20" y="0" width="2" height="25" fill="black" />
                        <rect x="24" y="0" width="1" height="25" fill="black" />
                        <rect x="27" y="0" width="2" height="25" fill="black" />
                        <rect x="31" y="0" width="1" height="25" fill="black" />
                        <rect x="34" y="0" width="3" height="25" fill="black" />
                        <rect x="39" y="0" width="1" height="25" fill="black" />
                        <rect x="42" y="0" width="2" height="25" fill="black" />
                        <rect x="46" y="0" width="1" height="25" fill="black" />
                        <rect x="49" y="0" width="2" height="25" fill="black" />
                    </g>
                </svg>
            </div>
            <div class="barcode-text">123456789</div>
        </div>

        <div class="label">
            <div class="product-name">Another Product with Longer Name</div>
            <div class="product-category">Category</div>
            <div class="barcode-container">
                <svg width="120" height="30" viewBox="0 0 120 30">
                    <g stroke="black" stroke-width="1">
                        <rect x="0" y="0" width="1" height="25" fill="black" />
                        <rect x="2" y="0" width="2" height="25" fill="black" />
                        <rect x="6" y="0" width="1" height="25" fill="black" />
                        <rect x="9" y="0" width="2" height="25" fill="black" />
                        <rect x="13" y="0" width="1" height="25" fill="black" />
                        <rect x="16" y="0" width="3" height="25" fill="black" />
                        <rect x="21" y="0" width="1" height="25" fill="black" />
                        <rect x="24" y="0" width="2" height="25" fill="black" />
                        <rect x="28" y="0" width="1" height="25" fill="black" />
                        <rect x="31" y="0" width="2" height="25" fill="black" />
                        <rect x="35" y="0" width="1" height="25" fill="black" />
                        <rect x="38" y="0" width="3" height="25" fill="black" />
                        <rect x="43" y="0" width="1" height="25" fill="black" />
                        <rect x="46" y="0" width="2" height="25" fill="black" />
                    </g>
                </svg>
            </div>
            <div class="barcode-text">987654321</div>
        </div>
    </div>

    <!-- Print labels (one per page) -->
    @foreach($productsWithBarcodes as $product)
        <div class="label print-only">
            <div class="product-name">{{ $product['name'] }}</div>
            <div class="barcode-container">
                {!! $product['barcode_html'] !!}
            </div>
            <div class="barcode-text">{{ $product['barcode_data'] }}</div>
        </div>
    @endforeach

    <script>
        window.addEventListener('beforeprint', function () {
            // Ensure proper print formatting
            document.body.style.margin = '0';
            document.body.style.padding = '0';
        });
    </script>
</body>

</html>