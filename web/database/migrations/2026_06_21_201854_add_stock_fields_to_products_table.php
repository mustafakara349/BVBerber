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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('critical_stock')->nullable()->after('stock_quantity');
            $table->decimal('average_cost_price', 10, 2)->nullable()->after('critical_stock');
            $table->decimal('last_purchase_price', 10, 2)->nullable()->after('average_cost_price');
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['critical_stock', 'average_cost_price', 'last_purchase_price', 'created_by', 'updated_by']);
        });
    }
};
