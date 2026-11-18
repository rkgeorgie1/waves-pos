@extends('layouts.app')
@section('title','New Warranty')
@section('content')
<h4>New Warranty</h4>
<form method="POST" action="{{ route('warranties.store') }}">
@csrf
<div class="row g-3">
    <div class="col-md-4"><label>Name</label><input name="name" class="form-control" required></div>
    <div class="col-md-2"><label>Duration</label><input name="duration" type="number" class="form-control" value="12" required></div>
    <div class="col-md-3"><label>Type</label><select name="duration_type" class="form-select"><option value="days">Days</option><option value="months" selected>Months</option><option value="years">Years</option></select></div>
    <div class="col-md-3"><label>Description</label><input name="description" class="form-control"></div>
</div>
<button class="btn btn-primary mt-3">Save</button>
</form>
@endsection
