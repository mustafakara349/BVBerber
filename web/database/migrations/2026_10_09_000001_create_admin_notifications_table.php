<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Yönetim paneli personeline özel (iç) sistem bildirimleri.
 *
 * Müşterilere giden mobil bildirimler `notifications` tablosunda kalmaya devam eder;
 * bu tablo ise yalnızca panel kullanıcılarına düşen, aksiyon alınabilir sistem
 * uyarılarını tutar. İki akışın ayrı tablolarda tutulması, panel uyarılarının
 * mobil uygulamaya sızmasını yapısal olarak engeller.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 30);
            $table->string('event', 60);
            $table->string('level', 20)->default('info');
            $table->string('title', 255);
            $table->text('body');
            $table->string('action_url', 500)->nullable();
            $table->string('action_label', 60)->nullable();
            $table->nullableMorphs('subject');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at'], 'idx_admin_notif_user_read');
            $table->index(['user_id', 'created_at'], 'idx_admin_notif_user_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
