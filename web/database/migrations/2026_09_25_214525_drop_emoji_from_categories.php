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
        Schema::table('cafe_categories', function (Blueprint $table) {
            $table->dropColumn('emoji');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('emoji');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cafe_categories', function (Blueprint $table) {
            $table->string('emoji')->nullable();
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('emoji')->nullable();
        });
    }
};
