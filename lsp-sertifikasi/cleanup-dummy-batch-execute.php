<?php
/**
 * EKSEKUSI — hapus batch dummy/uji coba beserta seluruh data terkait.
 * Dibungkus transaction: kalau ada error di tengah, semua di-rollback otomatis.
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

$batchIdsToDelete = [
    'DUMMY-BATCH-TUK-001-OQ8YGT',
    'BATCH-SK-DEMO-20260615151013',
    'BATCH-SK-DEMO-20260615110025',
    'UJI-COBA-SKEMA-PENGELOLAAN-ADM-KANTOR-TUK-003-YM7OZQ',
    'UJI-COBA-AZIZ-TUK-003-AGJT8Y',
    'TESTING-DUMMY-TUK-003-JZLVKB',
    'UJI-COBA-SEKRETRATIS-PERUSAHAAN-TUK-006-BUTN1W',
    'UJI-COBA-PENGELOLAAN-ADM-PERKANTORAN-TUK-000-FUDXPL',
    'UJI-COBA-STAF-ADMINISTRASI-TUK-000-PZNW8O',
    'UJI-COBA-SKEMA-SEKRETARIS-TUK-000-URDXHZ',
];

echo "=== MULAI PENGHAPUSAN BATCH DUMMY/UJI COBA ===\n\n";

DB::beginTransaction();

try {
    $totalAsesiDeleted = 0;
    $totalUserDeleted = 0;

    foreach ($batchIdsToDelete as $batchId) {
        $asesmens = \App\Models\Asesmen::where('collective_batch_id', $batchId)->get();

        if ($asesmens->isEmpty()) {
            echo "⚠️  Batch '{$batchId}' tidak ditemukan, dilewati.\n";
            continue;
        }

        echo "Menghapus batch: {$batchId} ({$asesmens->count()} asesi)...\n";

        $scheduleIds = $asesmens->pluck('schedule_id')->filter()->unique();

        foreach ($asesmens as $asesmen) {
            // ── Hapus file-file upload terkait asesi ini ──
            foreach (['photo_path', 'ktp_path', 'document_path', 'pre_assessment_file', 'physical_certificate_path'] as $col) {
                if ($asesmen->$col) {
                    Storage::disk('public_html')->delete($asesmen->$col);
                    Storage::disk('public')->delete($asesmen->$col); // jaga-jaga kalau ada yang di disk lama
                }
            }

            // ── Hapus dokumen turunan (APL-01/02, FR.AK, dsb) ──
            if ($asesmen->aplsatu) {
                $asesmen->aplsatu->buktiKelengkapan()->delete();
                $asesmen->aplsatu->delete();
            }
            if ($asesmen->apldua) {
                $asesmen->apldua->jawabans()->delete();
                $asesmen->apldua->delete();
            }
            $asesmen->frak01?->delete();
            $asesmen->frak04?->delete();
            $asesmen->frAk03?->delete();
            $asesmen->soalTeoriAsesi()->delete();
            $asesmen->jawabanObservasi()->delete();
            $asesmen->payments()->delete();
            $asesmen->certificate?->delete();

            // ── Hapus BeritaAcaraAsesi milik asesi ini ──
            \App\Models\BeritaAcaraAsesi::where('asesmen_id', $asesmen->id)->delete();

            // ── Hapus user login-nya (akun dummy, aman dihapus) ──
            $user = $asesmen->user;
            $asesmen->delete();
            if ($user) {
                $user->delete();
                $totalUserDeleted++;
            }

            $totalAsesiDeleted++;
        }

        // ── Hapus SK Hasil Ujikom kalau ada untuk batch ini ──
        $sk = \App\Models\SkHasilUjikom::where('collective_batch_id', $batchId)->first();
        if ($sk) {
            if ($sk->sk_path) {
                Storage::disk('private')->delete($sk->sk_path);
            }
            $sk->delete();
            echo "  → SK Hasil Ujikom '{$sk->nomor_sk}' ikut dihapus.\n";
        }

        // ── Hapus Schedule & BeritaAcara HANYA jika tidak dipakai batch lain ──
        foreach ($scheduleIds as $sid) {
            $stillUsed = \App\Models\Asesmen::where('schedule_id', $sid)->exists();
            if (!$stillUsed) {
                $schedule = \App\Models\Schedule::find($sid);
                if ($schedule) {
                    $ba = $schedule->beritaAcara;
                    if ($ba) {
                        if ($ba->file_path) {
                            Storage::disk('private')->delete($ba->file_path);
                        }
                        $ba->asesis()->delete();
                        $ba->delete();
                    }
                    $schedule->delete();
                    echo "  → Schedule #{$sid} (dan Berita Acara-nya) ikut dihapus karena tidak dipakai batch lain.\n";
                }
            } else {
                echo "  → Schedule #{$sid} DIPERTAHANKAN (masih dipakai batch/asesi lain).\n";
            }
        }

        echo "  ✅ Batch {$batchId} selesai dihapus.\n\n";
    }

    DB::commit();

    Log::info("[CLEANUP] Admin #" . (auth()->id() ?? 'tinker') . " menghapus " . count($batchIdsToDelete) . " batch dummy/uji coba. Total {$totalAsesiDeleted} asesi, {$totalUserDeleted} user.");

    echo "=== SELESAI ===\n";
    echo "Total asesi dihapus: {$totalAsesiDeleted}\n";
    echo "Total user dihapus : {$totalUserDeleted}\n";
    echo "Semua perubahan sudah di-COMMIT (permanen).\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ GAGAL — semua perubahan di-ROLLBACK.\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}