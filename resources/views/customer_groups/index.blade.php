@extends('layouts.app')
@section('title', 'Customer Groups')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Customer Groups</h4>
    <a href="{{ route('customer_groups.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">+ Add Customer Group</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th class="text-end">Discount %</th>
                    <th>Description</th>
                    <th class="text-center">Customers</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $g)
                    <tr>
                        <td class="fw-semibold"><a href="{{ route('customer_groups.show', $g) }}" class="text-decoration-none">{{ $g->name }}</a></td>
                        <td class="text-end">{{ number_format($g->discount_percent, 2) }}%</td>
                        <td>{{ $g->description ?? '-' }}</td>
                        <td class="text-center">{{ $g->customers_count }}</td>
                        <td class="text-center">
                            @if($g->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.82rem;">
                                    <li><a class="dropdown-item py-1" href="{{ route('customer_groups.show', $g) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item py-1" href="{{ route('customer_groups.edit', $g) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('customer_groups.destroy', $g) }}" onsubmit="return confirm('Delete this customer group?')">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item py-1 text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No customer groups found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $groups->links() }}
    </div>
</div>
@endsection
