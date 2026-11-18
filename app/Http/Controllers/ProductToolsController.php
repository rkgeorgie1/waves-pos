<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductToolsController extends Controller
{
    public function updatePrice()
    {
        $products = Product::where('is_active', true)->get();

        return view('products.update_price', compact('products'));
    }

    public function updatePriceStore(Request $request)
    {
        $data = $request->validate([
            'prices' => 'required|array',
            'prices.*.id' => 'required|exists:products,id',
            'prices.*.retail_price' => 'nullable|numeric|min:0',
            'prices.*.wholesale_price' => 'nullable|numeric|min:0',
            'prices.*.purchase_price' => 'nullable|numeric|min:0',
        ]);
        foreach ($data['prices'] as $p) {
            Product::where('id', $p['id'])->update([
                'retail_price' => $p['retail_price'] ?? null,
                'wholesale_price' => $p['wholesale_price'] ?? null,
                'purchase_price' => $p['purchase_price'] ?? null,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Prices updated.');
    }

    public function import()
    {
        return view('products.import');
    }

    public function importStore(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (empty($data['name'])) {
                continue;
            }
            Product::create([
                'name' => $data['name'],
                'sku' => $data['sku'] ?? 'SKU-'.uniqid(),
                'barcode' => $data['barcode'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? 0,
                'retail_price' => $data['retail_price'] ?? 0,
                'wholesale_price' => $data['wholesale_price'] ?? 0,
                'is_active' => true,
            ]);
            $count++;
        }
        fclose($handle);

        return redirect()->route('products.index')->with('success', "Imported {$count} products.");
    }

    public function printLabels(Request $request)
    {
        $ids = $request->get('ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $products = Product::whereIn('id', $ids)->get();
        if ($products->isEmpty()) {
            $products = Product::where('is_active', true)->limit(20)->get();
        }

        return view('products.print_labels', compact('products'));
    }
}
