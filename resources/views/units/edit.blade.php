@extends('layouts.app')
@section('title','Edit Unit')
@section('content')
<h4>Edit Unit</h4>
<form method="POST" action="{{ route('units.update',$unit) }}">@csrf @method('PUT')
<div class="mb-3"><label>Name</label><input name="name" class="form-control" value="{{ $unit->name }}" required></div>
<div class="mb-3"><label>Short Name</label><input name="short_name" class="form-control" value="{{ $unit->short_name }}" required></div>
<button class="btn btn-primary">Update</button>
</form>
@endsection