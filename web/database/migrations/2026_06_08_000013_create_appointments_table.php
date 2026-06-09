<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('appointment_code', 30)->unique();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->integer('total_duration');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'rejected', 'no_show', 'in_progress'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'partial', 'refunded'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'credit_card', 'bank_transfer', 'online'])->nullable();
            $table->enum('source', ['mobile_app', 'walk_in', 'admin_panel', 'phone', 'instagram', 'website'])->default('mobile_app');
            $table->text('customer_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('no_show')->default(false);
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'start_at'], 'idx_appointments_employee_date');
            $table->index('customer_id', 'idx_appointments_customer');
            $table->index('status', 'idx_appointments_status');
            $table->index(['branch_id', 'start_at'], 'idx_appointments_branch_date');
            $table->index('payment_status', 'idx_appointments_payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
