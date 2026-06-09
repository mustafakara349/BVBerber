<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('work_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('is_day_off')->default(false);
            $table->timestamps();

            $table->unique(['employee_id', 'work_date'], 'uq_employee_workdate');
            $table->index('work_date', 'idx_employee_schedules_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_schedules');
    }
};
