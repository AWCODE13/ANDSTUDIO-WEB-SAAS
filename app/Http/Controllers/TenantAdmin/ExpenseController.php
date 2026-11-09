<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $request->get('month', now()->format('Y-m'));
        
        // Menggunakan kolom 'expense_date' sesuai migrasi Anda
        $expenses = Expense::where('expense_date', 'like', $selectedMonth . '%')
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalExpense = $expenses->sum('amount');

        return view('tenant.expenses.index', compact('expenses', 'selectedMonth', 'totalExpense'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category'     => 'required|string|max:50',
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        // Tambahkan ID user yang sedang login ke dalam data yang akan disimpan
        $validated['created_by'] = Auth::id();

        Expense::create($validated);

        return back()->with('success', 'Catatan pengeluaran berhasil ditambahkan!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Catatan pengeluaran berhasil dihapus!');
    }
}