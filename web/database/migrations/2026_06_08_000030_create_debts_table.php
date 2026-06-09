<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->string('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid'); // unpaid, partial, paid
            $table->timestamps();
        });

        // Seed existing unpaid/partially paid completed appointments
        $unpaidAppointments = DB::table('appointments')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('status', 'completed')
            ->get();

        foreach ($unpaidAppointments as $appointment) {
            // Re-calculate paid amount from payments table
            $paidAmount = DB::table('payments')
                ->where('appointment_id', $appointment->id)
                ->sum('amount');

            // Find payment status
            $status = 'unpaid';
            if ($paidAmount > 0 && $paidAmount < $appointment->total_price) {
                $status = 'partial';
            } elseif ($paidAmount >= $appointment->total_price) {
                continue; // It's paid, we skip
            }

            DB::table('debts')->insert([
                'branch_id' => $appointment->branch_id,
                'customer_id' => $appointment->customer_id,
                'appointment_id' => $appointment->id,
                'amount' => $appointment->total_price,
                'paid_amount' => $paidAmount,
                'description' => 'Randevu Borcu - #' . $appointment->appointment_code,
                'due_date' => $appointment->start_at ? substr($appointment->start_at, 0, 10) : null,
                'status' => $status,
                'created_at' => $appointment->created_at ?? now(),
                'updated_at' => $appointment->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
