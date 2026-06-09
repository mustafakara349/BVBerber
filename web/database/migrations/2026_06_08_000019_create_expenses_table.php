<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->string('receipt_file', 255)->nullable();
            $table->timestamps();

            // Tarih bazlı gider sorguları için index (audit raporunda tespit edildi)
            $table->index('expense_date', 'idx_expenses_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
