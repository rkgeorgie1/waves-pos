@extends('layouts.app')
@section('title', 'Purchase '.$purchase->reference)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Purchase {{ $purchase->reference }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a>
        <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">All Purchases</a>
    </div>
</div>

<div class="card p-3 mb-3">
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="text-muted small">Supplier</div>
            <div class="fw-semibold">{{ $purchase->supplier->name ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small">Location</div>
            <div class="fw-semibold">{{ $purchase->warehouse->name ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small">Status</div>
            <div class="fw-semibold text-capitalize">{{ $purchase->status }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small">Date</div>
            <div class="fw-semibold">{{ ($purchase->ordered_at ? \Carbon\Carbon::parse($purchase->ordered_at) : $purchase->created_at)->format('d-m-Y H:i') }}</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Received</th>
                    <th class="text-end">Unit Cost</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $i)
                    <tr>
                        <td>{{ $i->product_name }}</td>
                        <td class="text-end">{{ number_format($i->quantity, 2) }}</td>
                        <td class="text-end">{{ number_format($i->received_quantity, 2) }}</td>
                        <td class="text-end">KSh {{ number_format($i->unit_cost, 2) }}</td>
                        <td class="text-end fw-semibold">KSh {{ number_format($i->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
        <div class="fs-5 fw-bold">Total: KSh {{ number_format($purchase->total, 2) }}</div>
        @if($purchase->status !== 'received')
            <form method="POST" action="{{ route('purchases.receive', $purchase) }}">@csrf<button class="btn btn-success">Receive Goods</button></form>
        @endif
    </div>
</div>
@endsection
