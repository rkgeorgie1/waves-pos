@extends('layouts.app')
@section('title','Add Expense')
@section('content')
<h4>Add Expense</h4>
<form method="POST" action="{{ route('expenses.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label>Category</label><select name="expense_category_id" class="form-select"><option value="">--</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
<div class="col-md-8"><label>Description</label><input name="description" class="form-control" required></div>
<div class="col-md-3"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
<div class="col-md-3"><label>Payment Method</label><select name="payment_method" class="form-select"><option value="cash">Cash</option><option value="mpesa">M-Pesa</option><option value="bank">Bank</option></select></div>
<div class="col-md-3"><label>Date</label><input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
</div>
<button class="btn btn-primary mt-3">Save</button>
</form>
@endsection