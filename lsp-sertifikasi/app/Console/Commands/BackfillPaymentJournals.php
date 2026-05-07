<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\Payment;
use App\Services\JournalService;
use Illuminate\Console\Command;

/**
 * BackfillPaymentJournals
 *
 * Buat jurnal yang hilang untuk pembayaran mandiri yang sudah diverifikasi
 * tapi tidak punya journal_entry.
 *
 * Cara pakai (SSH ke server Hostinger):
 *   php artisan journal:backfill-payments
 *   php artisan journal:backfill-payments --dry-run   (preview saja, tidak simpan)
 *   php artisan journal:backfill-payments --id=14     (satu payment saja)
 */
class BackfillPaymentJournals extends Command
{
    protected $signature   = 'journal:backfill-payments
                                {--dry-run : Preview tanpa menyimpan ke database}
                                {--id=     : Proses satu payment saja berdasarkan ID}';

    protected $description = 'Buat journal_entry yang hilang untuk payment mandiri yang sudah verified';

    public function handle(JournalService $journalService): int
    {
        $isDry = $this->option('dry-run');
        $onlyId = $this->option('id');

        $this->info($isDry
            ? '🔍  DRY RUN — tidak ada data yang disimpan'
            : '🚀  Memproses backfill jurnal...'
        );
        $this->newLine();

        // ── Cari payment yang belum punya jurnal pelunasan ──────────────────
        $query = Payment::with(['asesmen.skema'])
            ->where('status', 'verified')
            ->whereNotNull('verified_at');

        if ($onlyId) {
            $query->where('id', (int) $onlyId);
        }

        $payments = $query->get()->filter(function (Payment $p) {
            // Tidak punya jurnal pelunasan (ref_type = App\Models\Payment, tanpa suffix _piutang)
            return ! JournalEntry::where('ref_type', Payment::class)
                ->where('ref_id', $p->id)
                ->exists();
        });

        if ($payments->isEmpty()) {
            $this->info('✅  Semua payment sudah punya jurnal. Tidak ada yang perlu dibackfill.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Asesmen ID', 'Nama Asesi', 'Amount', 'Verified At', 'Aksi'],
            $payments->map(fn($p) => [
                $p->id,
                $p->asesmen_id,
                $p->asesmen->full_name ?? '-',
                'Rp ' . number_format($p->amount, 0, ',', '.'),
                $p->verified_at?->format('d/m/Y H:i'),
                $isDry ? '[preview]' : 'akan dibuat',
            ])
        );

        $this->newLine();

        if ($isDry) {
            $this->comment('Jalankan tanpa --dry-run untuk memproses ' . $payments->count() . ' payment.');
            return self::SUCCESS;
        }

        if (! $this->confirm('Lanjutkan membuat jurnal untuk ' . $payments->count() . ' payment?', true)) {
            $this->line('Dibatalkan.');
            return self::SUCCESS;
        }

        $success = 0;
        $failed  = 0;

        foreach ($payments as $payment) {
            $label = "Payment #{$payment->id} ({$payment->asesmen->full_name})";

            try {
                // 1. Buat jurnal piutang jika belum ada
                $piutangKey = Payment::class . '_piutang';
                if (! JournalEntry::existsFor($piutangKey, $payment->id)) {
                    $journalService->jurnalPiutangAsesi($payment);
                    $this->line("  ✔ {$label} — jurnal piutang dibuat");
                } else {
                    $this->line("  – {$label} — jurnal piutang sudah ada, dilewati");
                }

                // 2. Buat jurnal pelunasan (kas masuk)
                $journalService->jurnalPiutangLunas($payment);
                $this->info("  ✔ {$label} — jurnal pelunasan dibuat");

                $success++;
            } catch (\Throwable $e) {
                $this->error("  ✘ {$label} — GAGAL: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Selesai. Berhasil: {$success} | Gagal: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
