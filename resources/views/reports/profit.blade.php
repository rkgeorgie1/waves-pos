@extends('layouts.app')
@section('title','Profit Report')
@section('content')
<h4>Profit Report</h4>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3"><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="to" value="{{ $to }}" class="form-control"></div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
</form>
<table class="table table-striped bg-white">
    <tr><th>Revenue (Sales)</th><td>{{ number_format($revenue,2) }}</td></tr>
    <tr><th>Cost of Goods Sold</th><td>{{ number_format($cost,2) }}</td></tr>
    <tr><th>Gross Profit</th><td>{{ number_format($revenue - $cost,2) }}</td></tr>
    <tr><th>Expenses</th><td>{{ number_format($expenses,2) }}</td></tr>
    <tr><th>Net Profit</th><td>{{ number_format($revenue - $cost - $expenses,2) }}</td></tr>
</table>
<a href="{{ route('reports.export.profit') }}?from={{ $from }}&to={{ $to }}&format=csv" class="btn btn-success">CSV</a>
<a href="{{ route('reports.export.profit') }}?from={{ $from }}&to={{ $to }}&format=xlsx" class="btn btn-primary">Excel</a>
<a href="{{ route('reports.export.profit') }}?from={{ $from }}&to={{ $to }}&format=pdf" class="btn btn-danger">PDF</a>
<a href="{{ route('reports.export.profit') }}?from={{ $from }}&to={{ $to }}&format=doc" class="btn btn-secondary">Word</a>
<a href="{{ route('reports.index') }}" class="btn btn-secondary">Back</a>
@endsection
