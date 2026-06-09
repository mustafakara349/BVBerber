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
    public function index(Request $request)
    {
        $branchId = $this->getActiveBranchId();

        $query = Debt::forBranch($branchId)
            ->with(['customer', 'appointment'])
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            // By default, display unpaid & partial (active) debts
            if (!$request->filled('status')) {
                $query->whereIn('status', ['unpaid', 'partial']);
            }
        }

        // Apply type filter
        if ($request->filled('type') && $request->type !== 'all') {
            if ($request->type === 'appointment') {
                $query->whereNotNull('appointment_id');
            } elseif ($request->type === 'manual') {
                $query->whereNull('appointment_id');
            }
        }

        // Apply search filter (customer name, customer phone, appointment code, description)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($c) use ($search) {
                    $c->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('appointment', function ($a) use ($search) {
                    $a->where('appointment_code', 'like', "%{$search}%");
                })->orWhere('description', 'like', "%{$search}%");
            });
        }

        $debts = $query->paginate(15)->withQueryString();

        // Calculate summary cards based on the branch and filters (excluding pagination)
        $summaryQuery = Debt::forBranch($branchId);
        if ($request->filled('type') && $request->type !== 'all') {
            if ($request->type === 'appointment') {
                $summaryQuery->whereNotNull('appointment_id');
            } elseif ($request->type === 'manual') {
                $summaryQuery->whereNull('appointment_id');
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $summaryQuery->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($c) use ($search) {
                    $c->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('appointment', function ($a) use ($search) {
                    $a->where('appointment_code', 'like', "%{$search}%");
                })->orWhere('description', 'like', "%{$search}%");
            });
        }

        // We only sum active or currently filtered status for card statistics
        $statsQuery = clone $summaryQuery;
        if ($request->filled('status') && $request->status !== 'all') {
            $statsQuery->where('status', $request->status);
        } else {
            if (!$request->filled('status')) {
                $statsQuery->whereIn('status', ['unpaid', 'partial']);
            }
        }

        $totalDebt = (float) $statsQuery->sum('amount');
        $totalPaidOnActive = (float) $statsQuery->sum('paid_amount');
        $remainingDebt = $totalDebt - $totalPaidOnActive;

        // Calculate collected debt payments today
        $todayPaid = (float) Transaction::forBranch($branchId)
            ->income()
            ->where('description', 'like', 'Borç Tahsilatı%')
            ->whereDate('transaction_date', today())
            ->sum('amount');

        // Get customers for the manual debt modal (must be active role 'customer')
        $customers = User::customers()->active()->orderBy('first_name')->get();

        return view('finance.debts.index', compact(
            'debts',
            'totalDebt',
            'todayPaid',
            'remainingDebt',
            'customers'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        $customer = User::customers()->findOrFail($validated['customer_id']);
        $branchId = $this->getActiveBranchId();

        Debt::create([
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'amount' => $validated['amount'],
            'paid_amount' => 0.00,
            'description' => $validated['description'] ?? 'Manuel Borç',
            'due_date' => $validated['due_date'],
            'status' => 'unpaid',
        ]);

        return redirect()->route('finance.debts.index')
            ->with('success', 'Borç kaydı başarıyla eklendi.');
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
            return back()->with('error', 'Ödeme tutarı kalan borç tutarından fazla olamaz. Kalan borç: ₺' . number_format($remaining, 2, ',', '.'));
        }

        DB::transaction(function () use ($debt, $validated) {
            $paidAtDateTime = Carbon::parse($validated['paid_at']);
            if ($paidAtDateTime->isToday()) {
                $paidAtDateTime->setTimeFrom(now());
            }

            // Update Debt record paid amount
            $newPaidAmount = (float) $debt->paid_amount + (float) $validated['amount'];
            $debtStatus = 'partial';

            if ($newPaidAmount >= (float) $debt->amount - 0.005) {
                $debtStatus = 'paid';
                $newPaidAmount = $debt->amount; // Make it exact
            }

            $debt->update([
                'paid_amount' => $newPaidAmount,
                'status' => $debtStatus,
            ]);

            // Register dynamic Transaction in finance ledger
            $typeLabel = $debt->appointment ? '#' . $debt->appointment->appointment_code : 'Manuel Borç';
            $description = 'Borç Tahsilatı - ' . ($debt->customer?->full_name ?? 'Müşteri') . ' (' . $typeLabel . ')';

            Transaction::create([
                'branch_id' => $debt->branch_id,
                'created_by' => Auth::id(),
                'transaction_type' => TransactionType::Income,
                'amount' => $validated['amount'],
                'currency' => 'TRY',
                'payment_method' => $validated['payment_method'],
                'description' => $description,
                'transaction_date' => $paidAtDateTime,
                'appointment_id' => $debt->appointment_id,
            ]);

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

        return redirect()->route('finance.debts.index')
            ->with('success', 'Ödeme başarıyla tahsil edildi.');
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

        return redirect()->route('finance.debts.index')
            ->with('success', 'Borç kaydı başarıyla silindi.');
    }

    private function getActiveBranchId(): int
    {
        return session('active_branch_id', 1);
    }
}
