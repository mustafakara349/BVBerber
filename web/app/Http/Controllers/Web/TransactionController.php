<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Appointment;
use App\Enums\TransactionType;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $this->getActiveBranchId();

        $query = Transaction::forBranch($branchId)
            ->with(['appointment.customer', 'createdBy'])
            ->orderBy('transaction_date', 'desc');

        // Apply filters
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Paginate results
        $transactions = $query->paginate(15)->withQueryString();

        // Calculate summary cards (scoped to active filters if present, or all-time/active month)
        $summaryQuery = Transaction::forBranch($branchId);
        if ($request->filled('start_date')) {
            $summaryQuery->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $summaryQuery->whereDate('transaction_date', '<=', $request->end_date);
        }

        $totalIncome = (clone $summaryQuery)->income()->sum('amount');
        $totalExpense = (clone $summaryQuery)->expense()->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        return view('finance.transactions', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'netBalance'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|string|in:income,expense,refund',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,online',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $branchId = $this->getActiveBranchId();

        Transaction::create([
            'branch_id' => $branchId,
            'created_by' => Auth::id(),
            'transaction_type' => $validated['transaction_type'],
            'amount' => $validated['amount'],
            'currency' => 'TRY',
            'payment_method' => $validated['payment_method'],
            'description' => $validated['description'],
            'transaction_date' => $validated['transaction_date'],
        ]);

        return redirect()->route('finance.transactions')
            ->with('success', 'Finansal işlem başarıyla eklendi.');
    }

    public function destroy(Transaction $transaction)
    {
        // Prevent deletion of transactions that belong to another branch
        if ($transaction->branch_id !== $this->getActiveBranchId()) {
            abort(403, 'Yetkisiz işlem.');
        }

        $transaction->delete();

        return redirect()->route('finance.transactions')
            ->with('success', 'Finansal işlem başarıyla silindi.');
    }

    private function getActiveBranchId(): int
    {
        return session('active_branch_id', 1);
    }
}
