@extends('layouts.app')
@section('title','Edit Supplier')
@section('content')
<h4>Edit Supplier</h4>
<form method="POST" action="{{ route('suppliers.update',$supplier) }}">@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-6"><label>Name</label><input name="name" class="form-control" value="{{ $supplier->name }}" required></div>
<div class="col-md-6"><label>Business Name</label><input name="business_name" class="form-control" value="{{ $supplier->business_name }}"></div>
<div class="col-md-4"><label>Phone</label><input name="phone" class="form-control" value="{{ $supplier->phone }}"></div>
<div class="col-md-4"><label>Email</label><input name="email" class="form-control" value="{{ $supplier->email }}"></div>
<div class="col-md-4"><label>KRA PIN</label><input name="kra_pin" class="form-control" value="{{ $supplier->kra_pin }}"></div>
<div class="col-md-12"><label>Address</label><textarea name="address" class="form-control">{{ $supplier->address }}</textarea></div>
</div>
<button class="btn btn-primary mt-3">Update</button>
</form>
@endsection