@extends('layouts.app')
@section('title','Edit Expense')
@section('content')
<h4>Edit Expense</h4>
<form method="POST" action="{{ route('expenses.update',$expense) }}">@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-4"><label>Category</label><select name="expense_category_id" class="form-select"><option value="">--</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected($expense->expense_category_id==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
<div class="col-md-8"><label>Description</label><input name="description" class="form-control" value="{{ $expense->description }}" required></div>
<div class="col-md-3"><label>Amount</label><input type="number" step="0.01" name="amount" class="form-control" value="{{ $expense->amount }}" required></div>
<div class="col-md-3"><label>Payment Method</label><select name="payment_method" class="form-select"><option value="cash" @selected($expense->payment_method=='cash')>Cash</option><option value="mpesa" @selected($expense->payment_method=='mpesa')>M-Pesa</option><option value="bank" @selected($expense->payment_method=='bank')>Bank</option></select></div>
<div class="col-md-3"><label>Date</label><input type="date" name="expense_date" class="form-control" value="{{ $expense->expense_date }}" required></div>
</div>
<button class="btn btn-primary mt-3">Update</button>
</form>
@endsection