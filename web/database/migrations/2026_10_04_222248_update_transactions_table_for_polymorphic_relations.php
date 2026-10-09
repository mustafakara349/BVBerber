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
        Schema::table('transactions', function (Blueprint $table) {
            $table->nullableMorphs('reference');
            $table->string('category')->nullable()->after('transaction_type');
            $table->string('document_path')->nullable()->after('description');
        });

        // Migrate existing references to polymorphic relations
        \Illuminate\Support\Facades\DB::table('transactions')->whereNotNull('appointment_id')->update([
            'reference_type' => 'App\Models\Appointment',
            'reference_id' => \Illuminate\Support\Facades\DB::raw('appointment_id')
        ]);

        \Illuminate\Support\Facades\DB::table('transactions')->whereNotNull('expense_id')->update([
            'reference_type' => 'App\Models\Expense',
            'reference_id' => \Illuminate\Support\Facades\DB::raw('expense_id')
        ]);

        // Drop old foreign keys and columns
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('fk_transactions_appointment');
            $table->dropForeign('fk_transactions_expense');
            $table->dropColumn(['appointment_id', 'expense_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->onDelete('cascade');
        });

        // Revert data
        \Illuminate\Support\Facades\DB::table('transactions')->where('reference_type', 'App\Models\Appointment')->update([
            'appointment_id' => \Illuminate\Support\Facades\DB::raw('reference_id')
        ]);

        \Illuminate\Support\Facades\DB::table('transactions')->where('reference_type', 'App\Models\Expense')->update([
            'expense_id' => \Illuminate\Support\Facades\DB::raw('reference_id')
        ]);

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropMorphs('reference');
            $table->dropColumn(['category', 'document_path']);
        });
    }
};
