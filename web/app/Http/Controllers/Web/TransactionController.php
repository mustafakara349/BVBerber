<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Debt;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $branchId = $this->getActiveBranchId();

        $query = Transaction::forBranch($branchId)
            ->with(['reference', 'createdBy'])
            ->orderBy('transaction_date', 'desc');

        // Date Filter Logic
        $dateFilter = $request->input('date_filter', 'monthly'); // default to monthly
        
        switch ($dateFilter) {
            case 'daily':
                $query->whereDate('transaction_date', Carbon::today());
                break;
            case 'monthly':
                $query->whereMonth('transaction_date', Carbon::now()->month)
                      ->whereYear('transaction_date', Carbon::now()->year);
                break;
            case 'yearly':
                $query->whereYear('transaction_date', Carbon::now()->year);
                break;
            case 'custom':
                if ($request->filled('start_date')) {
                    $query->whereDate('transaction_date', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $query->whereDate('transaction_date', '<=', $request->end_date);
                }
                break;
            case 'all':
                // no date filter
                break;
        }

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Paginate results
        $transactions = $query->paginate(15)->withQueryString();

        // Calculate summary cards
        $summaryQuery = clone $query;
        // removing pagination limits for sum calculation isn't strictly necessary since we cloned the pre-paginated query, 
        // but we need to remove any applied offset/limit if present (clone is safe).
        $totalIncome = (clone $summaryQuery)->income()->sum('amount');
        $totalExpense = (clone $summaryQuery)->expense()->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        // Open debts/receivables for dynamic dropdown in create modal
        $receivables = Debt::receivable()->forBranch($branchId)->active()->get();
        $payables = Debt::payable()->forBranch($branchId)->active()->get();

        return view('finance.transactions.index', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'netBalance',
            'dateFilter',
            'receivables',
            'payables'
        ));
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->branch_id !== $this->getActiveBranchId()) {
            abort(403);
        }

        $transaction->load(['reference', 'createdBy']);
        return view('finance.transactions.show', compact('transaction'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|string|in:income,expense,refund',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,online',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:255',
            'document_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'debt_id' => 'nullable|exists:debts,id' // If tied to a debt payment
        ]);

        $branchId = $this->getActiveBranchId();

        $data = [
            'branch_id' => $branchId,
            'created_by' => Auth::id(),
            'transaction_type' => $validated['transaction_type'],
            'category' => $validated['category'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'TRY',
            'payment_method' => $validated['payment_method'],
            'description' => $validated['description'],
            'transaction_date' => $validated['transaction_date'],
        ];

        if ($request->hasFile('document_path')) {
            $data['document_path'] = $request->file('document_path')->store('transactions', 'public');
        }

        if (!empty($validated['debt_id'])) {
            $data['reference_type'] = Debt::class;
            $data['reference_id'] = $validated['debt_id'];
        }

        try {
            $this->transactionService->createTransaction($data);
            return redirect()->route('finance.transactions')->with('success', 'Finansal işlem başarıyla eklendi.');
        } catch (\Exception $e) {
            return back()->with('error', 'İşlem eklenirken hata oluştu: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Transaction $transaction)
    {
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
