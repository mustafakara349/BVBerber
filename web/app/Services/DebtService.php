<?php

namespace App\Services;

use App\Models\Debt;
use Illuminate\Support\Facades\DB;
use Exception;

class DebtService
{
    /**
     * Create a new debt (receivable or payable)
     *
     * @param array $data
     * @return Debt
     */
    public function createDebt(array $data): Debt
    {
        return DB::transaction(function () use ($data) {
            $debt = Debt::create($data);
            return $debt;
        });
    }

    /**
     * Delete a debt
     *
     * @param Debt $debt
     * @return bool
     */
    public function deleteDebt(Debt $debt): bool
    {
        return DB::transaction(function () use ($debt) {
            // Associated transactions should probably be handled,
            // but for now we just delete the debt.
            return $debt->delete();
        });
    }
}
