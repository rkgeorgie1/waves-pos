@extends('layouts.app')
@section('title', 'Sale ' . $sale->reference)

@section('content')
@php
    $currency = $settings['currency'] ?? 'KES';
    $businessName = $settings['business_name'] ?? 'Airwaves Wholesalers';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 fw-semibold">Invoice {{ $sale->reference }}</h5>
            @if($sale->payment_status === 'paid')
                <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
            @elseif($sale->payment_status === 'partial')
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Partial</span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ ucfirst($sale->payment_status ?? $sale->status) }}</span>
            @endif
        </div>
        <div class="text-muted small">
            Issued on {{ ($sale->sold_at ? \Carbon\Carbon::parse($sale->sold_at) : $sale->created_at)->format('d M Y, H:i') }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pos') }}" class="btn btn-sm btn-outline-secondary">&larr; POS Terminal</a>
        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">All Sales</a>
        <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn btn-sm btn-primary">
            <i class="bi bi-printer me-1"></i>Print Receipt
        </a>
        <a href="{{ route('sales.delivery_note', $sale) }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-truck me-1"></i>Delivery Note
        </a>
    </div>
</div>

<div class="card p-4 mb-3">
    <div class="row g-3 mb-4 pb-3 border-bottom">
        <div class="col-md-4">
            <div class="text-muted small text-uppercase fw-semibold mb-1">From</div>
            <div class="fw-semibold">{{ $businessName }}</div>
            <div class="text-muted small">Warehouse: {{ $sale->warehouse->name ?? 'Main Warehouse' }}</div>
            <div class="text-muted small">Served By: {{ $sale->user->name ?? 'System' }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Billed To</div>
            <div class="fw-semibold">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
            @if($sale->customer && $sale->customer->phone)
                <div class="text-muted small">Tel: {{ $sale->customer->phone }}</div>
            @endif
            @if($sale->customer && $sale->customer->email)
                <div class="text-muted small">{{ $sale->customer->email }}</div>
            @endif
        </div>
        <div class="col-md-4 text-md-end">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Summary</div>
            <div class="small text-muted">Invoice No.: <strong class="text-dark">{{ $sale->reference }}</strong></div>
            <div class="small text-muted">Payment Method: <strong class="text-dark">{{ $sale->payment_method_label }}</strong></div>
            <div class="small text-muted">Total Items: <strong class="text-dark">{{ rtrim(rtrim(number_format($sale->items->sum('quantity'), 2), '0'), '.') }}</strong></div>
            @if($sale->shipping_status)
                <div class="small text-muted">Shipping Status: <strong class="text-dark">{{ $sale->shipping_status }}</strong></div>
            @endif
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Product Description</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit Price ({{ $currency }})</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Line Total ({{ $currency }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $idx => $item)
                    <tr>
                        <td class="text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-medium">{{ $item->product_name }}</div>
                            @if($item->product && $item->product->sku)
                                <div class="text-muted small">SKU: {{ $item->product->sku }}</div>
                            @endif
                        </td>
                        <td class="text-end tabular-nums">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td class="text-end tabular-nums">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end tabular-nums">{{ number_format($item->discount, 2) }}</td>
                        <td class="text-end fw-semibold tabular-nums">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-between pt-3 border-top g-3">
        <div class="col-md-6">
            <h6 class="small fw-semibold text-uppercase text-muted mb-2">Payments Recorded</h6>
            @if($sale->payments && $sale->payments->count() > 0)
                <table class="table table-sm table-bordered mb-0" style="max-width: 360px;">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Method</th>
                            <th class="text-end">Amount ({{ $currency }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->payments as $p)
                            <tr>
                                <td class="small">{{ $p->reference }}</td>
                                <td class="small text-uppercase">{{ str_replace('_', ' ', $p->method) }}</td>
                                <td class="text-end small fw-medium tabular-nums">{{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-muted small">No separate payment records logged.</div>
            @endif
            @if($sale->notes)
                <div class="mt-3">
                    <div class="small fw-semibold text-muted">Sell Note</div>
                    <div class="small">{{ $sale->notes }}</div>
                </div>
            @endif
            @if($sale->staff_note)
                <div class="mt-2">
                    <div class="small fw-semibold text-muted">Staff Note</div>
                    <div class="small">{{ $sale->staff_note }}</div>
                </div>
            @endif

            <div class="mt-3 pt-3 border-top">
                <h6 class="small fw-semibold text-uppercase text-muted mb-2">Update Shipping Status &amp; Details</h6>
                <form method="POST" action="{{ route('sales.update_shipping', $sale) }}" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Shipping Status</label>
                        <select name="shipping_status" class="form-select form-select-sm">
                            <option value="">-- None --</option>
                            @foreach(['Ordered', 'Packed', 'Shipped', 'Delivered', 'Cancelled'] as $st)
                                <option value="{{ $st }}" {{ $sale->shipping_status === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small text-muted mb-1">Shipping Details</label>
                        <div class="input-group input-group-sm">
                            <input name="shipping_details" class="form-control" value="{{ $sale->shipping_details }}" placeholder="Delivery address, courier, phone...">
                            <button class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Subtotal</span>
                <span class="tabular-nums fw-medium">{{ $currency }} {{ number_format($sale->subtotal ?: $sale->items->sum('total'), 2) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Discount</span>
                <span class="tabular-nums">-{{ $currency }} {{ number_format($sale->discount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Tax / VAT</span>
                <span class="tabular-nums">{{ $currency }} {{ number_format($sale->tax, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between py-2 my-1 border-top border-bottom fw-bold fs-6">
                <span>Total</span>
                <span class="tabular-nums">{{ $currency }} {{ number_format($sale->total, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Amount Paid</span>
                <span class="tabular-nums text-success fw-semibold">{{ $currency }} {{ number_format($sale->paid, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Balance Due</span>
                <span class="tabular-nums {{ $sale->balance > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $currency }} {{ number_format(max(0, $sale->balance), 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
