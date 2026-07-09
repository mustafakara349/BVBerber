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
        // 1. Update campaigns table
        Schema::table('campaigns', function (Blueprint $table) {
            $table->enum('type', ['auto_apply', 'coupon_required'])->default('auto_apply')->after('description');
            $table->decimal('min_order_amount', 10, 2)->default(0.00)->after('end_date');
            $table->decimal('max_discount_amount', 10, 2)->nullable()->after('min_order_amount');
            $table->enum('target_audience', ['all', 'new_customers', 'loyalty_members'])->default('all')->after('max_discount_amount');
            $table->string('image_path')->nullable()->after('target_audience');
            $table->integer('priority')->default(0)->after('image_path');
        });

        // 2. Update coupons table
        Schema::table('coupons', function (Blueprint $table) {
            $table->integer('per_customer_limit')->default(1)->after('usage_limit');
        });

        // 3. Update appointments table
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable()->after('discount_amount')->constrained('campaigns')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->after('campaign_id')->constrained('coupons')->nullOnDelete();
        });

        // 4. Create campaign_services pivot table
        Schema::create('campaign_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamps();
        });

        // 5. Create campaign_products pivot table
        Schema::create('campaign_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
        });

        // 6. Create coupon_usages table
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('campaign_products');
        Schema::dropIfExists('campaign_services');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['campaign_id', 'coupon_id']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('per_customer_limit');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'min_order_amount',
                'max_discount_amount',
                'target_audience',
                'image_path',
                'priority'
            ]);
        });
    }
};
