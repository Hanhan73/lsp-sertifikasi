<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SuratController extends Controller
{
    // ─── SURAT MASUK ──────────────────────────────────────────────────────

    public function masukIndex()
    {
        $tahun  = request('tahun', now()->year);
        $surats = SuratMasuk::whereYear('tanggal_agenda', $tahun)
                            ->orderBy('nomor_urut')->get();

        $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $rekap     = $surats->groupBy(fn($s) => $s->tanggal_agenda->month);

        $tahunList = SuratMasuk::selectRaw('YEAR(tanggal_agenda) as tahun')
                            ->distinct()->orderByDesc('tahun')->pluck('tahun');
        if (!$tahunList->contains(now()->year)) {
            $tahunList = $tahunList->prepend(now()->year);
        }

        return view('admin.surat.masuk.index', compact('surats', 'rekap', 'bulanList', 'tahun', 'tahunList'));
    }

    public function masukCreate()
    {
        $nextNo = (SuratMasuk::max('nomor_urut') ?? 0) + 1;
        return view('admin.surat.masuk.create', compact('nextNo'));
    }

    public function masukStore(Request $request)
    {
        $data = $request->validate([
            'nomor_urut'     => 'required|integer',
            'tanggal_agenda' => 'required|date',
            'nomor_surat'    => 'required|string|max:255',
            'tanggal_surat'  => 'required|date',
            'dari'           => 'required|string|max:255',
            'isi_ringkas'    => 'required|string',
            'file'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('surat/masuk', 'public_html');
        }

        $data['created_by'] = auth()->id();
        unset($data['file']);

        SuratMasuk::create($data);
        return redirect()->route('admin.surat.masuk.index')->with('success', 'Surat masuk berhasil ditambahkan.');
    }

    public function masukEdit(SuratMasuk $suratMasuk)
    {
        return view('admin.surat.masuk.edit', ['surat' => $suratMasuk]);
    }

    public function masukUpdate(Request $request, SuratMasuk $suratMasuk)
    {
        $data = $request->validate([
            'nomor_urut'     => 'required|integer',
            'tanggal_agenda' => 'required|date',
            'nomor_surat'    => 'required|string|max:255',
            'tanggal_surat'  => 'required|date',
            'dari'           => 'required|string|max:255',
            'isi_ringkas'    => 'required|string',
            'file'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($suratMasuk->file_path) {
                Storage::disk('public_html')->delete($suratMasuk->file_path);
            }
            $data['file_path'] = $request->file('file')->store('surat/masuk', 'public_html');
        }

        unset($data['file']);
        $suratMasuk->update($data);
        return redirect()->route('admin.surat.masuk.index')->with('success', 'Surat masuk berhasil diperbarui.');
    }

    public function masukDestroy(SuratMasuk $suratMasuk)
    {
        if ($suratMasuk->file_path) {
            Storage::disk('public_html')->delete($suratMasuk->file_path);
        }
        $suratMasuk->delete();
        return back()->with('success', 'Surat masuk berhasil dihapus.');
    }

    public function masukDownload(SuratMasuk $suratMasuk)
    {
        abort_unless($suratMasuk->file_path && Storage::disk('public_html')->exists($suratMasuk->file_path), 404);
        return Storage::disk('public_html')->download($suratMasuk->file_path);
    }

    // ─── SURAT KELUAR ─────────────────────────────────────────────────────

    public function keluarIndex()
    {
            $tahun  = request('tahun', now()->year);  // ← tambah ini
    $surats = SuratKeluar::whereYear('tanggal_agenda', $tahun)  // ← filter by tahun
                         ->orderBy('nomor_urut')->get();

        $kodeKlasifikasi = [
            'ADM' => [
                'label' => 'ADMINISTRASI',
                'sub' => [
                    '00' => ['label' => 'BNSP', 'items' => ['01' => 'Sosialisasi LSP']],
                ],
            ],
            'OG' => [
                'label' => 'KEORGANISASIAN',
                'sub' => [
                    '00' => ['label' => 'Tempat Uji Kompetensi', 'items' => ['01' => 'Sosialisasi Kegiatan', '02' => 'Komite Skema', '03' => 'Komite Teknis', '04' => 'Penanda tangan sertifikat']],
                    '10' => ['label' => 'Tata Naskah', 'items' => ['01' => 'Logo LSPKAP']],
                    '20' => ['label' => 'Personil LSP', 'items' => ['01' => 'Dewan Pengarah', '02' => 'Komite Skema', '03' => 'Asesor', '04' => 'Staf']],
                ],
            ],
            'KU' => [
                'label' => 'KEUANGAN',
                'sub' => [
                    '00' => ['label' => 'Pelaksanaan Ujikom', 'items' => ['01' => 'Invoice', '02' => 'Pembayaran UJK', '03' => 'Honor Asesor', '04' => 'Honor panitia ujikom', '05' => 'Pembagian alokasi keuangan', '06' => 'Fee marketing', '07' => 'Distribusi sertifikat', '08' => 'Pembayaran TUK']],
                    '10' => ['label' => 'Operasional LSP', 'items' => ['01' => 'Honor kegiatan', '02' => 'Honor pengurus', '03' => 'Akomodasi', '04' => 'Transportasi', '05' => 'Konsumsi', '06' => 'Pengadaan ATK', '07' => 'Pengadaan peralatan/perlengkapan kantor']],
                ],
            ],
            'SER' => [
                'label' => 'SERTIFIKASI',
                'sub' => [
                    '00' => ['label' => 'Kerja sama/MoU', 'items' => []],
                    '10' => ['label' => 'Tempat Uji Kompetensi', 'items' => ['01' => 'Penawaran menjadi TUK', '02' => 'Pendaftaran menjadi TUK', '03' => 'Verifikasi TUK', '04' => 'Penetapan TUK Terverifikasi', '05' => 'Personil TUK', '06' => 'Peminjaman TUK']],
                    '20' => ['label' => 'Uji Kompetensi', 'items' => ['01' => 'Sosialisasi', '02' => 'Penawaran UJK', '03' => 'Pendaftaran UJK', '04' => 'Pra Asesmen', '05' => 'Penugasan Asesor', '06' => 'Pleno Hasil Ujikom', '07' => 'Penetapan Kelulusan', '08' => 'Pengajuan Blanko', '09' => 'Sertifikat Kompetensi', '10' => 'Materi Uji Kompetensi']],
                    '30' => ['label' => 'Asesor', 'items' => ['01' => 'Pelatihan Asesor Kompetensi BNSP', '02' => 'RCC Asesor Kompetensi BNSP', '03' => 'Sertifikat Pelatihan/Upgrading', '04' => 'Penetapan Asesor LSP', '05' => 'Pelatihan Teknis Calon Asesor LSP']],
                    '40' => ['label' => 'Skema Sertifikasi', 'items' => ['01' => 'Penambahan ruang lingkup', '02' => 'Verifikasi skema', '03' => 'Perangkat asesmen/MUK', '04' => 'Uji coba Asesmen', '05' => 'Full asesmen', '06' => 'Witness']],
                ],
            ],
            'MT' => [
                'label' => 'MUTU',
                'sub' => [
                    '00' => ['label' => 'Panduan Mutu', 'items' => []],
                ],
            ],
        ];

        // Rekap per bulan
    $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $rekap     = $surats->groupBy(fn($s) => $s->tanggal_agenda->month);
    $tahunList = SuratKeluar::selectRaw('YEAR(tanggal_agenda) as tahun')
                            ->distinct()->orderByDesc('tahun')->pluck('tahun');

    return view('admin.surat.keluar.index', compact('surats', 'kodeKlasifikasi', 'rekap', 'bulanList', 'tahun', 'tahunList'));
    }

    public function keluarCreate()
    {
        $nextNo = (SuratKeluar::max('nomor_urut') ?? 0) + 1;
        return view('admin.surat.keluar.create', compact('nextNo'));
    }

    public function keluarStore(Request $request)
    {
        $data = $request->validate([
            'nomor_urut'     => 'required|integer',
            'tanggal_agenda' => 'required|date',
            'nomor_surat'    => 'required|string|max:255',
            'tanggal_surat'  => 'required|date',
            'kepada'         => 'required|string|max:255',
            'isi_ringkas'    => 'required|string',
            'file'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'kode_klasifikasi' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('surat/keluar', 'public_html');
        }

        $data['created_by'] = auth()->id();
        unset($data['file']);

        SuratKeluar::create($data);
        return redirect()->route('admin.surat.keluar.index')->with('success', 'Surat keluar berhasil ditambahkan.');
    }

    public function keluarEdit(SuratKeluar $suratKeluar)
    {
        return view('admin.surat.keluar.edit', ['surat' => $suratKeluar]);
    }

    public function keluarUpdate(Request $request, SuratKeluar $suratKeluar)
    {
        $data = $request->validate([
            'nomor_urut'     => 'required|integer',
            'tanggal_agenda' => 'required|date',
            'nomor_surat'    => 'required|string|max:255',
            'tanggal_surat'  => 'required|date',
            'kepada'         => 'required|string|max:255',
            'isi_ringkas'    => 'required|string',
            'file'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($suratKeluar->file_path) {
                Storage::disk('public_html')->delete($suratKeluar->file_path);
            }
            $data['file_path'] = $request->file('file')->store('surat/keluar', 'public_html');
        }

        unset($data['file']);
        $suratKeluar->update($data);
        return redirect()->route('admin.surat.keluar.index')->with('success', 'Surat keluar berhasil diperbarui.');
    }

    public function keluarDestroy(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->file_path) {
            Storage::disk('public_html')->delete($suratKeluar->file_path);
        }
        $suratKeluar->delete();
        return back()->with('success', 'Surat keluar berhasil dihapus.');
    }

    public function keluarDownload(SuratKeluar $suratKeluar)
    {
        abort_unless($suratKeluar->file_path && Storage::disk('public_html')->exists($suratKeluar->file_path), 404);
        return Storage::disk('public_html')->download($suratKeluar->file_path);
    }

    public function masukPreview(SuratMasuk $suratMasuk)
    {
        abort_unless($suratMasuk->file_path && Storage::disk('public_html')->exists($suratMasuk->file_path), 404);

        $path     = Storage::disk('public_html')->path($suratMasuk->file_path);
        $mime     = Storage::disk('public_html')->mimeType($suratMasuk->file_path);
        $filename = basename($suratMasuk->file_path);

        return response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function keluarPreview(SuratKeluar $suratKeluar)
    {
        abort_unless($suratKeluar->file_path && Storage::disk('public_html')->exists($suratKeluar->file_path), 404);

        $path     = Storage::disk('public_html')->path($suratKeluar->file_path);
        $mime     = Storage::disk('public_html')->mimeType($suratKeluar->file_path);
        $filename = basename($suratKeluar->file_path);

        return response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    // ─── REKAP EXPORT TAHUNAN ─────────────────────────────────────────────────
public function keluarRekapExport(Request $request)
{
    $tahun  = $request->get('tahun', now()->year);
    $surats = SuratKeluar::whereYear('tanggal_agenda', $tahun)->orderBy('nomor_urut')->get();

    $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $rekap     = $surats->groupBy(fn($s) => $s->tanggal_agenda->month);

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Rekap Tahunan');

    // ── Header judul ──
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'REKAP SURAT KELUAR LSP-KAP TAHUN ' . $tahun);
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 13],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
        'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(28);

    // ── Sub header ──
    $headers = ['No', 'Bulan', 'Jumlah Surat', 'Kode Klasifikasi (Terbanyak)', 'Rincian Kode', 'No. Surat Pertama', 'No. Surat Terakhir'];
    foreach ($headers as $i => $h) {
        $col = chr(65 + $i);
        $sheet->setCellValue($col . '2', $h);
        $sheet->getStyle($col . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }
    $sheet->getRowDimension(2)->setRowHeight(20);

    // ── Data per bulan ──
    $row      = 3;
    $totalAll = 0;
    foreach ($bulanList as $i => $namaBulan) {
        $bulanNo = $i + 1;
        $data    = $rekap->get($bulanNo, collect());
        $jumlah  = $data->count();
        $totalAll += $jumlah;

        $kodes       = $data->pluck('kode_klasifikasi')->filter()->countBy()->sortDesc();
        $kodeTerbanyak = $kodes->keys()->first() ?? '—';
        $rincianKode = $kodes->map(fn($cnt, $k) => "$k ($cnt)")->implode(', ') ?: '—';

        $nomorPertama  = $data->first()?->nomor_surat ?? '—';
        $nomorTerakhir = $data->last()?->nomor_surat ?? '—';

        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $namaBulan);
        $sheet->setCellValue('C' . $row, $jumlah);
        $sheet->setCellValue('D' . $row, $kodeTerbanyak);
        $sheet->setCellValue('E' . $row, $rincianKode);
        $sheet->setCellValue('F' . $row, $nomorPertama);
        $sheet->setCellValue('G' . $row, $nomorTerakhir);

        // Warna baris alternate
        if ($jumlah === 0) {
            $sheet->getStyle("A{$row}:G{$row}")->getFont()->getColor()->setARGB('FFAAAAAA');
        } elseif ($i % 2 === 0) {
            $sheet->getStyle("A{$row}:G{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F7FF');
        }

        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
    }

    // ── Total ──
    $sheet->setCellValue('A' . $row, '');
    $sheet->setCellValue('B' . $row, 'TOTAL');
    $sheet->setCellValue('C' . $row, $totalAll);
    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
        'font' => ['bold' => true],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
    ]);

    // ── Border semua ──
    $sheet->getStyle("A2:G{$row}")->applyFromArray([
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']],
        ],
    ]);

    // ── Lebar kolom ──
    $sheet->getColumnDimension('A')->setWidth(6);
    $sheet->getColumnDimension('B')->setWidth(14);
    $sheet->getColumnDimension('C')->setWidth(14);
    $sheet->getColumnDimension('D')->setWidth(20);
    $sheet->getColumnDimension('E')->setWidth(50);
    $sheet->getColumnDimension('F')->setWidth(35);
    $sheet->getColumnDimension('G')->setWidth(35);

    // ── Sheet daftar lengkap ──
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Daftar Surat');
    $this->_fillDaftarSheet($sheet2, $surats, 'Daftar Surat Keluar Tahun ' . $tahun);

    $spreadsheet->setActiveSheetIndex(0);

    $writer   = new Xlsx($spreadsheet);
    $filename = "Rekap_Surat_Keluar_{$tahun}.xlsx";

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

// ─── REKAP EXPORT PER BULAN ───────────────────────────────────────────────
public function keluarRekapExportBulan(Request $request)
{
    $tahun  = $request->get('tahun', now()->year);
    $bulan  = $request->get('bulan', now()->month);
    $bulanList = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    $surats = SuratKeluar::whereYear('tanggal_agenda', $tahun)
                         ->whereMonth('tanggal_agenda', $bulan)
                         ->orderBy('nomor_urut')
                         ->get();

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Surat ' . $bulanList[$bulan]);

    $this->_fillDaftarSheet($sheet, $surats, "Daftar Surat Keluar — {$bulanList[$bulan]} {$tahun}");

    $writer   = new Xlsx($spreadsheet);
    $filename = "Surat_Keluar_{$bulanList[$bulan]}_{$tahun}.xlsx";

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

// ─── Helper: isi sheet daftar surat ──────────────────────────────────────
private function _fillDaftarSheet($sheet, $surats, string $judul): void
{
    $sheet->mergeCells('A1:H1');
    $sheet->setCellValue('A1', $judul);
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(24);

    $headers = ['No', 'Tgl Agenda', 'No. Surat', 'Tgl Surat', 'Kepada', 'Isi Ringkas', 'Kode Klasifikasi', 'Dokumen'];
    foreach ($headers as $i => $h) {
        $col = chr(65 + $i);
        $sheet->setCellValue($col . '2', $h);
        $sheet->getStyle($col . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    $row = 3;
    foreach ($surats as $i => $s) {
        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $s->tanggal_agenda->format('d/m/Y'));
        $sheet->setCellValue('C' . $row, $s->nomor_surat);
        $sheet->setCellValue('D' . $row, $s->tanggal_surat->format('d/m/Y'));
        $sheet->setCellValue('E' . $row, $s->kepada);
        $sheet->setCellValue('F' . $row, $s->isi_ringkas);
        $sheet->setCellValue('G' . $row, $s->kode_klasifikasi ?? '—');
        $sheet->setCellValue('H' . $row, $s->file_path ? 'Ada' : '—');

        if ($i % 2 === 0) {
            $sheet->getStyle("A{$row}:H{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFF');
        }
        $row++;
    }

    $sheet->getStyle("A2:H" . ($row - 1))->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
    ]);

    $sheet->getColumnDimension('A')->setWidth(6);
    $sheet->getColumnDimension('B')->setWidth(13);
    $sheet->getColumnDimension('C')->setWidth(35);
    $sheet->getColumnDimension('D')->setWidth(13);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(45);
    $sheet->getColumnDimension('G')->setWidth(20);
    $sheet->getColumnDimension('H')->setWidth(10);
}

public function masukRekapExport(Request $request)
{
    $tahun     = $request->get('tahun', now()->year);
    $surats    = SuratMasuk::whereYear('tanggal_agenda', $tahun)->orderBy('nomor_urut')->get();
    $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $rekap     = $surats->groupBy(fn($s) => $s->tanggal_agenda->month);

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Rekap Tahunan');

    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'REKAP SURAT MASUK LSP-KAP TAHUN ' . $tahun);
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(28);

    foreach (['No','Bulan','Jumlah Surat','Pengirim Terbanyak','Rincian Pengirim','No. Surat Pertama','No. Surat Terakhir'] as $i => $h) {
        $col = chr(65 + $i);
        $sheet->setCellValue($col . '2', $h);
        $sheet->getStyle($col . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }
    $sheet->getRowDimension(2)->setRowHeight(20);

    $row = 3; $totalAll = 0;
    foreach ($bulanList as $i => $namaBulan) {
        $bulanNo = $i + 1;
        $data    = $rekap->get($bulanNo, collect());
        $jumlah  = $data->count();
        $totalAll += $jumlah;

        $pengirim       = $data->pluck('dari')->filter()->countBy()->sortDesc();
        $pengirimTerbanyak = $pengirim->keys()->first() ?? '—';
        $rincian        = $pengirim->map(fn($cnt, $k) => "$k ($cnt)")->implode(', ') ?: '—';

        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $namaBulan);
        $sheet->setCellValue('C' . $row, $jumlah);
        $sheet->setCellValue('D' . $row, $pengirimTerbanyak);
        $sheet->setCellValue('E' . $row, $rincian);
        $sheet->setCellValue('F' . $row, $data->first()?->nomor_surat ?? '—');
        $sheet->setCellValue('G' . $row, $data->last()?->nomor_surat ?? '—');

        if ($jumlah === 0) {
            $sheet->getStyle("A{$row}:G{$row}")->getFont()->getColor()->setARGB('FFAAAAAA');
        } elseif ($i % 2 === 0) {
            $sheet->getStyle("A{$row}:G{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0FDF4');
        }
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
    }

    $sheet->setCellValue('B' . $row, 'TOTAL');
    $sheet->setCellValue('C' . $row, $totalAll);
    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
        'font' => ['bold' => true],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']],
    ]);
    $sheet->getStyle("A2:G{$row}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
    ]);

    foreach (['A'=>6,'B'=>14,'C'=>14,'D'=>30,'E'=>55,'F'=>35,'G'=>35] as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Daftar Surat');
    $this->_fillDaftarMasukSheet($sheet2, $surats, 'Daftar Surat Masuk Tahun ' . $tahun);

    $spreadsheet->setActiveSheetIndex(0);
    $writer = new Xlsx($spreadsheet);

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, "Rekap_Surat_Masuk_{$tahun}.xlsx", [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

public function masukRekapExportBulan(Request $request)
{
    $tahun     = $request->get('tahun', now()->year);
    $bulan     = $request->get('bulan', now()->month);
    $bulanList = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    $surats = SuratMasuk::whereYear('tanggal_agenda', $tahun)
                        ->whereMonth('tanggal_agenda', $bulan)
                        ->orderBy('nomor_urut')->get();

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Surat ' . $bulanList[$bulan]);
    $this->_fillDaftarMasukSheet($sheet, $surats, "Daftar Surat Masuk — {$bulanList[$bulan]} {$tahun}");

    $writer = new Xlsx($spreadsheet);
    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, "Surat_Masuk_{$bulanList[$bulan]}_{$tahun}.xlsx", [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

private function _fillDaftarMasukSheet($sheet, $surats, string $judul): void
{
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', $judul);
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(24);

    foreach (['No','Tgl Agenda','No. Surat','Tgl Surat','Dari','Isi Ringkas','Dokumen'] as $i => $h) {
        $col = chr(65 + $i);
        $sheet->setCellValue($col . '2', $h);
        $sheet->getStyle($col . '2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    $row = 3;
    foreach ($surats as $i => $s) {
        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $s->tanggal_agenda->format('d/m/Y'));
        $sheet->setCellValue('C' . $row, $s->nomor_surat);
        $sheet->setCellValue('D' . $row, $s->tanggal_surat->format('d/m/Y'));
        $sheet->setCellValue('E' . $row, $s->dari);
        $sheet->setCellValue('F' . $row, $s->isi_ringkas);
        $sheet->setCellValue('G' . $row, $s->file_path ? 'Ada' : '—');

        if ($i % 2 === 0) {
            $sheet->getStyle("A{$row}:G{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0FDF4');
        }
        $row++;
    }

    $sheet->getStyle("A2:G" . ($row - 1))->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
    ]);

    foreach (['A'=>6,'B'=>13,'C'=>35,'D'=>13,'E'=>30,'F'=>50,'G'=>10] as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }
}
}