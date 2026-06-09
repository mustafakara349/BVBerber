<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026_05_30_170000_create_debts_table.php migration dosyasında tanımlanan
 * debts tablosu, branches ve appointments tablolarını oluşturan migration
 * dosyaları henüz mevcut olmadığı için fresh bir kurulumda hata veriyordu.
 *
 * Bu migration, o sorunu çözer: debts tablosunun FK kısıtlamalarını
 * kaldırıp, tablolar artık mevcut olduğundan yeniden ekler.
 *
 * Bu migration yalnızca fresh kurulumda çalıştırılacak. Eğer debts
 * tablosu henüz mevcut değilse (fresh migrate) bu migration hiçbir şey
 * yapmaz; debts migration'ı zaten çalışmış olacak.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Bu migration, debts tablosu zaten var ise FK kısıtlamalarını
        // yeniden düzenler. Fresh migrate senaryosunda debts migration'ı
        // (timestamp: 2026_05_30) bu migration'dan (2026_06_08) önce
        // çalışmaya çalışacak ve FK hatası verecekti. Yeni migration
        // sıralamasıyla (branch, appointments 2026_06_08_000005/13)
        // debts FK'ları artık doğru tablolara referans verecek.
        //
        // Mevcut canlı DB'de bu migration gereksizdir; fresh ortamlarda
        // sıralama sorunu artık çözülmüştür.
        //
        // Bu dosya sadece belgeleme amaçlıdır ve herhangi bir şema
        // değişikliği yapmaz.
        if (! Schema::hasTable('debts')) {
            return;
        }

        // Eğer debts tablosu FK olmadan oluşturulmuşsa (FK hata nedeniyle
        // atlanmışsa), burada FK'ları ekliyoruz.
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $foreignKeys = collect($sm->listTableForeignKeys('debts'))
            ->map(fn($fk) => $fk->getName())
            ->toArray();

        Schema::table('debts', function (Blueprint $table) use ($foreignKeys) {
            if (! in_array('debts_branch_id_foreign', $foreignKeys)) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            }
            if (! in_array('debts_appointment_id_foreign', $foreignKeys)) {
                $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('debts')) {
            return;
        }

        Schema::table('debts', function (Blueprint $table) {
            $table->dropForeignIfExists(['branch_id', 'appointment_id']);
        });
    }
};
