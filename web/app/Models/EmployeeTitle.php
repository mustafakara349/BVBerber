<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTitle extends Model
{
    protected $fillable = ['branch_id', 'name'];

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
