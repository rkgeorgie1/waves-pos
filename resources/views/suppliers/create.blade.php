@extends('layouts.app')
@section('title','Add Supplier')
@section('content')
<h4>Add Supplier</h4>
<form method="POST" action="{{ route('suppliers.store') }}">@csrf
<div class="row g-3">
<div class="col-md-6"><label>Name</label><input name="name" class="form-control" required></div>
<div class="col-md-6"><label>Business Name</label><input name="business_name" class="form-control"></div>
<div class="col-md-4"><label>Phone</label><input name="phone" class="form-control"></div>
<div class="col-md-4"><label>Email</label><input name="email" class="form-control"></div>
<div class="col-md-4"><label>KRA PIN</label><input name="kra_pin" class="form-control"></div>
<div class="col-md-12"><label>Address</label><textarea name="address" class="form-control"></textarea></div>
</div>
<button class="btn btn-primary mt-3">Save</button>
</form>
@endsection