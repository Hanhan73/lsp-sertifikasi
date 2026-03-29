<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SkGeneratorService
 *
 * Generate Surat Tugas PDF untuk jadwal asesmen yang sudah disetujui Direktur.
 * Format mengikuti dokumen: Nomor SK, daftar asesor, daftar peserta per halaman.
 *
 * Requires: composer require tecnickcom/tcpdf
 * Atau gunakan reportlab via Python jika stack backend berbeda.
 *
 * Jika TCPDF belum tersedia, service ini menyediakan fallback HTML-to-PDF
 * menggunakan DomPDF (bawaaan Laravel).
 */
class SkGeneratorService
{
    /**
     * Generate nomor SK otomatis.
     * Format: 025/LSP-KAP/SER.{skema_kode}/{bulan_romawi}/{tahun}
     */
    public function generateSkNumber(Schedule $schedule): string
    {
        $count = Schedule::whereYear('approved_at', now()->year)
            ->whereMonth('approved_at', now()->month)
            ->where('approval_status', 'approved')
            ->count() + 1;

        $months = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        $bulan = $months[now()->month];
        $tahun = now()->year;
        $nomor = str_pad($count, 3, '0', STR_PAD_LEFT);

        return "{$nomor}/LSP-KAP/SER.20.07/{$bulan}/{$tahun}";
    }

    /**
     * Generate SK PDF dan simpan ke storage.
     * Mengembalikan path file yang tersimpan.
     */
    public function generate(Schedule $schedule): string
    {
        $schedule->load(['tuk', 'skema', 'asesor', 'asesmens', 'approvedBy']);

        $html = $this->buildHtml($schedule);

        // Coba DomPDF (tersedia di Laravel via barryvdh/laravel-dompdf)
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return $this->generateWithDompdf($schedule, $html);
        }

        // Fallback: simpan sebagai HTML (untuk lingkungan tanpa PDF library)
        return $this->saveAsHtml($schedule, $html);
    }

    /**
     * Generate menggunakan DomPDF.
     */
    private function generateWithDompdf(Schedule $schedule, string $html): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait');

        $filename = 'sk_' . $schedule->id . '_' . Str::slug($schedule->sk_number ?? now()->timestamp) . '.pdf';
        $path     = 'sk/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Fallback: simpan sebagai HTML jika tidak ada PDF library.
     */
    private function saveAsHtml(Schedule $schedule, string $html): string
    {
        $filename = 'sk_' . $schedule->id . '_' . now()->timestamp . '.html';
        $path     = 'sk/' . $filename;
        Storage::disk('public')->put($path, $html);
        return $path;
    }

    /**
     * Build HTML Surat Tugas — mengikuti format dokumen yang dilampirkan.
     */
    public function buildHtml(Schedule $schedule): string
    {
        $asesmens     = $schedule->asesmens;
        $tukName      = $schedule->tuk?->name ?? '-';
        $skemaName    = $schedule->skema?->name ?? '-';
        $tanggal      = $schedule->assessment_date->translatedFormat('d F Y');
        $skNumber     = $schedule->sk_number ?? '-';
        $direkturName = $schedule->approvedBy?->name ?? 'Direktur';
        $direkturNip  = $schedule->approvedBy?->asesor?->no_reg_met ?? '';

        // Daftar asesor — ambil dari schedule atau dari relasi
        // Jika ada 1 asesor di schedule, bisa ditampilkan; 
        // jika ingin multi-asesor, perlu tabel pivot schedule_asesor
        $asesorList = [];
        if ($schedule->asesor) {
            $asesorList[] = [
                'nama'   => $schedule->asesor->nama,
                'no_reg' => $schedule->asesor->no_reg_met ?? '-',
            ];
        }

        // Tanggal asesmen (mungkin 2 hari)
        $tanggalRange = $schedule->assessment_date->format('d') .
            ($schedule->end_time ? '' : '') .
            ' ' . $schedule->assessment_date->translatedFormat('F Y');

        // Split peserta per halaman (30 per halaman)
        $chunks = $asesmens->chunk(30);

        $html = $this->buildHeader($skNumber);
        $html .= $this->buildSuratTugasPage($skNumber, $tukName, $skemaName, $tanggal, $asesorList, $direkturName, $direkturNip, $schedule);

        // Lampiran: daftar peserta
        $pesertaAll = $asesmens->values();
        $pageSize   = 30;
        $pages      = ceil($pesertaAll->count() / $pageSize);

        for ($p = 0; $p < $pages; $p++) {
            $slice = $pesertaAll->slice($p * $pageSize, $pageSize);
            $startNo = $p * $pageSize + 1;
            $html .= $this->buildLampiranPage($skNumber, $tukName, $skemaName, $asesorList, $slice, $startNo);
        }

        $html .= '</body></html>';

        return $html;
    }

    private function buildHeader(string $skNumber): string
    {
        return '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Tugas ' . htmlspecialchars($skNumber) . '</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: "Times New Roman", Times, serif; font-size: 12pt; color: #000; }
  .page { width: 100%; max-width: 210mm; margin: 0 auto; padding: 20mm 25mm; page-break-after: always; }
  .page:last-child { page-break-after: auto; }

  /* Kop surat */
  .kop { display: flex; align-items: center; gap: 16px; border-bottom: 3px solid #c00; padding-bottom: 10px; margin-bottom: 20px; }
  .kop img { width: 70px; height: 70px; object-fit: contain; }
  .kop-text { flex: 1; }
  .kop-text h1 { font-size: 16pt; font-weight: bold; letter-spacing: 1px; }
  .kop-text h2 { font-size: 13pt; font-weight: bold; }
  .kop-text p { font-size: 9pt; margin-top: 2px; }

  /* Judul */
  .judul { text-align: center; margin: 24px 0 4px; }
  .judul h3 { font-size: 14pt; font-weight: bold; text-decoration: underline; letter-spacing: 1px; }
  .judul p { font-size: 11pt; }

  /* Body */
  .body-text { margin-top: 20px; text-align: justify; line-height: 1.8; }

  /* Tabel asesor */
  .tabel-asesor { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 12pt; }
  .tabel-asesor th { background: #000; color: #fff; padding: 6px 8px; text-align: center; font-weight: bold; }
  .tabel-asesor td { border: 1px solid #000; padding: 5px 8px; }
  .tabel-asesor tr:nth-child(even) td { background: #f9f9f9; }

  /* Tabel peserta */
  .tabel-peserta { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 11pt; }
  .tabel-peserta th { background: #000; color: #fff; padding: 5px 8px; text-align: center; font-weight: bold; }
  .tabel-peserta td { border: 1px solid #000; padding: 4px 8px; }
  .tabel-peserta tr:nth-child(even) td { background: #f9f9f9; }

  /* TTD */
  .ttd { margin-top: 40px; text-align: right; }
  .ttd p { margin-bottom: 4px; }
  .ttd .nama { font-weight: bold; text-decoration: underline; margin-top: 70px; }
  .ttd .nip { font-size: 10pt; }

  /* Lampiran header */
  .lampiran-header { margin-bottom: 16px; }
  .lampiran-judul { text-align: center; font-weight: bold; text-decoration: underline; margin: 16px 0 4px; font-size: 12pt; }
  .lampiran-sub { text-align: center; font-weight: bold; font-size: 11pt; }

  @media print {
    .page { page-break-after: always; padding: 15mm 20mm; }
    .page:last-child { page-break-after: auto; }
  }
</style>
</head>
<body>';
    }

    private function buildKop(): string
    {
        // Logo bisa diganti dengan path sebenarnya atau base64
        return '<div class="kop">
            <div style="width:70px;height:70px;border:2px solid #c00;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9pt;font-weight:bold;text-align:center;color:#c00;">BNSP<br>LSP</div>
            <div class="kop-text">
                <h1>LEMBAGA SERTIFIKASI PROFESI</h1>
                <h2>Kompetensi Administrasi Perkantoran</h2>
                <p>Jalan Otto Iskandar Dinata Nomor 392 Bandung Jawa Barat. Telepon 087716252855</p>
                <p>Email: lspkap2024@gmail.com. Laman: www.lsp-ap.com</p>
            </div>
        </div>';
    }

    private function buildSuratTugasPage(
        string $skNumber,
        string $tukName,
        string $skemaName,
        string $tanggal,
        array  $asesorList,
        string $direkturName,
        string $direkturNip,
        Schedule $schedule
    ): string {
        $asesorRows = '';
        foreach ($asesorList as $i => $a) {
            $no = $i + 1;
            $asesorRows .= "<tr>
                <td style=\"text-align:center;width:40px;\">{$no}</td>
                <td>" . htmlspecialchars($a['nama']) . "</td>
                <td>" . htmlspecialchars($a['no_reg']) . "</td>
            </tr>";
        }

        if (empty($asesorRows)) {
            $asesorRows = '<tr><td colspan="3" style="text-align:center;font-style:italic;">Asesor belum ditugaskan</td></tr>';
        }

        $tanggalSurat = now()->translatedFormat('d F Y');
        $pesertaCount = $schedule->asesmens->count();

        return '<div class="page">
            ' . $this->buildKop() . '

            <div class="judul">
                <h3>SURAT TUGAS</h3>
                <p>Nomor: ' . htmlspecialchars($skNumber) . '</p>
            </div>

            <div class="body-text">
                <p>Berdasarkan permohonan dari TUK ' . htmlspecialchars($tukName) . '
                   dan tentang Sertifikasi Kompetensi, maka Kami menugaskan:</p>
            </div>

            <table class="tabel-asesor" style="margin:16px 0;">
                <thead>
                    <tr>
                        <th style="width:40px;">NO</th>
                        <th>NAMA ASESOR</th>
                        <th>NOMOR REGISTRASI ASESOR</th>
                    </tr>
                </thead>
                <tbody>' . $asesorRows . '</tbody>
            </table>

            <div class="body-text">
                <p>untuk melaksanakan tugas sebagai penguji Sertifikasi Kompetensi pada Skema
                   <strong>' . htmlspecialchars($skemaName) . '</strong>
                   pada tanggal <strong>' . htmlspecialchars($tanggal) . '</strong>
                   beralamat <strong>' . htmlspecialchars($schedule->location ?? '-') . '</strong>.</p>
                <br>
                <p>Demikian surat tugas ini Kami sampaikan untuk dilaksanakan dengan penuh tanggung
                   jawab, atas perhatian dan kerjasamanya Kami ucapkan terima kasih.</p>
            </div>

            <div class="ttd">
                <p>' . htmlspecialchars($schedule->assessment_date->format('d F Y')) . '</p>
                <p>Direktur,</p>
                <p class="nama">' . htmlspecialchars($direkturName) . '</p>
                ' . ($direkturNip ? '<p class="nip">Met. ' . htmlspecialchars($direkturNip) . '</p>' : '') . '
            </div>
        </div>';
    }

    private function buildLampiranPage(
        string $skNumber,
        string $tukName,
        string $skemaName,
        array  $asesorList,
        $peserta,
        int    $startNo
    ): string {
        $rows = '';
        $no   = $startNo;
        foreach ($peserta as $p) {
            $rows .= '<tr>
                <td style="text-align:center;width:40px;">' . $no++ . '.</td>
                <td>' . htmlspecialchars($p->full_name) . '</td>
                <td></td>
            </tr>';
        }

        // Kolom asesor di kanan — tampilkan di baris pertama saja (rowspan)
        // Untuk PDF sederhana, tampilkan sebagai list terpisah
        $asesorListHtml = '';
        foreach ($asesorList as $i => $a) {
            $asesorListHtml .= '<li>' . ($i + 1) . '. ' . htmlspecialchars($a['nama']) . '</li>';
        }

        return '<div class="page">
            ' . $this->buildKop() . '

            <div class="lampiran-header">
                <p>Lampiran Surat Tugas</p>
                <p>Nomor: ' . htmlspecialchars($skNumber) . '</p>
            </div>

            <div class="lampiran-judul">DAFTAR PESERTA SERTIFIKASI KOMPETENSI</div>
            <div class="lampiran-sub">TUK ' . strtoupper(htmlspecialchars($tukName)) . '</div>
            <div class="lampiran-sub">SKEMA ' . strtoupper(htmlspecialchars($skemaName)) . '</div>

            <table class="tabel-peserta" style="margin-top:16px;">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama Peserta</th>
                        <th style="width:200px;">Asesor</th>
                    </tr>
                </thead>
                <tbody>' . $this->buildPesertaRowsWithAsesor($peserta, $startNo, $asesorList) . '</tbody>
            </table>
        </div>';
    }

    /**
     * Build peserta rows — asesor ditampilkan sebagai rowspan di baris pertama setiap halaman,
     * mengikuti format dokumen asli.
     */
    private function buildPesertaRowsWithAsesor($peserta, int $startNo, array $asesorList): string
    {
        $rows       = '';
        $no         = $startNo;
        $count      = count($peserta instanceof \Illuminate\Support\Collection ? $peserta->all() : $peserta);
        $asesorHtml = '';

        foreach ($asesorList as $i => $a) {
            $asesorHtml .= ($i + 1) . '. ' . htmlspecialchars($a['nama']) . '<br>';
        }

        $first = true;
        foreach ($peserta as $p) {
            if ($first) {
                $rows .= '<tr>
                    <td style="text-align:center;">' . $no++ . '.</td>
                    <td>' . htmlspecialchars($p->full_name) . '</td>
                    <td rowspan="' . $count . '" style="vertical-align:top;padding:8px;">' . $asesorHtml . '</td>
                </tr>';
                $first = false;
            } else {
                $rows .= '<tr>
                    <td style="text-align:center;">' . $no++ . '.</td>
                    <td>' . htmlspecialchars($p->full_name) . '</td>
                </tr>';
            }
        }

        return $rows;
    }
}