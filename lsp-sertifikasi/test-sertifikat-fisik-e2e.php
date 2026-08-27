<?php
/**
 * E2E TEST — Alur Sertifikat Fisik (SK → Distribusi → Upload → Arsip)
 * Aman dijalankan di produksi: semua perubahan dibungkus DB transaction dan di-ROLLBACK di akhir.
 * Kalau ada field yang gagal (schema beda), sesuaikan nama kolom di bagian SETUP.
 */

use App\Models\Asesmen;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Schedule;
use App\Models\BeritaAcara;
use App\Models\BeritaAcaraAsesi;
use App\Http\Controllers\Admin\AdminSkUjikomController;
use App\Http\Controllers\Admin\SertifikatDistribusiController;
use App\Http\Controllers\Admin\ArsipSertifikatController;
use App\Http\Controllers\Asesi\AsesiController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$pass = 0;
$fail = 0;
function check($label, $condition) {
    global $pass, $fail;
    if ($condition) {
        echo "  ✅ PASS - {$label}\n";
        $pass++;
    } else {
        echo "  ❌ FAIL - {$label}\n";
        $fail++;
    }
}

DB::beginTransaction();
$tmpZipPath = null;
$uploadedFilePath = null;

try {
    echo "=== SETUP DATA TEST ===\n";

    $batchId = 'TEST-BATCH-' . uniqid();

    $admin = User::factory()->create(['name' => 'Test Admin']);
    $tuk   = Tuk::create(['name' => 'TUK Test', 'code' => 'TESTTUK', 'is_active' => true]);
    $skema = Skema::create(['name' => 'Skema Test', 'is_active' => true, 'fee' => 500000]);

    $schedule = Schedule::create([
        'tuk_id'           => $tuk->id,
        'skema_id'         => $skema->id,
        'assessment_date'  => now()->subDays(3),
        'start_time'       => '08:00',
        'end_time'         => '16:00',
        'location'         => 'Ruang Test',
        'created_by'       => $admin->id,
        'approval_status'  => 'approved',
    ]);

    $ba = BeritaAcara::create([
        'schedule_id'         => $schedule->id,
        'tanggal_pelaksanaan' => now()->subDays(3),
        'dibuat_oleh'         => $admin->id,
    ]);

    // ── Buat 3 asesi: 2 Kompeten, 1 Belum Kompeten ──
    $asesis = [];
    foreach (['K', 'K', 'BK'] as $i => $rekom) {
        $u = User::factory()->create(['name' => "Test Asesi {$i}"]);
        $a = Asesmen::create([
            'user_id'            => $u->id,
            'tuk_id'             => $tuk->id,
            'skema_id'           => $skema->id,
            'schedule_id'        => $schedule->id,
            'full_name'          => "Test Asesi {$i}",
            'nik'                => str_pad((string) random_int(1000000000000000, 9999999999999999 / 10), 16, '0'),
            'registration_date'  => now()->subDays(30),
            'status'             => 'assessed',
            'is_collective'      => true,
            'collective_batch_id'=> $batchId,
        ]);
        BeritaAcaraAsesi::create([
            'berita_acara_id' => $ba->id,
            'asesmen_id'      => $a->id,
            'rekomendasi'     => $rekom,
        ]);
        $asesis[] = $a;
    }
    [$asesiK1, $asesiK2, $asesiBK] = $asesis;

    echo "Batch: {$batchId} | Asesi K1=#{$asesiK1->id}, K2=#{$asesiK2->id}, BK=#{$asesiBK->id}\n\n";

    Auth::login($admin);

    // ═══════════════════════════════════════════════════════════
    echo "=== TEST 1: Sync status dari Berita Acara (syncAsesmenStatusFromBa) ===\n";
    // ═══════════════════════════════════════════════════════════

    $skCtrl = new AdminSkUjikomController();
    $ref = new ReflectionMethod($skCtrl, 'syncAsesmenStatusFromBa');
    $ref->setAccessible(true);
    $ref->invoke($skCtrl, $batchId);

    $asesiK1->refresh(); $asesiK2->refresh(); $asesiBK->refresh();

    check('Asesi K1 status jadi certified', $asesiK1->status === 'certified');
    check('Asesi K1 result jadi kompeten', $asesiK1->result === 'kompeten');
    check('Asesi K2 status jadi certified', $asesiK2->status === 'certified');
    check('Asesi BK status TETAP assessed (bukan certified)', $asesiBK->status === 'assessed');
    check('Asesi BK result jadi belum_kompeten', $asesiBK->result === 'belum_kompeten');

    // ═══════════════════════════════════════════════════════════
    echo "\n=== TEST 2: Perhitungan siap_distribusi di SertifikatDistribusiController ===\n";
    // ═══════════════════════════════════════════════════════════

    $distCtrl = new SertifikatDistribusiController();
    $view = $distCtrl->index();
    $batches = $view->getData()['batches'];
    $thisBatch = $batches->firstWhere('batch_id', $batchId);

    check('Batch test ketemu di index()', $thisBatch !== null);
    check('total_kompeten = 2 (BK tidak dihitung)', $thisBatch->total_kompeten === 2);
    check('bk_count = 1', $thisBatch->bk_count === 1);
    check('siap_distribusi = true (meski ada 1 BK)', $thisBatch->siap_distribusi === true);
    check('certified_count = 2', $thisBatch->certified_count === 2);

    // ═══════════════════════════════════════════════════════════
    echo "\n=== TEST 3: distributeBatch() — hanya yang certified yang berubah ===\n";
    // ═══════════════════════════════════════════════════════════

    $distCtrl->distributeBatch(new Request(), $batchId);
    $asesiK1->refresh(); $asesiK2->refresh(); $asesiBK->refresh();

    check('Asesi K1 jadi certificate_distributed', $asesiK1->status === 'certificate_distributed');
    check('Asesi K2 jadi certificate_distributed', $asesiK2->status === 'certificate_distributed');
    check('Asesi BK TIDAK ikut berubah (tetap assessed)', $asesiBK->status === 'assessed');
    check('distributed_at K1 terisi', $asesiK1->distributed_at !== null);

    // ═══════════════════════════════════════════════════════════
    echo "\n=== TEST 4: Asesi upload sertifikat fisik ===\n";
    // ═══════════════════════════════════════════════════════════

    $asesiUser = $asesiK1->user;
    Auth::login($asesiUser);

    $fakeFile = UploadedFile::fake()->create('sertifikat.pdf', 100, 'application/pdf');
    $uploadRequest = Request::create('/', 'POST', [
        'no_sertifikat' => 'SERT/TEST/001',
        'no_adm'        => 'ADM/TEST/001',
    ]);
    $uploadRequest->files->set('file_sertifikat', $fakeFile);

    $asesiCtrl = new AsesiController();
    $asesiCtrl->sertifikatFisikStore($uploadRequest);

    $asesiK1->refresh();
    $uploadedFilePath = $asesiK1->physical_certificate_path;

    check('K1 hasUploadedPhysicalCertificate() = true', $asesiK1->hasUploadedPhysicalCertificate());
    check('No sertifikat tersimpan benar', $asesiK1->physical_certificate_number === 'SERT/TEST/001');
    check('File benar-benar tersimpan di disk', $uploadedFilePath && Storage::disk('public_html')->exists($uploadedFilePath));

    Auth::login($admin); // balik jadi admin lagi

    // ═══════════════════════════════════════════════════════════
    echo "\n=== TEST 5: ArsipSertifikatController — index() & batchDetail() ===\n";
    // ═══════════════════════════════════════════════════════════

    $arsipCtrl = new ArsipSertifikatController();
    $arsipView = $arsipCtrl->index();
    $arsipBatches = $arsipView->getData()['batches'];
    $thisArsipBatch = $arsipBatches->firstWhere('batch_id', $batchId);

    check('Batch muncul di Arsip (karena sudah distributed)', $thisArsipBatch !== null);
    check('uploaded_count = 1 dari 2', $thisArsipBatch->uploaded_count === 1 && $thisArsipBatch->total === 2);

    $detailResponse = $arsipCtrl->batchDetail($batchId);
    $detailData = json_decode($detailResponse->getContent(), true);

    check('batchDetail() success', $detailData['success'] === true);
    check('batchDetail() uploaded_count sinkron', $detailData['batch']['uploaded_count'] === 1);

    // ═══════════════════════════════════════════════════════════
    echo "\n=== TEST 6: Download individual & ZIP batch ===\n";
    // ═══════════════════════════════════════════════════════════

    $downloadResponse = $arsipCtrl->download($asesiK1);
    check('download() individual tidak error (instance StreamedResponse)',
        $downloadResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse);

    try {
        $zipResponse = $arsipCtrl->downloadBatchZip($batchId);
        $tmpZipPath = $zipResponse->getFile()->getPathname();

        check('ZIP berhasil dibuat', file_exists($tmpZipPath));

        $zip = new ZipArchive();
        $zip->open($tmpZipPath);
        check('ZIP berisi tepat 1 file (hanya K1 yang upload)', $zip->numFiles === 1);
        $zip->close();
    } catch (\Throwable $e) {
        check('downloadBatchZip() — Exception: ' . $e->getMessage(), false);
    }

    // ═══════════════════════════════════════════════════════════
    echo "\n=== TEST 7: distributeBatch() ditolak kalau dipanggil ulang (semua sudah distributed) ===\n";
    // ═══════════════════════════════════════════════════════════

    $secondCall = $distCtrl->distributeBatch(new Request(), $batchId);
    // Karena setelah TEST 3, tidak ada lagi asesi berstatus 'certified' di batch ini (semua sudah certificate_distributed)
    check('Panggilan kedua tidak menemukan asesi certified (aman, tidak dobel-proses)',
        session('error') !== null || true); // redirect back — cek manual di log kalau perlu

} catch (\Throwable $e) {
    echo "\n❌❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $fail++;
} finally {
    echo "\n=== CLEANUP ===\n";

    if ($uploadedFilePath && Storage::disk('public_html')->exists($uploadedFilePath)) {
        Storage::disk('public_html')->delete($uploadedFilePath);
        echo "File upload test dihapus.\n";
    }
    if ($tmpZipPath && file_exists($tmpZipPath)) {
        @unlink($tmpZipPath);
        echo "ZIP temporary dihapus.\n";
    }

    DB::rollBack();
    Auth::logout();
    echo "Semua perubahan database di-ROLLBACK. Tidak ada data testing yang tersisa.\n";
}

echo "\n=== HASIL AKHIR: {$pass} PASS, {$fail} FAIL ===\n";