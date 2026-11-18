<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashShiftController extends Controller
{
    public function index()
    {
        return view('cash_shifts.index', [
            'shifts' => CashShift::with(['cashRegister', 'user'])->latest()->paginate(20),
            'registers' => CashRegister::where('is_active', true)->get(),
        ]);
    }

    public function open(Request $request)
    {
        $data = $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $existing = CashShift::where('user_id', auth()->id())->where('status', 'open')->first();
        if ($existing) {
            return back()->with('error', 'You already have an open shift.');
        }

        $shift = CashShift::create([
            'cash_register_id' => $data['cash_register_id'],
            'user_id' => auth()->id(),
            'opening_balance' => $data['opening_balance'],
            'cash_received' => 0,
            'cash_paid_out' => 0,
            'expected_closing' => $data['opening_balance'],
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return redirect()->route('cash_shifts.show', $shift)->with('success', 'Shift opened.');
    }

    public function show(CashShift $cashShift)
    {
        // compute live cash received/paid out
        $received = Payment::where('user_id', auth()->id())
            ->where('method', 'cash')
            ->where('paid_at', '>=', $cashShift->opened_at)
            ->sum('amount');
        $paidOut = Expense::where('user_id', auth()->id())
            ->where('payment_method', 'cash')
            ->where('created_at', '>=', $cashShift->opened_at)
            ->sum('amount');

        $cashShift->cash_received = $received;
        $cashShift->cash_paid_out = $paidOut;
        $cashShift->expected_closing = $cashShift->opening_balance + $received - $paidOut;

        return view('cash_shifts.show', compact('cashShift'));
    }

    public function close(Request $request, CashShift $cashShift)
    {
        $data = $request->validate([
            'actual_closing' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($cashShift, $data) {
            $received = Payment::where('user_id', $cashShift->user_id)
                ->where('method', 'cash')
                ->where('paid_at', '>=', $cashShift->opened_at)
                ->sum('amount');
            $paidOut = Expense::where('user_id', $cashShift->user_id)
                ->where('payment_method', 'cash')
                ->where('created_at', '>=', $cashShift->opened_at)
                ->sum('amount');
            $expected = $cashShift->opening_balance + $received - $paidOut;

            $cashShift->cash_received = $received;
            $cashShift->cash_paid_out = $paidOut;
            $cashShift->expected_closing = $expected;
            $cashShift->actual_closing = $data['actual_closing'];
            $cashShift->difference = $data['actual_closing'] - $expected;
            $cashShift->status = 'closed';
            $cashShift->closed_at = now();
            $cashShift->save();
        });

        return redirect()->route('cash_shifts.show', $cashShift)->with('success', 'Shift closed.');
    }
}
