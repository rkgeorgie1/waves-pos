<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note {{ $warehouseTransfer->reference }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 20px; background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 1.4; }
        .toolbar { max-width: 760px; margin: 0 auto 10px; display: flex; gap: 6px; }
        .btn { display: inline-block; padding: 5px 10px; font-size: 12px; border: 1px solid #999; background: #fff; color: #000; text-decoration: none; cursor: pointer; }
        .btn-print { background: #000; border-color: #000; color: #fff; }
        .paper { max-width: 760px; margin: 0 auto; background: #fff; padding: 20px 24px; border: 1px solid #ccc; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .brand { }
        .brand img {
            display: block;
            max-height: 50px;
            max-width: 160px;
            height: auto;
            object-fit: contain;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .brand-name { font-size: 16px; font-weight: bold; }
        .brand-sub { font-size: 11px; color: #333; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 18px; }
        .doc-title .ref { font-size: 11px; margin-top: 2px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta td { padding: 2px 4px; font-size: 12px; vertical-align: top; }
        .meta .label { color: #555; width: 110px; }
        .meta .value { font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { border: 1px solid #000; border-bottom: 2px solid #000; padding: 5px 6px; font-size: 11px; text-align: left; background: #f2f2f2; }
        table.items td { border: 1px solid #ccc; padding: 5px 6px; font-size: 12px; }
        .totals { margin-top: 8px; font-size: 12px; text-align: right; }
        .signatures { display: table; width: 100%; margin-top: 24px; }
        .sig { display: table-cell; width: 33%; padding: 0 8px; font-size: 11px; color: #333; }
        .sig-line { border-top: 1px solid #000; margin-top: 28px; padding-top: 4px; }
        .footer { margin-top: 16px; text-align: center; font-size: 10px; color: #555; border-top: 1px solid #ccc; padding-top: 6px; }
        @media print { @page { margin: 10mm; } body { background: #fff; padding: 0; } .toolbar { display: none !important; } .paper { border: none; max-width: 100%; padding: 0; } }
    </style>
</head>
<body>
@php
    $settings = \App\Models\Setting::pluck('value', 'key');
    $businessName = $settings['business_name'] ?? 'Airwaves Wholesalers';
    $logoPath = trim((string) ($settings['business_logo'] ?? ''));
    $logoUrl = '';
    if ($logoPath !== '') {
        $logoUrl = str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')
            ? $logoPath
            : (str_starts_with($logoPath, '/') ? $logoPath : asset('storage/'.$logoPath));
    }
@endphp
<div class="toolbar">
    <a href="{{ route('warehouse_transfers.show', $warehouseTransfer) }}" class="btn">Back</a>
    <button type="button" class="btn btn-print" onclick="window.print()">Print</button>
</div>
<div class="paper">
    <div class="header">
        <div class="brand">
            @if($logoUrl !== '')
                <img src="{{ $logoUrl }}" alt="{{ $businessName }} logo">
            @else
                <div class="brand-name">{{ $businessName }}</div>
            @endif
            @if(!empty($settings['business_address']))<div class="brand-sub">{{ $settings['business_address'] }}</div>@endif
            @if(!empty($settings['business_phone']))<div class="brand-sub">Tel: {{ $settings['business_phone'] }}</div>@endif
        </div>
        <div class="doc-title">
            <h1>DELIVERY NOTE</h1>
            <div class="ref">{{ $warehouseTransfer->reference }}</div>
        </div>
    </div>

    <table class="meta">
        <tr><td class="label">Transfer #:</td><td class="value">{{ $warehouseTransfer->reference }}</td><td class="label">Date:</td><td class="value">{{ $warehouseTransfer->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td class="label">From:</td><td class="value">{{ $warehouseTransfer->fromWarehouse->name ?? '-' }}</td><td class="label">To:</td><td class="value">{{ $warehouseTransfer->toWarehouse->name ?? '-' }}</td></tr>
        <tr><td class="label">Status:</td><td class="value">{{ ucfirst($warehouseTransfer->status) }}</td><td></td><td></td></tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Item Description</th>
                <th style="width:60px; text-align:right;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($warehouseTransfer->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product_name }}@if($item->product && $item->product->sku) ({{ $item->product->sku }})@endif</td>
                    <td style="text-align:right;">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        Total Items: {{ $warehouseTransfer->items->count() }} &nbsp;|&nbsp; Total Qty: {{ rtrim(rtrim(number_format($warehouseTransfer->items->sum('quantity'), 2), '0'), '.') }}
    </div>

    @if($warehouseTransfer->notes)
        <div style="margin-top:8px; font-size:11px;"><strong>Notes:</strong> {{ $warehouseTransfer->notes }}</div>
    @endif

    <div class="signatures">
        <div class="sig"><div class="sig-line">Prepared by</div></div>
        <div class="sig"><div class="sig-line">Dispatched by</div></div>
        <div class="sig"><div class="sig-line">Received by</div></div>
    </div>

    <div class="footer">Goods transferred in good condition.</div>
</div>
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
</body>
</html>
