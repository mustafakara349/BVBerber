<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_code', 50)->unique();
            $table->string('title', 100)->nullable();
            $table->text('biography')->nullable();
            $table->date('hire_date');
            $table->date('leave_date')->nullable();
            $table->enum('salary_type', ['fixed', 'commission', 'fixed_plus_commission', 'hourly'])->default('fixed');
            $table->decimal('salary_amount', 10, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->integer('daily_work_limit')->default(20);
            $table->string('appointment_color', 20)->default('#000000');
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('branch_id', 'idx_employees_branch');
            $table->index('user_id', 'idx_employees_user');
            $table->index('is_active', 'idx_employees_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
