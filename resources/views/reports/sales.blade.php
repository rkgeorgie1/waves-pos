@extends('layouts.app')
@section('title','Sales Report')
@section('content')
<h4>Sales Report</h4>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3"><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="to" value="{{ $to }}" class="form-control"></div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
    <div class="col-md-4 text-end">
        <strong>Count:</strong> {{ $summary['count'] }} |
        <strong>Total:</strong> {{ number_format($summary['total'],2) }} |
        <strong>Paid:</strong> {{ number_format($summary['paid'],2) }}
    </div>
</form>
<div class="table-responsive">
<table class="table table-striped bg-white">
    <thead><tr><th>Reference</th><th>Date</th><th>Customer</th><th>Total</th><th>Paid</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($sales as $s)
        <tr><td>{{ $s->reference }}</td><td>{{ $s->created_at->format('Y-m-d H:i') }}</td><td>{{ $s->customer->name ?? 'Walk-in' }}</td><td>{{ number_format($s->total,2) }}</td><td>{{ number_format($s->paid,2) }}</td><td>{{ $s->status }}</td></tr>
    @endforeach
    </tbody>
</table>
</div>
{{ $sales->links() }}
<a href="{{ route('reports.export.sales') }}?from={{ $from }}&to={{ $to }}&format=csv" class="btn btn-success">CSV</a>
<a href="{{ route('reports.export.sales') }}?from={{ $from }}&to={{ $to }}&format=xlsx" class="btn btn-primary">Excel</a>
<a href="{{ route('reports.export.sales') }}?from={{ $from }}&to={{ $to }}&format=pdf" class="btn btn-danger">PDF</a>
<a href="{{ route('reports.export.sales') }}?from={{ $from }}&to={{ $to }}&format=doc" class="btn btn-secondary">Word</a>
<a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
@endsection
