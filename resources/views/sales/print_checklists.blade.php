<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Picking Checklist</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; }
        .order-card { 
            border: 2px solid #000; 
            margin-bottom: 20px; 
            page-break-inside: avoid;
        }
        .order-header {
            display: flex;
            background-color: #ececec;
            border-bottom: 2px solid #000;
            padding: 10px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .header-section {
            flex: 1;
            padding: 0 10px;
            border-right: 1px solid #ccc;
        }
        .header-section:last-child {
            border-right: none;
        }
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .items-table th, .items-table td { 
            border-bottom: 1px solid #ddd;
            border-right: 1px solid #ddd;
            padding: 8px 12px; 
            text-align: left; 
        }
        .items-table th { 
            background-color: #fafafa; 
            border-bottom: 2px solid #000;
            font-weight: bold;
        }
        .checkbox-col { width: 50px; text-align: center; }
        .checkbox-box { width: 22px; height: 22px; border: 2px solid #000; display: inline-block; vertical-align: middle; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <button class="no-print" onclick="window.print()" style="margin-bottom: 15px; padding: 5px 15px; cursor: pointer;">Print</button>
    <h2 style="margin-top: 0;">Picking Checklist</h2>
    <p>Printed: {{ date('Y-m-d H:i') }}</p>

    @foreach($sales as $sale)
    <div class="order-card">
        <div class="order-header">
            <div class="header-section">
                <strong>Order Ref:</strong> {{ $sale->Ref }}<br><br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->date)->format('Y-m-d H:i') }}
            </div>
            <div class="header-section">
                <strong>Customer:</strong> {{ $sale->client->name ?? 'N/A' }}<br><br>
                <strong>Phone:</strong> {{ $sale->client->phone ?? 'N/A' }}
            </div>
            <div class="header-section">
                <strong>Method:</strong> {{ $sale->shippingMethod->name ?? 'Standard' }}<br><br>
                <strong>Address:</strong> {{ $sale->shipment->shipping_address ?? $sale->client->adresse ?? '-' }}
            </div>
        </div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="checkbox-col">Done</th>
                    <th>Item Description</th>
                    <th class="text-right">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->details as $detail)
                <tr>
                    <td class="checkbox-col text-center">
                        <div class="checkbox-box"></div>
                    </td>
                    <td>
                        {{ $detail->product->name ?? 'Unknown Product' }} 
                        @if($detail->product_variant_id)
                            <br><small>(Variant: {{ $detail->productVariant->name ?? 'Unknown' }})</small>
                        @endif
                    </td>
                    <td class="text-right">
                        <strong style="font-size: 15px;">{{ $detail->quantity }}</strong> {{ $detail->product->unitSale->ShortName ?? '' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
</body>
</html>
