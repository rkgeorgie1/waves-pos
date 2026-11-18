@extends('layouts.app')
@section('title', 'Purchase Return ' . $purchaseReturn->reference)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Purchase Return {{ $purchaseReturn->reference }}</h4>
    <a href="{{ route('purchase_returns.index') }}" class="btn btn-sm btn-outline-secondary">All Purchase Returns</a>
</div>

<div class="card p-4">
    <div class="row g-3 mb-4 pb-3 border-bottom">
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Supplier</div>
            <div class="fw-semibold">{{ $purchaseReturn->supplier->name ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Purchase Ref</div>
            <div class="fw-semibold">{{ $purchaseReturn->purchase->reference ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Date</div>
            <div class="fw-semibold">{{ $purchaseReturn->created_at->format('d-m-Y H:i') }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Status</div>
            <div><span class="badge bg-success-subtle text-success border border-success-subtle">{{ ucfirst($purchaseReturn->status) }}</span></div>
        </div>
        @if($purchaseReturn->reason)
        <div class="col-md-12">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Reason</div>
            <div>{{ $purchaseReturn->reason }}</div>
        </div>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit Cost</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseReturn->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-end">KSh {{ number_format($item->unit_cost, 2) }}</td>
                        <td class="text-end fw-semibold">KSh {{ number_format($item->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No items recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mt-3 pt-3 border-top">
        <div class="fs-5 fw-bold text-danger">Total Return Amount: KSh {{ number_format($purchaseReturn->amount, 2) }}</div>
    </div>
</div>
@endsection
