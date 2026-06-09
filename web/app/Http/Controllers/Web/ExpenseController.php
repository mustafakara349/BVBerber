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

        $query = Expense::forBranch($branchId)
            ->with(['category', 'createdBy'])
            ->orderBy('expense_date', 'desc');

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        // Paginate results
        $expenses = $query->paginate(15)->withQueryString();

        // Get categories for selection and category management
        $categories = ExpenseCategory::where('branch_id', $branchId)->orWhereNull('branch_id')->get();

        // Calculate summary cards
        $summaryQuery = Expense::forBranch($branchId);
        if ($request->filled('start_date')) {
            $summaryQuery->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $summaryQuery->whereDate('expense_date', '<=', $request->end_date);
        }

        $totalExpenseThisMonth = (clone $summaryQuery)->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');
        $totalExpenseAllTime = (clone $summaryQuery)->sum('amount');
        $expenseCount = $summaryQuery->count();

        // Get top spending category
        $topCategory = DB::table('expenses')
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->where('expenses.branch_id', $branchId)
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total_amount'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total_amount')
            ->first();

        $topCategoryName = $topCategory ? $topCategory->name : 'Yok';
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
            'category_id'    => 'required|exists:expense_categories,id',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'description'    => 'nullable|string|max:500',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer',
            'receipt_file'   => 'nullable|file|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $branchId = $this->getActiveBranchId();

        // Kategorinin aktif şubeye ait olduğunu doğrula (şube izolasyonu)
        $category = ExpenseCategory::where('id', $validated['category_id'])
            ->where('branch_id', $branchId)
            ->first();

        if (! $category) {
            abort(403, 'Bu kategoriye erişim yetkiniz bulunmamaktadır.');
        }

        $receiptPath = null;

        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('receipts', 'public');
        }

        DB::transaction(function () use ($validated, $branchId, $receiptPath, $category) {
            // 1. Create the Expense
            $expense = Expense::create([
                'branch_id'    => $branchId,
                'category_id'  => $validated['category_id'],
                'created_by'   => Auth::id(),
                'amount'       => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'description'  => $validated['description'],
                'receipt_file' => $receiptPath,
            ]);

            // 2. Automatically create the corresponding Transaction for kasa balance integrity!
            Transaction::create([
                'branch_id'        => $branchId,
                'created_by'       => Auth::id(),
                'transaction_type' => TransactionType::Expense,
                'amount'           => $validated['amount'],
                'currency'         => 'TRY',
                'payment_method'   => $validated['payment_method'],
                'description'      => 'Gider Harcaması - ' . $category->name . ($validated['description'] ? ' (' . $validated['description'] . ')' : ''),
                'transaction_date' => $validated['expense_date'],
                'expense_id'       => $expense->id,
            ]);
        });

        return redirect()->route('finance.expenses')
            ->with('success', 'Gider harcaması ve ilgili kasa işlemi başarıyla eklendi.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->branch_id !== $this->getActiveBranchId()) {
            abort(403, 'Yetkisiz işlem.');
        }

        DB::transaction(function () use ($expense) {
            // Delete receipt file if it exists
            if ($expense->receipt_file) {
                Storage::disk('public')->delete($expense->receipt_file);
            }

            // Delete the linked transaction directly using the relation!
            $expense->transaction?->delete();

            $expense->delete();
        });

        return redirect()->route('finance.expenses')
            ->with('success', 'Gider kaydı ve ilgili kasa işlemi başarıyla silindi.');
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
