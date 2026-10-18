<?php

namespace App\Http\Controllers\Web;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Service;
use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Services\AppointmentService;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentRepositoryInterface $appointmentRepo,
        private AppointmentService $appointmentService,
        private DashboardService $dashboardService,
        private \App\Services\Contracts\AppointmentSlotServiceInterface $slotService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Appointment::class);

        $branchId = session('active_branch_id', 1);
        $user = auth()->user();

        $filters = [
            'status' => $request->get('status'),
            'employee_id' => $request->get('employee_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
        ];

        // Berberler yalnızca kendilerine atanmış randevuları görebilir
        if ($user?->role?->slug === 'barber') {
            $filters['employee_id'] = $user->employee?->id;
        }

        $appointments = $this->appointmentRepo->getForBranch($branchId, $filters, $request->get('per_page', 15));

        $employeesQuery = Employee::forBranch($branchId)
            ->active()
            ->whereHas('user', function ($q) {
                // Hardcoded role_id=5 yerine slug tabanlı sorgu kullanılır.
                // Rol ID'si değiştiğinde kırılmaz.
                $q->whereHas('role', fn ($r) => $r->where('slug', 'barber'));
            })
            ->with('user');

        if ($user?->role?->slug === 'barber' && $user->employee) {
            $employeesQuery->where('id', $user->employee->id);
        }

        $employees = $employeesQuery->get();
        $statuses = AppointmentStatus::cases();
        $activeBranch = \App\Models\Branch::find($branchId);

        return view('appointments.index', compact('appointments', 'employees', 'statuses', 'activeBranch'));
    }

    public function events(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $branchId = session('active_branch_id', 1);

        $query = Appointment::forBranch($branchId)
            ->whereBetween('start_at', [$request->start, $request->end])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->with(['customer', 'employee.user', 'appointmentServices.service']);

        // Berber takviminde yalnızca kendi randevuları gösterilir (diğer müşterilerin verileri sızdırılmaz)
        if (auth()->user()?->role?->slug === 'barber') {
            $query->where('employee_id', auth()->user()?->employee?->id);
        }

        $appointments = $query->get();

        $events = $appointments->map(function ($apt) {
            $customerName = $apt->customer->full_name ?? 'Bilinmeyen Müşteri';
            $barberName = $apt->employee->user->full_name ?? '-';
            $servicesStr = $apt->appointmentServices->map(fn($as) => $as->service?->name)->implode(', ');
            
            // Map status to custom colors
            $color = '#3b82f6'; // default blue (confirmed)
            if ($apt->status->value === 'completed') {
                $color = '#10b981'; // green
            } elseif ($apt->status->value === 'pending') {
                $color = '#f59e0b'; // warning orange
            } elseif ($apt->status->value === 'cancelled' || $apt->status->value === 'rejected') {
                $color = '#ef4444'; // red
            } elseif ($apt->status->value === 'no_show') {
                $color = '#6b7280'; // gray
            }

            return [
                'id' => $apt->id,
                'title' => $customerName . ' (' . $barberName . ')',
                'description' => $servicesStr,
                'start' => $apt->start_at->toIso8601String(),
                'end' => $apt->end_at ? $apt->end_at->toIso8601String() : $apt->start_at->copy()->addMinutes($apt->total_duration ?? 30)->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'url' => route('appointments.show', $apt),
            ];
        });

        return response()->json($events);
    }

    public function create(): View
    {
        $this->authorize('create', Appointment::class);

        $branchId = session('active_branch_id', 1);
        $employeesQuery = Employee::forBranch($branchId)->active()->visible()->with('user');
        if (auth()->user()?->role?->slug === 'barber' && auth()->user()?->employee) {
            $employeesQuery->where('id', auth()->user()?->employee->id);
        }
        $employees = $employeesQuery->get();
        $services = Service::forBranch($branchId)->active()->with('category')->get();
        $customers = \App\Models\User::customers()->active()->get();
        $coupons = \App\Models\Coupon::with('users')->get();

        return view('appointments.create', compact('employees', 'services', 'customers', 'coupons'));
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorize('update', $appointment);

        $branchId = session('active_branch_id', 1);
        $employees = Employee::forBranch($branchId)->active()->visible()->with('user')->get();
        $services = Service::forBranch($branchId)->active()->with('category')->get();
        $customers = \App\Models\User::customers()->active()->get();
        $coupons = \App\Models\Coupon::with('users')->get();

        $appointment->load(['appointmentServices.service']);

        // Düzenleme için create view'ını kullan (appointment verisi ile)
        return view('appointments.create', compact('employees', 'services', 'customers', 'coupons', 'appointment'));
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['source'] = 'admin_panel';
        $data['status'] = AppointmentStatus::Confirmed->value;

        // Berber yalnızca kendi adına randevu oluşturabilir
        if (auth()->user()?->role?->slug === 'barber') {
            $data['employee_id'] = auth()->user()?->employee?->id;
        }

        try {
            $this->appointmentService->createAppointment($data);
        } catch (\App\Exceptions\AppointmentConflictException $e) {
            return back()->withErrors(['start_at' => $e->getMessage()])->withInput();
        }

        // Yeni randevu oluşturulduğunda dashboard cache'ini temizle
        $this->dashboardService->flushBranchCache((int) $data['branch_id']);

        return redirect()->route('appointments.index')->with('success', 'Randevu başarıyla oluşturuldu.');
    }

    public function show(Appointment $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load([
            'customer', 'employee.user', 'branch',
            'appointmentServices.service', 'payments',
            'statusLogs.changedBy', 'review',
        ]);

        return view('appointments.show', compact('appointment'));
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('updateStatus', $appointment);

        $request->validate([
            'status' => 'required|string',
            'cancellation_reason' => $request->status === 'rejected' ? 'required|string|min:1' : 'nullable|string',
        ]);

        $status = AppointmentStatus::from($request->status);

        $this->appointmentService->updateStatus(
            $appointment,
            $status,
            auth()->id(),
            $request->get('note'),
            $request->get('cancellation_reason')
        );

        \App\Models\Debt::syncDebtForAppointment($appointment->fresh());

        return back()->with('success', 'Randevu durumu güncellendi.');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:confirmed,rejected',
            'cancellation_reason' => $request->status === 'rejected' ? 'required|string|min:1' : 'nullable|string',
            'appointment_ids' => 'nullable|string',
        ]);

        $status = AppointmentStatus::from($request->status);
        $user = auth()->user();
        $activeBranchId = (int) (session('active_branch_id') ?? $user->employee?->branch_id ?? 1);

        $query = Appointment::query()->forBranch($activeBranchId)->where('status', AppointmentStatus::Pending);

        if ($request->filled('appointment_ids')) {
            $ids = array_filter(array_map('intval', explode(',', (string) $request->appointment_ids)));
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
        }

        if ($user->role?->slug === 'barber' && $user->employee) {
            $query->where('employee_id', $user->employee->id);
        }

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            return back()->with('error', 'İşlem yapılacak bekleyen randevu bulunamadı.');
        }

        $updatedCount = 0;
        foreach ($appointments as $appointment) {
            if ($user->cannot('updateStatus', $appointment)) {
                continue;
            }

            $this->appointmentService->updateStatus(
                $appointment,
                $status,
                $user->id,
                $request->get('note'),
                $request->get('cancellation_reason')
            );

            \App\Models\Debt::syncDebtForAppointment($appointment->fresh());
            $updatedCount++;
        }

        $this->dashboardService->flushBranchCache($activeBranchId);

        $actionText = $status === AppointmentStatus::Confirmed ? 'onaylandı' : 'reddedildi';
        return back()->with('success', "Toplam {$updatedCount} randevu başarıyla {$actionText}.");
    }

    public function availableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'employee_id' => 'required|exists:employees,id',
            'duration' => 'nullable|integer|min:0'
        ]);

        $slots = $this->slotService->getAvailableSlots(
            employeeId: (int) $request->employee_id,
            date: $request->date,
            duration: (int) $request->get('duration', 30),
            branchId: (int) session('active_branch_id', 1)
        );

        return response()->json($slots);
    }

    public function storePayment(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('managePayment', $appointment);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,online',
            'paid_at' => 'required|date',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($appointment, $validated) {
            $paidAtDateTime = \Carbon\Carbon::parse($validated['paid_at']);
            if ($paidAtDateTime->isToday()) {
                $paidAtDateTime->setTimeFrom(now());
            }

            // 1. Create the Payment
            \App\Models\Payment::create([
                'appointment_id' => $appointment->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'],
                'paid_at' => $paidAtDateTime,
            ]);

            // 2. Re-calculate total paid amount
            $totalPaid = $appointment->payments()->sum('amount');

            // 3. Update appointment status & method
            $paymentStatus = \App\Enums\PaymentStatus::Unpaid;
            if ($totalPaid >= $appointment->total_price) {
                $paymentStatus = \App\Enums\PaymentStatus::Paid;
            } elseif ($totalPaid > 0) {
                $paymentStatus = \App\Enums\PaymentStatus::Partial;
            }

            $appointment->update([
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'],
            ]);

            // 4. Automatically create Transaction
            \App\Models\Transaction::create([
                'branch_id' => $appointment->branch_id,
                'created_by' => auth()->id(),
                'transaction_type' => \App\Enums\TransactionType::Income,
                'amount' => $validated['amount'],
                'currency' => 'TRY',
                'payment_method' => $validated['payment_method'],
                'description' => 'Randevu Ödemesi - #' . $appointment->appointment_code . ' (' . ($appointment->customer?->full_name ?? 'Müşteri') . ')',
                'transaction_date' => $paidAtDateTime,
                'reference_type' => \App\Models\Appointment::class,
                'reference_id' => $appointment->id,
            ]);
        });

        \App\Models\Debt::syncDebtForAppointment($appointment->fresh());

        // Ödeme alındığında dashboard kasa verileri güncellenmeli
        $this->dashboardService->flushBranchCache($appointment->branch_id);

        return back()->with('success', 'Ödeme başarıyla kaydedildi.');
    }

    public function destroyPayment(Appointment $appointment, \App\Models\Payment $payment): RedirectResponse
    {
        // 1. Doğrulama: Ödeme kaydının bu randevuya ait olduğundan emin olun (IDOR önleme)
        if ((int) $payment->appointment_id !== (int) $appointment->id) {
            abort(404, 'Ödeme kaydı belirtilen randevuya ait değil.');
        }

        // 2. Yetkilendirme: Yalnızca yetkili roller (yönetici, resepsiyonist) silebilir; berber silemez
        $this->authorize('deletePayment', $appointment);

        \Illuminate\Support\Facades\DB::transaction(function () use ($appointment, $payment) {
            // 1. Delete associated transaction from transactions table
            \App\Models\Transaction::where(function ($q) use ($appointment) {
                $q->where('reference_type', \App\Models\Appointment::class)
                  ->where('reference_id', $appointment->id);
            })
            ->orWhere('description', 'like', '%#' . $appointment->appointment_code . '%')
            ->where('amount', $payment->amount)
            ->where('transaction_type', \App\Enums\TransactionType::Income)
            ->first()
            ?->delete();

            // 2. Delete the payment record itself
            $payment->delete();

            // 3. Re-calculate total paid amount and update appointment
            $totalPaid = $appointment->payments()->sum('amount');
            $paymentStatus = \App\Enums\PaymentStatus::Unpaid;
            if ($totalPaid >= $appointment->total_price) {
                $paymentStatus = \App\Enums\PaymentStatus::Paid;
            } elseif ($totalPaid > 0) {
                $paymentStatus = \App\Enums\PaymentStatus::Partial;
            }

            $appointment->update([
                'payment_status' => $paymentStatus,
            ]);
        });

        \App\Models\Debt::syncDebtForAppointment($appointment->fresh());

        return back()->with('success', 'Ödeme kaydı ve ilişkili kasa işlemi silindi.');
    }

    public function completeWithPayment(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('updateStatus', $appointment);
        $this->authorize('managePayment', $appointment);

        if ($request->filled('amount')) {
            $rawAmount = str_replace(' ', '', (string) $request->amount);
            if (str_contains($rawAmount, ',') && !str_contains($rawAmount, '.')) {
                $rawAmount = str_replace(',', '.', $rawAmount);
            } elseif (str_contains($rawAmount, '.') && str_contains($rawAmount, ',')) {
                $rawAmount = str_replace(',', '.', str_replace('.', '', $rawAmount));
            }
            $request->merge(['amount' => $rawAmount]);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer,online',
            'paid_at' => 'required|date',
            'transaction_reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:250',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($appointment, $validated) {
            $paidAtDateTime = \Carbon\Carbon::parse($validated['paid_at']);
            if ($paidAtDateTime->isToday()) {
                $paidAtDateTime->setTimeFrom(now());
            }

            // 1. Update status to Completed using AppointmentService
            $this->appointmentService->updateStatus(
                $appointment,
                \App\Enums\AppointmentStatus::Completed,
                auth()->id(),
                $validated['note']
            );

            // 2. Create the Payment
            \App\Models\Payment::create([
                'appointment_id' => $appointment->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'],
                'paid_at' => $paidAtDateTime,
            ]);

            // 3. Re-calculate total paid amount
            $totalPaid = $appointment->payments()->sum('amount');

            // 4. Update appointment payment status & method
            $paymentStatus = \App\Enums\PaymentStatus::Unpaid;
            if ($totalPaid >= $appointment->total_price) {
                $paymentStatus = \App\Enums\PaymentStatus::Paid;
            } elseif ($totalPaid > 0) {
                $paymentStatus = \App\Enums\PaymentStatus::Partial;
            }

            $appointment->update([
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'],
            ]);

            // 5. Automatically create Transaction (Ciro/Income)
            \App\Models\Transaction::create([
                'branch_id' => $appointment->branch_id,
                'created_by' => auth()->id(),
                'transaction_type' => \App\Enums\TransactionType::Income,
                'amount' => $validated['amount'],
                'currency' => 'TRY',
                'payment_method' => $validated['payment_method'],
                'description' => 'Randevu Ödemesi - #' . $appointment->appointment_code . ' (' . ($appointment->customer?->full_name ?? 'Müşteri') . ')',
                'transaction_date' => $paidAtDateTime,
                'reference_type' => \App\Models\Appointment::class,
                'reference_id' => $appointment->id,
            ]);
        });

        \App\Models\Debt::syncDebtForAppointment($appointment->fresh());

        return back()->with('success', 'Randevu başarıyla tamamlandı ve ödemesi kaydedildi.');
    }
}
