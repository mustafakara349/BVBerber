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
        // 1. reviews tablosu
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique('appointment_id', 'uniq_reviews_appointment_id');
            $table->boolean('prompted_for_google')->default(false)->after('rating');
            $table->timestamp('redirected_to_google_at')->nullable()->after('prompted_for_google');
        });

        // 2. appointments tablosu
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('review_reminder_sent_at')->nullable()->after('status');
        });

        // 3. users tablosu
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_google_redirect_at')->nullable()->after('remember_token');
        });

        // 4. branch_settings tablosu
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->string('google_place_id')->nullable()->after('review_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->dropColumn('google_place_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_google_redirect_at');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('review_reminder_sent_at');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('uniq_reviews_appointment_id');
            $table->dropColumn(['prompted_for_google', 'redirected_to_google_at']);
        });
    }
};
