@extends('layouts.app')
@section('title','Platform Plans')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-tags me-2"></i>Subscription Plans</h4>
    <a href="{{ route('platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to console</a>
</div>

<div class="card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Existing plans</h5>
        <span class="text-muted small">Slug is fixed once created &mdash; all other fields can be edited.</span>
    </div>

    @foreach($plans as $plan)
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="fw-semibold fs-5">{{ $plan->name }}</span>
                    <code class="ms-2 small">{{ $plan->slug }}</code>
                    <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }} ms-2">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('platform.plans.update', $plan) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control form-control-sm" value="{{ old('name', $plan->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input name="description" class="form-control form-control-sm" value="{{ old('description', $plan->description) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Monthly price</label>
                        <input type="number" step="0.01" min="0" name="monthly_price" class="form-control form-control-sm" value="{{ old('monthly_price', $plan->monthly_price) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Yearly price</label>
                        <input type="number" step="0.01" min="0" name="yearly_price" class="form-control form-control-sm" value="{{ old('yearly_price', $plan->yearly_price) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <input name="currency" class="form-control form-control-sm" value="{{ old('currency', $plan->currency) }}" maxlength="3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stripe monthly price ID</label>
                        <input name="stripe_price_id" class="form-control form-control-sm" value="{{ old('stripe_price_id', $plan->stripe_price_id) }}" placeholder="price_...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stripe yearly price ID</label>
                        <input name="stripe_yearly_price_id" class="form-control form-control-sm" value="{{ old('stripe_yearly_price_id', $plan->stripe_yearly_price_id) }}" placeholder="price_...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max users</label>
                        <input type="number" min="1" name="max_users" class="form-control form-control-sm" value="{{ old('max_users', $plan->max_users) }}" placeholder="Unlimited">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max products</label>
                        <input type="number" min="1" name="max_products" class="form-control form-control-sm" value="{{ old('max_products', $plan->max_products) }}" placeholder="Unlimited">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max warehouses</label>
                        <input type="number" min="1" name="max_warehouses" class="form-control form-control-sm" value="{{ old('max_warehouses', $plan->max_warehouses) }}" placeholder="Unlimited">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max sales / month</label>
                        <input type="number" min="1" name="max_sales_per_month" class="form-control form-control-sm" value="{{ old('max_sales_per_month', $plan->max_sales_per_month) }}" placeholder="Unlimited">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active-{{ $plan->id }}" @checked(old('is_active', $plan->is_active))>
                        <label class="form-check-label small" for="active-{{ $plan->id }}">Plan is active (visible on billing page and landing page)</label>
                    </div>
                    <button class="btn btn-sm btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    @endforeach
</div>

<div class="card p-4">
    <h5 class="mb-3">Create a plan</h5>
    <form method="POST" action="{{ route('platform.plans.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input name="slug" class="form-control" value="{{ old('slug') }}" placeholder="starter" required>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <input name="description" class="form-control" value="{{ old('description') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Monthly price</label>
                <input type="number" step="0.01" min="0" name="monthly_price" class="form-control" value="{{ old('monthly_price', 0) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Yearly price</label>
                <input type="number" step="0.01" min="0" name="yearly_price" class="form-control" value="{{ old('yearly_price', 0) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Currency</label>
                <input name="currency" class="form-control" value="{{ old('currency', 'KES') }}" maxlength="3" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Max users</label>
                <input type="number" min="1" name="max_users" class="form-control" value="{{ old('max_users') }}" placeholder="Unlimited">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max products</label>
                <input type="number" min="1" name="max_products" class="form-control" value="{{ old('max_products') }}" placeholder="Unlimited">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max warehouses</label>
                <input type="number" min="1" name="max_warehouses" class="form-control" value="{{ old('max_warehouses') }}" placeholder="Unlimited">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max sales / month</label>
                <input type="number" min="1" name="max_sales_per_month" class="form-control" value="{{ old('max_sales_per_month') }}" placeholder="Unlimited">
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary">Create plan</button>
        </div>
    </form>
</div>
@endsection
