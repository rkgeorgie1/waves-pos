@extends('layouts.app')
@section('title', 'POS - Airwaves POS')

@push('styles')
<style>
    /* Full-screen UltimatePOS v6 layout (hide sidebar & default top navbar) */
    html, body {
        height: 100vh;
        overflow-y: auto;
        background: #eef2f6;
    }
    .sidebar,
    .navbar-upos,
    .col-md-10 > nav.navbar {
        display: none !important;
    }
    .col-md-10 {
        width: 100% !important;
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
    .main-content-wrapper {
        margin-left: 0 !important;
        height: auto !important;
        overflow-y: visible !important;
    }
    main.p-4 {
        padding: 0 !important;
        height: calc(100vh - 60px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Top POS Header Bar */
    .upos-topbar {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        padding: 8px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .upos-top-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #374151;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.15s ease;
    }
    .upos-top-btn:hover {
        background: #f3f4f6;
        color: #111827;
    }

    /* Main 2-Column Workspace */
    .upos-main {
        flex: 1;
        min-height: 0;
        padding: 12px 16px;
    }
    .upos-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        height: 100%;
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: 14px;
    }

    /* Cart Table inside Left Card */
    .upos-table-container {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin: 8px 0;
    }
    .upos-cart-table {
        margin-bottom: 0;
    }
    .upos-cart-table thead th {
        background: #f9fafb;
        color: #111827;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 8px 10px;
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .upos-cart-table tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 0.82rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .upos-cart-table .input-group {
        flex-wrap: nowrap;
    }
    .upos-cart-table .input-group > .form-control {
        min-width: 42px;
        width: 100%;
    }

    /* Left Card Bottom Totals Table */
    .upos-totals-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.82rem;
    }

    /* Right Column Category & Brand Gradient Buttons + Product Grid */
    .upos-filter-btn {
        background: #3b82f6;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 7px 14px;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .upos-product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
        align-content: start;
        padding: 4px 2px;
    }
    .upos-product-box {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 112px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .upos-product-box:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.12);
        transform: translateY(-1px);
    }
    .upos-product-icon {
        width: 36px;
        height: 36px;
        margin: 0 auto 4px;
        border-radius: 8px;
        background: #eff6ff;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }
    .upos-product-name {
        font-size: 0.76rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .upos-product-sub {
        font-size: 0.7rem;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Bottom Fixed Footer Dock (waves.riset42.com style) */
    .upos-footer {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 8px 16px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.04);
    }
    .upos-action-link {
        background: transparent;
        border: none;
        padding: 4px 10px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #374151;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        border-radius: 8px;
    }
    .upos-action-link:hover {
        background: #f3f4f6;
    }
    .upos-action-link i {
        font-size: 1.05rem;
        margin-bottom: 1px;
    }
    .upos-btn-multipipay {
        background: #111827;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .upos-btn-multipipay:hover {
        background: #1f2937;
        color: #fff;
    }
    .upos-btn-cash {
        background: #10b981;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 20px;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .upos-btn-cash:hover {
        background: #059669;
        color: #fff;
    }
    .upos-btn-mpesa {
        background: #0284c7;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .upos-btn-mpesa:hover {
        background: #0369a1;
        color: #fff;
    }
    .upos-btn-cancel {
        background: #ef4444;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .upos-btn-cancel:hover {
        background: #dc2626;
        color: #fff;
    }
    .upos-btn-recent {
        background: #3b82f6;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.84rem;
        font-weight: 600;
    }
    .upos-btn-recent:hover {
        opacity: 0.95;
        color: #fff;
    }

    /* Mobile POS layout */
    @media (max-width: 767.98px) {
        .upos-topbar {
            flex-wrap: wrap;
            gap: 8px;
            padding: 8px 12px;
        }
        .upos-topbar > div:first-child {
            flex: 1 1 100%;
        }
        .upos-topbar #warehouse {
            width: 100% !important;
            max-width: 100%;
        }
        .upos-top-btn {
            width: 30px;
            height: 30px;
        }
        .upos-main {
            padding: 8px;
        }
        .upos-card {
            padding: 10px;
        }
        .upos-table-container {
            overflow-x: auto;
        }
        .upos-cart-table {
            min-width: 560px;
        }
        .upos-cart-table thead th,
        .upos-cart-table tbody td {
            padding: 6px 8px;
        }
        .upos-product-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .upos-product-box {
            min-height: 96px;
        }
        .upos-footer {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
            padding: 8px 10px 10px;
        }
        .upos-footer-summary {
            grid-column: 1 / -1;
            grid-row: 1;
            justify-content: space-between;
            gap: 8px;
        }
        .upos-footer-summary .fs-5 {
            font-size: 0.9rem !important;
            line-height: 1.2;
        }
        .upos-footer-summary .upos-btn-recent {
            padding: 5px 8px;
            font-size: 0.72rem;
            white-space: nowrap;
        }
        .upos-footer-actions {
            grid-column: 1 / -1;
            grid-row: 2;
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
            align-items: stretch;
        }
        .upos-footer-actions > button {
            width: 100%;
            min-height: 36px;
            margin: 0;
            padding: 6px 4px;
            font-size: 0.72rem;
            white-space: nowrap;
            justify-content: center;
        }
        .upos-footer-actions .upos-action-link {
            flex-direction: row;
            gap: 4px;
        }
        .upos-footer-actions .upos-action-link i {
            margin-bottom: 0;
        }
        .upos-footer-actions .upos-btn-cash { order: 1; }
        .upos-footer-actions .upos-btn-mpesa { order: 2; }
        .upos-footer-actions .upos-btn-multipipay { order: 3; }
        .upos-footer-actions .upos-action-quotation { order: 4; }
        .upos-footer-actions #holdBtn { order: 5; }
        .upos-footer-actions .upos-btn-cancel { order: 6; }

        /* Let the whole POS screen scroll down to the footer on phones */
        main.p-4 {
            height: auto !important;
            min-height: 100dvh;
            overflow: visible !important;
        }
        .upos-product-grid {
            overflow: visible !important;
            min-height: auto;
        }
        .upos-footer {
            position: sticky;
            bottom: 0;
            z-index: 20;
        }
    }
</style>
@endpush

@section('content')
<!-- Top Header Bar -->
<div class="upos-topbar">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold text-secondary small"><i class="bi bi-geo-alt-fill text-primary me-1"></i>Location:</span>
            <select id="warehouse" class="form-select form-select-sm fw-semibold" style="width: 190px; border-radius: 8px;">
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <span class="text-muted small fw-medium d-none d-md-inline" id="posLiveClock">{{ now()->format('m/d/Y H:i') }}</span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('dashboard') }}" class="upos-top-btn" title="Go Back to Dashboard">
            <i class="bi bi-skip-backward-fill text-primary"></i>
        </a>
        <button type="button" class="upos-top-btn" data-bs-toggle="modal" data-bs-target="#heldModal" title="Suspended Sales ({{ $held->count() }})">
            <i class="bi bi-pause-circle-fill text-warning"></i>
        </button>
        <a href="{{ route('cash_shifts.index') }}" class="upos-top-btn" title="Cash Register / Shift Details">
            <i class="bi bi-briefcase-fill text-success"></i>
        </a>
        <a href="{{ route('sales.pos_list') }}" class="upos-top-btn" title="List POS Sales">
            <i class="bi bi-list-check text-info"></i>
        </a>
    </div>
</div>

<!-- Main 2-Column POS Body -->
<div class="upos-main">
    <div class="row g-3 h-100">
        <!-- Left Column: Customer, Search, Cart Table, Totals -->
        <div class="col-md-7 h-100">
            <div class="upos-card">
                <!-- Customer + Product Search Row -->
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                            <select id="customer" class="form-select">
                                <option value="">Walk-In Customer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route('customers.create') }}" class="btn btn-outline-primary" title="Add Customer"><i class="bi bi-plus-circle-fill"></i></a>
                        </div>
                    </div>

                    <div class="col-md-5 position-relative">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input id="search" class="form-control" placeholder="Enter Product name / SKU / Scan bar code" autofocus autocomplete="off">
                            <a href="{{ route('products.create') }}" class="btn btn-outline-primary" title="Add New Product"><i class="bi bi-plus-circle-fill"></i></a>
                        </div>
                        <div id="results" class="list-group position-absolute w-100 shadow mt-1" style="max-height: 220px; overflow-y: auto; z-index: 50;"></div>
                    </div>

                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white" title="Selling Price Group"><i class="bi bi-cash-coin"></i></span>
                            <select id="priceTierSelect" class="form-select" onchange="renderCatalog()">
                                <option value="retail">Retail Price</option>
                                <option value="wholesale">Wholesale Price</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Cart Items Table -->
                <div class="upos-table-container">
                    <table class="table upos-cart-table" id="cart">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center" style="width: 125px;">Quantity</th>
                                <th class="text-end" style="width: 115px;">Price inc. tax</th>
                                <th class="text-end" style="width: 110px;">Subtotal</th>
                                <th class="text-center" style="width: 36px;"><i class="bi bi-x-lg"></i></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Summary Box at Bottom of Left Card -->
                <div class="upos-totals-box">
                    <div class="row g-2 mb-2 pb-2 border-bottom">
                        <div class="col-6">
                            <strong>Items:</strong> <span id="cartCount" class="fw-bold">0.00</span>
                            <span id="heldBadge" class="badge bg-warning text-dark ms-2 d-none">Resumed</span>
                        </div>
                        <div class="col-6 text-end">
                            <strong>Total:</strong> <span id="subtotalDisplay" class="fw-bold">0.00</span>
                        </div>
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fw-medium">Discount (-):</span>
                                <input id="discount" type="number" step="0.01" min="0" class="form-control form-control-sm py-0 px-2" style="width: 85px; height: 26px;" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fw-medium">Order Tax (+):</span>
                                <input id="tax" type="number" step="0.01" min="0" class="form-control form-control-sm py-0 px-2" style="width: 85px; height: 26px;" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fw-medium">Shipping:</span>
                                <select id="shipping_status" class="form-select form-select-sm py-0 px-2" style="height: 26px;">
                                    <option value="">None</option>
                                    <option value="Ordered">Ordered</option>
                                    <option value="Packed">Packed</option>
                                    <option value="Shipped">Shipped</option>
                                    <option value="Delivered">Delivered</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Category & Brand Filters + Product Grid -->
        <div class="col-md-5 h-100 d-flex flex-column" style="min-height: 0;">
            <div class="row g-2 mb-2 flex-shrink-0">
                <div class="col-6">
                    <select id="categoryFilter" class="form-select form-select-sm fw-semibold" style="border-radius: 10px; height: 36px;" onchange="renderCatalog()">
                        <option value="all">&#9776; Category (All)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <select id="brandFilter" class="form-select form-select-sm fw-semibold" style="border-radius: 10px; height: 36px;" onchange="renderCatalog()">
                        <option value="all">&#9776; Brands (All)</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="upos-product-grid" id="productGrid"></div>
        </div>
    </div>
</div>

<!-- Fixed Bottom Footer Bar (waves.riset42.com style) -->
<div class="upos-footer">
    <div class="upos-footer-actions d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="upos-action-link upos-action-quotation" onclick="saveAsQuotation()">
            <i class="bi bi-file-earmark-text text-warning"></i>
            <span class="d-none d-md-inline">Quotation</span><span class="d-md-none">Quote</span>
        </button>

        <button id="holdBtn" type="button" class="upos-action-link">
            <i class="bi bi-pause-circle text-danger"></i>
            <span>Suspend</span>
        </button>

        <button type="button" class="upos-btn-multipipay" onclick="openPayModal()">
            <i class="bi bi-cash-stack me-1"></i><span class="d-none d-md-inline">Multiple Pay</span><span class="d-md-none">Multi Pay</span>
        </button>

        <button type="button" class="upos-btn-mpesa" onclick="quickPay('mpesa')">
            <i class="bi bi-phone me-1"></i> M-Pesa
        </button>

        <button type="button" class="upos-btn-cash" onclick="quickPay('cash')">
            <i class="bi bi-cash me-1"></i> Cash
        </button>

        <button type="button" class="upos-btn-cancel" onclick="clearCart()">
            <i class="bi bi-x-circle me-1"></i> Cancel
        </button>
    </div>

    <div class="upos-footer-summary d-flex align-items-center gap-4">
        <div class="fs-5 fw-bold text-dark">
            Total Payable: <span class="text-success">KSh <span id="total">0.00</span></span>
        </div>

        <button type="button" class="upos-btn-recent" data-bs-toggle="modal" data-bs-target="#recentTransactionsModal">
            <i class="bi bi-clock-history me-1"></i><span class="d-none d-md-inline">Recent Transactions</span><span class="d-md-none">Recent</span>
        </button>
    </div>
</div>

<!-- Multiple Pay / Checkout Modal -->
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Amount</label>
                                <input id="paid" type="number" step="0.01" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">Payment Method</label>
                                <select id="method" class="form-select form-select-sm">
                                    <option value="cash">Cash</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold">Split Payment Lines</span>
                                <button type="button" class="btn btn-xs btn-sm btn-outline-primary py-0 px-2" onclick="addPayment()">+ Add Payment Row</button>
                            </div>
                            <div id="payments"></div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Sell Note</label>
                                <textarea id="notes" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">Staff Note</label>
                                <textarea id="staff_note" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small mb-1">Shipping Details</label>
                                <input id="shipping_details" class="form-control form-control-sm" placeholder="Delivery address, courier, phone...">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="border rounded p-3 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span>Total Items:</span>
                                    <strong id="modalTotalItems">0</strong>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span>Total Payable:</span>
                                    <strong>KSh <span id="modalTotalPayable">0.00</span></strong>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span>Total Paying:</span>
                                    <strong>KSh <span id="modalTotalPaying">0.00</span></strong>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom text-success">
                                    <span>Change Return:</span>
                                    <strong>KSh <span id="modalChange">0.00</span></strong>
                                </div>
                                <div class="d-flex justify-content-between py-1 text-danger">
                                    <span>Balance:</span>
                                    <strong>KSh <span id="modalBalance">0.00</span></strong>
                                </div>
                            </div>
                            <button id="checkout" type="button" class="btn btn-primary w-100 mt-3">Finalize Payment</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suspended (Held) Sales Modal -->
<div class="modal fade" id="heldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">Suspended Sales</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-sm">
                    <select id="heldSelect" class="form-select">
                        <option value="">-- Select suspended sale --</option>
                        @foreach($held as $h)
                            <option value="{{ $h->id }}">{{ $h->reference }} &mdash; KSh {{ number_format($h->total, 2) }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary" onclick="resumeHeld()" data-bs-dismiss="modal">Resume</button>
                    <button type="button" class="btn btn-danger" onclick="discardHeld()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions Modal -->
<div class="modal fade" id="recentTransactionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">Recent Transactions</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Invoice No.</th>
                            <th>Customer</th>
                            <th class="text-end">Total</th>
                            <th class="pe-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $idx => $rt)
                            <tr>
                                <td class="ps-3">{{ $idx + 1 }}</td>
                                <td class="fw-semibold">{{ $rt->reference }}</td>
                                <td>{{ $rt->customer->name ?? 'Walk-In Customer' }}</td>
                                <td class="text-end">KSh {{ number_format($rt->total, 2) }}</td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('sales.show', $rt) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a>
                                    <a href="{{ route('sales.receipt', $rt) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">Print</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No recent transactions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const catalogProducts = @json($products);
let cart = [];
let heldId = null;

function getProductPrice(p) {
    const tier = document.getElementById('priceTierSelect').value;
    return tier === 'wholesale' && p.wholesale_price > 0
        ? parseFloat(p.wholesale_price)
        : parseFloat(p.retail_price);
}

function renderCatalog() {
    const grid = document.getElementById('productGrid');
    const catId = document.getElementById('categoryFilter').value;
    const brandId = document.getElementById('brandFilter').value;

    const filtered = catalogProducts.filter(p => {
        const matchCat = catId === 'all' || String(p.category_id) === String(catId);
        const matchBrand = brandId === 'all' || String(p.brand_id) === String(brandId);
        return matchCat && matchBrand;
    });

    if (!filtered.length) {
        grid.innerHTML = `<div class="text-muted small py-4 text-center" style="grid-column: 1 / -1;">No products available.</div>`;
        return;
    }

    grid.innerHTML = filtered.map(p => {
        const price = getProductPrice(p);
        return `<div class="upos-product-box" onclick="addProductToCart(${p.id})">
            <div class="upos-product-icon"><i class="bi bi-box-seam"></i></div>
            <div class="upos-product-name" title="${p.name.replace(/"/g, '&quot;')}">${p.name}</div>
            <div class="upos-product-sub">(${p.sku || '-'}) &bull; KSh ${price.toLocaleString()}</div>
        </div>`;
    }).join('');
}

function addProductToCart(productId, productObj = null) {
    const p = productObj || catalogProducts.find(item => item.id === productId);
    if (!p) return;
    const price = getProductPrice(p);
    const existing = cart.find(c => c.id === p.id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({
            id: p.id,
            name: p.name,
            sku: p.sku || '',
            price: price,
            qty: 1,
            stock: p.stock ?? 0,
            discount: 0
        });
    }
    renderCart();
}

function updateQty(index, delta) {
    if (!cart[index]) return;
    const next = parseFloat((cart[index].qty + delta).toFixed(4));
    if (next <= 0) {
        cart.splice(index, 1);
    } else {
        cart[index].qty = next;
    }
    renderCart();
}

function clearCart() {
    cart = [];
    heldId = null;
    document.getElementById('heldBadge').classList.add('d-none');
    renderCart();
}

function renderCart() {
    const tbody = document.querySelector('#cart tbody');
    tbody.innerHTML = '';
    let subtotal = 0;
    let totalQty = 0;

    cart.forEach((c, i) => {
        const line = (c.qty * c.price) - (c.discount || 0);
        subtotal += line;
        totalQty += c.qty;
        tbody.innerHTML += `<tr>
            <td>
                <div class="fw-semibold text-dark">${c.name}</div>
                <div class="text-muted" style="font-size: 0.72rem;">${c.sku || ''} ${c.stock !== undefined ? '(' + c.stock + ' in stock)' : ''}</div>
            </td>
            <td class="text-center">
                <div class="input-group input-group-sm">
                    <button class="btn btn-outline-danger px-2" type="button" onclick="updateQty(${i}, -1)">&minus;</button>
                    <input type="number" step="0.0001" value="${c.qty}" onchange="cart[${i}].qty=Math.max(0.0001, parseFloat(this.value)||1);renderCart()" class="form-control text-center px-1">
                    <button class="btn btn-outline-success px-2" type="button" onclick="updateQty(${i}, 1)">&#43;</button>
                </div>
            </td>
            <td class="text-end">
                <input type="number" step="0.01" value="${c.price.toFixed(2)}" onchange="cart[${i}].price=parseFloat(this.value)||0;renderCart()" class="form-control form-control-sm text-end">
            </td>
            <td class="text-end fw-semibold">${line.toFixed(2)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm text-danger p-0" onclick="cart.splice(${i},1);renderCart()"><i class="bi bi-x-lg"></i></button>
            </td>
        </tr>`;
    });

    const disc = parseFloat(document.getElementById('discount').value) || 0;
    const tax = parseFloat(document.getElementById('tax').value) || 0;
    const grand = Math.max(0, subtotal - disc + tax);

    document.getElementById('cartCount').textContent = totalQty.toFixed(2);
    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
    document.getElementById('total').textContent = grand.toFixed(2);
    document.getElementById('paid').value = grand.toFixed(2);
    updateModalSummary();
}

function updateModalSummary() {
    const grand = parseFloat(document.getElementById('total').textContent) || 0;
    const splitPays = collectPayments();
    const paying = splitPays.length
        ? splitPays.reduce((s, p) => s + p.amount, 0)
        : (parseFloat(document.getElementById('paid').value) || 0);

    document.getElementById('modalTotalItems').textContent = document.getElementById('cartCount').textContent;
    document.getElementById('modalTotalPayable').textContent = grand.toFixed(2);
    document.getElementById('modalTotalPaying').textContent = paying.toFixed(2);
    document.getElementById('modalChange').textContent = Math.max(0, paying - grand).toFixed(2);
    document.getElementById('modalBalance').textContent = Math.max(0, grand - paying).toFixed(2);
}

function openPayModal() {
    if (!cart.length) {
        alert('Please add items first.');
        return;
    }
    updateModalSummary();
    new bootstrap.Modal(document.getElementById('payModal')).show();
}

async function saveAsQuotation() {
    if (!cart.length) {
        alert('Please add items first.');
        return;
    }
    const payload = {
        customer_id: document.getElementById('customer').value || null,
        discount: parseFloat(document.getElementById('discount').value) || 0,
        tax: parseFloat(document.getElementById('tax').value) || 0,
        notes: document.getElementById('notes')?.value || null,
        items: cart.map(c => ({ product_id: c.id, quantity: c.qty, unit_price: c.price })),
    };
    const res = await fetch('{{ route('quotations.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
        heldId = null;
        window.location.href = '/quotations/' + data.quotation_id;
    } else {
        alert('Error creating quotation.');
    }
}

async function quickPay(method) {
    if (!cart.length) {
        alert('Please add items first.');
        return;
    }
    document.getElementById('method').value = method;
    document.getElementById('payments').innerHTML = '';
    const grand = parseFloat(document.getElementById('total').textContent) || 0;
    document.getElementById('paid').value = grand.toFixed(2);
    const url = heldId ? '/pos/held/' + heldId + '/complete' : '/pos/sale';
    const data = await submitSale(url);
    if (data.success) {
        heldId = null;
        window.location.href = '/sales/' + data.sale_id;
    } else {
        alert('Error completing sale.');
    }
}

['discount', 'tax', 'paid'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        if (id !== 'paid') renderCart();
        else updateModalSummary();
    });
});

document.getElementById('search').addEventListener('input', async function () {
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('results').innerHTML = '';
        return;
    }
    const res = await fetch('/api/products/search?q=' + encodeURIComponent(q));
    const data = await res.json();
    const el = document.getElementById('results');
    el.innerHTML = '';
    data.forEach(p => {
        const a = document.createElement('a');
        a.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-1 small';
        a.innerHTML = `<span>${p.name} (${p.sku}) &mdash; Stk: ${p.stock}</span><strong>KSh ${parseFloat(p.retail_price).toFixed(2)}</strong>`;
        a.onclick = () => {
            addProductToCart(p.id, p);
            document.getElementById('search').value = '';
            el.innerHTML = '';
        };
        el.appendChild(a);
    });
});

document.getElementById('search').addEventListener('keydown', async function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const q = this.value.trim();
        if (!q) return;
        const res = await fetch('/api/products/search?q=' + encodeURIComponent(q));
        const data = await res.json();
        if (data.length > 0) {
            addProductToCart(data[0].id, data[0]);
            this.value = '';
            document.getElementById('results').innerHTML = '';
        }
    }
});

function addPayment() {
    const div = document.createElement('div');
    div.className = 'payrow d-flex gap-1 mb-1';
    div.innerHTML = `<select class="form-select form-select-sm method" style="width:130px"><option value="cash">Cash</option><option value="mpesa">M-Pesa</option><option value="bank">Bank</option></select>
        <input type="number" step="0.01" class="form-control form-control-sm amount" placeholder="Amount" oninput="updateModalSummary()">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove();updateModalSummary()">x</button>`;
    document.getElementById('payments').appendChild(div);
}

function collectPayments() {
    const rows = document.querySelectorAll('#payments .payrow');
    const pays = [];
    rows.forEach(r => {
        const m = r.querySelector('.method').value;
        const a = parseFloat(r.querySelector('.amount').value) || 0;
        if (a > 0) pays.push({ method: m, amount: a });
    });
    return pays;
}

async function submitSale(url) {
    const pays = collectPayments();
    const payload = {
        customer_id: document.getElementById('customer').value || null,
        warehouse_id: document.getElementById('warehouse').value,
        items: cart.map(c => ({ product_id: c.id, quantity: c.qty, unit_price: c.price, discount: c.discount })),
        discount: parseFloat(document.getElementById('discount').value) || 0,
        tax: parseFloat(document.getElementById('tax').value) || 0,
        paid: parseFloat(document.getElementById('paid').value) || 0,
        payment_method: document.getElementById('method').value,
        shipping_status: document.getElementById('shipping_status')?.value || null,
        shipping_details: document.getElementById('shipping_details')?.value || null,
        notes: document.getElementById('notes')?.value || null,
        staff_note: document.getElementById('staff_note')?.value || null,
    };
    if (pays.length) payload.payments = pays;
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    });
    return res.json();
}

document.getElementById('checkout').addEventListener('click', async () => {
    const url = heldId ? '/pos/held/' + heldId + '/complete' : '/pos/sale';
    const data = await submitSale(url);
    if (data.success) {
        heldId = null;
        window.location.href = '/sales/' + data.sale_id;
    } else {
        alert('Error completing sale.');
    }
});

document.getElementById('holdBtn').addEventListener('click', async () => {
    if (!cart.length) return;
    const data = await submitSale('/pos/hold');
    if (data.success) {
        heldId = null;
        cart = [];
        renderCart();
        location.reload();
    } else {
        alert('Error suspending sale.');
    }
});

async function resumeHeld() {
    const id = document.getElementById('heldSelect').value;
    if (!id) return;
    const res = await fetch('/pos/held/' + id);
    const data = await res.json();
    cart = data.items.map(i => ({ id: i.id, name: i.name, sku: '', price: i.price, qty: i.qty, discount: i.discount }));
    document.getElementById('customer').value = data.customer_id || '';
    document.getElementById('warehouse').value = data.warehouse_id;
    document.getElementById('discount').value = data.discount;
    document.getElementById('tax').value = data.tax;
    heldId = id;
    document.getElementById('heldBadge').classList.remove('d-none');
    renderCart();
}

async function discardHeld() {
    const id = document.getElementById('heldSelect').value;
    if (!id) return;
    if (!confirm('Discard this suspended sale?')) return;
    const res = await fetch('/pos/held/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    const data = await res.json();
    if (data.success) location.reload();
}

renderCatalog();
renderCart();
</script>
@endpush
