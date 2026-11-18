<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use Illuminate\Http\Request;

class CustomerLedgerController extends Controller
{
    public function index(Request $request)
    {
        $customerId = $request->get('customer_id');
        $entries = CustomerLedger::with('customer')
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->latest()->paginate(30);

        return view('customer_ledger.index', [
            'entries' => $entries,
            'customers' => Customer::orderBy('name')->get(),
            'customerId' => $customerId,
        ]);
    }
}
