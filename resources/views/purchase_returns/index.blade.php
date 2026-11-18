@extends('layouts.app')
@section('title', 'Purchase Returns')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Purchase Returns</h4>
    <a href="{{ route('purchase_returns.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">+ Add Purchase Return</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Reference No</th>
                    <th>Supplier</th>
                    <th>Purchase Ref</th>
                    <th>Reason</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $r)
                    <tr>
                        <td>{{ $r->created_at->format('d-m-Y H:i') }}</td>
                        <td class="fw-semibold"><a href="{{ route('purchase_returns.show', $r) }}" class="text-decoration-none">{{ $r->reference }}</a></td>
                        <td>{{ $r->supplier->name ?? '-' }}</td>
                        <td>{{ $r->purchase->reference ?? '-' }}</td>
                        <td>{{ $r->reason ?? '-' }}</td>
                        <td class="text-end fw-semibold text-danger">KSh {{ number_format($r->amount, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('purchase_returns.show', $r) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No purchase returns found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $returns->links() }}
    </div>
</div>
@endsection
