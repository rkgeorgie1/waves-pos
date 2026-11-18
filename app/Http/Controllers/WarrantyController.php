<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    public function index()
    {
        return view('warranties.index', ['warranties' => Warranty::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('warranties.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'duration' => 'required|integer|min:0',
            'duration_type' => 'required|string',
            'description' => 'nullable|string',
        ]);
        $data['is_active'] = true;
        Warranty::create($data);

        return redirect()->route('warranties.index')->with('success', 'Warranty created.');
    }
}
