<?php

namespace App\Models;

use App\Enums\SalaryType;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'branch_id', 'user_id', 'employee_code',
        'title', 'biography',
        'hire_date', 'leave_date',
        'salary_type', 'salary_amount', 'commission_rate',
        'daily_work_limit', 'appointment_color',
        'is_visible', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'leave_date' => 'date',
            'salary_type' => SalaryType::class,
            'salary_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'employee_services');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->user?->full_name ?? '';
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
