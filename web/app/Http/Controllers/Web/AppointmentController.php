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
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request): View
    {
        $branchId = session('active_branch_id', 1);

        $appointments = $this->appointmentRepo->getForBranch($branchId, [
            'status' => $request->get('status'),
            'employee_id' => $request->get('employee_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
        ], $request->get('per_page', 15));

        $employees = Employee::forBranch($branchId)
            ->active()
            ->whereHas('user', function ($q) {
                // Hardcoded role_id=5 yerine slug tabanlı sorgu kullanılır.
                // Rol ID'si değiştiğinde kırılmaz.
                $q->whereHas('role', fn ($r) => $r->where('slug', 'barber'));
            })
            ->with('user')
            ->get();
        $statuses = AppointmentStatus::cases();
        $activeBranch = \App\Models\Branch::find($branchId);

        return view('appointments.index', compact('appointments', 'employees', 'statuses', 'activeBranch'));
    }

    public function events(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $branchId = session('active_branch_id', 1);

        $appointments = Appointment::forBranch($branchId)
            ->whereBetween('start_at', [$request->start, $request->end])
            ->with(['customer', 'employee.user', 'appointmentServices.service'])
            ->get();

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
        $branchId = session('active_branch_id', 1);
        $employees = Employee::forBranch($branchId)->active()->visible()->with('user')->get();
        $services = Service::forBranch($branchId)->active()->get();
        $customers = \App\Models\User::customers()->active()->get();

        return view('appointments.create', compact('employees', 'services', 'customers'));
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['source'] = 'admin_panel';

        $this->appointmentService->createAppointment($data);

        // Yeni randevu oluşturulduğunda dashboard cache'ini temizle
        $this->dashboardService->flushBranchCache((int) $data['branch_id']);

        return redirect()->route('appointments.index')->with('success', 'Randevu başarıyla oluşturuldu.');
    }

    public function show(Appointment $appointment): View
    {
        if ($appointment->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $appointment->load([
            'customer', 'employee.user', 'branch',
            'appointmentServices.service', 'payments',
            'statusLogs.changedBy', 'review',
        ]);

        return view('appointments.show', compact('appointment'));
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        if ($appointment->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

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

    public function availableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $date = $request->date;
        $employeeId = $request->employee_id;
        $branchId = session('active_branch_id', 1);

        $appointments = Appointment::forBranch($branchId)
            ->where('employee_id', $employeeId)
            ->whereDate('start_at', $date)
            ->whereNotIn('status', ['cancelled', 'rejected', 'no_show'])
            ->get();

        // Dynamically fetch branch working hours
        $branch = \App\Models\Branch::find($branchId);
        $openTimeStr = '08:00:00';
        $closeTimeStr = '20:00:00';

        if ($branch) {
            if ($branch->opening_time) {
                $openTimeStr = $branch->opening_time instanceof \DateTimeInterface 
                    ? $branch->opening_time->format('H:i:s') 
                    : (string) $branch->opening_time;
            }
            if ($branch->closing_time) {
                $closeTimeStr = $branch->closing_time instanceof \DateTimeInterface 
                    ? $branch->closing_time->format('H:i:s') 
                    : (string) $branch->closing_time;
            }
        }

        $slots = [];
        $startTime = \Carbon\Carbon::parse($date . ' ' . $openTimeStr);
        $endTime = \Carbon\Carbon::parse($date . ' ' . $closeTimeStr);

        while ($startTime < $endTime) {
            $slotTime = $startTime->copy();
            $isAvailable = true;

            foreach ($appointments as $apt) {
                $aptStart = \Carbon\Carbon::parse($apt->start_at);
                $aptEnd = $apt->end_at ? \Carbon\Carbon::parse($apt->end_at) : $aptStart->copy()->addMinutes($apt->total_duration ?? 30);
                
                if ($slotTime >= $aptStart && $slotTime < $aptEnd) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($date === today()->toDateString() && $slotTime <= now()) {
                $isAvailable = false;
            }

            $slots[] = [
                'time' => $slotTime->format('H:i'),
                'is_available' => $isAvailable
            ];

            $startTime->addMinutes(30);
        }

        return response()->json($slots);
    }

    public function storePayment(Request $request, Appointment $appointment): RedirectResponse
    {
        if ($appointment->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

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
                'appointment_id' => $appointment->id,
            ]);
        });

        \App\Models\Debt::syncDebtForAppointment($appointment->fresh());

        // Ödeme alındığında dashboard kasa verileri güncellenmeli
        $this->dashboardService->flushBranchCache($appointment->branch_id);

        return back()->with('success', 'Ödeme başarıyla kaydedildi.');
    }

    public function destroyPayment(Appointment $appointment, \App\Models\Payment $payment): RedirectResponse
    {
        if ($appointment->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($appointment, $payment) {
            // 1. Delete associated transaction from transactions table
            \App\Models\Transaction::where('appointment_id', $appointment->id)
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
        if ($appointment->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
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
                'appointment_id' => $appointment->id,
            ]);
        });

        \App\Models\Debt::syncDebtForAppointment($appointment->fresh());

        return back()->with('success', 'Randevu başarıyla tamamlandı ve ödemesi kaydedildi.');
    }
}
