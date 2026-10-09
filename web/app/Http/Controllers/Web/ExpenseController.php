<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $this->getActiveBranchId();

        $query = Transaction::expense()->forBranch($branchId)
            ->with(['createdBy'])
            ->orderBy('transaction_date', 'desc');

        // Apply filters
        if ($request->filled('category_name')) {
            $query->where('category', $request->category_name);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Paginate results
        $expenses = $query->paginate(15)->withQueryString();

        // Get categories for selection and category management
        $categories = ExpenseCategory::where('branch_id', $branchId)->orWhereNull('branch_id')->get();

        // Calculate summary cards
        $summaryQuery = Transaction::expense()->forBranch($branchId);
        if ($request->filled('start_date')) {
            $summaryQuery->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $summaryQuery->whereDate('transaction_date', '<=', $request->end_date);
        }

        $totalExpenseThisMonth = (clone $summaryQuery)->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('amount');
        $totalExpenseAllTime = (clone $summaryQuery)->sum('amount');
        $expenseCount = $summaryQuery->count();

        // Get top spending category
        $topCategory = DB::table('transactions')
            ->where('branch_id', $branchId)
            ->where('transaction_type', TransactionType::Expense->value)
            ->whereNotNull('category')
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->first();

        $topCategoryName = $topCategory ? $topCategory->category : 'Yok';
        $topCategoryAmount = $topCategory ? $topCategory->total_amount : 0;

        return view('finance.expenses', compact(
            'expenses',
            'categories',
            'totalExpenseThisMonth',
            'totalExpenseAllTime',
            'expenseCount',
            'topCategoryName',
            'topCategoryAmount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name'  => 'required|string',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'description'    => 'nullable|string|max:500',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer',
            'receipt_file'   => 'nullable|file|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $branchId = $this->getActiveBranchId();

        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('receipts', 'public');
        }

        Transaction::create([
            'branch_id'        => $branchId,
            'created_by'       => Auth::id(),
            'transaction_type' => TransactionType::Expense,
            'category'         => $validated['category_name'],
            'amount'           => $validated['amount'],
            'currency'         => 'TRY',
            'payment_method'   => $validated['payment_method'],
            'description'      => $validated['description'],
            'transaction_date' => $validated['expense_date'],
            'document_path'    => $receiptPath,
        ]);

        return redirect()->route('finance.expenses')
            ->with('success', 'Gider harcaması başarıyla kaydedildi.');
    }

    public function destroy(Transaction $expense)
    {
        if ($expense->branch_id !== $this->getActiveBranchId()) {
            abort(403, 'Yetkisiz işlem.');
        }

        if ($expense->document_path) {
            Storage::disk('public')->delete($expense->document_path);
        }

        $expense->delete();

        return redirect()->route('finance.expenses')
            ->with('success', 'Gider kaydı başarıyla silindi.');
    }

    // Quick creation of Expense Categories
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:250',
        ]);

        $branchId = $this->getActiveBranchId();

        // Check if category name already exists for this branch
        $exists = ExpenseCategory::where('branch_id', $branchId)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['name' => 'Bu gider kategorisi zaten mevcut.']);
        }

        ExpenseCategory::create([
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('finance.expenses')
            ->with('success', 'Yeni gider kategorisi başarıyla oluşturuldu.');
    }

    private function getActiveBranchId(): int
    {
        return session('active_branch_id', 1);
    }
}
