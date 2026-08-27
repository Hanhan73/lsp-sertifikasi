<?php
// cek-duplikat-asesmen.php

$namaDicari = 'Asesi Kelompok A 1';

$asesmens = \App\Models\Asesmen::where('full_name', $namaDicari)->get();

echo "Total row Asesmen dengan nama '{$namaDicari}': {$asesmens->count()}\n\n";

foreach ($asesmens as $a) {
    echo "ID #{$a->id} | user_id={$a->user_id} | status={$a->status} | batch={$a->collective_batch_id} | created_at={$a->created_at}\n";
}

echo "\n--- Cek per user_id ---\n";
if ($asesmens->isNotEmpty()) {
    $userId = $asesmens->first()->user_id;
    $semuaUntukUser = \App\Models\Asesmen::where('user_id', $userId)->get();
    echo "Total row Asesmen untuk user_id={$userId}: {$semuaUntukUser->count()}\n";
    foreach ($semuaUntukUser as $a) {
        echo "  ID #{$a->id} | status={$a->status} | batch={$a->collective_batch_id}\n";
    }

    echo "\n--- Ini yang diambil dashboard() (first() tanpa order) ---\n";
    $dashboardResult = \App\Models\Asesmen::where('user_id', $userId)->first();
    echo "Yang ke-load di dashboard: ID #{$dashboardResult->id} | status={$dashboardResult->status}\n";
}