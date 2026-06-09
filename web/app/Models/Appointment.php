<?php

namespace App\Models;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasUuid, SoftDeletes, Auditable;

    protected $fillable = [
        'uuid', 'branch_id', 'customer_id', 'employee_id',
        'appointment_code', 'start_at', 'end_at', 'total_duration',
        'subtotal', 'discount_amount', 'tax_amount', 'total_price',
        'status', 'payment_status', 'payment_method', 'source',
        'customer_note', 'internal_note', 'cancellation_reason',
        'cancelled_by', 'cancelled_at', 'completed_at',
        'no_show', 'reminder_sent', 'checked_in_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => AppointmentStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'source' => AppointmentSource::class,
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'no_show' => 'boolean',
            'reminder_sent' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function appointmentServices(): HasMany
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(AppointmentStatusLog::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function debt(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Debt::class);
    }

    // Scopes
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('start_at', $date);
    }

    public function scopeForDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('start_at', [$from, $to]);
    }

    public function scopeStatus($query, AppointmentStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now())
            ->whereIn('status', [AppointmentStatus::Pending, AppointmentStatus::Confirmed]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_at', today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', AppointmentStatus::Completed);
    }
}
