<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('transaction_type', ['income', 'expense', 'refund']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('TRY');
            $table->enum('payment_method', ['cash', 'credit_card', 'bank_transfer', 'online'])->nullable();
            $table->text('description')->nullable();
            $table->dateTime('transaction_date');
            $table->timestamps();

            $table->index(['branch_id', 'transaction_date'], 'idx_transactions_branch_date');
            $table->index('transaction_type', 'idx_transactions_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
