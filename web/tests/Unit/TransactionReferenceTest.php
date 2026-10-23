<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Debt;
use App\Models\Expense;
use App\Models\Transaction;
use Tests\TestCase;

class TransactionReferenceTest extends TestCase
{
    public function test_transaction_reference_appointment_accessors()
    {
        $appointment = new Appointment([
            'id' => 172,
            'uuid' => 'e64796e8-1676-4022-857e-f3ff8fb8e17b',
            'appointment_code' => 'BV-4AAFTQHA',
        ]);
        $appointment->id = 172;

        $transaction = new Transaction([
            'reference_type' => Appointment::class,
            'reference_id' => 172,
        ]);
        $transaction->setRelation('reference', $appointment);

        $this->assertEquals('BV-4AAFTQHA', $transaction->reference_code);
        $this->assertEquals('Randevu', $transaction->reference_label);
        $this->assertEquals(route('appointments.show', $appointment), $transaction->reference_url);
    }

    public function test_transaction_reference_debt_accessors()
    {
        $debt = new Debt([
            'customer_id' => 5,
            'type' => 'receivable',
        ]);
        $debt->id = 25;

        $transaction = new Transaction([
            'reference_type' => Debt::class,
            'reference_id' => 25,
        ]);
        $transaction->setRelation('reference', $debt);

        $this->assertEquals('25', $transaction->reference_code);
        $this->assertEquals('Borç / Alacak Kaydı', $transaction->reference_label);
        $this->assertEquals(route('finance.debts.details', ['customer_id' => 5, 'type' => 'receivable']), $transaction->reference_url);
    }

    public function test_transaction_reference_expense_accessors()
    {
        $expense = new Expense([
            'amount' => 500,
        ]);
        $expense->id = 10;

        $transaction = new Transaction([
            'reference_type' => Expense::class,
            'reference_id' => 10,
        ]);
        $transaction->setRelation('reference', $expense);

        $this->assertEquals('10', $transaction->reference_code);
        $this->assertEquals('Gider Kaydı', $transaction->reference_label);
        $this->assertEquals(route('finance.expenses'), $transaction->reference_url);
    }
}
