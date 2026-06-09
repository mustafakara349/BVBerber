<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->integer('appointment_interval')->default(30);
            $table->integer('cancellation_limit_hours')->default(2);
            $table->string('currency', 10)->default('TRY');
            $table->boolean('loyalty_enabled')->default(true);
            $table->boolean('review_enabled')->default(true);
            $table->boolean('online_payment_enabled')->default(false);
            $table->timestamps();

            $table->unique('branch_id', 'uq_branch_settings_branch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_settings');
    }
};
