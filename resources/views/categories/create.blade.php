@extends('layouts.app')
@section('title','Add Category')
@section('content')
<h4>Add Category</h4>
<form method="POST" action="{{ route('categories.store') }}">@csrf
<div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
<div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
<button class="btn btn-primary">Save</button>
</form>
@endsection