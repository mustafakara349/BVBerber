<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    protected $fillable = [
        'branch_id',
        'customer_id',
        'appointment_id',
        'amount',
        'paid_amount',
        'description',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Accessors
    public function getRemainingAmountAttribute(): float
    {
        return (float) ($this->amount - $this->paid_amount);
    }

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    // Scopes
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }

    // Helpers
    public static function syncDebtForAppointment(Appointment $appointment): void
    {
        // Debt is only active for completed appointments which are unpaid or partial
        $isEligible = ($appointment->status?->value ?? $appointment->status) === 'completed' && 
                      in_array(($appointment->payment_status?->value ?? $appointment->payment_status), ['unpaid', 'partial']);

        if (!$isEligible) {
            // Delete if exists
            self::where('appointment_id', $appointment->id)->delete();
            return;
        }

        // Calculate paid amount from payments relation
        $paidAmount = $appointment->payments()->sum('amount');

        // Check if actually fully paid
        if ($paidAmount >= $appointment->total_price) {
            // Update appointment status to paid if it was not set
            if (($appointment->payment_status?->value ?? $appointment->payment_status) !== 'paid') {
                $appointment->update(['payment_status' => \App\Enums\PaymentStatus::Paid]);
            }
            self::where('appointment_id', $appointment->id)->delete();
            return;
        }

        $status = 'unpaid';
        if ($paidAmount > 0) {
            $status = 'partial';
        }

        self::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'branch_id' => $appointment->branch_id,
                'customer_id' => $appointment->customer_id,
                'amount' => $appointment->total_price,
                'paid_amount' => $paidAmount,
                'description' => 'Randevu Borcu - #' . $appointment->appointment_code,
                'due_date' => $appointment->start_at,
                'status' => $status,
            ]
        );
    }
}
