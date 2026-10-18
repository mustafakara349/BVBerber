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
            if (!Schema::hasColumn('reviews', 'prompted_for_google')) {
                $table->boolean('prompted_for_google')->default(false)->after('rating');
            }
            if (!Schema::hasColumn('reviews', 'redirected_to_google_at')) {
                $table->timestamp('redirected_to_google_at')->nullable()->after('prompted_for_google');
            }
        });

        try {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unique('appointment_id', 'uniq_reviews_appointment_id');
            });
        } catch (\Throwable) {
            // Index already exists
        }

        // 2. appointments tablosu
        if (!Schema::hasColumn('appointments', 'review_reminder_sent_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->timestamp('review_reminder_sent_at')->nullable()->after('status');
            });
        }

        // 3. users tablosu
        if (!Schema::hasColumn('users', 'last_google_redirect_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_google_redirect_at')->nullable()->after('remember_token');
            });
        }

        // 4. branch_settings tablosu
        Schema::table('branch_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('branch_settings', 'google_place_id')) {
                $table->string('google_place_id')->nullable()->after('review_enabled');
            }
            if (!Schema::hasColumn('branch_settings', 'google_review_url')) {
                $table->string('google_review_url', 500)->nullable()->after('review_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            if (Schema::hasColumn('branch_settings', 'google_place_id')) {
                $table->dropColumn('google_place_id');
            }
            if (Schema::hasColumn('branch_settings', 'google_review_url')) {
                $table->dropColumn('google_review_url');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_google_redirect_at')) {
                $table->dropColumn('last_google_redirect_at');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'review_reminder_sent_at')) {
                $table->dropColumn('review_reminder_sent_at');
            }
        });

        Schema::table('reviews', function (Blueprint $table) {
            try {
                $table->dropUnique('uniq_reviews_appointment_id');
            } catch (\Throwable) {}

            $dropCols = [];
            if (Schema::hasColumn('reviews', 'prompted_for_google')) {
                $dropCols[] = 'prompted_for_google';
            }
            if (Schema::hasColumn('reviews', 'redirected_to_google_at')) {
                $dropCols[] = 'redirected_to_google_at';
            }
            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
