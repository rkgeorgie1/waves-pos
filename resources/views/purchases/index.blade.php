@extends('layouts.app')
@section('title', 'Purchases')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Purchases</h4>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">+ Add Purchase</a>
</div>

<!-- Filters Card -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('purchases.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Date Range</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    <span class="input-group-text">to</span>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Payment Status</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="due" {{ request('payment_status') === 'due' ? 'selected' : '' }}>Due</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Purchase Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>Ordered</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm px-3">Clear</a>
            </div>
        </div>
    </form>
</div>

<!-- Purchases Table -->
<div class="card p-3" style="overflow: visible;">
    <div class="table-responsive" style="overflow: visible;">
        <table class="table table-hover align-middle mb-0" style="overflow: visible; font-size: 0.8rem; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <thead class="table-light">
                <tr>
                    <th style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #e5e7eb; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Date</th>
                    <th style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #e5e7eb; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Reference No</th>
                    <th style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #e5e7eb; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Location</th>
                    <th style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #e5e7eb; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Supplier</th>
                    <th style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #e5e7eb; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Purchase Status</th>
                    <th class="text-end" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #e5e7eb; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Grand Total</th>
                    <th class="text-end" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #e5e7eb; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Payment Due</th>
                    <th class="text-end" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #e5e7eb; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                    <tr>
                        <td>{{ ($p->ordered_at ? \Carbon\Carbon::parse($p->ordered_at) : $p->created_at)->format('d-m-Y H:i') }}</td>
                        <td class="fw-semibold"><a href="{{ route('purchases.show', $p) }}" class="text-decoration-none">{{ $p->reference }}</a></td>
                        <td>{{ $p->warehouse->name ?? 'Main Warehouse' }}</td>
                        <td>{{ $p->supplier->name ?? '-' }}</td>
                        <td>
                            @if($p->status === 'received')
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem; padding: 3px 6px; border-radius: 6px; font-weight: 500;">Received</span>
                            @elseif($p->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.68rem; padding: 3px 6px; border-radius: 6px; font-weight: 500;">Pending</span>
                            @else
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.68rem; padding: 3px 6px; border-radius: 6px; font-weight: 500;">{{ ucfirst($p->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">KSh {{ number_format($p->total, 2) }}</td>
                        <td class="text-end text-danger">KSh {{ number_format(max(0, $p->total - $p->paid), 2) }}</td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.82rem;">
                                    <li><a class="dropdown-item py-1" href="{{ route('purchases.show', $p) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                    @if($p->status !== 'received')
                                        <li>
                                            <form method="POST" action="{{ route('purchases.receive', $p) }}">@csrf<button class="dropdown-item py-1 text-success"><i class="bi bi-check2-circle me-2"></i>Receive Goods</button></form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No purchases found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $purchases->links() }}
    </div>
</div>
@endsection
