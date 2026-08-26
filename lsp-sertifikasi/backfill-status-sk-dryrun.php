<?php
// backfill-status-sk-dryrun.php
// DRY RUN — tidak melakukan update apapun ke database, cuma menampilkan apa yang AKAN terjadi.

$sks = \App\Models\SkHasilUjikom::where('status', 'approved')->get();

echo "=== DRY RUN — Total SK approved: {$sks->count()} ===\n\n";

$totalWouldUpdateK  = 0;
$totalWouldUpdateBk = 0;

foreach ($sks as $sk) {
    $batchId = $sk->collective_batch_id;

    $schedules = \App\Models\Schedule::whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
        ->pluck('id');

    $rekomendasiMap = \App\Models\BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $schedules))
        ->whereIn('rekomendasi', ['K', 'BK'])
        ->get()
        ->pluck('rekomendasi', 'asesmen_id');

    $kIds  = $rekomendasiMap->filter(fn($r) => $r === 'K')->keys();
    $bkIds = $rekomendasiMap->filter(fn($r) => $r === 'BK')->keys();

    // ── Hitung berapa yang SEBENARNYA akan berubah (bukan yang sudah sesuai) ──
    $kWouldUpdate = \App\Models\Asesmen::whereIn('id', $kIds)
        ->where('collective_batch_id', $batchId)
        ->where('status', '!=', 'certified')
        ->count(); // ← count() bukan update()

    $bkWouldUpdate = \App\Models\Asesmen::whereIn('id', $bkIds)
        ->where('collective_batch_id', $batchId)
        ->whereNotIn('status', ['certified', 'certificate_distributed'])
        ->count(); // ← count() bukan update()

    $totalWouldUpdateK  += $kWouldUpdate;
    $totalWouldUpdateBk += $bkWouldUpdate;

    echo "Batch {$batchId} (SK: {$sk->nomor_sk})\n";
    echo "  Total peserta K di BA : {$kIds->count()} | akan diupdate jadi 'certified' : {$kWouldUpdate}\n";
    echo "  Total peserta BK di BA: {$bkIds->count()} | akan diupdate jadi 'assessed' (BK): {$bkWouldUpdate}\n";
    echo "  ---\n";
}

echo "\n=== RINGKASAN DRY RUN ===\n";
echo "Total asesi yang AKAN diupdate jadi 'certified': {$totalWouldUpdateK}\n";
echo "Total asesi yang AKAN diupdate jadi 'assessed' (BK): {$totalWouldUpdateBk}\n";
echo "\nTidak ada perubahan data. Jalankan versi backfill asli untuk eksekusi beneran.\n";