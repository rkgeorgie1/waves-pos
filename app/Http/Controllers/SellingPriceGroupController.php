<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SellingPriceGroup;
use Illuminate\Http\Request;

class SellingPriceGroupController extends Controller
{
    public function index()
    {
        return view('selling_price_groups.index', ['groups' => SellingPriceGroup::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('selling_price_groups.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string', 'description' => 'nullable|string']);
        $data['is_active'] = true;
        SellingPriceGroup::create($data);

        return redirect()->route('selling_price_groups.index')->with('success', 'Group created.');
    }

    public function show(SellingPriceGroup $sellingPriceGroup)
    {
        $sellingPriceGroup->load('products');
        $products = Product::where('is_active', true)->get();

        return view('selling_price_groups.show', compact('sellingPriceGroup', 'products'));
    }

    public function assign(Request $request, SellingPriceGroup $sellingPriceGroup)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price' => 'required|numeric|min:0',
        ]);
        $sellingPriceGroup->products()->syncWithoutDetaching([$data['product_id'] => ['price' => $data['price']]]);

        return back()->with('success', 'Price assigned.');
    }
}
