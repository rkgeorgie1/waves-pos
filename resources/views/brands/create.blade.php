@extends('layouts.app')
@section('title','Add Brand')
@section('content')
<h4>Add Brand</h4>
<form method="POST" action="{{ route('brands.store') }}">@csrf
<div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
<button class="btn btn-primary">Save</button>
</form>
@endsection