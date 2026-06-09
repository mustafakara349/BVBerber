<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bu migration, Laravel default users şablonunu production şemasına
 * dönüştürür. Mevcut 0001_01_01_000000_create_users_table.php'yi
 * değiştirmeden, eksik kolonları ekler ve gereksiz kolonları kaldırır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Production şemasında olmayan default kolonları kaldır
            $table->dropColumn('name');

            // Production şemasına uygun kolonları ekle
            $table->char('uuid', 36)->unique()->after('id');
            $table->foreignId('role_id')->after('uuid')->constrained('roles')->onDelete('restrict');
            $table->string('first_name', 100)->after('role_id');
            $table->string('last_name', 100)->after('first_name');
            $table->string('phone', 20)->unique()->nullable()->after('email');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('password');
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('profile_photo', 255)->nullable()->after('birth_date');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('phone_verified_at');
            $table->enum('status', ['active', 'inactive', 'blocked', 'deleted'])->default('active')->after('last_login_at');
            $table->softDeletes();

            // Indexler
            $table->index('role_id', 'idx_users_role');
            $table->index('status', 'idx_users_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_status');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uuid', 'role_id', 'first_name', 'last_name',
                'phone', 'gender', 'birth_date', 'profile_photo',
                'phone_verified_at', 'last_login_at', 'status',
            ]);
            $table->string('name');
        });
    }
};
