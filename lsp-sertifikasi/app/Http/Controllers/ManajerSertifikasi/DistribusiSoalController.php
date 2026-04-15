<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\DistribusiPortofolio;
use App\Models\DistribusiSoalObservasi;
use App\Models\DistribusiSoalTeori;
use App\Models\BeritaAcara;
use App\Models\BeritaAcaraAsesi;
use App\Models\HasilObservasi;
use App\Models\HasilPortofolio;
use App\Models\Schedule;
use App\Models\PaketSoalObservasi;
use App\Models\Portofolio;
use App\Models\Skema;
use App\Models\SoalObservasi;
use App\Models\SoalTeori;
use App\Models\SoalTeoriAsesi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;


class DistribusiSoalController extends Controller
{
    // =========================================================================
    // BANK SOAL — INDEX PER SKEMA
    // =========================================================================

    public function indexBankSoal(): View
    {
        $skemas = Skema::where('is_active', true)->orderBy('id')->get();

        $stats = [];
        foreach ($skemas as $skema) {
            $stats[$skema->id] = [
                'observasi'  => SoalObservasi::where('skema_id', $skema->id)->count(),
                'teori'      => SoalTeori::where('skema_id', $skema->id)->count(),
                'portofolio' => Portofolio::where('skema_id', $skema->id)->count(),
            ];
        }

        return view('manajer-sertifikasi.bank-soal.index', compact('skemas', 'stats'));
    }

    public function showBankSoal(Request $request, Skema $skema): View
    {
        $soalObservasi = SoalObservasi::with('paket')
            ->where('skema_id', $skema->id)
            ->get();

        $soalTeori = SoalTeori::where('skema_id', $skema->id)
            ->latest()
            ->paginate(20);

        $jumlahTeori = SoalTeori::where('skema_id', $skema->id)->count();

        $portofolios = Portofolio::where('skema_id', $skema->id)
            ->latest()
            ->get();

        return view('manajer-sertifikasi.bank-soal.show', compact(
            'skema', 'soalObservasi', 'soalTeori', 'jumlahTeori', 'portofolios'
        ));
    }

    // =========================================================================
    // BANK SOAL — SOAL OBSERVASI (scoped ke skema)
    // =========================================================================

    public function storeSoalObservasiBySkema(Request $request, Skema $skema): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
        ]);

        SoalObservasi::create([
            'skema_id'    => $skema->id,
            'judul'       => $request->judul,
            'dibuat_oleh' => Auth::id(),
        ]);

        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Soal observasi berhasil dibuat. Upload paket di bawah.')
            ->withFragment('pane-observasi');
    }

    public function destroySoalObservasiBySkema(Skema $skema, SoalObservasi $soalObservasi): RedirectResponse
    {
        foreach ($soalObservasi->paket as $paket) {
            Storage::disk('private')->delete($paket->file_path);
        }
        $soalObservasi->delete();

        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Soal observasi beserta semua paket berhasil dihapus.')
            ->withFragment('pane-observasi');
    }

    // =========================================================================
    // BANK SOAL — PAKET OBSERVASI (scoped ke skema)
    // =========================================================================

public function storePaketBySkema(Request $request, Skema $skema, SoalObservasi $soalObservasi): RedirectResponse
{
    $request->validate([
        'kode_paket' => 'required|string|max:10',
        'file'       => 'required|file|mimes:pdf|max:10240',
        'lampiran'   => 'nullable|file|mimes:doc,docx|max:20480',
    ]);

    $kode = strtoupper(trim($request->kode_paket));

    if ($soalObservasi->paket()->where('kode_paket', $kode)->exists()) {
        return back()->withErrors(['kode_paket' => "Paket {$kode} sudah ada."]);
    }

    $file     = $request->file('file');
    $lampiran = $request->file('lampiran');

    PaketSoalObservasi::create([
        'soal_observasi_id' => $soalObservasi->id,
        'kode_paket'        => $kode,
        'judul'             => "Paket {$kode}",
        'file_path'         => $file->store('soal/observasi/paket', 'private'),
        'file_name'         => $file->getClientOriginalName(),
        'lampiran_path'     => $lampiran ? $lampiran->store('soal/observasi/lampiran', 'private') : null,
        'lampiran_name'     => $lampiran?->getClientOriginalName(),
        'dibuat_oleh'       => Auth::id(),
    ]);

    return back()->with('success', "Paket {$kode} berhasil diupload.");
}
    /**
     * [FIX #1] Download paket observasi dari bank soal (scoped ke skema)
     * Pastikan disk 'private' dikonfigurasi di config/filesystems.php
     */
    public function downloadPaketBySkema(Skema $skema, PaketSoalObservasi $paket)
    {
        abort_unless(
            $paket->file_path && Storage::disk('private')->exists($paket->file_path),
            404,
            'File paket tidak ditemukan.'
        );

        return Storage::disk('private')->download($paket->file_path, $paket->file_name);
    }

    public function destroyPaketBySkema(Skema $skema, PaketSoalObservasi $paket): RedirectResponse
    {
        if ($paket->file_path) {
            Storage::disk('private')->delete($paket->file_path);
        }
        $paket->delete();
        return back()->with('success', 'Paket berhasil dihapus.');
    }

    // =========================================================================
    // BANK SOAL — SOAL TEORI (scoped ke skema, pilihan a-e)
    // =========================================================================

    public function storeSoalTeoriBySkema(Request $request, Skema $skema): RedirectResponse
    {
        $request->validate([
            'pertanyaan'    => 'required|string',
            'pilihan_a'     => 'required|string|max:500',
            'pilihan_b'     => 'required|string|max:500',
            'pilihan_c'     => 'required|string|max:500',
            'pilihan_d'     => 'required|string|max:500',
            'pilihan_e'     => 'nullable|string|max:500',
            'jawaban_benar' => 'required|in:a,b,c,d,e',
        ]);

        SoalTeori::create([
            'skema_id'      => $skema->id,
            'pertanyaan'    => $request->pertanyaan,
            'pilihan_a'     => $request->pilihan_a,
            'pilihan_b'     => $request->pilihan_b,
            'pilihan_c'     => $request->pilihan_c,
            'pilihan_d'     => $request->pilihan_d,
            'pilihan_e'     => $request->pilihan_e,
            'jawaban_benar' => $request->jawaban_benar,
            'dibuat_oleh'   => Auth::id(),
        ]);

        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Soal teori berhasil ditambahkan.')
            ->withFragment('pane-teori');
    }

    public function updateSoalTeoriBySkema(Request $request, Skema $skema, SoalTeori $soalTeori): RedirectResponse
    {
        $request->validate([
            'pertanyaan'    => 'required|string',
            'pilihan_a'     => 'required|string|max:500',
            'pilihan_b'     => 'required|string|max:500',
            'pilihan_c'     => 'required|string|max:500',
            'pilihan_d'     => 'required|string|max:500',
            'pilihan_e'     => 'nullable|string|max:500',
            'jawaban_benar' => 'required|in:a,b,c,d,e',
        ]);

        $soalTeori->update($request->only([
            'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'pilihan_e', 'jawaban_benar',
        ]));

        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Soal teori berhasil diperbarui.')
            ->withFragment('pane-teori');
    }

    public function destroySoalTeoriBySkema(Skema $skema, SoalTeori $soalTeori): RedirectResponse
    {
        $soalTeori->delete();
        return back()->with('success', 'Soal teori berhasil dihapus.');
    }

    public function downloadTemplateSoalTeori(Skema $skema)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Soal Teori');

    // ── Header row ──
    $headers = ['No', 'Pertanyaan', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Pilihan E', 'Jawaban Benar'];
    foreach ($headers as $col => $header) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
        $sheet->setCellValue($cell, $header);
    }

    // Style header
    $sheet->getStyle('A1:H1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
    ]);

    // Column widths
    $widths = ['A' => 5, 'B' => 60, 'C' => 30, 'D' => 30, 'E' => 30, 'F' => 30, 'G' => 30, 'H' => 15];
    foreach ($widths as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    // ── Contoh data (3 baris) ──
    $examples = [
        [1, 'Contoh pertanyaan soal teori...', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Pilihan E (opsional)', 'a'],
        [2, 'Fungsi VLOOKUP digunakan untuk...', 'Membuat grafik', 'Mencari nilai dalam tabel', 'Menghitung rata-rata', 'Menyortir data', 'Membuat pivot table', 'b'],
        [3, 'Shortcut menyimpan dokumen adalah...', 'Ctrl+P', 'Ctrl+Z', 'Ctrl+S', 'Ctrl+C', 'Ctrl+V', 'c'],
    ];

    foreach ($examples as $rowIdx => $row) {
        $rowNum = $rowIdx + 2;
        foreach ($row as $colIdx => $val) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . $rowNum;
            $sheet->setCellValue($cell, $val);
        }

        // Style baris contoh
        $sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);

        // Dropdown validasi kolom Jawaban Benar
        $validation = $sheet->getCell("H{$rowNum}")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST)
            ->setErrorStyle(DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(false)
            ->setShowDropDown(false)
            ->setFormula1('"a,b,c,d,e"');
    }

    // ── Sheet petunjuk ──
    $guide = $spreadsheet->createSheet();
    $guide->setTitle('Petunjuk');
    $guide->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE SOAL TEORI');
    $guide->setCellValue('A3', 'Kolom');
    $guide->setCellValue('B3', 'Keterangan');
    $guide->setCellValue('C3', 'Wajib?');

    $guideData = [
        ['No', 'Nomor urut soal (boleh dikosongkan, sistem akan mengisi otomatis)', 'Tidak'],
        ['Pertanyaan', 'Teks pertanyaan soal teori', 'Ya'],
        ['Pilihan A', 'Opsi jawaban A', 'Ya'],
        ['Pilihan B', 'Opsi jawaban B', 'Ya'],
        ['Pilihan C', 'Opsi jawaban C', 'Ya'],
        ['Pilihan D', 'Opsi jawaban D', 'Ya'],
        ['Pilihan E', 'Opsi jawaban E', 'Ya'],
        ['Jawaban Benar', 'Isi dengan huruf kecil: a, b, c, d, atau e', 'Ya'],
    ];

    foreach ($guideData as $i => $row) {
        $rowNum = $i + 4;
        $guide->setCellValue("A{$rowNum}", $row[0]);
        $guide->setCellValue("B{$rowNum}", $row[1]);
        $guide->setCellValue("C{$rowNum}", $row[2]);
    }

    $guide->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1E40AF']]]);
    $guide->getStyle('A3:C3')->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']]]);
    $guide->getColumnDimension('A')->setWidth(20);
    $guide->getColumnDimension('B')->setWidth(60);
    $guide->getColumnDimension('C')->setWidth(10);

    // ── Stream download ──
    $filename = 'Template_Soal_Teori_' . \Illuminate\Support\Str::slug($skema->name) . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function importSoalTeori(Request $request, Skema $skema): RedirectResponse
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls|max:10240',
    ]);

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getPathname());
        $sheet = $spreadsheet->getSheetByName('Soal Teori') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        // Skip baris header (baris pertama)
        $dataRows = array_slice($rows, 1);

        $imported = 0;
        $errors = [];
        $skipped = 0;

        foreach ($dataRows as $index => $row) {
            $rowNum = $index + 2; // baris Excel (1-indexed + header)

            // Skip baris kosong
            $pertanyaan = trim($row[1] ?? '');
            if (empty($pertanyaan)) {
                $skipped++;
                continue;
            }

            $pilihanA = trim($row[2] ?? '');
            $pilihanB = trim($row[3] ?? '');
            $pilihanC = trim($row[4] ?? '');
            $pilihanD = trim($row[5] ?? '');
            $pilihanE = trim($row[6] ?? '') ?: null;
            $jawaban  = strtolower(trim($row[7] ?? ''));

            // Validasi kolom wajib
            if (empty($pilihanA) || empty($pilihanB) || empty($pilihanC) || empty($pilihanD)) {
                $errors[] = "Baris {$rowNum}: Pilihan A–D wajib diisi.";
                continue;
            }

            if (!in_array($jawaban, ['a', 'b', 'c', 'd', 'e'])) {
                $errors[] = "Baris {$rowNum}: Jawaban benar harus a, b, c, d, atau e (ditemukan: '{$jawaban}').";
                continue;
            }

            if ($jawaban === 'e' && empty($pilihanE)) {
                $errors[] = "Baris {$rowNum}: Jawaban 'e' dipilih tapi Pilihan E kosong.";
                continue;
            }

            SoalTeori::create([
                'skema_id'      => $skema->id,
                'pertanyaan'    => $pertanyaan,
                'pilihan_a'     => $pilihanA,
                'pilihan_b'     => $pilihanB,
                'pilihan_c'     => $pilihanC,
                'pilihan_d'     => $pilihanD,
                'pilihan_e'     => $pilihanE,
                'jawaban_benar' => $jawaban,
                'dibuat_oleh'   => Auth::id(),
            ]);

            $imported++;
        }

        $message = "{$imported} soal berhasil diimport.";
        if ($skipped > 0) $message .= " {$skipped} baris kosong dilewati.";

        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', $message)
            ->with('import_errors', $errors)
            ->withFragment('pane-teori');

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('[SOAL_TEORI][import] ' . $e->getMessage());
        return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
    }
}

    // =========================================================================
    // BANK SOAL — PORTOFOLIO (scoped ke skema)
    // =========================================================================

    public function storePortofolioBySkema(Request $request, Skema $skema): RedirectResponse
    {
        $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file'      => 'nullable|file|mimes:xlsx,xlsm,xls,pdf,doc,docx|max:20480',
        ]);
    
        $file = $request->file('file');
    
        Portofolio::create([
            'skema_id'    => $skema->id,
            'judul'       => $request->judul,
            'deskripsi'   => $request->deskripsi,
            'file_path'   => $file ? $file->store('portofolio', 'private') : null,
            'file_name'   => $file?->getClientOriginalName(),
            'tipe_file'   => $file?->getClientOriginalExtension(),
            'dibuat_oleh' => Auth::id(),
        ]);
    
        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Form penilaian portofolio berhasil disimpan.')
            ->withFragment('pane-portofolio');
    }
    /**
     * [FIX #1] Download portofolio dari bank soal (scoped ke skema)
     */
    public function downloadPortofolioBySkema(Skema $skema, Portofolio $portofolio)
    {
        abort_unless($portofolio->hasFile(), 404, 'File tidak tersedia.');
        abort_unless(
            Storage::disk('private')->exists($portofolio->file_path),
            404,
            'File tidak ditemukan di storage.'
        );

        return Storage::disk('private')->download($portofolio->file_path, $portofolio->file_name);
    }

    public function destroyPortofolioBySkema(Skema $skema, Portofolio $portofolio): RedirectResponse
    {
        if ($portofolio->hasFile()) {
            Storage::disk('private')->delete($portofolio->file_path);
        }
        $portofolio->delete();
        return back()->with('success', 'Form portofolio berhasil dihapus.');
    }

    // =========================================================================
    // DETAIL JADWAL
    // =========================================================================

    public function show(Schedule $schedule): View
    {
        $schedule->load([
            'skema', 'tuk', 'asesor.user', 'asesmens.user',
            'distribusiSoalObservasi.soalObservasi.paket',
            'distribusiSoalObservasi.paketSoalObservasi',
            'distribusiSoalTeori.soalAsesi',
            'distribusiPortofolio.portofolio',
        ]);

        $skemaId = $schedule->skema_id;

        return view('manajer-sertifikasi.show', [
            'schedule'               => $schedule,
            'soalObservasiTersedia'  => SoalObservasi::with('paket')->where('skema_id', $skemaId)->get(),
            'portofolioTersedia'     => Portofolio::where('skema_id', $skemaId)->get(),
            'jumlahBankSoalTeori'    => SoalTeori::where('skema_id', $skemaId)->count(),
            'distribusiObservasiIds' => $schedule->distribusiSoalObservasi->pluck('soal_observasi_id'),
            'distribusiPortofolioIds'=> $schedule->distribusiPortofolio->pluck('portofolio_id'),
            'distribusiTeori'        => $schedule->distribusiSoalTeori,
        ]);
    }

    // =========================================================================
    // SOAL OBSERVASI
    // =========================================================================

    public function indexSoalObservasi(Request $request): View
    {
        $query = SoalObservasi::with('skema', 'dibuatOleh', 'paket', 'distribusi');
        if ($request->skema_id) $query->where('skema_id', $request->skema_id);

        return view('manajer-sertifikasi.soal-observasi.index', [
            'soalObservasi' => $query->latest()->paginate(15),
            'skemas'        => Skema::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function createSoalObservasi(): View
    {
        return view('manajer-sertifikasi.soal-observasi.create', [
            'skemas' => Skema::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeSoalObservasi(Request $request): RedirectResponse
    {
        $request->validate([
            'skema_id' => 'required|exists:skemas,id',
            'judul'    => 'required|string|max:255',
        ]);

        $observasi = SoalObservasi::create([
            'skema_id'    => $request->skema_id,
            'judul'       => $request->judul,
            'dibuat_oleh' => Auth::id(),
        ]);

        $back = $request->redirect_back;
        if ($back && str_starts_with($back, url('/manajer-sertifikasi'))) {
            return redirect($back)
                ->with('success', 'Soal observasi dibuat. Tambahkan paket sekarang.')
                ->withFragment('pane-observasi');
        }

        return redirect()->route('manajer-sertifikasi.soal-observasi.show', $observasi)
            ->with('success', 'Soal observasi berhasil dibuat. Tambahkan paket di dalamnya.');
    }

    public function showSoalObservasi(SoalObservasi $soalObservasi): View
    {
        $soalObservasi->load('skema', 'paket.dibuatOleh', 'distribusi.schedule.skema');
        return view('manajer-sertifikasi.soal-observasi.show', compact('soalObservasi'));
    }

    public function destroySoalObservasi(SoalObservasi $soalObservasi): RedirectResponse
    {
        foreach ($soalObservasi->paket as $paket) {
            Storage::disk('private')->delete($paket->file_path);
        }
        $soalObservasi->delete();

        return redirect()->route('manajer-sertifikasi.soal-observasi.index')
            ->with('success', 'Soal observasi beserta semua paket berhasil dihapus.');
    }

    // ── Paket di dalam Observasi ──────────────────────────────────────────

public function storePaketObservasi(Request $request, SoalObservasi $soalObservasi): RedirectResponse
{
    $request->validate([
        'kode_paket' => 'required|string|max:10',
        'judul'      => 'required|string|max:255',
        'file'       => 'required|file|mimes:pdf|max:10240',
        'lampiran'   => 'nullable|file|mimes:doc,docx|max:20480',
    ]);

    if ($soalObservasi->paket()->where('kode_paket', strtoupper($request->kode_paket))->exists()) {
        return back()->withErrors(['kode_paket' => "Paket {$request->kode_paket} sudah ada."]);
    }

    $file     = $request->file('file');
    $lampiran = $request->file('lampiran');

    PaketSoalObservasi::create([
        'soal_observasi_id' => $soalObservasi->id,
        'kode_paket'        => strtoupper($request->kode_paket),
        'judul'             => $request->judul,
        'file_path'         => $file->store('soal/observasi/paket', 'private'),
        'file_name'         => $file->getClientOriginalName(),
        'lampiran_path'     => $lampiran ? $lampiran->store('soal/observasi/lampiran', 'private') : null,
        'lampiran_name'     => $lampiran?->getClientOriginalName(),
        'dibuat_oleh'       => Auth::id(),
    ]);

    return back()->with('success', "Paket {$request->kode_paket} berhasil diupload.");
}

    /**
     * [FIX #1] Download paket observasi — tambah cek exists()
     */
    public function downloadPaketObservasi(PaketSoalObservasi $paket)
    {
        abort_unless(
            $paket->file_path && Storage::disk('private')->exists($paket->file_path),
            404,
            'File paket tidak ditemukan di storage.'
        );

        return Storage::disk('private')->download($paket->file_path, $paket->file_name);
    }

    public function destroyPaketObservasi(PaketSoalObservasi $paket): RedirectResponse
    {
        if ($paket->file_path) {
            Storage::disk('private')->delete($paket->file_path);
        }
        $paket->delete();
        return back()->with('success', 'Paket berhasil dihapus.');
    }

    // Tambah method download lampiran
public function downloadLampiranBySkema(Skema $skema, PaketSoalObservasi $paket)
{
    abort_unless(
        $paket->lampiran_path && Storage::disk('private')->exists($paket->lampiran_path),
        404,
        'File lampiran tidak ditemukan.'
    );
    return Storage::disk('private')->download($paket->lampiran_path, $paket->lampiran_name);
}

public function downloadLampiranObservasi(PaketSoalObservasi $paket)
{
    abort_unless(
        $paket->lampiran_path && Storage::disk('private')->exists($paket->lampiran_path),
        404,
        'File lampiran tidak ditemukan.'
    );
    return Storage::disk('private')->download($paket->lampiran_path, $paket->lampiran_name);
}

    // ── Distribusi Observasi ke Jadwal ────────────────────────────────────

    public function distribusiSoalObservasi(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'              => 'required|exists:schedules,id',
            'soal_observasi_id'        => 'required|exists:soal_observasi,id',
            'paket_soal_observasi_id'  => 'required|exists:paket_soal_observasi,id',
        ]);

        $paket = \App\Models\PaketSoalObservasi::where('id', $request->paket_soal_observasi_id)
            ->where('soal_observasi_id', $request->soal_observasi_id)
            ->firstOrFail();

        DistribusiSoalObservasi::updateOrCreate(
            [
                'schedule_id'       => $request->schedule_id,
                'soal_observasi_id' => $request->soal_observasi_id,
            ],
            [
                'paket_soal_observasi_id' => $paket->id,
                'didistribusikan_oleh'    => Auth::id(),
            ]
        );

        return back()->with('success', "Soal observasi '{$paket->soalObservasi->judul}' — Paket {$paket->kode_paket} berhasil didistribusikan.");
    }

    /**
     * [FIX #3] Hapus distribusi observasi — redirect eksplisit dengan fragment
     */
    public function hapusDistribusiSoalObservasi(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'       => 'required|exists:schedules,id',
            'soal_observasi_id' => 'required|exists:soal_observasi,id',
        ]);

        DistribusiSoalObservasi::where([
            'schedule_id'       => $request->schedule_id,
            'soal_observasi_id' => $request->soal_observasi_id,
        ])->delete();

        return redirect()
            ->route('manajer-sertifikasi.jadwal.show', $request->schedule_id)
            ->with('success', 'Distribusi soal observasi dihapus.')
            ->withFragment('pane-observasi');
    }

    // =========================================================================
    // PORTOFOLIO
    // =========================================================================

    public function indexPortofolio(Request $request): View
    {
        $query = Portofolio::with('skema', 'dibuatOleh', 'distribusi');
        if ($request->skema_id) $query->where('skema_id', $request->skema_id);

        return view('manajer-sertifikasi.portofolio.index', [
            'portofolios' => $query->latest()->paginate(15),
            'skemas'      => Skema::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function createPortofolio(): View
    {
        return view('manajer-sertifikasi.portofolio.create', [
            'skemas' => Skema::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storePortofolio(Request $request): RedirectResponse
    {
        $request->validate([
            'skema_id'  => 'required|exists:skemas,id',
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file'      => 'nullable|file|max:20480',
        ]);

        $file = $request->file('file');

        Portofolio::create([
            'skema_id'    => $request->skema_id,
            'judul'       => $request->judul,
            'deskripsi'   => $request->deskripsi,
            'file_path'   => $file ? $file->store('portofolio', 'private') : null,
            'file_name'   => $file?->getClientOriginalName(),
            'tipe_file'   => $file?->getClientOriginalExtension(),
            'dibuat_oleh' => Auth::id(),
        ]);

        $back = $request->redirect_back;
        if ($back && str_starts_with($back, url('/manajer-sertifikasi'))) {
            return redirect($back)->with('success', 'Portofolio berhasil disimpan.');
        }

        return redirect()->route('manajer-sertifikasi.portofolio.index')
            ->with('success', 'Portofolio berhasil disimpan.');
    }

    /**
     * [FIX #1] Download portofolio — tambah cek exists()
     */
    public function downloadPortofolio(Portofolio $portofolio)
    {
        abort_unless($portofolio->hasFile(), 404, 'File tidak tersedia.');
        abort_unless(
            Storage::disk('private')->exists($portofolio->file_path),
            404,
            'File tidak ditemukan di storage.'
        );

        return Storage::disk('private')->download($portofolio->file_path, $portofolio->file_name);
    }

    public function destroyPortofolio(Portofolio $portofolio): RedirectResponse
    {
        if ($portofolio->hasFile()) {
            Storage::disk('private')->delete($portofolio->file_path);
        }
        $portofolio->delete();
        return back()->with('success', 'Portofolio berhasil dihapus.');
    }

    public function distribusiPortofolio(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'   => 'required|exists:schedules,id',
            'portofolio_id' => 'required|exists:portofolio,id',
        ]);
    
        DistribusiPortofolio::updateOrCreate(
            ['schedule_id' => $request->schedule_id, 'portofolio_id' => $request->portofolio_id],
            ['didistribusikan_oleh' => Auth::id()]
        );
    
        $porto = Portofolio::find($request->portofolio_id);
    
        return back()->with('success', "Form penilaian '{$porto->judul}' berhasil didistribusikan.");
    }

    /**
     * [FIX #3] Hapus distribusi portofolio — redirect eksplisit ke jadwal
     */
    public function hapusDistribusiPortofolio(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'   => 'required|exists:schedules,id',
            'portofolio_id' => 'required|exists:portofolio,id',
        ]);
    
        DistribusiPortofolio::where([
            'schedule_id'   => $request->schedule_id,
            'portofolio_id' => $request->portofolio_id,
        ])->delete();
    
        return redirect()
            ->route('manajer-sertifikasi.jadwal.show', $request->schedule_id)
            ->with('success', 'Distribusi portofolio berhasil dihapus.')
            ->withFragment('pane-portofolio');
    }

    // =========================================================================
    // SOAL TEORI PG
    // =========================================================================

    public function indexSoalTeori(Request $request): View
    {
        $query = SoalTeori::with('skema', 'dibuatOleh');
        if ($request->skema_id) $query->where('skema_id', $request->skema_id);
        if ($request->q) $query->where('pertanyaan', 'like', '%' . $request->q . '%');

        $ringkasanSkema = SoalTeori::select('soal_teori.skema_id', DB::raw('count(*) as total'))
            ->join('skemas', 'skemas.id', '=', 'soal_teori.skema_id')
            ->addSelect('skemas.name as skema_name')
            ->groupBy('soal_teori.skema_id', 'skemas.name')
            ->get();

        return view('manajer-sertifikasi.soal-teori.index', [
            'soalTeori'      => $query->latest()->paginate(20),
            'skemas'         => Skema::where('is_active', true)->orderBy('name')->get(),
            'ringkasanSkema' => $ringkasanSkema,
        ]);
    }

    public function storeSoalTeori(Request $request): RedirectResponse
    {
        $request->validate([
            'skema_id'      => 'required|exists:skemas,id',
            'pertanyaan'    => 'required|string',
            'pilihan_a'     => 'required|string|max:500',
            'pilihan_b'     => 'required|string|max:500',
            'pilihan_c'     => 'required|string|max:500',
            'pilihan_d'     => 'required|string|max:500',
            'jawaban_benar' => 'required|in:a,b,c,d',
        ]);

        SoalTeori::create([
            ...$request->only([
                'skema_id', 'pertanyaan',
                'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'jawaban_benar',
            ]),
            'dibuat_oleh' => Auth::id(),
        ]);

        return redirect()->route('manajer-sertifikasi.soal-teori.index')
            ->with('success', 'Soal teori berhasil ditambahkan.');
    }

    public function updateSoalTeori(Request $request, SoalTeori $soalTeori): RedirectResponse
    {
        $request->validate([
            'skema_id'      => 'required|exists:skemas,id',
            'pertanyaan'    => 'required|string',
            'pilihan_a'     => 'required|string|max:500',
            'pilihan_b'     => 'required|string|max:500',
            'pilihan_c'     => 'required|string|max:500',
            'pilihan_d'     => 'required|string|max:500',
            'jawaban_benar' => 'required|in:a,b,c,d',
        ]);

        $soalTeori->update($request->only([
            'skema_id', 'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'jawaban_benar',
        ]));

        return redirect()->route('manajer-sertifikasi.soal-teori.index')
            ->with('success', 'Soal teori berhasil diperbarui.');
    }

    public function destroySoalTeori(SoalTeori $soalTeori): RedirectResponse
    {
        $soalTeori->delete();
        return back()->with('success', 'Soal teori berhasil dihapus.');
    }

    /**
     * [FIX #2] Distribusi soal teori — tambah field durasi_menit (default 30)
     */
    public function distribusiSoalTeori(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'  => 'required|exists:schedules,id',
            'jumlah_soal'  => 'required|integer|min:1',
            'durasi_menit' => 'nullable|integer|min:1|max:300',
        ]);

        $schedule    = Schedule::with('asesmens')->findOrFail($request->schedule_id);
        $bankSoalIds = SoalTeori::where('skema_id', $schedule->skema_id)->pluck('id')->toArray();
        $totalBank   = count($bankSoalIds);

        if ($totalBank < $request->jumlah_soal) {
            return back()->withErrors([
                'jumlah_soal' => "Bank soal hanya punya {$totalBank} soal, tidak cukup untuk {$request->jumlah_soal} soal.",
            ])->withInput();
        }

        $durasi = $request->durasi_menit ?? 30;

        DB::transaction(function () use ($request, $schedule, $bankSoalIds, $durasi) {
            DistribusiSoalTeori::where('schedule_id', $schedule->id)->delete();

            $distribusi = DistribusiSoalTeori::create([
                'schedule_id'          => $schedule->id,
                'jumlah_soal'          => $request->jumlah_soal,
                'durasi_menit'         => $durasi,
                'didistribusikan_oleh' => Auth::id(),
            ]);

            foreach ($schedule->asesmens as $asesmen) {
                $terpilih = collect($bankSoalIds)->shuffle()->take($request->jumlah_soal)->values();

                SoalTeoriAsesi::insert($terpilih->map(fn ($id, $idx) => [
                    'distribusi_soal_teori_id' => $distribusi->id,
                    'asesmen_id'               => $asesmen->id,
                    'soal_teori_id'            => $id,
                    'urutan'                   => $idx + 1,
                    'jawaban'                  => null,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ])->toArray());
            }
        });

        return redirect()->route('manajer-sertifikasi.jadwal.show', $schedule)
            ->with('success', "{$request->jumlah_soal} soal teori ({$durasi} menit) berhasil didistribusikan ke {$schedule->asesmens->count()} asesi.");
    }

    // =========================================================================
    // DAFTAR HADIR
    // =========================================================================

    public function daftarHadir(Schedule $schedule): \Illuminate\Http\Response
    {
        $schedule->load(['tuk', 'skema', 'asesor.user', 'asesmens']);

        $ttdAsesor = $schedule->asesor?->user?->signature_image;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.daftar-hadir', [
            'schedule'  => $schedule,
            'asesmens'  => $schedule->asesmens,
            'asesor'    => $schedule->asesor,
            'ttdAsesor' => $ttdAsesor,
        ])->setPaper('A4', 'portrait');

        $filename = 'Daftar_Hadir_'
            . str_replace(' ', '_', $schedule->skema->name ?? 'Asesmen')
            . '_' . $schedule->assessment_date->format('d-m-Y') . '.pdf';

        return $pdf->stream($filename);
    }

    public function rekapPenilaian(Schedule $schedule): \Illuminate\View\View
    {
        $schedule->load([
            'skema', 'tuk', 'asesor.user',
            'asesmens.soalTeoriAsesi.soalTeori',
            'distribusiSoalObservasi.soalObservasi',
            'distribusiPortofolio.portofolio',
            'beritaAcara.asesis',
        ]);

        $hasilObservasi  = HasilObservasi::where('schedule_id', $schedule->id)->get();
        $hasilPortofolio = HasilPortofolio::where('schedule_id', $schedule->id)->get();
        $beritaAcara     = $schedule->beritaAcara;

        $rekomendasiMap = [];
        if ($beritaAcara) {
            foreach ($beritaAcara->asesis as $ba) {
                $rekomendasiMap[$ba->asesmen_id] = $ba->rekomendasi;
            }
        }

        return view('manajer-sertifikasi.rekap-penilaian', [
            'schedule'        => $schedule,
            'hasilObservasi'  => $hasilObservasi,
            'hasilPortofolio' => $hasilPortofolio,
            'beritaAcara'     => $beritaAcara,
            'rekomendasiMap'  => $rekomendasiMap,
            'totalObservasi'  => $schedule->distribusiSoalObservasi->count(),
            'totalPortofolio' => $schedule->distribusiPortofolio->count(),
        ]);
    }

    /**
     * [FIX #1] Download hasil observasi — tambah cek exists()
     */
    public function downloadHasilObservasi(Schedule $schedule, SoalObservasi $soalObservasi): \Illuminate\Http\Response
    {
        $hasil = HasilObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();

        abort_unless(
            Storage::disk('private')->exists($hasil->file_path),
            404,
            'File hasil observasi tidak ditemukan di storage.'
        );

        return Storage::disk('private')->download($hasil->file_path, $hasil->file_name);
    }

    /**
     * [FIX #1] Download hasil portofolio — tambah cek exists()
     */
    public function downloadHasilPortofolio(Schedule $schedule, Portofolio $portofolio): \Illuminate\Http\Response
    {
        $hasil = HasilPortofolio::where([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
        ])->firstOrFail();

        abort_unless(
            Storage::disk('private')->exists($hasil->file_path),
            404,
            'File hasil portofolio tidak ditemukan di storage.'
        );

        return Storage::disk('private')->download($hasil->file_path, $hasil->file_name);
    }

    public function downloadFileBeritaAcara(Schedule $schedule): \Illuminate\Http\Response
    {
        $ba = $schedule->beritaAcara;
        abort_unless($ba && $ba->file_path, 404, 'File tidak tersedia.');
        abort_unless(
            Storage::disk('private')->exists($ba->file_path),
            404,
            'File berita acara tidak ditemukan di storage.'
        );

        return Storage::disk('private')->download($ba->file_path, $ba->file_name);
    }

    /**
     * [FIX #5] Upload form penilaian observasi — hanya .xlsx
     * POST /manajer-sertifikasi/jadwal/{schedule}/observasi/{soalObservasi}/form-penilaian
     */
    public function uploadFormPenilaianObservasi(Request $request, Schedule $schedule, SoalObservasi $soalObservasi): RedirectResponse
    {
        $request->validate([
            // [FIX #5] hanya xlsx (xlsm diizinkan karena macro-enabled, xls lama)
            'file' => 'required|file|mimes:xlsx,xlsm,xls|max:20480',
        ]);

        $dist = DistribusiSoalObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();

        // Hapus file lama jika ada
        if ($dist->form_penilaian_path && Storage::disk('private')->exists($dist->form_penilaian_path)) {
            Storage::disk('private')->delete($dist->form_penilaian_path);
        }

        $file = $request->file('file');
        $path = $file->store("form-penilaian/observasi/{$schedule->id}", 'private');

        $dist->update([
            'form_penilaian_path' => $path,
            'form_penilaian_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', "Form penilaian '{$soalObservasi->judul}' berhasil diupload.");
    }

    /**
     * [FIX #1] Download form penilaian observasi — tambah cek exists()
     * GET /manajer-sertifikasi/jadwal/{schedule}/observasi/{soalObservasi}/form-penilaian
     */
    public function downloadFormPenilaianObservasi(Schedule $schedule, SoalObservasi $soalObservasi)
    {
        $dist = DistribusiSoalObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();

        abort_unless(
            $dist->form_penilaian_path && Storage::disk('private')->exists($dist->form_penilaian_path),
            404,
            'Form penilaian belum diupload atau file tidak ditemukan.'
        );

        return Storage::disk('private')->download($dist->form_penilaian_path, $dist->form_penilaian_name);
    }

    /**
     * Hapus form penilaian observasi
     * DELETE /manajer-sertifikasi/jadwal/{schedule}/observasi/{soalObservasi}/form-penilaian
     */
    public function hapusFormPenilaianObservasi(Schedule $schedule, SoalObservasi $soalObservasi): RedirectResponse
    {
        $dist = DistribusiSoalObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();

        if ($dist->form_penilaian_path && Storage::disk('private')->exists($dist->form_penilaian_path)) {
            Storage::disk('private')->delete($dist->form_penilaian_path);
        }

        $dist->update([
            'form_penilaian_path' => null,
            'form_penilaian_name' => null,
        ]);

        return back()->with('success', 'Form penilaian dihapus.');
    }

    // =========================================================================
    // [FIX #4] FORM PENILAIAN PORTOFOLIO — upload, download, hapus
    // =========================================================================

    /**
     * Upload form penilaian untuk distribusi portofolio
     * POST /manajer-sertifikasi/jadwal/{schedule}/portofolio/{portofolio}/form-penilaian
     */
    public function uploadFormPenilaianPortofolio(Request $request, Schedule $schedule, Portofolio $portofolio): RedirectResponse
    {
        $request->validate([
            // [FIX #5] hanya xlsx
            'file' => 'required|file|mimes:xlsx,xlsm,xls|max:20480',
        ]);

        $dist = DistribusiPortofolio::where([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
        ])->firstOrFail();

        // Hapus file lama jika ada
        if (isset($dist->form_penilaian_path) && $dist->form_penilaian_path && Storage::disk('private')->exists($dist->form_penilaian_path)) {
            Storage::disk('private')->delete($dist->form_penilaian_path);
        }

        $file = $request->file('file');
        $path = $file->store("form-penilaian/portofolio/{$schedule->id}", 'private');

        $dist->update([
            'form_penilaian_path' => $path,
            'form_penilaian_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', "Form penilaian '{$portofolio->judul}' berhasil diupload.");
    }

    /**
     * Download form penilaian portofolio
     * GET /manajer-sertifikasi/jadwal/{schedule}/portofolio/{portofolio}/form-penilaian
     */
    public function downloadFormPenilaianPortofolio(Schedule $schedule, Portofolio $portofolio)
    {
        $dist = DistribusiPortofolio::where([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
        ])->firstOrFail();

        abort_unless(
            isset($dist->form_penilaian_path) && $dist->form_penilaian_path && Storage::disk('private')->exists($dist->form_penilaian_path),
            404,
            'Form penilaian portofolio belum diupload atau file tidak ditemukan.'
        );

        return Storage::disk('private')->download($dist->form_penilaian_path, $dist->form_penilaian_name);
    }

    /**
     * [FIX #4] Hapus form penilaian portofolio
     * DELETE /manajer-sertifikasi/jadwal/{schedule}/portofolio/{portofolio}/form-penilaian
     */
    public function hapusFormPenilaianPortofolio(Schedule $schedule, Portofolio $portofolio): RedirectResponse
    {
        $dist = DistribusiPortofolio::where([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
        ])->firstOrFail();

        // Log untuk debugging
        Log::info('Menghapus form penilaian portofolio', [
            'schedule_id' => $schedule->id,
            'portofolio_id' => $portofolio->id,
            'form_path' => $dist->form_penilaian_path,
        ]);

        if ($dist->form_penilaian_path && Storage::disk('private')->exists($dist->form_penilaian_path)) {
            Storage::disk('private')->delete($dist->form_penilaian_path);
        }

        $dist->update([
            'form_penilaian_path' => null,
            'form_penilaian_name' => null,
        ]);

        return back()->with('success', 'Form penilaian portofolio berhasil dihapus.');
    }

public function pdfBeritaAcara(Schedule $schedule): \Illuminate\Http\Response
{
    $schedule->load([
        'skema', 'tuk', 'asesor.user',
        'asesmens',
        'beritaAcara.asesis',
    ]);

    $ba = $schedule->beritaAcara;
    abort_unless($ba, 404, 'Berita acara belum tersedia.');

    // View pdf.berita-acara butuh $rekMap (bukan $rekomendasiMap)
    $rekMap = $ba->asesis->pluck('rekomendasi', 'asesmen_id');

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.berita-acara', [
        'schedule'    => $schedule,
        'beritaAcara' => $ba,
        'rekMap'      => $rekMap,
        'asesor'      => $schedule->asesor,
    ])->setPaper('A4', 'portrait');

    $filename = 'Berita_Acara_'
        . str_replace(' ', '_', $schedule->skema->name ?? 'Asesmen')
        . '_' . $schedule->assessment_date->format('d-m-Y') . '.pdf';

    return $pdf->stream($filename);
}
}