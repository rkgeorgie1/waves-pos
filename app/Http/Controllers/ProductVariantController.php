<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index()
    {
        return view('product_variants.index', ['variants' => ProductVariant::with('product')->latest()->paginate(30)]);
    }

    public function create()
    {
        return view('product_variants.create', ['products' => Product::where('is_active', true)->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string',
            'sku' => 'nullable|string',
            'barcode' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        ProductVariant::create($data);

        return redirect()->route('product_variants.index')->with('success', 'Variant created.');
    }
}
