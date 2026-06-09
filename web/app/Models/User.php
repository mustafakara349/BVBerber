<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid, SoftDeletes, Auditable;

    protected $fillable = [
        'uuid',
        'role_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'gender',
        'birth_date',
        'profile_photo',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'birth_date' => 'date',
            'gender' => Gender::class,
            'status' => UserStatus::class,
            'password' => 'hashed',
        ];
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function loyaltyAccount(): HasOne
    {
        return $this->hasOne(LoyaltyAccount::class, 'customer_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class, 'customer_id');
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class, 'customer_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::Active);
    }

    public function scopeCustomers($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('slug', 'customer'));
    }

    public function scopeStaff($query)
    {
        return $query->whereHas('role', fn ($q) => $q->whereIn('slug', ['super_admin', 'owner', 'manager', 'receptionist', 'barber']));
    }

    // Helpers
    public function isAdmin(): bool
    {
        return in_array($this->role?->slug, ['super_admin', 'owner', 'manager']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'super_admin';
    }

    public function isBarber(): bool
    {
        return $this->role?->slug === 'barber';
    }

    public function isCustomer(): bool
    {
        return $this->role?->slug === 'customer';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role?->hasPermission($permission) ?? false;
    }
}
