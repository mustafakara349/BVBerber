<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cafe_products', function (Blueprint $table) {
            $table->foreignId('cafe_category_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            // Drop old string category if it exists
            if (Schema::hasColumn('cafe_products', 'category')) {
                $table->dropColumn('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cafe_products', function (Blueprint $table) {
            $table->dropForeign(['cafe_category_id']);
            $table->dropColumn('cafe_category_id');
            $table->string('category')->nullable()->after('branch_id');
        });
    }
};
