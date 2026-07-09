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
            $table->integer('per_customer_limit')->nullable()->default(null)->after('priority');
        });

        // 2. Create campaign_categories table
        Schema::create('campaign_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->timestamps();
        });

        // 3. Create campaign_usages table
        Schema::create('campaign_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();
        });

        // 4. Update coupons table
        // Drop foreign key to campaigns (if exists) and column
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign('fk_coupons_campaign');
            $table->dropColumn('campaign_id');
            
            // Add new independent coupon fields
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage')->after('code');
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            
            // Add title and description since it's independent now
            $table->string('title', 255)->nullable()->after('user_id');
            $table->text('description')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'discount_type', 'discount_value', 'title', 'description']);
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('cascade');
        });

        Schema::dropIfExists('campaign_usages');
        Schema::dropIfExists('campaign_categories');

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('per_customer_limit');
        });
    }
};
