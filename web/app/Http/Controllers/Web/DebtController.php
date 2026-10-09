<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Payment;
use App\Enums\TransactionType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DebtController extends Controller
{
    public function receivables(Request $request)
    {
        $branchId = $this->getActiveBranchId();

        $query = Debt::receivable()->forBranch($branchId)
            ->selectRaw('
                customer_id,
                counterparty_name,
                COUNT(id) as debt_count,
                SUM(amount) as total_amount,
                SUM(paid_amount) as total_paid,
                SUM(amount - paid_amount) as remaining_amount,
                MIN(due_date) as nearest_due_date,
                MAX(created_at) as last_debt_date
            ')
            ->with('customer')
            ->groupBy('customer_id', 'counterparty_name')
            ->orderByRaw('SUM(amount - paid_amount) DESC');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else if (!$request->filled('status')) {
            $query->whereIn('status', ['unpaid', 'partial']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($c) use ($search) {
                    $c->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('appointment', function ($a) use ($search) {
                    $a->where('appointment_code', 'like', "%{$search}%");
                })->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%");
            });
        }

        $debts = $query->paginate(15)->withQueryString();

        $statsQuery = Debt::receivable()->forBranch($branchId);
        if ($request->filled('status') && $request->status !== 'all') {
            $statsQuery->where('status', $request->status);
        } else if (!$request->filled('status')) {
            $statsQuery->whereIn('status', ['unpaid', 'partial']);
        }

        $totalDebt = (float) $statsQuery->sum('amount');
        $totalPaidOnActive = (float) $statsQuery->sum('paid_amount');
        $remainingDebt = $totalDebt - $totalPaidOnActive;

        $customers = User::customers()->active()->orderBy('first_name')->get();

        return view('finance.debts.receivables', compact(
            'debts', 'totalDebt', 'remainingDebt', 'customers'
        ));
    }

    public function payables(Request $request)
    {
        $branchId = $this->getActiveBranchId();

        $query = Debt::payable()->forBranch($branchId)
            ->selectRaw('
                customer_id,
                counterparty_name,
                COUNT(id) as debt_count,
                SUM(amount) as total_amount,
                SUM(paid_amount) as total_paid,
                SUM(amount - paid_amount) as remaining_amount,
                MIN(due_date) as nearest_due_date,
                MAX(created_at) as last_debt_date
            ')
            ->with('customer')
            ->groupBy('customer_id', 'counterparty_name')
            ->orderByRaw('SUM(amount - paid_amount) DESC');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else if (!$request->filled('status')) {
            $query->whereIn('status', ['unpaid', 'partial']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('counterparty_name', 'like', "%{$search}%");
            });
        }

        $debts = $query->paginate(15)->withQueryString();

        $statsQuery = Debt::payable()->forBranch($branchId);
        if ($request->filled('status') && $request->status !== 'all') {
            $statsQuery->where('status', $request->status);
        } else if (!$request->filled('status')) {
            $statsQuery->whereIn('status', ['unpaid', 'partial']);
        }

        $totalDebt = (float) $statsQuery->sum('amount');
        $totalPaidOnActive = (float) $statsQuery->sum('paid_amount');
        $remainingDebt = $totalDebt - $totalPaidOnActive;

        return view('finance.debts.payables', compact(
            'debts', 'totalDebt', 'remainingDebt'
        ));
    }

    public function details(Request $request)
    {
        $branchId = $this->getActiveBranchId();
        
        $type = $request->query('type', 'receivable');
        $customerId = $request->query('customer_id');
        $counterpartyName = $request->query('counterparty_name');

        if (!$customerId && !$counterpartyName) {
            return redirect()->back()->with('error', 'Geçersiz borçlu bilgisi.');
        }

        $query = Debt::where('type', $type)->forBranch($branchId)
                     ->with(['customer', 'appointment.payments', 'transactions'])
                     ->orderBy('created_at', 'desc');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } else {
            $query->where('counterparty_name', $counterpartyName)
                  ->whereNull('customer_id');
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $debts = $query->paginate(15)->withQueryString();

        // Calculate totals for this specific person
        $statsQuery = clone $query;
        $totalDebt = (float) $statsQuery->sum('amount');
        $totalPaid = (float) $statsQuery->sum('paid_amount');
        $remainingDebt = $totalDebt - $totalPaid;

        return view('finance.debts.details', compact(
            'debts', 'type', 'customerId', 'counterpartyName', 'totalDebt', 'totalPaid', 'remainingDebt'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:receivable,payable',
            'customer_id' => 'nullable|exists:users,id',
            'counterparty_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        $branchId = $this->getActiveBranchId();

        Debt::create([
            'branch_id' => $branchId,
            'type' => $validated['type'],
            'customer_id' => $validated['customer_id'] ?? null,
            'counterparty_name' => $validated['counterparty_name'] ?? null,
            'amount' => $validated['amount'],
            'paid_amount' => 0.00,
            'description' => $validated['description'] ?? 'Manuel Kayıt',
            'due_date' => $validated['due_date'],
            'status' => 'unpaid',
        ]);

        $route = $validated['type'] === 'receivable' ? 'finance.receivables.index' : 'finance.payables.index';
        return redirect()->route($route)->with('success', 'Kayıt başarıyla eklendi.');
    }

    public function pay(Request $request, Debt $debt)
    {
        if ($debt->branch_id !== $this->getActiveBranchId()) {
            abort(403, 'Yetkisiz işlem.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,online',
            'paid_at' => 'required|date',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        $remaining = $debt->remaining_amount;

        if ($validated['amount'] > $remaining + 0.01) {
            return back()->with('error', 'İşlem tutarı kalan tutardan fazla olamaz. Kalan: ₺' . number_format($remaining, 2, ',', '.'));
        }

        DB::transaction(function () use ($debt, $validated) {
            $paidAtDateTime = Carbon::parse($validated['paid_at']);
            if ($paidAtDateTime->isToday()) {
                $paidAtDateTime->setTimeFrom(now());
            }

            // Create Transaction via TransactionService would be better, but we can do it inline or call service.
            // Since we created TransactionService, let's use it.
            $data = [
                'branch_id' => $debt->branch_id,
                'created_by' => Auth::id(),
                'transaction_type' => $debt->type === 'receivable' ? TransactionType::Income->value : TransactionType::Expense->value,
                'category' => $debt->type === 'receivable' ? 'Alacak Tahsilatı' : 'Borç Ödemesi',
                'amount' => $validated['amount'],
                'currency' => 'TRY',
                'payment_method' => $validated['payment_method'],
                'description' => ($debt->type === 'receivable' ? 'Tahsilat - ' : 'Ödeme - ') . ($debt->counterparty_name ?? $debt->customer?->full_name ?? 'Bilinmeyen'),
                'transaction_date' => $paidAtDateTime,
                'reference_type' => Debt::class,
                'reference_id' => $debt->id,
            ];

            $transactionService = app(\App\Services\TransactionService::class);
            $transactionService->createTransaction($data);

            // If it is linked to an appointment, record a payment on the appointment
            if ($debt->appointment_id) {
                Payment::create([
                    'appointment_id' => $debt->appointment_id,
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'transaction_reference' => $validated['transaction_reference'],
                    'paid_at' => $paidAtDateTime,
                ]);

                // Update appointment payment_status
                $appointment = $debt->appointment;
                $totalPaid = $appointment->payments()->sum('amount');
                
                $appointmentPaymentStatus = PaymentStatus::Unpaid;
                if ($totalPaid >= $appointment->total_price) {
                    $appointmentPaymentStatus = PaymentStatus::Paid;
                } elseif ($totalPaid > 0) {
                    $appointmentPaymentStatus = PaymentStatus::Partial;
                }

                $appointment->update([
                    'payment_status' => $appointmentPaymentStatus,
                    'payment_method' => $validated['payment_method'],
                ]);
            }
        });

        return back()->with('success', 'İşlem başarıyla kaydedildi.');
    }

    public function destroy(Debt $debt)
    {
        if ($debt->branch_id !== $this->getActiveBranchId()) {
            abort(403, 'Yetkisiz işlem.');
        }

        // Randevu borçları doğrudan silinemez
        if ($debt->appointment_id !== null) {
            return back()->with('error', 'Randevu borçları doğrudan silinemez. Randevu durumunu veya ödemelerini güncelleyerek işlem yapın.');
        }

        // Kısmen ödenmiş borçlar silinemez; kasada ilişkili Transaction kayıtları mevcuttur.
        // Silme işlemi finansal tutarsızlığa (orphan Transaction) yol açar.
        if ((float) $debt->paid_amount > 0) {
            return back()->with('error', 'Kısmen ödenmiş borç kaydı silinemez. Toplam: ₺' . number_format($debt->amount, 2, ',', '.') . ' / Ödenen: ₺' . number_format($debt->paid_amount, 2, ',', '.') . '. Lütfen yetkili ile iletişime geçin.');
        }

        // paid_amount = 0 olan manuel borçlar için kasada hiçbir tahsilat izi yoktur;
        // güvenle silebiliriz.
        $debt->delete();

        return back()->with('success', 'Kayıt başarıyla silindi.');
    }

    private function getActiveBranchId(): int
    {
        return session('active_branch_id', 1);
    }
}
