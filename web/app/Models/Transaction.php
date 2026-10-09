<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reference()
    {
        return $this->morphTo();
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
