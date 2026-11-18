@extends('layouts.app')
@section('title','Edit Brand')
@section('content')
<h4>Edit Brand</h4>
<form method="POST" action="{{ route('brands.update',$brand) }}">@csrf @method('PUT')
<div class="mb-3"><label>Name</label><input name="name" class="form-control" value="{{ $brand->name }}" required></div>
<button class="btn btn-primary">Update</button>
</form>
@endsection