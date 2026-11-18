<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        return view('brands.index', ['brands' => Brand::latest()->get()]);
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $r)
    {
        Brand::create($r->validate(['name' => 'required|string|unique:brands,name']));

        return redirect()->route('brands.index')->with('success', 'Saved.');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $r, Brand $brand)
    {
        $brand->update($r->validate(['name' => 'required|string|unique:brands,name,'.$brand->id]));

        return redirect()->route('brands.index')->with('success', 'Updated.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Deleted.');
    }
}
