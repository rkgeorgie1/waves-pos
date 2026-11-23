@extends('layouts.app')
@section('title', 'List POS')

@push('styles')
<style>
    .sales-compact-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        font-size: 0.71rem;
        line-height: 1.2;
    }
    @media (max-width: 1199.98px) {
        .sales-compact-table {
            min-width: 1200px;
            table-layout: auto;
        }
        .sales-compact-table th {
            white-space: nowrap;
        }
    }
    .sales-compact-table th,
    .sales-compact-table td {
        padding: 5px 4px !important;
        vertical-align: middle;
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
    }
    .sales-compact-table th {
        font-size: 0.67rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.01em;
        background: #f8fafc;
        color: #475569;
    }
    .sales-compact-table .badge {
        font-size: 0.65rem;
        padding: 2px 4px;
    }
    .sales-compact-table .btn-action {
        font-size: 0.68rem;
        padding: 1px 5px;
    }
    .editable-cell {
        cursor: pointer;
    }
    .editable-cell:hover {
        background: #f1f5f9 !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <h5 class="mb-0 fw-semibold">POS Sales</h5>
    <a href="{{ route('pos') }}" class="btn btn-sm btn-primary">+ Open POS</a>
</div>

<div class="card table-responsive">
    <table class="table table-hover table-bordered mb-0 sales-compact-table">
        <thead>
            <tr>
                <th style="width: 5.5%;">Action</th>
                <th style="width: 6.5%;">Date</th>
                <th style="width: 5%;">Invoice No.</th>
                <th style="width: 6.5%;">Customer name</th>
                <th style="width: 6%;">Contact Number</th>
                <th style="width: 6.5%;">Location</th>
                <th style="width: 5%;">Payment Status</th>
                <th style="width: 5.5%;">Payment Method</th>
                <th class="text-end" style="width: 6%;">Total amount</th>
                <th class="text-end" style="width: 6%;">Total paid</th>
                <th class="text-end" style="width: 5%;">Sell Due</th>
                <th class="text-end" style="width: 5%;">Sell Return Due</th>
                <th style="width: 5%;">Shipping Status</th>
                <th class="text-center" style="width: 4%;">Total Items</th>
                <th style="width: 6%;">Added By</th>
                <th style="width: 7.5%;">Sell note</th>
                <th style="width: 7.5%;">Staff note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $s)
                @php
                    $sellDue = max(0, (float) $s->total - (float) $s->paid);
                    $returnDue = (float) ($s->return_due ?? 0);
                    $totalItems = (float) ($s->total_items ?? 0);
                    $saleDate = $s->sold_at ? \Carbon\Carbon::parse($s->sold_at) : $s->created_at;
                @endphp
                <tr>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle btn-action w-100" type="button" data-bs-toggle="dropdown">
                                Act
                            </button>
                            <ul class="dropdown-menu dropdown-menu-start" style="font-size: 0.78rem;">
                                <li><a class="dropdown-item py-1" href="{{ route('sales.show', $s) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item py-1" href="{{ route('sales.receipt', $s) }}" target="_blank"><i class="bi bi-printer me-2"></i>Print Receipt</a></li>
                                <li>
                                    <button type="button" class="dropdown-item py-1"
                                        data-id="{{ $s->id }}"
                                        data-ref="{{ $s->reference }}"
                                        data-status="{{ $s->shipping_status }}"
                                        data-details="{{ $s->shipping_details }}"
                                        data-notes="{{ $s->notes }}"
                                        data-staff="{{ $s->staff_note }}"
                                        onclick="openShippingModal(this)">
                                        <i class="bi bi-truck me-2"></i>Edit Shipping
                                    </button>
                                </li>
                                <li><a class="dropdown-item py-1" href="{{ route('sales_returns.create') }}"><i class="bi bi-arrow-return-left me-2"></i>Sell Return</a></li>
                            </ul>
                        </div>
                    </td>
                    <td>{{ $saleDate->format('d-m-Y H:i') }}</td>
                    <td class="fw-semibold">
                        <a href="{{ route('sales.show', $s) }}" class="text-decoration-none">{{ $s->reference }}</a>
                    </td>
                    <td>{{ $s->customer->name ?? 'Walk-in' }}</td>
                    <td>{{ $s->customer->phone ?? '-' }}</td>
                    <td>{{ $s->warehouse->name ?? 'Airwaves Electronics' }}</td>
                    <td>
                        @if($s->payment_status === 'paid')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                        @elseif($s->payment_status === 'partial')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Partial</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ ucfirst($s->payment_status ?: 'Due') }}</span>
                        @endif
                    </td>
                    <td>{{ $s->payment_method_label }}</td>
                    <td class="text-end fw-medium">KSh {{ number_format($s->total, 0) }}</td>
                    <td class="text-end">KSh {{ number_format($s->paid, 0) }}</td>
                    <td class="text-end">KSh {{ number_format($sellDue, 0) }}</td>
                    <td class="text-end">{{ $returnDue > 0 ? 'KSh ' . number_format($returnDue, 0) : '' }}</td>
                    <td class="editable-cell"
                        title="Click to edit shipping"
                        data-id="{{ $s->id }}"
                        data-ref="{{ $s->reference }}"
                        data-status="{{ $s->shipping_status }}"
                        data-details="{{ $s->shipping_details }}"
                        data-notes="{{ $s->notes }}"
                        data-staff="{{ $s->staff_note }}"
                        onclick="openShippingModal(this)">
                        @if(!empty($s->shipping_status))
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ ucfirst($s->shipping_status) }}</span>
                        @else
                            <span class="text-muted"><i class="bi bi-pencil-square"></i> Set</span>
                        @endif
                    </td>
                    <td class="text-center">{{ rtrim(rtrim(number_format($totalItems, 2), '0'), '.') ?: 0 }}</td>
                    <td>{{ $s->user->name ?? '-' }}</td>
                    <td>{{ $s->notes }}</td>
                    <td>{{ $s->staff_note }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class="text-center text-muted py-4">No POS sales found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-2">
    {{ $sales->links() }}
</div>

<!-- Edit Shipping Modal -->
<div class="modal fade" id="shippingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="shippingForm" class="modal-content">
            @csrf
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">Edit Shipping &amp; Notes &mdash; Invoice <span id="modalInvoiceRef"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Shipping Status</label>
                    <select name="shipping_status" id="modalShippingStatus" class="form-select">
                        <option value="">-- None --</option>
                        <option value="Ordered">Ordered</option>
                        <option value="Packed">Packed</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Shipping Details</label>
                    <textarea name="shipping_details" id="modalShippingDetails" class="form-control" rows="2" placeholder="Delivery address, rider/courier name, phone..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sell Note</label>
                    <textarea name="notes" id="modalSellNote" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label">Staff Note</label>
                    <textarea name="staff_note" id="modalStaffNote" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openShippingModal(el) {
    const id = el.dataset.id;
    document.getElementById('shippingForm').action = '/sales/' + id + '/shipping';
    document.getElementById('modalInvoiceRef').textContent = el.dataset.ref || '';
    document.getElementById('modalShippingStatus').value = el.dataset.status || '';
    document.getElementById('modalShippingDetails').value = el.dataset.details || '';
    document.getElementById('modalSellNote').value = el.dataset.notes || '';
    document.getElementById('modalStaffNote').value = el.dataset.staff || '';
    new bootstrap.Modal(document.getElementById('shippingModal')).show();
}
</script>
@endpush
