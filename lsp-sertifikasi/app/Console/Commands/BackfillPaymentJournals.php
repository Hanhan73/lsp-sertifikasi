<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\User;
use App\Services\JournalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * Cara pakai:
 *   php artisan journal:backfill-payments --dry-run
 *   php artisan journal:backfill-payments
 *   php artisan journal:backfill-payments --id=14
 */
class BackfillPaymentJournals extends Command
{
    protected $signature   = 'journal:backfill-payments
                                {--dry-run : Preview tanpa menyimpan}
                                {--id=     : Proses satu payment saja}';

    protected $description = 'Buat journal_entry yang hilang untuk payment mandiri yang sudah verified';

    public function handle(JournalService $journalService): int
    {
        $isDry  = $this->option('dry-run');
        $onlyId = $this->option('id');

        // Login sebagai bendahara/admin agar created_by tidak null
        $actor = User::whereHas('roles', fn($q) => $q->whereIn('name', ['bendahara', 'admin']))->first()
            ?? User::first();

        if (! $actor) {
            $this->error('Tidak ada user di database.');
            return self::FAILURE;
        }

        Auth::login($actor);
        $this->line("Menjalankan sebagai: {$actor->name} (ID: {$actor->id})");
        $this->info($isDry ? '🔍 DRY RUN — tidak ada data yang disimpan' : '🚀 Memproses backfill...');
        $this->newLine();

        $query = Payment::with(['asesmen.skema'])
            ->where('status', 'verified')
            ->whereNotNull('verified_at');

        if ($onlyId) {
            $query->where('id', (int) $onlyId);
        }

        $payments = $query->get()->filter(fn(Payment $p) =>
            ! JournalEntry::where('ref_type', Payment::class)->where('ref_id', $p->id)->exists()
        );

        if ($payments->isEmpty()) {
            $this->info('✅ Semua payment sudah punya jurnal.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Nama Asesi', 'Amount', 'Verified At'],
            $payments->map(fn($p) => [
                $p->id,
                $p->asesmen->full_name ?? '-',
                'Rp ' . number_format($p->amount, 0, ',', '.'),
                $p->verified_at?->format('d/m/Y H:i'),
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
                // 1. Jurnal piutang (jika belum ada)
                $piutangKey = Payment::class . '_piutang';
                if (! JournalEntry::existsFor($piutangKey, $payment->id)) {
                    $journalService->jurnalPiutangAsesi($payment->fresh(['asesmen.skema']));
                    $this->line("  ✔ {$label} — jurnal piutang dibuat");
                } else {
                    $this->line("  – {$label} — jurnal piutang sudah ada, dilewati");
                }

                // 2. Jurnal pelunasan
                $journalService->jurnalPiutangLunas($payment->fresh(['asesmen.skema']));
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