@extends('layouts.app')
@section('title','Invoices')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoices</h4>
    <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary btn-sm">Back to billing</a>
</div>

<div class="card p-4">
    @if($invoices->isEmpty())
        <p class="text-muted mb-0">No invoices are available here yet. Payment records are shared by the admin on WhatsApp.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr><th>Date</th><th>Reference</th><th>Status</th><th>Total</th></tr>
                </thead>
                <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice['date']->format('Y-m-d') }}</td>
                        <td>{{ $invoice['reference'] }}</td>
                        <td>{{ $invoice['status'] }}</td>
                        <td>{{ $invoice['currency'] }} {{ number_format($invoice['amount'], 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
