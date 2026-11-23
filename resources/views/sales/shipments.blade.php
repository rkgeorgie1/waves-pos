@extends('layouts.app')
@section('title', 'Shipments')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h5 class="mb-0 fw-semibold">Shipments</h5>
    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">All Sales</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Location</th>
                    <th style="width: 170px;">Shipping Status</th>
                    <th style="width: 280px;">Shipping Details</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                    <tr>
                        <td>{{ ($s->sold_at ? \Carbon\Carbon::parse($s->sold_at) : $s->created_at)->format('d-m-Y H:i') }}</td>
                        <td class="fw-semibold">
                            <a href="{{ route('sales.show', $s) }}" class="text-decoration-none">{{ $s->reference }}</a>
                        </td>
                        <td>{{ $s->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $s->customer->phone ?? '-' }}</td>
                        <td>{{ $s->warehouse->name ?? 'Airwaves Electronics' }}</td>
                        <td colspan="3">
                            <form method="POST" action="{{ route('sales.update_shipping', $s) }}" class="d-flex gap-2 align-items-center">
                                @csrf
                                <select name="shipping_status" class="form-select form-select-sm" style="width: 155px;">
                                    <option value="">-- Status --</option>
                                    @foreach(['Ordered', 'Packed', 'Shipped', 'Delivered', 'Cancelled'] as $st)
                                        <option value="{{ $st }}" {{ $s->shipping_status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                                <input name="shipping_details" class="form-control form-control-sm" value="{{ $s->shipping_details }}" placeholder="Delivery address, courier, phone...">
                                <button class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No shipments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $sales->links() }}
</div>
@endsection
