<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Airwaves POS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="theme-color" content="#4f46e5">
    <style>
        body {
            background: #f3f4f6;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 0.875rem;
            color: #1f2937;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            color: #111827;
        }
        .tabular-nums, td, th {
            font-variant-numeric: tabular-nums;
        }
        /* Fix layout so sidebar stays fixed and only main content scrolls */
        html, body {
            height: 100%;
            overflow: hidden;
        }
        @media (max-width: 767.98px) {
            .sidebar { transform: translateX(-100%); transition: transform .2s ease; width: 260px !important; }
            .sidebar.show { transform: translateX(0); }
            .main-content-wrapper { margin-left: 0 !important; }
            .mobile-toggle { display: inline-flex !important; }
            .sidebar { height: 100dvh; }
            .sidebar-backdrop {
                display: block !important;
                position: fixed;
                inset: 0;
                background: rgba(17, 24, 39, 0.45);
                z-index: 99;
                opacity: 0;
                visibility: hidden;
                transition: opacity .2s ease, visibility .2s ease;
            }
            .sidebar-backdrop.show {
                opacity: 1;
                visibility: visible;
            }
        }
        @media (max-width: 575.98px) {
            .navbar-upos { padding: 0 10px; }
            .navbar-upos .ms-auto { gap: 4px !important; }
            .navbar-upos .fw-semibold.ms-2 { display: none; }
            .upos-pos-pill { padding: 6px 9px; }
            .upos-header-btn { padding: 6px 9px; font-size: 0.76rem; }
            main.p-4 { padding: 12px !important; }
            .table { font-size: 0.78rem; }
            .table > :not(caption) > * > * { padding: 0.4rem 0.5rem; }
        }
        .sidebar-backdrop { display: none; }
        .mobile-toggle { display: none; }
        .mobile-toggle {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.45);
            color: #ffffff;
        }
        .mobile-toggle:hover, .mobile-toggle:focus {
            background: rgba(255, 255, 255, 0.28);
            border-color: #ffffff;
            color: #ffffff;
        }
        .container-fluid {
            height: 100vh;
            overflow: hidden;
        }
        .sidebar {
            height: 100vh;
            overflow-y: auto;
            position: fixed;
            top: 0;
            left: 0;
            width: 16.666667%; /* col-md-2 equivalent */
            z-index: 100;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            color: #374151;
        }
        .main-content-wrapper {
            margin-left: 16.666667%;
            height: 100vh;
            overflow-y: auto;
            position: relative;
        }
        .sidebar-brand {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            border-bottom: 1px solid #e5e7eb;
            background: #ffffff;
        }
        .sidebar-brand-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .online-dot {
            width: 9px;
            height: 9px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
        }
        .sidebar a {
            color: #4b5563;
            text-decoration: none;
            display: block;
            padding: 9px 14px;
            border-radius: 10px;
            margin: 2px 10px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .sidebar a:hover {
            background: #f3f4f6;
            color: #111827;
        }
        .sidebar a.active {
            background: #eff6ff;
            color: #3b82f6;
            font-weight: 600;
        }
        .sidebar .collapse a {
            font-size: 0.81rem;
            padding: 7px 14px 7px 36px;
            color: #6b7280;
        }
        .sidebar .collapse a.active {
            background: #eff6ff;
            color: #3b82f6;
        }
        .sidebar .bi-chevron-down {
            transition: transform .2s ease;
            font-size: 0.72rem;
        }
        .sidebar a[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }

        /* UltimatePOS v6 Indigo-to-Blue Gradient Top Navbar */
        .navbar-upos {
            height: 60px;
            background: #3b82f6;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .upos-header-btn {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 10px;
            padding: 6px 13px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .upos-header-btn:hover {
            background: rgba(255, 255, 255, 0.26);
            color: #ffffff;
        }
        .upos-pos-pill {
            background: #ffffff;
            color: #3b82f6;
            border-radius: 10px;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }
        .upos-pos-pill:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            background: #ffffff;
        }

        /* Mobile: scroll the document itself so the last row is always reachable */
        @media (max-width: 767.98px) {
            html, body {
                height: auto !important;
                min-height: 100%;
                overflow: visible !important;
            }
            .container-fluid {
                height: auto !important;
                overflow: visible !important;
            }
            .container-fluid > .row {
                display: block;
            }
            .main-content-wrapper {
                height: auto !important;
                min-height: 100dvh;
                overflow: visible !important;
                padding-bottom: 24px;
            }
            .sidebar {
                position: fixed !important;
                height: 100dvh !important;
                overflow-y: auto !important;
            }
            .navbar-upos {
                position: sticky;
                top: 0;
                z-index: 50;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0" id="appSidebar">
            <div class="sidebar-brand">
                @php $sidebarLogo = \App\Models\Setting::where('key','business_logo')->value('value'); @endphp
                <span class="sidebar-brand-title">
                    @if($sidebarLogo)
                        <img src="{{ asset('storage/'.$sidebarLogo) }}" alt="Logo" style="max-height:32px; max-width:120px;">
                    @else
                        Airwaves POS
                    @endif
                </span>
            </div>
            <nav class="mt-2 pb-4">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-house-door me-2"></i>Home</a>
                @php
                    $productsMenuOpen = request()->routeIs('products.*', 'product_variants.*', 'warranties.*', 'selling_price_groups.*', 'categories.*', 'brands.*', 'units.*');
                    $purchasesMenuOpen = request()->routeIs('purchases.*');
                    $sellMenuOpen = request()->routeIs('sales.*', 'pos', 'pos.*', 'quotations.*', 'sales_returns.*');
                    $inventoryMenuOpen = request()->routeIs('inventory.*', 'warehouse_transfers.*', 'product_batches.*', 'serial_numbers.*');
                    $customersMenuOpen = request()->routeIs('customers.*', 'customer_groups.*');
                    $financeMenuOpen = request()->routeIs('expenses.*', 'cash_shifts.*', 'customer_ledger.*', 'supplier_ledger.*');
                    $settingsMenuOpen = request()->routeIs('settings.*');
                @endphp
                <a href="#productsDropdown" data-bs-toggle="collapse" role="button" aria-expanded="{{ $productsMenuOpen ? 'true' : 'false' }}" aria-controls="productsDropdown" class="d-flex align-items-center justify-content-between {{ $productsMenuOpen ? 'active' : '' }}">
                    <span><i class="bi bi-box-seam me-2"></i>Products</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ $productsMenuOpen ? 'show' : '' }}" id="productsDropdown">
                    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.index') ? 'active' : '' }}">List Products</a>
                    <a href="{{ route('products.create') }}" class="{{ request()->routeIs('products.create') ? 'active' : '' }}">Add Product</a>
                    <a href="{{ route('products.update_price') }}" class="{{ request()->routeIs('products.update_price') ? 'active' : '' }}">Update Price</a>
                    <a href="{{ route('products.import') }}" class="{{ request()->routeIs('products.import') ? 'active' : '' }}">Import Products</a>
                    <a href="{{ route('products.print_labels') }}" class="{{ request()->routeIs('products.print_labels') ? 'active' : '' }}">Print Labels</a>
                    <a href="{{ route('product_variants.index') }}" class="{{ request()->routeIs('product_variants.*') ? 'active' : '' }}">Variations</a>
                    <a href="{{ route('warranties.index') }}" class="{{ request()->routeIs('warranties.*') ? 'active' : '' }}">Warranties</a>
                    <a href="{{ route('selling_price_groups.index') }}" class="{{ request()->routeIs('selling_price_groups.*') ? 'active' : '' }}">Selling Price Groups</a>
                    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">Categories</a>
                </div>

                <a href="#customersDropdown" data-bs-toggle="collapse" role="button" aria-expanded="{{ $customersMenuOpen ? 'true' : 'false' }}" aria-controls="customersDropdown" class="d-flex align-items-center justify-content-between {{ $customersMenuOpen ? 'active' : '' }}">
                    <span><i class="bi bi-people me-2"></i>Customers</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ $customersMenuOpen ? 'show' : '' }}" id="customersDropdown">
                    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">List Customers</a>
                    <a href="{{ route('customer_groups.index') }}" class="{{ request()->routeIs('customer_groups.*') ? 'active' : '' }}">Customer Groups</a>
                </div>
                <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="bi bi-truck me-2"></i>Suppliers</a>

                <a href="#purchasesDropdown" data-bs-toggle="collapse" role="button" aria-expanded="{{ $purchasesMenuOpen ? 'true' : 'false' }}" aria-controls="purchasesDropdown" class="d-flex align-items-center justify-content-between {{ $purchasesMenuOpen ? 'active' : '' }}">
                    <span><i class="bi bi-arrow-down-circle me-2"></i>Purchases</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ $purchasesMenuOpen ? 'show' : '' }}" id="purchasesDropdown">
                    <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.index', 'purchases.show') ? 'active' : '' }}">List Purchases</a>
                    <a href="{{ route('purchases.create') }}" class="{{ request()->routeIs('purchases.create') ? 'active' : '' }}">Add Purchase</a>
                </div>

                <a href="#sellDropdown" data-bs-toggle="collapse" role="button" aria-expanded="{{ $sellMenuOpen ? 'true' : 'false' }}" aria-controls="sellDropdown" class="d-flex align-items-center justify-content-between {{ $sellMenuOpen ? 'active' : '' }}">
                    <span><i class="bi bi-cart-check me-2"></i>Sell</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ $sellMenuOpen ? 'show' : '' }}" id="sellDropdown">
                    <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.index', 'sales.show') ? 'active' : '' }}">All sales</a>
                    <a href="{{ route('sales.create') }}" class="{{ request()->routeIs('sales.create') ? 'active' : '' }}">Add Sale</a>
                    <a href="{{ route('sales.pos_list') }}" class="{{ request()->routeIs('sales.pos_list') ? 'active' : '' }}">List POS</a>
                    <a href="{{ route('pos') }}" class="{{ request()->routeIs('pos') ? 'active' : '' }}">POS</a>
                    <a href="{{ route('sales.drafts.create') }}" class="{{ request()->routeIs('sales.drafts.create') ? 'active' : '' }}">Add Draft</a>
                    <a href="{{ route('sales.drafts.index') }}" class="{{ request()->routeIs('sales.drafts.index') ? 'active' : '' }}">List Drafts</a>
                    <a href="{{ route('quotations.create') }}" class="{{ request()->routeIs('quotations.create') ? 'active' : '' }}">Add Quotation</a>
                    <a href="{{ route('quotations.index') }}" class="{{ request()->routeIs('quotations.index', 'quotations.show') ? 'active' : '' }}">List quotations</a>
                    <a href="{{ route('sales.shipments') }}" class="{{ request()->routeIs('sales.shipments') ? 'active' : '' }}">Shipments</a>
                    <a href="{{ route('sales.import') }}" class="{{ request()->routeIs('sales.import') ? 'active' : '' }}">Import Sales</a>
                    <a href="{{ route('sales_returns.index') }}" class="{{ request()->routeIs('sales_returns.*') ? 'active' : '' }}">List Sell Return</a>
                </div>

                <a href="#inventoryDropdown" data-bs-toggle="collapse" role="button" aria-expanded="{{ $inventoryMenuOpen ? 'true' : 'false' }}" aria-controls="inventoryDropdown" class="d-flex align-items-center justify-content-between {{ $inventoryMenuOpen ? 'active' : '' }}">
                    <span><i class="bi bi-archive me-2"></i>Inventory</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ $inventoryMenuOpen ? 'show' : '' }}" id="inventoryDropdown">
                    <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">Stock Balances</a>
                    <a href="{{ route('warehouse_transfers.index') }}" class="{{ request()->routeIs('warehouse_transfers.*') ? 'active' : '' }}">Stock Transfers</a>
                    <a href="{{ route('product_batches.index') }}" class="{{ request()->routeIs('product_batches.*') ? 'active' : '' }}">Batches/Expiry</a>
                    <a href="{{ route('serial_numbers.index') }}" class="{{ request()->routeIs('serial_numbers.*') ? 'active' : '' }}">Serials</a>
                </div>
                <a href="#financeDropdown" data-bs-toggle="collapse" role="button" aria-expanded="{{ $financeMenuOpen ? 'true' : 'false' }}" aria-controls="financeDropdown" class="d-flex align-items-center justify-content-between {{ $financeMenuOpen ? 'active' : '' }}">
                    <span><i class="bi bi-wallet2 me-2"></i>Finance</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ $financeMenuOpen ? 'show' : '' }}" id="financeDropdown">
                    <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">Expenses</a>
                    <a href="{{ route('cash_shifts.index') }}" class="{{ request()->routeIs('cash_shifts.*') ? 'active' : '' }}">Cash Shifts</a>
                    <a href="{{ route('customer_ledger.index') }}" class="{{ request()->routeIs('customer_ledger.*') ? 'active' : '' }}">Customer Ledger</a>
                    <a href="{{ route('supplier_ledger.index') }}" class="{{ request()->routeIs('supplier_ledger.*') ? 'active' : '' }}">Supplier Ledger</a>
                </div>
                <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-bar-chart-line me-2"></i>Reports</a>

                @if(auth()->user()?->is_platform_admin)
                    <a href="{{ route('platform.dashboard') }}" class="{{ request()->routeIs('platform.*') ? 'active' : '' }}"><i class="bi bi-cloud me-2"></i>Platform Console</a>
                @endif
                <a href="{{ route('billing.index') }}" class="{{ request()->routeIs('billing.*') ? 'active' : '' }}"><i class="bi bi-credit-card me-2"></i>Billing &amp; Plans</a>

                <a href="#settingsDropdown" data-bs-toggle="collapse" role="button" aria-expanded="{{ $settingsMenuOpen ? 'true' : 'false' }}" aria-controls="settingsDropdown" class="d-flex align-items-center justify-content-between {{ $settingsMenuOpen ? 'active' : '' }}">
                    <span><i class="bi bi-gear me-2"></i>Settings</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ $settingsMenuOpen ? 'show' : '' }}" id="settingsDropdown">
                    <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.index') ? 'active' : '' }}">System Settings</a>
                    <a href="{{ route('settings.receipt') }}" class="{{ request()->routeIs('settings.receipt') ? 'active' : '' }}">Receipt Settings</a>
                    <a href="{{ route('settings.staff') }}" class="{{ request()->routeIs('settings.staff') ? 'active' : '' }}">Staff Management</a>
                    <a href="{{ route('settings.currencies') }}" class="{{ request()->routeIs('settings.currencies') ? 'active' : '' }}">Currencies</a>
                    <a href="{{ route('settings.warehouses') }}" class="{{ request()->routeIs('settings.warehouses') ? 'active' : '' }}">Warehouses</a>
                    <a href="{{ route('settings.access') }}" class="{{ request()->routeIs('settings.access') ? 'active' : '' }}">Access Management</a>
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">My Account</a>
                </div>
            </nav>
        </div>
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
        <div class="main-content-wrapper">
            <div class="col-md-10 p-0">
                <nav class="navbar-upos">
                <div class="d-flex align-items-center gap-2">
                    <span class="upos-header-btn d-none d-md-inline-flex">
                        <i class="bi bi-calendar3"></i> {{ now()->format('d/m/Y') }}
                    </span>
                </div>
                <button class="btn btn-sm btn-outline-secondary mobile-toggle me-2" id="mobileSidebarToggle" type="button" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <a href="{{ route('pos') }}" class="upos-pos-pill">
                        <i class="bi bi-grid-3x3-gap-fill"></i> POS
                    </a>
                    <a href="{{ route('reports.profit') }}" class="upos-header-btn" title="Today's Profit">
                        <i class="bi bi-cash-coin"></i>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="upos-header-btn position-relative" title="Notifications">
                        <i class="bi bi-bell"></i>
                        @if(auth()->user()->unreadNotificationsCount() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                {{ auth()->user()->unreadNotificationsCount() }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('profile.edit') }}" class="upos-header-btn" title="Account settings">
                        <i class="bi bi-person-circle"></i><span class="d-none d-md-inline ms-1">Account</span>
                    </a>
                    <span class="fw-semibold ms-2">{{ auth()->user()->name ?? '' }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf
                        <button class="upos-header-btn" type="submit">Logout</button>
                    </form>
                </div>
            </nav>
            <main class="p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger rounded-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </main>
        </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    }

    (function () {
        const sidebar = document.getElementById('appSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggle = document.getElementById('mobileSidebarToggle');
        const storageKey = 'posSidebarOpen';

        function setSidebar(open) {
            sidebar.classList.toggle('show', open);
            backdrop.classList.toggle('show', open);
            try {
                sessionStorage.setItem(storageKey, open ? '1' : '0');
            } catch (error) {
                // Storage may be unavailable in private browsing.
            }
        }

        let wasOpen = false;
        try {
            wasOpen = sessionStorage.getItem(storageKey) === '1';
        } catch (error) {
            // Storage may be unavailable in private browsing.
        }
        if (wasOpen) {
            sidebar.classList.add('show');
            backdrop.classList.add('show');
        }

        toggle?.addEventListener('click', function () {
            setSidebar(!sidebar.classList.contains('show'));
        });
        backdrop?.addEventListener('click', function () {
            setSidebar(false);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setSidebar(false);
            }
        });
    })();

    (function () {
        const forms = document.querySelectorAll('form[data-auto-filter]');
        const debounceMs = 350;

        if (! forms.length) {
            return;
        }

        let debounceTimer = null;

        forms.forEach(function (form) {
            const focusKey = 'posAutoFilter:' + form.getAttribute('action');

            function submitForm() {
                clearTimeout(debounceTimer);

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                    return;
                }

                form.submit();
            }

            form.addEventListener('input', function (event) {
                const field = event.target;

                if (! (field instanceof HTMLInputElement)) {
                    return;
                }

                const caretFields = ['text', 'search', 'tel', 'url', 'email', 'password'];

                if (caretFields.includes(field.type) && field.name) {
                    try {
                        sessionStorage.setItem(focusKey, JSON.stringify({
                            name: field.name,
                            start: field.selectionStart,
                            end: field.selectionEnd,
                        }));
                    } catch (error) {
                        // Storage may be unavailable in private browsing.
                    }
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitForm, debounceMs);
            });

            form.addEventListener('change', function (event) {
                const field = event.target;
                const isAutoSubmitField = field instanceof HTMLSelectElement
                    || (field instanceof HTMLInputElement && ['date', 'checkbox', 'radio'].includes(field.type));

                if (isAutoSubmitField) {
                    submitForm();
                }
            });

            form.addEventListener('submit', function () {
                clearTimeout(debounceTimer);
            });

            try {
                const state = JSON.parse(sessionStorage.getItem(focusKey) || 'null');
                sessionStorage.removeItem(focusKey);

                if (state && state.name) {
                    const field = form.elements.namedItem(state.name);

                    if (field && typeof field.focus === 'function') {
                        field.focus();

                        if (typeof field.setSelectionRange === 'function' && state.start !== null) {
                            field.setSelectionRange(state.start, state.end ?? state.start);
                        }
                    }
                }
            } catch (error) {
                // Storage may be unavailable in private browsing.
            }
        });
    })();
</script>
@stack('scripts')
</body>
</html>
