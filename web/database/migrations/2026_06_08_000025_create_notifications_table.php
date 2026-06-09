<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 255);
            $table->text('body');
            $table->string('type', 100)->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
