<?php
/**
 * DRY RUN — cek dampak penghapusan batch dummy/uji coba.
 * TIDAK menghapus apapun, cuma menampilkan apa yang akan terjadi.
 */

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

echo "=== DRY RUN — CEK DAMPAK PENGHAPUSAN BATCH DUMMY/UJI COBA ===\n\n";

$totalAsesi = 0;
$totalUser = 0;

foreach ($batchIdsToDelete as $batchId) {
    $asesmens = \App\Models\Asesmen::where('collective_batch_id', $batchId)->get();

    if ($asesmens->isEmpty()) {
        echo "⚠️  Batch '{$batchId}' TIDAK DITEMUKAN (mungkin sudah dihapus / typo)\n\n";
        continue;
    }

    $userIds = $asesmens->pluck('user_id')->filter();
    $scheduleIds = $asesmens->pluck('schedule_id')->filter()->unique();

    $skUjikom = \App\Models\SkHasilUjikom::where('collective_batch_id', $batchId)->first();
    $beritaAcaraCount = \App\Models\BeritaAcara::whereIn('schedule_id', $scheduleIds)->count();

    echo "Batch: {$batchId}\n";
    echo "  Total asesi          : {$asesmens->count()}\n";
    echo "  Total user terkait   : {$userIds->count()}\n";
    echo "  Total schedule terkait: {$scheduleIds->count()}\n";
    echo "  Ada SK Hasil Ujikom  : " . ($skUjikom ? "YA ({$skUjikom->nomor_sk})" : 'TIDAK') . "\n";
    echo "  Ada Berita Acara     : {$beritaAcaraCount}\n";

    // Cek apakah schedule dipakai batch lain juga (jangan sampai kehapus punya orang lain)
    foreach ($scheduleIds as $sid) {
        $usedByOther = \App\Models\Asesmen::where('schedule_id', $sid)
            ->where('collective_batch_id', '!=', $batchId)
            ->exists();
        if ($usedByOther) {
            echo "  ⚠️  PERINGATAN: schedule #{$sid} juga dipakai batch lain — TIDAK akan dihapus schedule-nya\n";
        }
    }

    echo "\n";

    $totalAsesi += $asesmens->count();
    $totalUser += $userIds->count();
}

echo "=== RINGKASAN ===\n";
echo "Total asesi yang akan dihapus     : {$totalAsesi}\n";
echo "Total akun user yang akan dihapus : {$totalUser}\n";
echo "\nTidak ada perubahan data. Jalankan versi eksekusi setelah yakin.\n";