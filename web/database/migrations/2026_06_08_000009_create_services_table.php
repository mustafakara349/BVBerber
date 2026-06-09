<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('service_categories')->onDelete('set null');
            $table->string('name', 150);
            $table->string('slug', 150);
            $table->text('description')->nullable();
            $table->integer('duration_minutes');
            $table->integer('buffer_time')->default(0);
            $table->decimal('price', 10, 2);
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->enum('gender_type', ['male', 'female', 'unisex'])->default('unisex');
            $table->string('image', 255)->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_id', 'slug'], 'uq_service_slug_branch');
            $table->index('branch_id', 'idx_services_branch');
            $table->index('category_id', 'idx_services_category');
            $table->index('is_active', 'idx_services_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
