@extends('layouts.app')
@section('title', 'Customer ' . $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Customer Details</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-primary">Edit</a>
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">All Customers</a>
    </div>
</div>

<div class="card p-4 mb-3">
    <div class="row g-3 mb-4 pb-3 border-bottom">
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Name</div>
            <div class="fw-semibold">{{ $customer->name }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Business Name</div>
            <div>{{ $customer->business_name ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Phone</div>
            <div>{{ $customer->phone ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Email</div>
            <div>{{ $customer->email ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">KRA PIN</div>
            <div>{{ $customer->kra_pin ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Customer Group</div>
            <div>{{ $customer->customerGroup->name ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Category</div>
            <div>{{ $customer->category ?? '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Credit Limit</div>
            <div>KSh {{ number_format($customer->credit_limit ?? 0, 2) }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Balance</div>
            <div class="{{ $customer->balance > 0 ? 'text-danger fw-bold' : 'fw-bold' }}">KSh {{ number_format($customer->balance ?? 0, 2) }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Total Sales</div>
            <div class="fw-bold">KSh {{ number_format($totalSales, 2) }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Total Paid</div>
            <div class="fw-bold text-success">KSh {{ number_format($totalPaid, 2) }}</div>
        </div>
        @if($customer->address)
        <div class="col-md-12">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Address</div>
            <div>{{ $customer->address }}</div>
        </div>
        @endif
    </div>

    <h6 class="fw-semibold text-uppercase text-muted mb-3">Recent Sales</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Balance</th>
                    <th>Payment Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->sales as $s)
                    <tr>
                        <td>{{ ($s->sold_at ? \Carbon\Carbon::parse($s->sold_at) : $s->created_at)->format('d-m-Y H:i') }}</td>
                        <td class="fw-semibold"><a href="{{ route('sales.show', $s) }}" class="text-decoration-none">{{ $s->reference }}</a></td>
                        <td class="text-end">KSh {{ number_format($s->total, 2) }}</td>
                        <td class="text-end">KSh {{ number_format($s->paid, 2) }}</td>
                        <td class="text-end {{ $s->balance > 0 ? 'text-danger' : '' }}">KSh {{ number_format($s->balance, 2) }}</td>
                        <td>
                            @if($s->payment_status === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                            @elseif($s->payment_status === 'partial')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Partial</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ ucfirst($s->payment_status ?: 'Due') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('sales.show', $s) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No sales recorded for this customer.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
