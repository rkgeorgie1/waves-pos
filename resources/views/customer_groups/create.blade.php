@extends('layouts.app')
@section('title', 'Add Customer Group')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Add Customer Group</h4>
    <a href="{{ route('customer_groups.index') }}" class="btn btn-sm btn-outline-secondary">All Customer Groups</a>
</div>

<form method="POST" action="{{ route('customer_groups.store') }}">
@csrf
<div class="card p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Group Name:*</label>
            <input name="name" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Discount Percent:</label>
            <input type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control form-control-sm" value="0">
        </div>
        <div class="col-md-12">
            <label class="form-label fw-semibold small">Description:</label>
            <textarea name="description" class="form-control form-control-sm" rows="3"></textarea>
        </div>
        <div class="col-md-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                <label class="form-check-label" for="isActive">Active</label>
            </div>
        </div>
    </div>
</div>
<button type="submit" class="btn btn-primary mt-3 px-4 fw-semibold">Save</button>
</form>
@endsection
