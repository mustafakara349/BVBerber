<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchSetting extends Model
{
    protected $fillable = [
        'branch_id',
        'appointment_interval',
        'cancellation_limit_hours',
        'currency',
        'loyalty_enabled',
        'review_enabled',
        'online_payment_enabled',
    ];

    protected function casts(): array
    {
        return [
            'loyalty_enabled' => 'boolean',
            'review_enabled' => 'boolean',
            'online_payment_enabled' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
