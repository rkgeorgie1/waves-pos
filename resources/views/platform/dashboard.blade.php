@extends('layouts.app')
@section('title','Platform Console')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-cloud me-2"></i>Platform Console</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('platform.tenants') }}" class="btn btn-outline-primary btn-sm">Organizations</a>
        <a href="{{ route('platform.plans') }}" class="btn btn-outline-primary btn-sm">Plans</a>
        <a href="{{ route('platform.settings') }}" class="btn btn-outline-success btn-sm">WhatsApp</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 h-100">
            <div class="text-muted small">Organizations</div>
            <div class="fs-3 fw-semibold">{{ $tenantCount }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 h-100">
            <div class="text-muted small">Active subscriptions</div>
            <div class="fs-3 fw-semibold">{{ $activeSubscriptionCount }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 h-100">
            <div class="text-muted small">Available plans</div>
            <div class="fs-3 fw-semibold">{{ $planCount }}</div>
        </div>
    </div>
</div>

<div class="card p-4">
    <h5 class="mb-3">Recent organizations</h5>
    @if($recentTenants->isEmpty())
        <p class="text-muted mb-0">No organizations yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Name</th><th>Slug</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                @foreach($recentTenants as $tenant)
                    <tr>
                        <td>{{ $tenant->name }}</td>
                        <td>{{ $tenant->slug }}</td>
                        <td><span class="badge {{ $tenant->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($tenant->status) }}</span></td>
                        <td>{{ $tenant->created_at?->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
