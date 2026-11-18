<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('expenses.index', ['expenses' => Expense::with('category')->latest()->paginate(20), 'categories' => ExpenseCategory::all()]);
    }

    public function create()
    {
        return view('expenses.create', ['categories' => ExpenseCategory::all()]);
    }

    public function store(Request $r)
    {
        Expense::create($r->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'expense_date' => 'required|date',
        ]) + ['reference' => 'EXP-'.now()->format('YmdHis'), 'user_id' => auth()->id(), 'status' => 'approved']);

        return redirect()->route('expenses.index')->with('success', 'Saved.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', ['expense' => $expense, 'categories' => ExpenseCategory::all()]);
    }

    public function update(Request $r, Expense $expense)
    {
        $expense->update($r->validate([
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'expense_date' => 'required|date',
        ]));

        return redirect()->route('expenses.index')->with('success', 'Updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Deleted.');
    }
}
