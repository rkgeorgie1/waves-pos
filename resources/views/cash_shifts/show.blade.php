@extends('layouts.app')
@section('title', 'Cash Shift')
@section('content')
<h4>Cash Shift (Z-Report)</h4>
<div class="card p-3 mb-3">
    <p>Register: {{ $cashShift->cashRegister->name ?? '-' }} | User: {{ $cashShift->user->name ?? '-' }}</p>
    <p>Opened: {{ $cashShift->opened_at }} | Closed: {{ $cashShift->closed_at ?? '—' }} | Status: {{ $cashShift->status }}</p>
    <table class="table">
        <tr><th>Opening Balance</th><td>{{ number_format($cashShift->opening_balance,2) }}</td></tr>
        <tr><th>Cash Received</th><td>{{ number_format($cashShift->cash_received,2) }}</td></tr>
        <tr><th>Cash Paid Out</th><td>{{ number_format($cashShift->cash_paid_out,2) }}</td></tr>
        <tr><th>Expected Closing</th><td>{{ number_format($cashShift->expected_closing,2) }}</td></tr>
        <tr><th>Actual Closing</th><td>{{ $cashShift->actual_closing !== null ? number_format($cashShift->actual_closing,2) : '—' }}</td></tr>
        <tr><th>Difference</th><td>{{ $cashShift->difference !== null ? number_format($cashShift->difference,2) : '—' }}</td></tr>
    </table>
    @if($cashShift->status === 'open')
    <form method="POST" action="{{ route('cash_shifts.close', $cashShift) }}">
        @csrf
        <div class="mb-2"><label>Actual Closing Cash</label><input name="actual_closing" type="number" step="0.01" class="form-control" required></div>
        <button class="btn btn-danger">Close Shift (Z-Report)</button>
    </form>
    @endif
</div>
<a href="{{ route('cash_shifts.index') }}" class="btn btn-secondary">Back</a>
@endsection
