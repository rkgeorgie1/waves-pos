@extends('layouts.app')
@section('title','Expenses')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Expenses</h4><a href="{{ route('expenses.create') }}" class="btn btn-primary">Add</a></div>
<table class="table bg-white"><thead><tr><th>Ref</th><th>Category</th><th>Description</th><th>Amount</th><th>Date</th><th></th></tr></thead><tbody>
@foreach($expenses as $e)<tr><td>{{ $e->reference }}</td><td>{{ $e->category->name ?? '-' }}</td><td>{{ $e->description }}</td><td>{{ number_format($e->amount,2) }}</td><td>{{ $e->expense_date }}</td><td><a href="{{ route('expenses.edit',$e) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td></tr>@endforeach
</tbody></table>
{{ $expenses->links() }}
@endsection