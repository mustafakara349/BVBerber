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
        // Eğer kullanıcı doğrudan başlangıç veya bitiş tarihi girdiyse, öncelikli olarak özel tarih aralığı uygulanır
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $dateFilter = 'custom';
        } else {
            $dateFilter = $request->input('date_filter', 'monthly');
        }
        
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
                // Tüm zamanlar (tarih kısıtı yok)
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
        $receivables = Debt::receivable()->forBranch($branchId)->active()->with('customer')->get();
        $payables = Debt::payable()->forBranch($branchId)->active()->with('customer')->get();

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

        $transaction->load([
            'createdBy',
            'reference' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Appointment::class => ['customer', 'employee.user'],
                    \App\Models\Debt::class => ['customer'],
                    \App\Models\Expense::class => ['category'],
                ]);
            }
        ]);

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
            'description' => $validated['description'] ?? null,
            'transaction_date' => $validated['transaction_date'],
        ];

        if ($request->hasFile('document_path')) {
            $data['document_path'] = $request->file('document_path')->store('transactions', 'public');
        }

        if (!empty($validated['debt_id'])) {
            $debt = Debt::where('branch_id', $branchId)->findOrFail($validated['debt_id']);

            // Mutlak İş Mantığı Kontrolü:
            // 1) Bizim borcumuz / ödememiz (payable) ise bu işlem MUTLAKA GİDER (- / expense) olmalıdır.
            // 2) Bize olan borç / alacak (receivable) ise bu işlem MUTLAKA GELİR (+ / income) olmalıdır.
            if ($debt->type === 'payable') {
                $data['transaction_type'] = \App\Enums\TransactionType::Expense;
                if (empty($data['category'])) {
                    $data['category'] = 'Borç Ödemesi';
                }
                if (empty($data['description'])) {
                    $data['description'] = 'Ödeme - ' . ($debt->counterparty_name ?? 'Borç Ödemesi');
                }
            } elseif ($debt->type === 'receivable') {
                $data['transaction_type'] = \App\Enums\TransactionType::Income;
                if (empty($data['category'])) {
                    $data['category'] = 'Alacak Tahsilatı';
                }
                if (empty($data['description'])) {
                    $debtorName = $debt->counterparty_name ?? ($debt->customer?->full_name ?? 'Müşteri');
                    $data['description'] = 'Tahsilat - ' . $debtorName;
                }
            }

            $data['reference_type'] = Debt::class;
            $data['reference_id'] = $debt->id;
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

        // Eğer silinen işlem bir borç/alacak kaydı ile ilişkili ise borç bakiyesini geri al
        if ($transaction->reference_type === Debt::class && $transaction->reference_id) {
            $debt = Debt::find($transaction->reference_id);
            if ($debt) {
                $debt->paid_amount = max(0, (float) $debt->paid_amount - (float) $transaction->amount);
                if ($debt->paid_amount <= 0) {
                    $debt->status = 'unpaid';
                } elseif ($debt->paid_amount < $debt->amount) {
                    $debt->status = 'partial';
                } else {
                    $debt->status = 'paid';
                }
                $debt->save();
            }
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
