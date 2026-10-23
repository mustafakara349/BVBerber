<?php

namespace App\Models;

use App\Observers\ReviewObserver;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ReviewObserver::class])]
class Review extends Model
{
    use Auditable;

    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'employee_id',
        'rating',
        'comment',
        'prompted_for_google',
        'redirected_to_google_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'prompted_for_google' => 'boolean',
        'redirected_to_google_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
