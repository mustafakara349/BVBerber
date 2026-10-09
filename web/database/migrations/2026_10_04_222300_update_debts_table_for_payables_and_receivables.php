<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->enum('type', ['receivable', 'payable'])->default('receivable')->after('id');
            $table->foreignId('customer_id')->nullable()->change();
            $table->string('counterparty_name')->nullable()->after('customer_id');
            $table->boolean('is_installment')->default(false)->after('status');
            $table->foreignId('parent_debt_id')->nullable()->after('is_installment')->constrained('debts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->dropForeign(['parent_debt_id']);
            $table->dropColumn(['type', 'counterparty_name', 'is_installment', 'parent_debt_id']);
        });
    }
};
