<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $r)
    {
        $q = $r->get('q');
        $suppliers = Supplier::when($q, fn ($x) => $x->where('name', 'like', "%$q%"))->latest()->paginate(20);

        return view('suppliers.index', compact('suppliers', 'q'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $r)
    {
        Supplier::create($r->validate([
            'name' => 'required|string',
            'business_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'kra_pin' => 'nullable|string',
            'payment_terms' => 'nullable|integer',
        ]));

        return redirect()->route('suppliers.index')->with('success', 'Saved.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $r, Supplier $supplier)
    {
        $supplier->update($r->validate([
            'name' => 'required|string',
            'business_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'kra_pin' => 'nullable|string',
            'payment_terms' => 'nullable|integer',
        ]));

        return redirect()->route('suppliers.index')->with('success', 'Updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Deleted.');
    }
}
