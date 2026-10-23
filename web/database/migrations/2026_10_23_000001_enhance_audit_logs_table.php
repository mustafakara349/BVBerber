<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'status')) {
                $table->string('status', 20)->default('success')->after('model_id')->index();
            }

            // High-performance query indexes for millions of audit logs
            $table->index('created_at', 'audit_logs_created_at_idx');
            $table->index(['status', 'created_at'], 'audit_logs_status_created_at_idx');
            $table->index(['action', 'created_at'], 'audit_logs_action_created_at_idx');
            $table->index(['model_type', 'model_id'], 'audit_logs_model_type_model_id_idx');
            $table->index(['user_id', 'created_at'], 'audit_logs_user_id_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_created_at_idx');
            $table->dropIndex('audit_logs_status_created_at_idx');
            $table->dropIndex('audit_logs_action_created_at_idx');
            $table->dropIndex('audit_logs_model_type_model_id_idx');
            $table->dropIndex('audit_logs_user_id_created_at_idx');

            if (Schema::hasColumn('audit_logs', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
