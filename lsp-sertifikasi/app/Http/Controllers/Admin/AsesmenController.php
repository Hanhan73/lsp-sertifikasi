<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Tuk;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * AsesmenController
 *
 * Mengelola:
 *  - Daftar semua asesi (dengan tab Per TUK)
 *  - Detail satu asesi (halaman + AJAX)
 *  - Batch detail per TUK
 *  - Export biodata batch ke Excel
 *  - Input hasil asesmen
 *  - Generate sertifikat
 */
class AsesmenController extends Controller
{
    // =========================================================
    // DAFTAR SEMUA ASESI
    // =========================================================

    public function index()
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema', 'payment', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Untuk tab Per TUK: list semua TUK yang punya asesmen
        $tuks = Tuk::withCount('asesmens')
            ->has('asesmens')
            ->orderBy('name')
            ->get();

        return view('admin.asesi.index', compact('asesmens', 'tuks'));
    }

    // =========================================================
    // DETAIL ASESI
    // =========================================================

    public function show(Asesmen $asesmen)
    {
        $asesmen->load([
            'user',
            'tuk',
            'skema.unitKompetensis',
            'schedule.asesor',
            'registrar',
            'adminVerifier',
            'aplsatu.buktiKelengkapan',
            'apldua.jawabans.elemen',
            'frak01',
            'frak04',
            'certificate',
            'payment',
        ]);

        $batchMembers = null;
        if ($asesmen->is_collective && $asesmen->collective_batch_id) {
            $batchMembers = Asesmen::with(['user', 'aplsatu', 'apldua', 'frak01', 'frak04'])
                ->where('collective_batch_id', $asesmen->collective_batch_id)
                ->get();
        }

        return view('admin.asesmen.show', compact('asesmen', 'batchMembers'));
    }

    /**
     * JSON + rendered HTML untuk modal AJAX di halaman index.
     */
    public function detail(Asesmen $asesmen)
    {
        try {
            $asesmen->load([
                'user',
                'tuk',
                'assignedTuk',
                'assigner',
                'skema',
                'payment',
                'payments',
                'schedule',
                'certificate',
                'registrar',
                'assessorRegistrar',
                'tukVerifier',
                'assessor',
            ]);

            $html = view('admin.asesmens.partials.detail-modal', compact('asesmen'))->render();

            return response()->json(['success' => true, 'html' => $html]);

        } catch (\Exception $e) {
            Log::error('[ASESMEN][detail] ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // BATCH DETAIL (per TUK)
    // =========================================================

    /**
     * Halaman detail batch kolektif.
     * Menampilkan status dokumen per asesi + progress APL-01/02, FR.AK.01/04.
     * Tombol "Mulai Asesmen" hanya muncul jika semua member berstatus data_completed.
     */
    public function batchShow(string $batchId)
    {
        $asesmens = Asesmen::with([
            'user',
            'tuk',
            'skema',
            'aplsatu',
            'apldua',
            'frak01',
            'frak04',
            'registrar',
        ])
            ->where('collective_batch_id', $batchId)
            ->orderBy('full_name')
            ->get();

        abort_if($asesmens->isEmpty(), 404, 'Batch tidak ditemukan.');

        $firstBatch = $asesmens->first();

        // Cek apakah semua masih data_completed (boleh mulai asesmen)
        $allDataCompleted = $asesmens->every(fn($a) => $a->status === 'data_completed');

        // Apakah asesmen sudah dimulai (ada yang status != registered/data_completed)
        $asesmenStarted = $asesmens->contains(
            fn($a) => !in_array($a->status, ['registered', 'data_completed'])
        );

        // Progress dokumen
        $docProgress = [
            'apl01'  => $asesmens->filter(fn($a) => $a->aplsatu !== null)->count(),
            'apl02'  => $asesmens->filter(fn($a) => $a->apldua  !== null)->count(),
            'frak01' => $asesmens->filter(fn($a) => $a->frak01  !== null)->count(),
            'frak04' => $asesmens->filter(fn($a) => $a->frak04  !== null)->count(),
            'total'  => $asesmens->count(),
        ];

        $allDocsComplete = $docProgress['apl01']  === $docProgress['total']
                        && $docProgress['apl02']  === $docProgress['total']
                        && $docProgress['frak01'] === $docProgress['total'];

        return view('admin.asesi.batch-show', compact(
            'batchId',
            'asesmens',
            'firstBatch',
            'allDataCompleted',
            'asesmenStarted',
            'docProgress',
            'allDocsComplete'
        ));
    }

    // =========================================================
    // EXPORT BIODATA BATCH KE EXCEL
    // =========================================================

    /**
     * Export biodata semua peserta dalam satu batch ke file Excel (.xlsx).
     * Hanya data biodata awal (bukan APL-01).
     */
    public function exportBatchBiodata(string $batchId)
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema'])
            ->where('collective_batch_id', $batchId)
            ->orderBy('full_name')
            ->get();

        abort_if($asesmens->isEmpty(), 404, 'Batch tidak ditemukan.');

        // Kolom sesuai form complete-data asesi
        $headers = [
            'No',
            // Data Pribadi
            'Nama Lengkap',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Telepon/HP',
            'Email',
            'Alamat',
            'Kode Provinsi',
            'Kode Kota',
            // Data Pendidikan & Pekerjaan
            'Pendidikan Terakhir',
            'Pekerjaan',
            'Sumber Anggaran',
            'Asal Lembaga',
            // Data Sertifikasi
            'Skema Sertifikasi',
            'TUK',
            'Tanggal Pilihan',
            // Meta
            'Tanggal Daftar',
        ];

        $rows = [];
        foreach ($asesmens as $i => $a) {
            $rows[] = [
                $i + 1,
                // Data Pribadi
                $a->full_name ?? $a->user->name ?? '-',
                $a->nik ?? '-',
                $a->birth_place ?? '-',
                $a->birth_date ? $a->birth_date->format('d/m/Y') : '-',
                $a->gender === 'L' ? 'Laki-laki' : ($a->gender === 'P' ? 'Perempuan' : '-'),
                $a->phone ?? '-',
                $a->email ?? $a->user->email ?? '-',
                $a->address ?? '-',
                $a->province_code ?? '-',
                $a->city_code ?? '-',
                // Data Pendidikan & Pekerjaan
                $a->education ?? '-',
                $a->occupation ?? '-',
                $a->budget_source ?? '-',
                $a->institution ?? '-',
                // Data Sertifikasi
                $a->skema->name ?? '-',
                $a->tuk->name ?? '-',
                $a->preferred_date ? $a->preferred_date->format('d/m/Y') : '-',
                // Meta
                $a->registration_date ? $a->registration_date->format('d/m/Y') : '-',
            ];
        }

        // Gunakan PhpSpreadsheet jika tersedia, fallback ke CSV
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return $this->exportAsXlsx($batchId, $headers, $rows);
        }

        return $this->exportAsCsv($batchId, $headers, $rows);
    }

    private function exportAsXlsx(string $batchId, array $headers, array $rows)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Biodata Peserta');

        // Header row
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);

        // Data rows
        foreach ($rows as $i => $row) {
            $sheet->fromArray($row, null, 'A' . ($i + 2));
        }

        // Auto-width kolom
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze row pertama
        $sheet->freezePane('A2');

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'biodata_batch_' . $batchId . '_' . date('Ymd') . '.xlsx';
        $tmpPath  = storage_path('app/tmp_' . $filename);

        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function exportAsCsv(string $batchId, array $headers, array $rows)
    {
        $filename = 'biodata_batch_' . $batchId . '_' . date('Ymd') . '.csv';

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // BOM untuk Excel agar baca UTF-8 dengan benar
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // =========================================================
    // INPUT HASIL ASESMEN
    // =========================================================

    public function assessments()
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema', 'schedule'])
            ->whereIn('status', ['pre_assessment_completed', 'scheduled'])
            ->get();

        return view('admin.asesmens.assessments', compact('asesmens'));
    }

    public function inputHasil(Request $request, Asesmen $asesmen)
    {
        $request->validate([
            'result'       => 'required|in:kompeten,belum_kompeten',
            'result_notes' => 'nullable|string',
        ]);

        $asesmen->update([
            'result'       => $request->result,
            'result_notes' => $request->result_notes,
            'assessed_by'  => auth()->id(),
            'assessed_at'  => now(),
            'status'       => 'assessed',
        ]);

        if ($request->result === 'kompeten') {
            $this->generateCertificate($asesmen);
        }

        return redirect()->route('admin.assessments')
            ->with('success', 'Hasil asesmen berhasil disimpan!');
    }

    // =========================================================
    // RENAME BATCH
    // =========================================================

    /**
     * Rename batch — update collective_batch_id semua member dalam batch.
     * Format baru: <NAMA_BARU>-<TUK_CODE>-<SUFFIX_LAMA>
     * Suffix (kode unik di ujung) tetap dipertahankan agar tidak ada duplikasi.
     */
    public function renameBatch(Request $request, string $batchId)
    {
        $request->validate([
            'batch_name' => 'required|string|max:50',
        ]);

        // Ambil semua asesmen dalam batch ini
        $asesmens = Asesmen::where('collective_batch_id', $batchId)->get();
        abort_if($asesmens->isEmpty(), 404, 'Batch tidak ditemukan.');

        // Ekstrak suffix (bagian terakhir setelah '-') agar suffix unik tetap dipakai
        // Format lama: NAMA-TUKCODE-SUFFIX6CHAR
        // Kita ambil 6 karakter terakhir sebagai suffix
        $suffix = substr($batchId, -6);
        // Ambil kode TUK dari asesmen
        $tukCode = $asesmens->first()->tuk->code ?? 'LSP';

        // Buat batch ID baru
        $newSlug    = strtoupper(\Illuminate\Support\Str::slug($request->batch_name, '-'));
        $newBatchId = $newSlug . '-' . strtoupper($tukCode) . '-' . strtoupper($suffix);

        // Cek tidak ada batch lain dengan ID yang sama
        if ($newBatchId !== $batchId && Asesmen::where('collective_batch_id', $newBatchId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama batch sudah digunakan. Silakan pilih nama lain.',
            ], 422);
        }

        // Update semua member batch
        \Illuminate\Support\Facades\DB::transaction(function () use ($asesmens, $newBatchId) {
            foreach ($asesmens as $asesmen) {
                $asesmen->update(['collective_batch_id' => $newBatchId]);
            }
        });

        \Illuminate\Support\Facades\Log::info(
            "Admin #{auth()->id()} renamed batch '{$batchId}' → '{$newBatchId}'"
        );

        return response()->json([
            'success'      => true,
            'message'      => 'Nama batch berhasil diubah.',
            'new_batch_id' => $newBatchId,
        ]);
    }

    // =========================================================
    // EXPORT SEMUA BIODATA ASESI
    // =========================================================

    /**
     * Export biodata semua asesi (atau hasil filter) ke Excel/CSV.
     *
     * Query params yang didukung:
     *   ?status=   → filter by status
     *   ?type=     → mandiri | collective
     *   ?tuk_id=   → filter by TUK
     *   ?skema_id= → filter by Skema
     */
    public function exportAllBiodata(Request $request)
    {
        $query = Asesmen::with(['user', 'tuk', 'skema'])
            ->orderBy('registration_date', 'desc');

        // Filter opsional via query string
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->input('type') === 'mandiri') {
            $query->where('is_collective', false);
        } elseif ($request->input('type') === 'collective') {
            $query->where('is_collective', true);
        }
        if ($request->filled('tuk_id')) {
            $query->where('tuk_id', $request->tuk_id);
        }
        if ($request->filled('skema_id')) {
            $query->where('skema_id', $request->skema_id);
        }

        $asesmens = $query->get();

        $headers = [
            'No',
            // Data Pribadi
            'Nama Lengkap',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Telepon/HP',
            'Email',
            'Alamat',
            'Kode Provinsi',
            'Kode Kota',
            // Pendidikan & Pekerjaan
            'Pendidikan Terakhir',
            'Pekerjaan',
            'Sumber Anggaran',
            'Asal Lembaga',
            // Sertifikasi
            'Skema Sertifikasi',
            'TUK',
            'Tanggal Pilihan',
            'Ikut Pelatihan',
            // Meta
            'Jenis Pendaftaran',
            'Batch ID',
            'Status',
            'Tanggal Daftar',
        ];

        $rows = [];
        foreach ($asesmens as $i => $a) {
            $rows[] = [
                $i + 1,
                $a->full_name ?? $a->user->name ?? '-',
                $a->nik ?? '-',
                $a->birth_place ?? '-',
                $a->birth_date ? $a->birth_date->format('d/m/Y') : '-',
                $a->gender === 'L' ? 'Laki-laki' : ($a->gender === 'P' ? 'Perempuan' : '-'),
                $a->phone ?? '-',
                $a->email ?? $a->user->email ?? '-',
                $a->address ?? '-',
                $a->province_code ?? '-',
                $a->city_code ?? '-',
                $a->education ?? '-',
                $a->occupation ?? '-',
                $a->budget_source ?? '-',
                $a->institution ?? '-',
                $a->skema->name ?? '-',
                $a->tuk->name ?? '-',
                $a->preferred_date ? $a->preferred_date->format('d/m/Y') : '-',
                $a->training_flag ? 'Ya' : 'Tidak',
                $a->is_collective ? 'Kolektif' : 'Mandiri',
                $a->collective_batch_id ?? '-',
                $a->status_label ?? $a->status,
                $a->registration_date ? $a->registration_date->format('d/m/Y') : '-',
            ];
        }

        $suffix   = date('Ymd_His');
        $filename = 'biodata_semua_asesi_' . $suffix;

        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return $this->exportAllAsXlsx($filename, $headers, $rows, $asesmens->count());
        }

        return $this->exportAllAsCsv($filename, $headers, $rows);
    }

    private function exportAllAsXlsx(string $filename, array $headers, array $rows, int $total)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Biodata Asesi');

        // Header row
        $sheet->fromArray($headers, null, 'A1');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Data rows
        foreach ($rows as $i => $row) {
            $sheet->fromArray($row, null, 'A' . ($i + 2));
        }

        // Zebra striping (opsional, tiap baris genap)
        for ($r = 2; $r <= $total + 1; $r += 2) {
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0F4FF']],
            ]);
        }

        // Auto-width
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A2');

        $writer  = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmpPath = storage_path('app/tmp_' . $filename . '.xlsx');
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function exportAllAsCsv(string $filename, array $headers, array $rows)
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }
    
    // =========================================================
    // PRIVATE — SERTIFIKAT
    // =========================================================

    private function generateCertificate(Asesmen $asesmen): void
    {
        $certNumber = Certificate::generateCertificateNumber();

        $pdf = Pdf::loadView('certificates.template', [
            'asesmen'            => $asesmen,
            'certificate_number' => $certNumber,
            'issued_date'        => now(),
        ]);

        $path = 'certificates/certificate_' . $asesmen->id . '_' . time() . '.pdf';
        Storage::put('public/' . $path, $pdf->output());

        Certificate::create([
            'asesmen_id'         => $asesmen->id,
            'certificate_number' => $certNumber,
            'issued_date'        => now(),
            'valid_until'        => now()->addYears(3),
            'pdf_path'           => $path,
            'generated_by'       => auth()->id(),
        ]);

        $asesmen->update(['status' => 'certified']);
    }



}