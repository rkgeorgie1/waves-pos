@extends('layouts.app')
@section('title', 'Receipt Settings')

@section('content')
<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.index') }}"><i class="bi bi-sliders me-1"></i>System Settings</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('settings.receipt') }}"><i class="bi bi-printer me-1"></i>Receipt Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.staff') }}"><i class="bi bi-people me-1"></i>Staff Management</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.currencies') }}"><i class="bi bi-currency-exchange me-1"></i>Currencies</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('settings.access') }}"><i class="bi bi-shield-lock me-1"></i>Access Management</a></li>
</ul>

<div class="row g-4">
    <!-- Left Column: Receipt Configuration Form -->
    <div class="col-lg-7">
        <div class="card p-4">
            <h5 class="mb-3">Receipt Customization</h5>
            <form method="POST" action="{{ route('settings.receipt.update') }}" id="receiptForm">
                @csrf
                <h6 class="text-muted text-uppercase small fw-semibold mb-3">Header &amp; Business Information</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Business / Store Name</label>
                        <input name="business_name" class="form-control" value="{{ $receiptSettings['business_name'] }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Receipt Title</label>
                        <input name="receipt_header_title" class="form-control" value="{{ $receiptSettings['receipt_header_title'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input name="business_phone" class="form-control" value="{{ $receiptSettings['business_phone'] }}" placeholder="+254 700 000000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input name="business_email" class="form-control" value="{{ $receiptSettings['business_email'] }}" placeholder="sales@airwaves.co.ke">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address / Location Line</label>
                        <input name="business_address" class="form-control" value="{{ $receiptSettings['business_address'] }}" placeholder="Industrial Area, Nairobi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tax PIN / KRA PIN</label>
                        <input name="business_tax_pin" class="form-control" value="{{ $receiptSettings['business_tax_pin'] }}" placeholder="P051234567X">
                    </div>
                </div>

                <h6 class="text-muted text-uppercase small fw-semibold mb-3">Paper Size &amp; Display Options</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Paper Width</label>
                        <select name="receipt_paper_size" class="form-select">
                            <option value="80mm" {{ $receiptSettings['receipt_paper_size'] === '80mm' ? 'selected' : '' }}>80mm (Standard Thermal Roll)</option>
                            <option value="58mm" {{ $receiptSettings['receipt_paper_size'] === '58mm' ? 'selected' : '' }}>58mm (Compact Thermal Roll)</option>
                            <option value="A4" {{ $receiptSettings['receipt_paper_size'] === 'A4' ? 'selected' : '' }}>A4 (Full Page Invoice)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="receipt_show_warehouse" value="1" id="chkWarehouse" {{ $receiptSettings['receipt_show_warehouse'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chkWarehouse">Show Warehouse / Branch Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="receipt_show_customer" value="1" id="chkCustomer" {{ $receiptSettings['receipt_show_customer'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chkCustomer">Show Customer Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="receipt_show_cashier" value="1" id="chkCashier" {{ $receiptSettings['receipt_show_cashier'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chkCashier">Show Cashier ("Served By")</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="receipt_show_sku" value="1" id="chkSku" {{ $receiptSettings['receipt_show_sku'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chkSku">Show Item SKU Code</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="receipt_show_tax" value="1" id="chkTax" {{ $receiptSettings['receipt_show_tax'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chkTax">Show Tax / VAT Line</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="receipt_show_payments" value="1" id="chkPayments" {{ $receiptSettings['receipt_show_payments'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chkPayments">Show Payment Method Breakdown</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="receipt_show_barcode" value="1" id="chkBarcode" {{ $receiptSettings['receipt_show_barcode'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chkBarcode">Show Reference Code Box</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="receipt_auto_print" value="1" id="chkAutoPrint" {{ $receiptSettings['receipt_auto_print'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chkAutoPrint">Auto-Open Print Dialog on Receipt Load</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="text-muted text-uppercase small fw-semibold mb-3">Footer Messages</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Return Policy / Terms Note</label>
                        <textarea name="receipt_footer_note" class="form-control" rows="2">{{ $receiptSettings['receipt_footer_note'] }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Closing Thank-You Line</label>
                        <input name="receipt_thank_you" class="form-control" value="{{ $receiptSettings['receipt_thank_you'] }}">
                    </div>
                </div>

                <button class="btn btn-primary">Save Receipt Settings</button>
            </form>
        </div>
    </div>

    <!-- Right Column: Sample Receipt Preview -->
    <div class="col-lg-5">
        <div class="card p-4 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-semibold">Sample Receipt Preview</h6>
                <span class="badge bg-secondary">{{ $receiptSettings['receipt_paper_size'] }}</span>
            </div>
            <div class="bg-white mx-auto p-3 border" style="width: 300px; font-family: Arial, Helvetica, sans-serif; font-size: 11.5px; line-height: 1.35; color: #000;">
                <div class="text-center">
                    <div class="fw-bold" style="font-size: 15px;">{{ $receiptSettings['business_name'] }}</div>
                    @if($receiptSettings['receipt_show_warehouse'] == '1')
                        <div>Main Warehouse</div>
                    @endif
                    @if(!empty($receiptSettings['business_address']))
                        <div>{{ $receiptSettings['business_address'] }}</div>
                    @endif
                    @if(!empty($receiptSettings['business_phone']))
                        <div>Tel: {{ $receiptSettings['business_phone'] }}</div>
                    @endif
                    @if(!empty($receiptSettings['business_email']))
                        <div>{{ $receiptSettings['business_email'] }}</div>
                    @endif
                    @if(!empty($receiptSettings['business_tax_pin']))
                        <div>PIN: {{ $receiptSettings['business_tax_pin'] }}</div>
                    @endif
                    @if(!empty($receiptSettings['receipt_header_title']))
                        <div class="fw-bold mt-2 py-1" style="border-top: 1px solid #000; border-bottom: 1px solid #000;">{{ $receiptSettings['receipt_header_title'] }}</div>
                    @endif
                </div>
                <div class="mt-2">
                    <div class="d-flex justify-content-between"><span>Receipt #:</span><strong>SAL-20261117-001</strong></div>
                    <div class="d-flex justify-content-between"><span>Date:</span><span>{{ now()->format('d/m/Y H:i') }}</span></div>
                    @if($receiptSettings['receipt_show_customer'] == '1')
                        <div class="d-flex justify-content-between"><span>Customer:</span><span>Walk-in</span></div>
                    @endif
                    @if($receiptSettings['receipt_show_cashier'] == '1')
                        <div class="d-flex justify-content-between"><span>Cashier:</span><span>{{ auth()->user()->name ?? 'Administrator' }}</span></div>
                    @endif
                </div>
                <hr style="border-top: 1px solid #000; opacity: 1; margin: 6px 0;">
                <div class="d-flex justify-content-between fw-bold pb-1 mb-1" style="border-bottom: 1px solid #000;">
                    <span>Item</span><span>Qty</span><span>Price</span><span>Total</span>
                </div>
                <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dotted #ccc;">
                    <div>
                        <div>LED Floodlight 50W</div>
                        @if($receiptSettings['receipt_show_sku'] == '1')
                            <div style="font-size: 10px;">(SKU-001)</div>
                        @endif
                    </div>
                    <span>2</span>
                    <span>1,250.00</span>
                    <span class="fw-bold">2,500.00</span>
                </div>
                <div class="mt-2">
                    <div class="d-flex justify-content-between"><span>Subtotal (1 item)</span><span>2,500.00</span></div>
                    @if($receiptSettings['receipt_show_tax'] == '1')
                        <div class="d-flex justify-content-between"><span>VAT / Tax</span><span>400.00</span></div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold py-1 my-1" style="font-size: 13px; border-top: 1px solid #000; border-bottom: 1px solid #000;">
                        <span>TOTAL (KES)</span><span>2,900.00</span>
                    </div>
                    @if($receiptSettings['receipt_show_payments'] == '1')
                        <div class="d-flex justify-content-between"><span>Paid (CASH)</span><span>3,000.00</span></div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold"><span>Change</span><span>100.00</span></div>
                </div>
                <hr style="border-top: 1px dashed #000; opacity: 1; margin: 8px 0;">
                <div class="text-center" style="font-size: 10.5px;">
                    @if(!empty($receiptSettings['receipt_footer_note']))
                        <div>{{ $receiptSettings['receipt_footer_note'] }}</div>
                    @endif
                    @if(!empty($receiptSettings['receipt_thank_you']))
                        <div class="fw-bold mt-1">{{ $receiptSettings['receipt_thank_you'] }}</div>
                    @endif
                    @if($receiptSettings['receipt_show_barcode'] == '1')
                        <div class="mt-1" style="font-size: 10px;">Ref: SAL-20261117-001</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
