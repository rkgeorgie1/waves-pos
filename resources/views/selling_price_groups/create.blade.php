@extends('layouts.app')
@section('title','New Selling Price Group')
@section('content')
<h4>New Selling Price Group</h4>
<form method="POST" action="{{ route('selling_price_groups.store') }}">
@csrf
<div class="row g-3">
    <div class="col-md-4"><label>Name</label><input name="name" class="form-control" required></div>
    <div class="col-md-8"><label>Description</label><input name="description" class="form-control"></div>
</div>
<button class="btn btn-primary mt-3">Save</button>
</form>
@endsection
