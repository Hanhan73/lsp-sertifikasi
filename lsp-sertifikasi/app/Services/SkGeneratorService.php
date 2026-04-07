<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Support\Facades\Storage;

class SkGeneratorService
{
    public function generateSkNumber(Schedule $schedule): string
    {
        $count = Schedule::whereYear('approved_at', now()->year)
            ->whereMonth('approved_at', now()->month)
            ->where('approval_status', 'approved')
            ->count();

        $months = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        $bulan = $months[$schedule->approved_at->month];
        $tahun = $schedule->approved_at->year;
        $nomor = str_pad($count, 3, '0', STR_PAD_LEFT);

        return "{$nomor}/LSP-KAP/SER.20.07/{$bulan}/{$tahun}";
    }

    public function generate(Schedule $schedule): string
    {
        $schedule->load(['tuk', 'skema', 'asesor', 'asesmens', 'approvedBy']);
        $html = $this->buildHtml($schedule);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return $this->generateWithDompdf($schedule, $html);
        }

        return $this->saveAsHtml($schedule, $html);
    }

    private function generateWithDompdf(Schedule $schedule, string $html): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait');

        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', false);

        $filename = 'st_' . $schedule->id . '_' . now()->timestamp . '.pdf';
        $path     = 'st/' . $filename;

        Storage::disk('private')->put($path, $pdf->output());

        return $path;
    }

    private function saveAsHtml(Schedule $schedule, string $html): string
    {
        $filename = 'st_' . $schedule->id . '_' . now()->timestamp . '.html';
        $path     = 'st/' . $filename;
        Storage::disk('private')->put($path, $html);
        return $path;
    }

    // =========================================================================
    // HTML Builder
    // =========================================================================

    public function buildHtml(Schedule $schedule): string
    {
        $html  = $this->buildDocHead($schedule->sk_number ?? '-');
        $html .= $this->buildSuratTugasPage($schedule);

        $asesmens = $schedule->asesmens->values();
        $pageSize = 20;
        $pages    = (int) ceil($asesmens->count() / $pageSize);

        for ($p = 0; $p < $pages; $p++) {
            $slice   = $asesmens->slice($p * $pageSize, $pageSize);
            $startNo = $p * $pageSize + 1;
            $html   .= $this->buildLampiranPage($schedule, $slice, $startNo);
        }

        $html .= '</body></html>';
        return $html;
    }

    private function buildDocHead(string $skNumber): string
    {
        // Kunci untuk DomPDF agar margin benar:
        // 1. @page { margin } — ini yang mengatur margin kertas di DomPDF
        // 2. body { margin: 0 } — jangan double margin
        // 3. Semua lebar elemen pakai 100% bukan px fixed
        // 4. Tabel pakai width:100%, bukan px
        // 5. display:flex TIDAK didukung DomPDF — pakai table

        return '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Tugas ' . htmlspecialchars($skNumber) . '</title>
<style>
  @page {
    size: A4;
    margin: 0;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: "Times New Roman", Times, serif;
    font-size: 12pt;
    color: #000;
    background: #fff;
    padding: 1.3cm 3cm 2cm 3cm;

  }

  .page {
    width: 100%;
    page-break-after: always;
  }
  .page:last-child { page-break-after: auto; }

  /* Kop surat */
  .kop-img   { width: 100%; height: auto; display: block; }
  .kop-border { border-bottom: 3px solid #ffffff; margin-bottom: 14pt; }

  /* Judul */
  .judul { text-align: center; margin: 16pt 0 4pt; }
  .judul h3 {
    font-size: 14pt;
    font-weight: bold;
    letter-spacing: 1pt;
    text-transform: uppercase;
  }
  .judul p { font-size: 12pt; margin-top: 1pt; }

  /* Body teks */
  .body-text {
    margin-top: 12pt;
    text-align: justify;
    line-height: 1.9;
    font-size: 12pt;
  }
  .body-text p { margin-bottom: 8pt; }

  /* Asesor block */
  .asesor-block { margin: 8pt 0 8pt 16pt; font-size: 12pt; line-height: 1.9; }
  .asesor-table { border: none; border-collapse: collapse; }
  .asesor-label { width: 150pt; padding: 1pt 0; vertical-align: top; }
  .asesor-colon { width: 12pt; padding: 1pt 0; vertical-align: top; }
  .asesor-value { padding: 1pt 0; vertical-align: top; }

  /* TTD */
  .ttd-outer  { margin-top:24pt; width: 100%; }
  .ttd-table  { width: 100%; border: none; border-collapse: collapse; }
  .ttd-left   { width: 60%; }
  .ttd-right  { width: 40%; text-align: center; vertical-align: top; font-size: 12pt; line-height: 1.2; }
  .ttd-img    { width: 250pt; height: 130pt; display: block; margin: 0 auto; }
  .ttd-placeholder { width: 140pt; height: 70pt; display: block; margin: 0 auto; }
  .nama-direktur { font-weight: bold; text-decoration: underline; font-size: 12pt; }
  .nip-direktur  { font-size: 10pt; }

  /* Lampiran */
  .lampiran-header { margin-bottom: 8pt; line-height: 1.7; font-size: 12pt; }
  .lampiran-judul  { text-align: center; font-weight: bold; margin: 10pt 0 2pt; font-size: 12pt; }
  .lampiran-sub    { text-align: center; font-weight: bold; font-size: 11pt; line-height: 1.6; }

  /* Tabel peserta */
  .tabel-peserta { width: 100%; border-collapse: collapse; margin-top: 10pt; font-size: 11pt; }
  .tabel-peserta th {
    background: #ffffff;
    color: #000000;
    padding: 5pt 8pt;
    text-align: center;
    font-weight: bold;
    border: 1pt solid #222222;
  }
  .tabel-peserta td { border: 1pt solid #020202; padding: 4pt 8pt; vertical-align: top; }
  .tabel-peserta tr.even td { background: #f5f5f5; }
</style>
</head>
<body>';
    }

    // =========================================================================
    // Halaman 1: Surat Tugas
    // =========================================================================

    private function buildSuratTugasPage(Schedule $schedule): string
    {
        $skNumber     = $schedule->sk_number ?? '-';
        $tukName      = $schedule->tuk?->name ?? '-';
        $skemaName    = $schedule->skema?->name ?? '-';
        $location     = $schedule->location ?? '-';
        $direkturName = $schedule->approvedBy?->name ?? 'Direktur';
        $direkturNip  = $schedule->approvedBy?->asesor?->no_reg_met ?? '';

        $tanggalAsesmen = $this->formatTanggalId($schedule->assessment_date);
        $tanggalSurat   = $schedule->approved_at
            ? $this->formatTanggalId($schedule->approved_at)
            : $this->formatTanggalId(now());

        $asesorNama  = $schedule->asesor?->nama ?? '-';
        $asesorNoReg = $schedule->asesor?->no_reg_met ?? '-';

        $kopHtml = $this->buildKop();
        $ttdHtml = $this->buildTtd($tanggalSurat, $direkturName, $direkturNip);

        return '<div class="page">
            ' . $kopHtml . '

            <div class="judul">
                <h3>Surat Tugas</h3>
                <p style="margin-bottom:32pt;">Nomor: ' . htmlspecialchars($skNumber) . '</p>
            </div>

            <div class="body-text">
                <p>Berdasarkan permohonan dari ' . htmlspecialchars($tukName) . '
                dan tentang Sertifikasi Kompetensi, maka Kami menugaskan:</p>
            </div>

            <div class="asesor-block">
                <table class="asesor-table">
                    <tr>
                        <td class="asesor-label">Nama</td>
                        <td class="asesor-colon">:</td>
                        <td class="asesor-value">' . htmlspecialchars($asesorNama) . '</td>
                    </tr>
                    <tr>
                        <td class="asesor-label">Nomor Registrasi</td>
                        <td class="asesor-colon">:</td>
                        <td class="asesor-value">' . htmlspecialchars($asesorNoReg) . '</td>
                    </tr>
                </table>
            </div>

            <div class="body-text">
                <p>untuk melaksanakan tugas sebagai penguji Sertifikasi Kompetensi pada Skema
                ' . htmlspecialchars($skemaName) . '
                pada tanggal ' . htmlspecialchars($tanggalAsesmen) . '
                beralamat ' . htmlspecialchars($location) . '.</p>

                <p>Demikian surat tugas ini Kami sampaikan untuk dilaksanakan dengan penuh tanggung
                jawab, atas perhatian dan kerjasamanya Kami ucapkan terima kasih.</p>
            </div>

            ' . $ttdHtml . '
        </div>';
    }

    // =========================================================================
    // Halaman Lampiran
    // =========================================================================

    private function buildLampiranPage(Schedule $schedule, $peserta, int $startNo): string
    {
        $skNumber   = $schedule->sk_number ?? '-';
        $tukName    = $schedule->tuk?->name ?? '-';
        $skemaName  = $schedule->skema?->name ?? '-';
        $asesorNama = $schedule->asesor?->nama ?? '-';

        $pesertaList = $peserta instanceof \Illuminate\Support\Collection
            ? $peserta->all()
            : (array) $peserta;
        $count = count($pesertaList);

        $rows = '';
        $no   = $startNo;
        $i    = 0;
        foreach ($pesertaList as $p) {
            $evenClass  = ($i % 2 === 1) ? ' class="even"' : '';
            $asesorCell = ($i === 0)
                ? '<td style="width:130pt;vertical-align:top;" rowspan="' . $count . '">' . htmlspecialchars($asesorNama) . '</td>'
                : '';
            $rows .= '<tr' . $evenClass . '>
                <td style="text-align:center;width:30pt;">' . $no++ . '.</td>
                <td>' . htmlspecialchars($p->full_name ?? '-') . '</td>
                ' . $asesorCell . '
            </tr>';
            $i++;
        }

        $kopHtml = $this->buildKop();

        return '<div class="page">
            ' . $kopHtml . '

            <div class="lampiran-header">
                <p>Lampiran Surat Tugas</p>
                <p>Nomor: ' . htmlspecialchars($skNumber) . '</p>
            </div>

            <div class="lampiran-judul">DAFTAR PESERTA SERTIFIKASI KOMPETENSI</div>
            <div class="lampiran-sub"> ' . strtoupper(htmlspecialchars($tukName)) . '</div>
            <div class="lampiran-sub">SKEMA ' . strtoupper(htmlspecialchars($skemaName)) . '</div>

            <table class="tabel-peserta">
                <thead>
                    <tr>
                        <th style="width:30pt;">No</th>
                        <th>Nama Peserta</th>
                        <th style="width:130pt;">Asesor</th>
                    </tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>
        </div>';
    }

    // =========================================================================
    // Komponen: Kop Surat
    // =========================================================================

    private function buildKop(): string
    {
        $kopPath = storage_path('app/public/images/kop_surat.png');

        if (file_exists($kopPath)) {
            $b64 = base64_encode(file_get_contents($kopPath));
            $src = 'data:image/png;base64,' . $b64;
            return '<div class="kop-border">
                <img src="' . $src . '" class="kop-img" alt="Kop Surat LSP-KAP">
            </div>';
        }

        // Fallback pakai table (DomPDF tidak support flex)
        return '<div class="kop-border" style="padding-bottom:8pt;">
            <table style="width:100%;border:none;border-collapse:collapse;">
                <tr>
                    <td style="width:75pt;vertical-align:middle;padding-right:10pt;">
                        <div style="width:65pt;height:65pt;border:2pt solid #cc0000;border-radius:32pt;text-align:center;font-size:8pt;font-weight:bold;color:#cc0000;padding-top:20pt;">BNSP<br>LSP</div>
                    </td>
                    <td style="vertical-align:middle;border-left:2pt solid #555;padding-left:10pt;">
                        <div style="font-size:15pt;font-weight:bold;font-family:Arial,sans-serif;">LEMBAGA SERTIFIKASI PROFESI</div>
                        <div style="font-size:12pt;font-weight:bold;font-family:Arial,sans-serif;">Kompetensi Administrasi Perkantoran</div>
                        <div style="font-size:9pt;margin-top:2pt;">
                            Jalan Otto Iskandar Dinata Nomor 392 Bandung Jawa Barat. Telepon 087716252855<br>
                            Email: lspkap2024@gmail.com. Laman: www.lsp-ap.com
                        </div>
                    </td>
                </tr>
            </table>
        </div>';
    }

    // =========================================================================
    // Komponen: TTD Direktur
    // =========================================================================

    private function buildTtd(string $tanggalSurat, string $direkturName, string $direkturNip): string
    {
        $ttdPath = storage_path('app/private/direktur/ttd.png');

        if (file_exists($ttdPath)) {
            $b64        = base64_encode(file_get_contents($ttdPath));
            $ttdImgHtml = '<img src="data:image/png;base64,' . $b64 . '" class="ttd-img" alt="TTD Direktur">';
        } else {
            $ttdImgHtml = '<div class="ttd-placeholder"></div>';
        }

        // Format tanggal singkat tanpa nama hari
        $tanggalSingkat = $this->formatTanggalSingkat($tanggalSurat);

        return '<div class="ttd-outer">
            <table class="ttd-table">
                <tr>
                    <td class="ttd-left"></td>
                    <td class="ttd-right">
                        <p style="line-height:1.2;margin-bottom:-15pt;">Bandung, ' . $tanggalSingkat . '</p>
                        ' . $ttdImgHtml . '
                    </td>
                </tr>
            </table>
        </div>';
    }

    // =========================================================================
    // Helper: Format tanggal bahasa Indonesia
    // =========================================================================

    private function formatTanggalId(\DateTimeInterface|string $date): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        $hari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis',
            'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];
        $bulan = [
            1 => 'Januari',  2 => 'Februari', 3 => 'Maret',
            4 => 'April',    5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',     8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $namaHari  = $hari[$date->format('l')] ?? $date->format('l');
        $namaBulan = $bulan[(int) $date->format('n')] ?? $date->format('F');

        return $namaHari . ', ' . $date->format('d') . ' ' . $namaBulan . ' ' . $date->format('Y');
    }

    // Format tanggal singkat: "01 April 2026" (tanpa nama hari)
    private function formatTanggalSingkat(string $tanggalPanjang): string
    {
        // Input: "Rabu, 01 April 2026" → Output: "01 April 2026"
        $parts = explode(', ', $tanggalPanjang, 2);
        return $parts[1] ?? $tanggalPanjang;
    }
}