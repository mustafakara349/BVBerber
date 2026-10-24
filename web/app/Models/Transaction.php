<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use Auditable;

    protected $fillable = [
        'branch_id', 'reference_type', 'reference_id', 'created_by',
        'transaction_type', 'category', 'amount', 'currency',
        'payment_method', 'description', 'document_path', 'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'transaction_type' => TransactionType::class,
            'payment_method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Transaction $transaction) {
            if ($transaction->branch_id) {
                app(\App\Services\DashboardService::class)->flushBranchCache((int) $transaction->branch_id);
            }
        });

        static::deleted(function (Transaction $transaction) {
            if ($transaction->branch_id) {
                app(\App\Services\DashboardService::class)->flushBranchCache((int) $transaction->branch_id);
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reference()
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * İlişkili kaydın doğrudan detay URL'ini döner
     */
    public function getReferenceUrlAttribute(): ?string
    {
        if (!$this->reference) {
            return null;
        }

        if ($this->reference instanceof Appointment) {
            return route('appointments.show', $this->reference);
        }

        if ($this->reference instanceof Debt) {
            if ($this->reference->customer_id) {
                return route('finance.debts.details', [
                    'customer_id' => $this->reference->customer_id,
                    'type' => $this->reference->type,
                ]);
            }
            if ($this->reference->counterparty_name) {
                return route('finance.debts.details', [
                    'counterparty_name' => $this->reference->counterparty_name,
                    'type' => $this->reference->type,
                ]);
            }
            return route($this->reference->type === 'payable' ? 'finance.debts.payables' : 'finance.debts.receivables');
        }

        if ($this->reference instanceof Expense) {
            return route('finance.expenses');
        }

        if ($this->reference instanceof PurchaseOrder) {
            return route('purchase-orders.show', $this->reference);
        }

        return null;
    }

    /**
     * İlişkili kaydın kullanıcı dostu kodunu veya numarasını döner
     */
    public function getReferenceCodeAttribute(): string
    {
        if ($this->reference instanceof Appointment && !empty($this->reference->appointment_code)) {
            return $this->reference->appointment_code;
        }

        if ($this->reference instanceof PurchaseOrder && !empty($this->reference->invoice_number)) {
            return '#' . $this->reference->invoice_number;
        }

        return (string) ($this->reference_id ?? '-');
    }

    /**
     * İlişkili kaydın Türkçe tür başlığını döner
     */
    public function getReferenceLabelAttribute(): string
    {
        return match (class_basename($this->reference_type)) {
            'Appointment' => 'Randevu',
            'Debt' => 'Borç / Alacak Kaydı',
            'Expense' => 'Gider Kaydı',
            'PurchaseOrder' => 'Mal Alımı',
            default => class_basename($this->reference_type ?? 'Kayıt'),
        };
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeIncome($query)
    {
        return $query->where('transaction_type', TransactionType::Income);
    }

    public function scopeExpense($query)
    {
        return $query->where('transaction_type', TransactionType::Expense);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }
}
