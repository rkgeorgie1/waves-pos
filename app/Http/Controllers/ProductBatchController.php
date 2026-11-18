<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ProductBatchController extends Controller
{
    public function index()
    {
        return view('product_batches.index', ['batches' => ProductBatch::with(['product', 'warehouse'])->latest()->paginate(30)]);
    }

    public function create()
    {
        return view('product_batches.create', [
            'products' => Product::where('is_active', true)->get(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'batch_number' => 'required|string',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
        ]);
        ProductBatch::create($data);

        return redirect()->route('product_batches.index')->with('success', 'Batch recorded.');
    }
}
