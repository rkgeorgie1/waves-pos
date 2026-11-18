<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use Illuminate\Http\Request;

class SupplierLedgerController extends Controller
{
    public function index(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        $entries = SupplierLedger::with('supplier')
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->latest()->paginate(30);

        return view('supplier_ledger.index', [
            'entries' => $entries,
            'suppliers' => Supplier::orderBy('name')->get(),
            'supplierId' => $supplierId,
        ]);
    }
}
