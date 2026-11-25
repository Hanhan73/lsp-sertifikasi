<?php
/**
 * Verifikasi Laporan Rekap Asesi.
 * Jalankan:  php artisan tinker --execute="require base_path('rekap_asesi_check.php');"
 *
 * Menghitung ulang rekap secara INDEPENDEN (tarik data mentah per asesi,
 * klasifikasi di PHP) lalu membandingkannya dengan RekapAsesiController::buildRekap().
 * Ubah $filters di bawah untuk mencocokkan dengan filter di halaman admin.
 */

use Illuminate\Support\Facades\DB;

$filters = [
    'dari'     => null,   // contoh '2026-01-01'
    'sampai'   => null,   // contoh '2026-12-31'
    'skema_id' => null,
    'tuk_id'   => null,
];

// ── 1. Data mentah per asesi ────────────────────────────────────────────
$rows = DB::select("
    SELECT a.id, a.full_name, a.result, a.skema_id, sk.name AS skema,
           a.collective_batch_id, a.institution, s.institution_name,
           COALESCE(a.assigned_tuk_id, a.tuk_id, s.tuk_id) AS tuk_id,
           s.asesor_id, x.nama AS asesor, s.assessment_date,
           COALESCE(ua.is_dummy,0) AS d_asesi, COALESCE(ut.is_dummy,0) AS d_tuk, COALESCE(ux.is_dummy,0) AS d_asesor,
           (SELECT b.rekomendasi FROM berita_acara_asesi b WHERE b.asesmen_id = a.id ORDER BY b.id DESC LIMIT 1) AS rek_ba
    FROM asesmens a
    LEFT JOIN schedules s ON s.id = a.schedule_id
    LEFT JOIN skemas sk   ON sk.id = a.skema_id
    LEFT JOIN users ua    ON ua.id = a.user_id
    LEFT JOIN tuks t      ON t.id = COALESCE(a.assigned_tuk_id, a.tuk_id, s.tuk_id)
    LEFT JOIN users ut    ON ut.id = t.user_id
    LEFT JOIN asesors x   ON x.id = s.asesor_id
    LEFT JOIN users ux    ON ux.id = x.user_id
");
$tukNames = DB::table('tuks')->pluck('name', 'id');

$all   = collect($rows);
$dummy = $all->filter(fn($r) => $r->d_asesi || $r->d_tuk || $r->d_asesor);
$data  = $all->reject(fn($r) => $r->d_asesi || $r->d_tuk || $r->d_asesor)
    ->filter(function ($r) use ($filters) {
        $d = $r->assessment_date ? substr($r->assessment_date, 0, 10) : null;
        if ($filters['dari']   && (!$d || $d < $filters['dari']))   return false;
        if ($filters['sampai'] && (!$d || $d > $filters['sampai'])) return false;
        if ($filters['skema_id'] && (int) $r->skema_id !== (int) $filters['skema_id']) return false;
        if ($filters['tuk_id']   && (int) $r->tuk_id   !== (int) $filters['tuk_id'])   return false;
        return true;
    })
    ->map(function ($r) use ($tukNames) {
        $r->hasil = match (true) {
            $r->result === 'kompeten'       => 'K',
            $r->result === 'belum_kompeten' => 'BK',
            strtoupper(trim((string) $r->rek_ba)) === 'K'  => 'K',
            strtoupper(trim((string) $r->rek_ba)) === 'BK' => 'BK',
            default => null,
        };
        $r->g_skema  = $r->skema ?? 'Tanpa Skema';
        $r->g_tuk    = $tukNames[$r->tuk_id] ?? 'Tanpa TUK';
        $r->g_bulan  = $r->assessment_date ? substr($r->assessment_date, 0, 7) : 'Belum Dijadwalkan';
        $r->g_asesor = $r->asesor ?? 'Belum Ada Asesor';
        return $r;
    });

$fallbackCount = $data->filter(fn($r) => $r->result === null && $r->hasil !== null)->count();

echo "\n=== REKAP ASESI (independen) ===\n";
echo "Filter      : " . json_encode($filters) . "\n";
echo "Total asesmen di DB : {$all->count()} | dummy dikecualikan: {$dummy->count()} | dihitung: {$data->count()}\n";
echo "Hasil dari fallback rekomendasi BA (result null): {$fallbackCount}\n";

$summ = function ($c) {
    return [
        'daftar'   => $c->count(),
        'kompeten' => $c->where('hasil', 'K')->count(),
        'bk'       => $c->where('hasil', 'BK')->count(),
        'belum'    => $c->whereNull('hasil')->count(),
        'asesor'   => $c->pluck('asesor_id')->filter()->unique()->count(),
    ];
};

$independent = [];
foreach (['skema' => 'g_skema', 'tuk' => 'g_tuk', 'bulan' => 'g_bulan', 'asesor' => 'g_asesor'] as $tab => $key) {
    echo "\n--- Per " . strtoupper($tab) . " ---\n";
    printf("%-50s %7s %7s %7s %7s %7s\n", 'Kelompok', 'Daftar', 'K', 'BK', 'Belum', 'Asesor');
    $groups = $data->groupBy($key)->sortKeys();
    $sumDaftar = 0;
    foreach ($groups as $label => $g) {
        $s = $summ($g);
        $sumDaftar += $s['daftar'];
        $independent[$tab][(string) $label] = $s;
        printf("%-50s %7d %7d %7d %7d %7s\n", mb_strimwidth((string) $label, 0, 50), $s['daftar'], $s['kompeten'], $s['bk'], $s['belum'], $tab === 'asesor' ? '-' : $s['asesor']);
    }
    $t = $summ($data);
    printf("%-50s %7d %7d %7d %7d %7s\n", 'TOTAL', $t['daftar'], $t['kompeten'], $t['bk'], $t['belum'], $tab === 'asesor' ? '-' : $t['asesor']);
    echo "Cek: jumlah Daftar per baris = {$sumDaftar} " . ($sumDaftar === $t['daftar'] ? 'OK' : 'SELISIH!') . "\n";
}

$t = $summ($data);
echo "\nCek: Daftar ({$t['daftar']}) = K + BK + Belum (" . ($t['kompeten'] + $t['bk'] + $t['belum']) . ") → "
    . ($t['daftar'] === $t['kompeten'] + $t['bk'] + $t['belum'] ? 'OK' : 'SELISIH!') . "\n";

// ── 2. Bandingkan dengan controller ─────────────────────────────────────
$ctrl  = app(\App\Http\Controllers\Admin\RekapAsesiController::class)->buildRekap($filters);
$diffs = [];
foreach ($ctrl['rows'] as $tab => $list) {
    $ctrlSum = 0;
    foreach ($list as $r) {
        $ctrlSum += $r['daftar'];
    }
    if ($ctrlSum !== $t['daftar']) $diffs[] = "Tab {$tab}: total daftar controller {$ctrlSum} ≠ {$t['daftar']}";
    if (count($list) !== count($independent[$tab] ?? [])) $diffs[] = "Tab {$tab}: jumlah baris controller " . count($list) . " ≠ " . count($independent[$tab] ?? []);
}
foreach (['daftar', 'kompeten', 'bk', 'belum', 'asesor'] as $k) {
    if ($ctrl['total'][$k] !== $t[$k]) $diffs[] = "Total {$k}: controller {$ctrl['total'][$k]} ≠ independen {$t[$k]}";
}
echo "\n=== Perbandingan dengan controller: " . (empty($diffs) ? 'SEMUA COCOK' : 'ADA SELISIH') . " ===\n";
foreach ($diffs as $d) echo " - {$d}\n";

// ── 3. Detail batch: total batch per baris harus = angka Daftar baris ──
$ctrlObj = app(\App\Http\Controllers\Admin\RekapAsesiController::class);
$bad = [];
$nRows = 0;
$nBatch = 0;
foreach ($ctrl['rows'] as $tab => $list) {
    foreach ($list as $r) {
        $nRows++;
        $params = array_filter($filters, fn($v) => $v !== null) + ['bagian' => $tab, 'key' => $r['key']];
        $json = $ctrlObj->detail(\Illuminate\Http\Request::create('/', 'GET', $params))->getData(true);
        $nBatch += count(array_filter($json['batches'], fn($b) => $b['batch_id'] !== null));
        foreach (['daftar', 'kompeten', 'bk', 'belum'] as $k) {
            if ($json['total'][$k] !== $r[$k]) {
                $bad[] = "{$tab} / {$r['label']}: {$k} baris {$r[$k]} ≠ total batch {$json['total'][$k]}";
            }
        }
    }
}
echo "=== Detail batch ({$nRows} baris dicek, {$nBatch} batch): " . (empty($bad) ? 'SEMUA COCOK' : 'ADA SELISIH') . " ===\n";
foreach (array_slice($bad, 0, 20) as $d) echo " - {$d}\n";

// ── 4. Halaman Rekap Per Batch: angka & asal lembaga per batch ──────────
$mode = function ($values) {
    $c = collect($values)->map(fn($v) => trim((string) $v))->filter()->countBy();
    if ($c->isEmpty()) return null;
    $max = $c->max();
    return $c->filter(fn($n) => $n === $max)->keys()->sort()->first(); // seri → alfabetis
};
$kolektif = $data->filter(fn($r) => $r->collective_batch_id !== null);
$byBatch  = $kolektif->groupBy('collective_batch_id');
// Batch selesai = semua asesi batch (dalam filter) sudah punya hasil K/BK
$selesai  = $byBatch->map(fn($rows) => $rows->every(fn($r) => $r->hasil !== null));
$groupKey = [
    'skema'  => fn($r) => $r->skema_id === null ? '_null' : (string) $r->skema_id,
    'tuk'    => fn($r) => $r->tuk_id === null ? '_null' : (string) $r->tuk_id,
    'bulan'  => fn($r) => $r->assessment_date ? substr($r->assessment_date, 0, 7) : '_null',
    'asesor' => fn($r) => $r->asesor_id === null ? '_null' : (string) $r->asesor_id,
];
$batchSumm = function ($rows) use ($selesai) {
    $ids = $rows->pluck('collective_batch_id')->unique();
    return [
        'n_batch' => $ids->count(),
        'selesai' => $ids->filter(fn($id) => $selesai[$id])->count(),
        'proses'  => $ids->reject(fn($id) => $selesai[$id])->count(),
        'asesi'   => $rows->count(),
        'asesor'  => $rows->pluck('asesor_id')->filter()->unique()->count(),
    ];
};
$batchCtrl = app(\App\Http\Controllers\Admin\RekapBatchController::class);
$pageB = $batchCtrl->buildBatchSummary($filters);
$badB  = [];

$tot = $batchSumm($kolektif);
foreach (['n_batch', 'selesai', 'proses', 'asesi', 'asesor'] as $k) {
    if ($pageB['total'][$k] !== $tot[$k]) $badB[] = "Total {$k}: halaman {$pageB['total'][$k]} ≠ independen {$tot[$k]}";
}
if ($pageB['total']['asesi'] + $pageB['total']['mandiri'] !== $t['daftar']) {
    $badB[] = "Asesi batch + mandiri ≠ total daftar {$t['daftar']}";
}
echo "\n--- Rekap Per Batch: TOTAL {$tot['n_batch']} batch ({$tot['selesai']} selesai, {$tot['proses']} masih proses), {$tot['asesi']} asesi dalam batch, {$pageB['total']['mandiri']} mandiri ---\n";

$lembagaSeen = [];
$srcCount = ['jadwal' => 0, 'asesi' => 0, 'kosong' => 0];
$nDetail = 0;
foreach ($pageB['rows'] as $tab => $groups) {
    $indepTab = $kolektif->groupBy($groupKey[$tab]);
    if (count($groups) !== $indepTab->count()) $badB[] = "Tab {$tab}: jumlah kelompok " . count($groups) . " ≠ independen {$indepTab->count()}";
    printf("\n%-50s %7s %8s %7s %7s\n", "Per " . strtoupper($tab), 'Batch', 'Selesai', 'Proses', 'Asesi');
    foreach ($groups as $g) {
        $s = $batchSumm($indepTab[$g['key']] ?? collect());
        printf("%-50s %7d %8d %7d %7d\n", mb_strimwidth($g['label'], 0, 50), $g['n_batch'], $g['selesai'], $g['proses'], $g['asesi']);
        foreach (['n_batch', 'selesai', 'proses', 'asesi', 'asesor'] as $k) {
            if ($tab === 'asesor' && $k === 'asesor') continue;
            if ($s[$k] !== $g[$k]) $badB[] = "Tab {$tab} / {$g['label']}: {$k} {$g[$k]} ≠ independen {$s[$k]}";
        }
        // isi modal detail kelompok ini
        $params = array_filter($filters, fn($v) => $v !== null) + ['bagian' => $tab, 'key' => $g['key']];
        $json = $batchCtrl->detail(\Illuminate\Http\Request::create('/', 'GET', $params))->getData(true);
        $nDetail++;
        if ($json['total']['n_batch'] !== $g['n_batch']) $badB[] = "Detail {$tab} / {$g['label']}: {$json['total']['n_batch']} batch ≠ {$g['n_batch']}";
        if ($json['total']['daftar'] !== $g['asesi'])    $badB[] = "Detail {$tab} / {$g['label']}: {$json['total']['daftar']} asesi ≠ {$g['asesi']}";
        foreach ($json['batches'] as $b) {
            if ($b['selesai'] !== $selesai[$b['batch_id']]) $badB[] = "Detail {$b['batch_id']}: status selesai salah";
            if (!isset($lembagaSeen[$b['batch_id']])) {
                $lembagaSeen[$b['batch_id']] = true;
                $all = $byBatch[$b['batch_id']];
                $exp = $mode($all->pluck('institution_name'))
                    ?? $mode($all->pluck('institution')->map(fn($v) => mb_strtoupper(trim((string) $v))))
                    ?? '-';
                if ($exp !== $b['lembaga']) $badB[] = "{$b['batch_id']}: lembaga '{$b['lembaga']}' ≠ independen '{$exp}'";
                $srcCount[$b['lembaga_src'] ?? 'kosong']++;
            }
        }
    }
}
echo "\n=== Rekap Per Batch ({$nDetail} kelompok & modal detail dicek; asal lembaga dari jadwal: {$srcCount['jadwal']}, dari isian asesi: {$srcCount['asesi']}, kosong: {$srcCount['kosong']}): "
    . (empty($badB) ? 'SEMUA COCOK' : 'ADA SELISIH') . " ===\n";
foreach (array_slice($badB, 0, 20) as $d) echo " - {$d}\n";
echo "\n";
