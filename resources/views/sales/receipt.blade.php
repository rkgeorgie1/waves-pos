<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $sale->reference }}</title>
    @php
        $paperSize = $settings['receipt_paper_size'] ?? '80mm';
        $paperWidth = $paperSize === '58mm' ? '58mm' : ($paperSize === 'A4' ? '190mm' : '80mm');
    @endphp
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 20px 10px;
            background: #f1f1f1;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.35;
        }
        .toolbar {
            width: {{ $paperWidth }};
            max-width: 100%;
            margin: 0 auto 12px;
            display: flex;
            gap: 6px;
        }
        .btn {
            display: inline-block;
            padding: 6px 10px;
            font-size: 12px;
            font-family: Arial, Helvetica, sans-serif;
            border: 1px solid #bbb;
            background: #fff;
            color: #000;
            text-decoration: none;
            cursor: pointer;
            text-align: center;
            flex: 1;
        }
        .btn-print {
            background: #000;
            border-color: #000;
            color: #fff;
            font-weight: bold;
        }
        .receipt-paper {
            width: {{ $paperWidth }};
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 14px 12px;
            border: 1px solid #ddd;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 2px;
        }
        .receipt-logo {
            display: block;
            width: 100%;
            max-width: 120px;
            max-height: 48px;
            height: auto;
            margin: 0 auto 5px;
            object-fit: contain;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .store-line {
            font-size: 11px;
            margin: 1px 0;
        }
        .doc-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 6px;
            padding: 3px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .rule-dashed {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .rule-solid {
            border: none;
            border-top: 1px solid #000;
            margin: 6px 0;
        }
        .meta-table, .items-table, .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-variant-numeric: tabular-nums;
        }
        .meta-table td {
            padding: 1px 0;
            font-size: 11px;
        }
        .items-table th {
            font-size: 11px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding: 4px 0;
            text-align: left;
        }
        .items-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 11.5px;
            border-bottom: 1px dotted #ccc;
        }
        .totals-table td {
            padding: 2px 0;
            font-size: 11.5px;
        }
        .totals-table tr.grand-row td {
            font-size: 14px;
            font-weight: bold;
            padding: 5px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .footer {
            margin-top: 10px;
            font-size: 11px;
            text-align: center;
        }
        @media print {
            @page {
                margin: 0;
            }
            body {
                background: #fff;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .receipt-paper {
                border: none;
                width: {{ $paperWidth }};
                padding: 6px 4px;
            }
        }
    </style>
</head>
<body>
@php
    $businessName = $settings['business_name'] ?? 'Airwaves Wholesalers';
    $currency = $settings['currency'] ?? 'KES';
    $headerTitle = $settings['receipt_header_title'] ?? 'CASH SALE RECEIPT';
    $showWarehouse = ($settings['receipt_show_warehouse'] ?? '1') === '1';
    $showCustomer = ($settings['receipt_show_customer'] ?? '1') === '1';
    $showCashier = ($settings['receipt_show_cashier'] ?? '1') === '1';
    $showSku = ($settings['receipt_show_sku'] ?? '0') === '1';
    $showTax = ($settings['receipt_show_tax'] ?? '1') === '1';
    $showPayments = ($settings['receipt_show_payments'] ?? '1') === '1';
    $showBarcode = ($settings['receipt_show_barcode'] ?? '1') === '1';
    $autoPrint = ($settings['receipt_auto_print'] ?? '1') === '1';
    $footerNote = $settings['receipt_footer_note'] ?? 'Goods once sold are subject to company return policy.';
    $thankYou = $settings['receipt_thank_you'] ?? 'Thank you for shopping with us.';

    $logoPath = trim((string) ($settings['business_logo'] ?? ''));
    $logoUrl = '';
    if ($logoPath !== '') {
        $logoUrl = str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')
            ? $logoPath
            : (str_starts_with($logoPath, '/') ? $logoPath : asset('storage/'.$logoPath));
    }

    $subtotal = (float) ($sale->subtotal ?: $sale->items->sum('total'));
    $discount = (float) $sale->discount;
    $tax = (float) $sale->tax;
    $total = (float) $sale->total;
    $paid = (float) $sale->paid;
    $diff = $paid - $total;
@endphp

<div class="toolbar">
    <a href="{{ route('pos') }}" class="btn">POS</a>
    <a href="{{ route('sales.show', $sale) }}" class="btn">Invoice</a>
    <a href="{{ route('settings.receipt') }}" class="btn">Setup</a>
    <button type="button" class="btn btn-print" onclick="window.print()">Print</button>
</div>

<div class="receipt-paper">
    <div class="text-center">
        @if($logoUrl !== '')
            <img class="receipt-logo" src="{{ $logoUrl }}" alt="{{ $businessName }} logo">
        @else
            <div class="store-name">{{ $businessName }}</div>
        @endif
        @if($showWarehouse && $sale->warehouse)
            <div class="store-line">{{ $sale->warehouse->name }}</div>
        @endif
        @if(!empty($settings['business_address']))
            <div class="store-line">{{ $settings['business_address'] }}</div>
        @endif
        @if(!empty($settings['business_phone']))
            <div class="store-line">Tel: {{ $settings['business_phone'] }}</div>
        @endif
        @if(!empty($settings['business_email']))
            <div class="store-line">{{ $settings['business_email'] }}</div>
        @endif
        @if(!empty($settings['business_tax_pin']))
            <div class="store-line">PIN: {{ $settings['business_tax_pin'] }}</div>
        @endif
        @if(!empty($headerTitle))
            <div class="doc-title">{{ $headerTitle }}</div>
        @endif
    </div>

    <table class="meta-table" style="margin-top: 6px;">
        <tr>
            <td>Receipt #:</td>
            <td class="text-right bold">{{ $sale->reference }}</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td class="text-right">{{ ($sale->sold_at ? \Carbon\Carbon::parse($sale->sold_at) : $sale->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
        @if($showCustomer)
            <tr>
                <td>Customer:</td>
                <td class="text-right">{{ $sale->customer->name ?? 'Walk-in' }}</td>
            </tr>
        @endif
        @if($showCashier)
            <tr>
                <td>Cashier:</td>
                <td class="text-right">{{ $sale->user->name ?? 'Cashier' }}</td>
            </tr>
        @endif
    </table>

    <hr class="rule-solid">

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right" style="width: 36px;">Qty</th>
                <th class="text-right" style="width: 58px;">Price</th>
                <th class="text-right" style="width: 66px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}
                        @if($showSku && $item->product && $item->product->sku)
                            <br><span style="font-size: 10px;">({{ $item->product->sku }})</span>
                        @endif
                    </td>
                    <td class="text-right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right bold">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table" style="margin-top: 6px;">
        <tr>
            <td>Subtotal ({{ $sale->items->count() }} {{ Str::plural('item', $sale->items->count()) }})</td>
            <td class="text-right">{{ number_format($subtotal, 2) }}</td>
        </tr>
        @if($discount > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">-{{ number_format($discount, 2) }}</td>
            </tr>
        @endif
        @if($showTax && $tax > 0)
            <tr>
                <td>VAT / Tax</td>
                <td class="text-right">{{ number_format($tax, 2) }}</td>
            </tr>
        @endif
        <tr class="grand-row">
            <td>TOTAL ({{ $currency }})</td>
            <td class="text-right">{{ number_format($total, 2) }}</td>
        </tr>
        @if($showPayments && $sale->payments && $sale->payments->count() > 0)
            @foreach($sale->payments as $pay)
                <tr>
                    <td>Paid ({{ strtoupper(str_replace('_', ' ', $pay->method)) }})</td>
                    <td class="text-right">{{ number_format($pay->amount, 2) }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td>Tendered</td>
                <td class="text-right">{{ number_format($paid, 2) }}</td>
            </tr>
        @endif
        @if($diff >= 0)
            <tr>
                <td class="bold">Change</td>
                <td class="text-right bold">{{ number_format($diff, 2) }}</td>
            </tr>
        @else
            <tr>
                <td class="bold">Balance Due</td>
                <td class="text-right bold">{{ number_format(abs($diff), 2) }}</td>
            </tr>
        @endif
    </table>

    <hr class="rule-dashed">

    <div class="footer">
        @if(!empty($footerNote))
            <div>{{ $footerNote }}</div>
        @endif
        @if(!empty($thankYou))
            <div class="bold" style="margin-top: 4px;">{{ $thankYou }}</div>
        @endif
        @if($showBarcode)
            <div style="margin-top: 6px; font-size: 11px; font-weight: 600; letter-spacing: 0.3px;">{{ $sale->reference }}</div>
        @endif
    </div>
</div>

@if($autoPrint)
<script>
    window.addEventListener('load', function () {
        var images = Array.prototype.slice.call(document.images);
        Promise.all(images.map(function (image) {
            if (image.complete) {
                return Promise.resolve();
            }
            return new Promise(function (resolve) {
                image.addEventListener('load', resolve, { once: true });
                image.addEventListener('error', resolve, { once: true });
            });
        })).then(function () {
            window.print();
        });
    });
</script>
@endif
</body>
</html>
