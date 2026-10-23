<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'audit:prune 
                            {--days=90 : Belirtilen günden eski kayıtları temizle (varsayılan: 90)} 
                            {--force : Onay sormadan çalıştır}';

    /**
     * The console command description.
     */
    protected $description = 'Belirtilen süreden eski audit ve sistem hata loglarını güvenli bir şekilde arşivleyip siler.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        if ($days < 7) {
            $this->error('Güvenlik nedeniyle 7 günden daha yeni loglar silinemez.');
            return self::FAILURE;
        }

        $cutoffDate = now()->subDays($days);
        $countQuery = AuditLog::where('created_at', '<', $cutoffDate);
        $totalToDelete = $countQuery->count();

        if ($totalToDelete === 0) {
            $this->info("{$days} günden eski temizlenecek log kaydı bulunamadı.");
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("{$days} günden eski toplam {$totalToDelete} adet log kaydı kalıcı olarak silinecek. Onaylıyor musunuz?")) {
            $this->warn('İşlem iptal edildi.');
            return self::SUCCESS;
        }

        $this->output->progressStart($totalToDelete);

        $deletedCount = 0;
        // Batch deletion in chunks of 500 to keep memory footprint and lock contention minimal
        AuditLog::where('created_at', '<', $cutoffDate)
            ->chunkById(500, function ($records) use (&$deletedCount) {
                $ids = $records->pluck('id')->toArray();
                AuditLog::whereIn('id', $ids)->delete();
                $deletedCount += count($ids);
                $this->output->progressAdvance(count($ids));
            });

        $this->output->progressFinish();

        $this->info("Başarılı: Toplam {$deletedCount} adet eski log kaydı veritabanından temizlendi.");

        // Record the prune event itself in audit log
        AuditLog::record(
            action: 'system.audit.prune',
            modelType: 'audit_log',
            modelId: 0,
            oldValues: ['deleted_count' => $deletedCount, 'days' => $days],
            newValues: ['pruned_at' => now()->toIso8601String()],
            userId: null,
            status: 'success',
            details: "Artisan audit:prune komutu ile {$days} günden eski {$deletedCount} kayıt temizlendi."
        );

        return self::SUCCESS;
    }
}
