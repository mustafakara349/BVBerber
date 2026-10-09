<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    /**
     * Create a new transaction
     *
     * @param array $data
     * @return Transaction
     */
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            
            // If this transaction is a payment for a debt (receivable or payable)
            if (isset($data['reference_type']) && $data['reference_type'] === Debt::class && isset($data['reference_id'])) {
                $this->updateDebtBalance($data['reference_id'], $data['amount']);
            }

            return $transaction;
        });
    }

    /**
     * Update debt paid amount when a transaction is tied to it
     *
     * @param int $debtId
     * @param float $amount
     * @return void
     * @throws Exception
     */
    protected function updateDebtBalance(int $debtId, float $amount): void
    {
        $debt = Debt::lockForUpdate()->find($debtId);
        
        if (!$debt) {
            throw new Exception("Borç kaydı bulunamadı.");
        }

        $debt->paid_amount += $amount;

        if ($debt->paid_amount >= $debt->amount) {
            $debt->status = 'paid';
            $debt->paid_amount = $debt->amount; // Prevent overpayment in status
        } elseif ($debt->paid_amount > 0) {
            $debt->status = 'partial';
        } else {
            $debt->status = 'unpaid';
        }

        $debt->save();
    }
}
