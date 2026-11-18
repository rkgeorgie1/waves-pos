@extends('layouts.app')
@section('title', 'Edit Customer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Edit Customer</h4>
    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">All Customers</a>
</div>

<form method="POST" action="{{ route('customers.update', $customer) }}">
@csrf @method('PUT')
<div class="card p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Name:*</label>
            <input name="name" class="form-control form-control-sm" value="{{ $customer->name }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Business Name:</label>
            <input name="business_name" class="form-control form-control-sm" value="{{ $customer->business_name }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Phone:</label>
            <input name="phone" class="form-control form-control-sm" value="{{ $customer->phone }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Email:</label>
            <input type="email" name="email" class="form-control form-control-sm" value="{{ $customer->email }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">KRA PIN:</label>
            <input name="kra_pin" class="form-control form-control-sm" value="{{ $customer->kra_pin }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Address:</label>
            <textarea name="address" class="form-control form-control-sm" rows="2">{{ $customer->address }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Customer Group:</label>
            <select name="customer_group_id" class="form-select form-select-sm">
                <option value="">None</option>
                @foreach($customerGroups as $g)
                    <option value="{{ $g->id }}" @selected($customer->customer_group_id == $g->id)>{{ $g->name }} ({{ $g->discount_percent }}% discount)</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Category:</label>
            <input name="category" class="form-control form-control-sm" value="{{ $customer->category }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Credit Limit:</label>
            <input type="number" step="0.01" name="credit_limit" class="form-control form-control-sm" value="{{ $customer->credit_limit }}">
        </div>
    </div>
</div>
<button type="submit" class="btn btn-primary mt-3 px-4 fw-semibold">Update</button>
</form>
@endsection
