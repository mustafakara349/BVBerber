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
        Schema::table('product_sales', function (Blueprint $table) {
            $table->string('sale_code', 50)->nullable()->after('created_by');
            $table->string('payment_method', 50)->nullable()->after('sale_code');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('payment_method');
            $table->text('notes')->nullable()->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_sales', function (Blueprint $table) {
            $table->dropColumn(['sale_code', 'payment_method', 'discount_amount', 'notes']);
        });
    }
};
