<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupon_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Migrate existing user_ids to pivot table
        $couponsWithUser = DB::table('coupons')->whereNotNull('user_id')->get();
        foreach ($couponsWithUser as $coupon) {
            DB::table('coupon_user')->insert([
                'coupon_id' => $coupon->id,
                'user_id' => $coupon->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('coupons', function (Blueprint $table) {
            // drop foreign key first
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        // Migrate back from pivot table (only one user per coupon will be saved)
        $couponUsers = DB::table('coupon_user')->get()->groupBy('coupon_id');
        foreach ($couponUsers as $couponId => $users) {
            DB::table('coupons')->where('id', $couponId)->update(['user_id' => $users->first()->user_id]);
        }

        Schema::dropIfExists('coupon_user');
    }
};
