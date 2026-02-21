<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shipping Labels</title>
    <style>
        @media print {
            @page {
                size: 4in 6in;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .page-break {
                page-break-after: always;
            }
        }
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0; 
            margin: 0;
            padding: 0;
        }
        .label-container {
            width: 4in;
            height: 6in;
            background: #fff;
            box-sizing: border-box;
            position: relative;
            margin: 10px auto; 
            border: 1px solid #000;
            display: flex;
            flex-direction: column;
        }
        @media print {
            .label-container {
                margin: 0;
                border: none;
                width: 100%;
                height: 100%;
            }
        }

        table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            font-size: 11px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        /* Top Header */
        .top-logo {
            text-align: center;
            vertical-align: middle;
            height: 60px;
        }
        .top-logo img {
            max-width: 90%;
            max-height: 50px;
        }
        .top-method {
            text-align: center;
            vertical-align: middle;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        /* Date / Barcode Area */
        .date-area {
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
            height: 60px;
        }
        .barcode-area {
            padding: 0 !important;
            text-align: center;
            vertical-align: top;
        }
        .ref-label {
            font-size: 10px;
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 2px 4px;
        }
        .barcode-flex {
            display: flex;
            align-items: stretch;
            width: 100%;
            height: 48px;
        }
        .barcode-svg-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            border-right: 1px solid #000;
            height: 100%;
        }
        .qrcode-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 55px;
            height: 100%;
        }
        
        /* Addresses */
        .address-header {
            background-color: #000 !important;
            color: #fff !important;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 11px;
            padding: 3px 6px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .address-col {
            width: 50%;
        }
        .address-fields {
            height: 110px;
        }
        .address-fields div {
            margin-bottom: 2px;
        }

        /* Tracking Sticker Area */
        .tracking-sticker-area {
            height: 80px;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            font-weight: normal;
            border-top: none;
        }

        /* Additional Info */
        .additional-info {
            height: 40px;
            font-weight: bold;
            font-size: 11px;
        }

        /* Handle with care */
        .handle-care {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            vertical-align: middle;
            height: 25px;
        }

        /* Footer */
        .footer-row td {
            font-size: 9px;
            padding: 2px 6px;
            border-top: none;
            height: 15px;
            vertical-align: middle;
        }
    </style>
    <!-- JS Barcode Libraries from CDN to ensure they load -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
</head>
<body onload="setTimeout(() => window.print(), 1000)">

@foreach($sales as $sale)
    @php
        $setting = \App\Models\Setting::first();
        
        $shippingMethodName = $sale->shippingMethod->name ?? 'Standard Shipping';
        $isPickup = str_contains(strtolower($shippingMethodName), 'pickup') || str_contains(strtolower($shippingMethodName), 'collection');
        
        // Use Shipment Ref if available, otherwise Sale Ref
        $companyRef = $sale->shipment->Ref ?? $sale->Ref;
        
        // Ship To Logic
        if ($isPickup) {
             $toName = "PICKUP ORDER";
             $toPhone = $pos_settings->CompanyPhone ?? '';
             $toAddress = ($pos_settings->CompanyAdress ?? '') . '<br>(Customer will collect)';
        } else {
             $toName = $sale->client->name ?? 'Customer';
             $toPhone = $sale->client->phone ?? '';
             $toAddress = $sale->shipment->shipping_address ?? $sale->client->adresse ?? '';
             // City/State parsing could be complex, assuming combined in address for now.
        }

        // Store Info
        $storeName = $setting->CompanyName ?? 'Store Name';
        $storePhone = $setting->CompanyPhone ?? '';
        $storeEmail = $setting->email ?? '';
        $storeAddress = $setting->CompanyAdress ?? '';
        $logo = $setting->logo ?? null;
    @endphp

    <div class="label-container{{ !$loop->last ? ' page-break' : '' }}">
        <table>
            <!-- Row 1: Logo and Shipping Method -->
            <tr>
                <td class="top-logo" style="width: 50%;">
                    @if($logo)
                        <img src="/images/{{$logo}}" alt="Logo">
                    @else
                        <b>COMPANY LOGO</b>
                    @endif
                </td>
                <td class="top-method" style="width: 50%;">
                    {{ $shippingMethodName }}
                </td>
            </tr>

            <!-- Row 2: Date and Barcode -->
            <tr>
                <td class="date-area">
                    Date: {{ \Carbon\Carbon::parse($sale->date)->format('M d, Y') }}
                </td>
                <td class="barcode-area">
                    <div class="ref-label">Company Reference:</div>
                    <div class="barcode-flex">
                        <div class="barcode-svg-container">
                            <svg id="barcode-{{$sale->id}}"></svg>
                        </div>
                        <div class="qrcode-container">
                            <div id="qrcode-{{$sale->id}}"></div>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- Row 3: Address Headers -->
            <tr>
                <td class="address-header address-col" style="border-right: none;">SHIP FROM</td>
                <td class="address-header address-col" style="border-left: none;">SHIP TO</td>
            </tr>

            <!-- Row 4: Address Content -->
            <tr>
                <td class="address-fields">
                    <div>Name: {{ $storeName }}</div>
                    <div>Address: {{ $storeAddress }}</div>
                    <div>Phone: {{ $storePhone }}</div>
                    <div>Email: {{ $storeEmail }}</div>
                </td>
                <td class="address-fields">
                    <div>Name: {{ $toName }}</div>
                    <div>Address: {!! nl2br(e($toAddress)) !!}</div>
                    <div>Phone: {{ $toPhone }}</div>
                    <div>Email: {{ $sale->client->email ?? '' }}</div>
                </td>
            </tr>

            <!-- Row 5: Sticker Area -->
            <tr>
                <td colspan="2" class="tracking-sticker-area">
                    COURIER TRACKING LABEL / STICKER AREA
                </td>
            </tr>

            <!-- Row 6: Additional Info -->
            <tr>
                <td colspan="2" class="additional-info">
                    Additional Information:<br>
                    <span style="font-weight:normal; font-size: 10px;">{{ $sale->notes ?: implode(', ', $sale->details->pluck('product.name')->toArray()) }}</span>
                </td>
            </tr>

            <!-- Row 7: Handle with Care -->
            <tr>
                <td colspan="2" class="handle-care">
                    PLEASE HANDLE WITH CARE
                </td>
            </tr>

            <!-- Row 8: Footer -->
            <tr class="footer-row">
                <td style="border-right:none;">Packed: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</td>
                <td style="text-align:right; border-left:none;">Page 1 of 1</td>
            </tr>
        </table>

        <!-- Init Scripts for this label -->
        <script>
            // Generate Code128
            try {
                JsBarcode("#barcode-{{$sale->id}}", "{{$companyRef}}", {
                    format: "CODE128",
                    width: 1.2,
                    height: 35,
                    displayValue: false,
                    margin: 0
                });
            } catch (e) {
                console.error("Barcode error", e);
            }

            // Generate QR
            try {
                new QRCode(document.getElementById("qrcode-{{$sale->id}}"), {
                    text: "{{$companyRef}}",
                    width: 45,
                    height: 45,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.L
                });
            } catch (e) {
                console.error("QR error", e);
            }
        </script>
    </div>
@endforeach

</body>
</html>
