<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Sale;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $r)
    {
        $q = $r->get('q');
        $customers = Customer::with('customerGroup')
            ->when($q, fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('phone', 'like', "%$q%")->orWhere('business_name', 'like', "%$q%"))
            ->latest()->paginate(20);

        return view('customers.index', compact('customers', 'q'));
    }

    public function create()
    {
        return view('customers.create', [
            'customerGroups' => CustomerGroup::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string',
            'business_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'kra_pin' => 'nullable|string',
            'category' => 'nullable|string',
            'credit_limit' => 'nullable|numeric',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $customer = Customer::create($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer saved.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['customerGroup', 'sales' => fn ($q) => $q->latest()->limit(10)]);
        $totalSales = Sale::where('customer_id', $customer->id)->sum('total');
        $totalPaid = Sale::where('customer_id', $customer->id)->sum('paid');

        return view('customers.show', compact('customer', 'totalSales', 'totalPaid'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', [
            'customer' => $customer,
            'customerGroups' => CustomerGroup::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $r, Customer $customer)
    {
        $data = $r->validate([
            'name' => 'required|string',
            'business_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'kra_pin' => 'nullable|string',
            'category' => 'nullable|string',
            'credit_limit' => 'nullable|numeric',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
