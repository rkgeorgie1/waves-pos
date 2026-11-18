<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $groups = CustomerGroup::withCount('customers')->latest()->paginate(20);

        return view('customer_groups.index', compact('groups'));
    }

    public function create()
    {
        return view('customer_groups.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        CustomerGroup::create([
            'name' => $data['name'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('customer_groups.index')->with('success', 'Customer group created.');
    }

    public function show(CustomerGroup $customerGroup)
    {
        $customerGroup->load('customers');

        return view('customer_groups.show', compact('customerGroup'));
    }

    public function edit(CustomerGroup $customerGroup)
    {
        return view('customer_groups.edit', compact('customerGroup'));
    }

    public function update(Request $request, CustomerGroup $customerGroup)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name,'.$customerGroup->id,
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $customerGroup->update([
            'name' => $data['name'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('customer_groups.index')->with('success', 'Customer group updated.');
    }

    public function destroy(CustomerGroup $customerGroup)
    {
        $customerGroup->delete();

        return redirect()->route('customer_groups.index')->with('success', 'Customer group deleted.');
    }
}
