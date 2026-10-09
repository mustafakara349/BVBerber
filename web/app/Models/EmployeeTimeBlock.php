<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTimeBlock extends Model
{
    protected $fillable = [
        'employee_id',
        'type', // full_day, partial_time
        'date',
        'start_time',
        'end_time',
        'reason',
        'created_by'
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
