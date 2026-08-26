<?php
// backfill-status-sk.php

$sks = \App\Models\SkHasilUjikom::where('status', 'approved')->get();

echo "Total SK approved: {$sks->count()}\n\n";

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

    $updatedK = 0;
    $updatedBk = 0;

    if ($kIds->isNotEmpty()) {
        $updatedK = \App\Models\Asesmen::whereIn('id', $kIds)
            ->where('collective_batch_id', $batchId)
            ->where('status', '!=', 'certified')
            ->update(['status' => 'certified', 'result' => 'kompeten']);
    }

    if ($bkIds->isNotEmpty()) {
        $updatedBk = \App\Models\Asesmen::whereIn('id', $bkIds)
            ->where('collective_batch_id', $batchId)
            ->whereNotIn('status', ['certified', 'certificate_distributed'])
            ->update(['status' => 'assessed', 'result' => 'belum_kompeten']);
    }

    echo "Batch {$batchId} (SK: {$sk->nomor_sk}) → K updated: {$updatedK}, BK updated: {$updatedBk}\n";
}

echo "\nSelesai.\n";