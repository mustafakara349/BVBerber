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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->text('terms')->nullable()->after('description');
            $table->enum('trigger_type', ['all', 'categories', 'services'])->default('all')->after('terms');
            $table->enum('reward_type', ['discount', 'gift_product', 'gift_cafe'])->default('discount')->after('trigger_type');
            $table->foreignId('reward_product_id')->nullable()->after('reward_type')->constrained('products')->nullOnDelete();
            $table->foreignId('reward_cafe_product_id')->nullable()->after('reward_product_id')->constrained('cafe_products')->nullOnDelete();
        });
        
        // Ensure discount_type and discount_value can be nullable or we can just let Laravel set default values in controller.
        // Doing it at controller level is safer for ENUMs.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['reward_product_id']);
            $table->dropForeign(['reward_cafe_product_id']);
            $table->dropColumn([
                'terms',
                'trigger_type',
                'reward_type',
                'reward_product_id',
                'reward_cafe_product_id'
            ]);
        });
    }
};
