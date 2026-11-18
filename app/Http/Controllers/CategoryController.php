<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index', ['categories' => Category::latest()->get()]);
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $r)
    {
        $d = $r->validate(['name' => 'required|string', 'description' => 'nullable|string']);
        $d['slug'] = Str::slug($d['name']);
        Category::create($d);

        return redirect()->route('categories.index')->with('success', 'Saved.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $r, Category $category)
    {
        $d = $r->validate(['name' => 'required|string', 'description' => 'nullable|string']);
        $d['slug'] = Str::slug($d['name']);
        $category->update($d);

        return redirect()->route('categories.index')->with('success', 'Updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Deleted.');
    }
}
