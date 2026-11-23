@extends('layouts.app')
@section('title','Platform Organizations')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Organizations</h4>
    <a href="{{ route('platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to console</a>
</div>

<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('platform.tenants') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Search by user email</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" class="form-control" placeholder="name@example.com" value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm px-3">Search</button>
                <a href="{{ route('platform.tenants') }}" class="btn btn-outline-secondary btn-sm px-3">Clear</a>
            </div>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Organization</th>
                    <th>Users</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Plan</th>
                    <th>Subscription</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($tenants as $tenant)
                @php
                    $subscription = $subscriptions->get($tenant->id);
                    $plan = $subscription ? $plans->firstWhere('id', $subscription->plan_id) : null;
                @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $tenant->name }}</div>
                        <div class="text-muted small">{{ $tenant->slug }}</div>
                    </td>
                    <td>{{ $userCounts->get($tenant->id, 0) }}</td>
                    <td>{{ $productCounts->get($tenant->id, 0) }}</td>
                    <td>
                        <span class="badge {{ $tenant->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($tenant->status) }}</span>
                    </td>
                    <td>{{ $plan?->name ?? '—' }}</td>
                    <td>
                        @if($subscription && $subscription->isCurrentlyActive())
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-warning text-dark">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex justify-content-end gap-2">
                            <form method="POST" action="{{ route('platform.tenants.status', $tenant) }}" class="d-flex gap-1 align-items-center">
                                @csrf
                                <select name="status" class="form-select form-select-sm" style="width:auto">
                                    <option value="active" @selected($tenant->isActive())>Active</option>
                                    <option value="suspended" @selected(! $tenant->isActive())>Suspended</option>
                                </select>
                                <button class="btn btn-sm btn-outline-secondary">Status</button>
                            </form>
                            <form method="POST" action="{{ route('platform.tenants.plan', $tenant) }}" class="d-flex gap-1 align-items-center">
                                @csrf
                                <select name="plan_id" class="form-select form-select-sm" style="width:auto">
                                    @foreach($plans as $option)
                                        <option value="{{ $option->id }}" @selected($plan?->id === $option->id)>{{ $option->name }}</option>
                                    @endforeach
                                </select>
                                <select name="billing_interval" class="form-select form-select-sm" style="width:auto">
                                    <option value="monthly" @selected($subscription?->billing_interval === 'monthly')>Monthly</option>
                                    <option value="yearly" @selected($subscription?->billing_interval === 'yearly')>Yearly</option>
                                </select>
                                <button class="btn btn-sm btn-primary">Apply</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No organizations match this email.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
