@extends('layouts.app')
@section('title', 'Customer Group ' . $customerGroup->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Customer Group: {{ $customerGroup->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('customer_groups.edit', $customerGroup) }}" class="btn btn-sm btn-primary">Edit</a>
        <a href="{{ route('customer_groups.index') }}" class="btn btn-sm btn-outline-secondary">All Customer Groups</a>
    </div>
</div>

<div class="card p-4 mb-3">
    <div class="row g-3 mb-4 pb-3 border-bottom">
        <div class="col-md-4">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Name</div>
            <div class="fw-semibold">{{ $customerGroup->name }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Discount Percent</div>
            <div class="fw-semibold">{{ number_format($customerGroup->discount_percent, 2) }}%</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Status</div>
            <div>
                @if($customerGroup->is_active)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                @endif
            </div>
        </div>
        @if($customerGroup->description)
        <div class="col-md-12">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Description</div>
            <div>{{ $customerGroup->description }}</div>
        </div>
        @endif
    </div>

    <h6 class="fw-semibold text-uppercase text-muted mb-3">Customers in this Group</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customerGroup->customers as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->phone ?? '-' }}</td>
                        <td>{{ $c->email ?? '-' }}</td>
                        <td class="text-end">KSh {{ number_format($c->balance ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No customers in this group.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
