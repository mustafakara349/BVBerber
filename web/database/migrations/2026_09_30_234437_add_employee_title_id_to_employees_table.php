<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('employee_title_id')->nullable()->after('title')->constrained()->nullOnDelete();
        });

        // Verileri migrate et (varsa mevcut string 'title' değerlerini id'ye çevir)
        $employees = DB::table('employees')->whereNotNull('title')->where('title', '!=', '')->get();
        foreach ($employees as $emp) {
            $title = DB::table('employee_titles')
                ->where('branch_id', $emp->branch_id)
                ->where('name', $emp->title)
                ->first();
            
            if (!$title) {
                $titleId = DB::table('employee_titles')->insertGetId([
                    'branch_id' => $emp->branch_id,
                    'name' => $emp->title,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $titleId = $title->id;
            }

            DB::table('employees')->where('id', $emp->id)->update(['employee_title_id' => $titleId]);
        }

        // Drop the old title column
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('title')->nullable()->after('employee_title_id');
        });

        $employees = DB::table('employees')->whereNotNull('employee_title_id')->get();
        foreach ($employees as $emp) {
            $title = DB::table('employee_titles')->where('id', $emp->employee_title_id)->first();
            if ($title) {
                DB::table('employees')->where('id', $emp->id)->update(['title' => $title->name]);
            }
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['employee_title_id']);
            $table->dropColumn('employee_title_id');
        });
    }
};
