<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class SerialNumberController extends Controller
{
    public function index()
    {
        return view('serial_numbers.index', ['serials' => SerialNumber::with(['product', 'warehouse'])->latest()->paginate(30)]);
    }

    public function create()
    {
        return view('serial_numbers.create', [
            'products' => Product::where('is_active', true)->get(),
            'warehouses' => Warehouse::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'serial' => 'required|string|unique:serial_numbers,serial',
            'status' => 'required|string',
        ]);
        SerialNumber::create($data);

        return redirect()->route('serial_numbers.index')->with('success', 'Serial recorded.');
    }
}
