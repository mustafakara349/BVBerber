<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->unique(['employee_id', 'service_id'], 'uq_employee_service');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_services');
    }
};
