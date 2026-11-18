@extends('layouts.app')
@section('title','List Drafts')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Drafts</h4>
    <a href="{{ route('sales.drafts.create') }}" class="btn btn-primary">Add Draft</a>
</div>
<table class="table bg-white">
    <thead><tr><th>Ref</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr></thead>
    <tbody>
    @foreach($drafts as $d)
        <tr>
            <td>{{ $d->reference }}</td>
            <td>{{ $d->customer->name ?? 'Walk-in' }}</td>
            <td><span class="badge bg-secondary">{{ $d->status }}</span></td>
            <td>{{ number_format($d->total, 2) }}</td>
            <td>{{ $d->created_at->format('d M Y H:i') }}</td>
            <td>
                <a href="{{ route('sales.show', $d) }}" class="btn btn-sm btn-outline-secondary">View</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $drafts->links() }}
@endsection
