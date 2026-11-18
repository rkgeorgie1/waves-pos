@extends('layouts.app')
@section('title','Add Unit')
@section('content')
<h4>Add Unit</h4>
<form method="POST" action="{{ route('units.store') }}">@csrf
<div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
<div class="mb-3"><label>Short Name</label><input name="short_name" class="form-control" required></div>
<button class="btn btn-primary">Save</button>
</form>
@endsection