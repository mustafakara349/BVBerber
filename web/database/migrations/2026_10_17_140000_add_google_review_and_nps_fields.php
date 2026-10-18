<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unique index eklenmeden önce olası mükerrer kayıtlar temizlenir (en eski kayıt korunur).
        $duplicates = DB::table('reviews')
            ->select('appointment_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('appointment_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('reviews')
                ->where('appointment_id', $dup->appointment_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->unique('appointment_id', 'uniq_reviews_appointment_id');
            $table->boolean('prompted_for_google')->default(false)->after('rating');
            $table->timestamp('redirected_to_google_at')->nullable()->after('prompted_for_google');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('review_reminder_sent_at')->nullable()->after('completed_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_google_redirect_at')->nullable()->after('last_login_at');
        });

        Schema::table('branch_settings', function (Blueprint $table) {
            $table->string('google_review_url', 500)->nullable()->after('review_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->dropColumn('google_review_url');
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
