<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        return view('units.index', ['units' => Unit::latest()->get()]);
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $r)
    {
        Unit::create($r->validate(['name' => 'required|string', 'short_name' => 'required|string|unique:units,short_name']));

        return redirect()->route('units.index')->with('success', 'Saved.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $r, Unit $unit)
    {
        $unit->update($r->validate(['name' => 'required|string', 'short_name' => 'required|string|unique:units,short_name,'.$unit->id]));

        return redirect()->route('units.index')->with('success', 'Updated.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Deleted.');
    }
}
