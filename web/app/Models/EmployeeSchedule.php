<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id', 'work_date',
        'start_time', 'end_time',
        'break_start', 'break_end',
        'is_day_off',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'is_day_off' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
