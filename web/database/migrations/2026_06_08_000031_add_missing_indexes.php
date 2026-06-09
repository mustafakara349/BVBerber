<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit raporunda tespit edilen eksik indeksler:
 * - appointment_services: employee_id + service_id composite index
 * - expenses: expense_date index (tarih bazlı gider raporları için)
 */
return new class extends Migration
{
    public function up(): void
    {
        // appointment_services tablosuna composite index
        if (Schema::hasTable('appointment_services')) {
            Schema::table('appointment_services', function (Blueprint $table) {
                // Eğer daha önce eklenmemişse ekle
                $table->index(['employee_id', 'service_id'], 'idx_appt_services_employee_service');
            });
        }

        // expenses tablosuna expense_date index (zaten 2026_06_08_000019'da eklendi,
        // ancak mevcut DB'de yoksa buradan eklenir)
        if (Schema::hasTable('expenses') && ! $this->indexExists('expenses', 'idx_expenses_date')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->index('expense_date', 'idx_expenses_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('appointment_services')) {
            Schema::table('appointment_services', function (Blueprint $table) {
                $table->dropIndex('idx_appt_services_employee_service');
            });
        }

        if (Schema::hasTable('expenses') && $this->indexExists('expenses', 'idx_expenses_date')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropIndex('idx_expenses_date');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = \Illuminate\Support\Facades\Schema::getConnection();
        
        if ($connection->getDriverName() === 'sqlite') {
            return true;
        }

        $indexes = collect(\Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        ));

        return $indexes->isNotEmpty();
    }
};
