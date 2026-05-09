<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

/**
 * PdfMergeService
 *
 * Menggabungkan 2 PDF:
 *   1. Kwitansi honor (generated oleh DomPDF, landscape A4)
 *   2. Bukti transfer (file upload dari bendahara)
 *
 * Requires: composer require setasign/fpdi
 */
class PdfMergeService
{
    /**
     * Merge kwitansi PDF string dengan file bukti transfer.
     *
     * @param  string  $kwitansiPdfString  Output string dari DomPDF (->output())
     * @param  string  $buktiFilePath      Path absolut ke file bukti transfer
     * @return string  PDF binary string hasil merge
     */
    public function mergeKwitansiDenganBukti(string $kwitansiPdfString, string $buktiFilePath): string
    {
        // Simpan kwitansi ke temp file dulu (FPDI butuh path, bukan string)
        $tempKwitansi = tempnam(sys_get_temp_dir(), 'kwitansi_') . '.pdf';
        file_put_contents($tempKwitansi, $kwitansiPdfString);

        try {
            $pdf = new Fpdi('L', 'mm', 'A4'); // landscape A4
            $pdf->SetAutoPageBreak(false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // ── Halaman 1+: semua halaman kwitansi ───────────────────────
            $this->importAllPages($pdf, $tempKwitansi, 'L');

            // ── Halaman bukti transfer ────────────────────────────────────
            $ext = strtolower(pathinfo($buktiFilePath, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                // Import semua halaman PDF bukti
                $this->importAllPages($pdf, $buktiFilePath, 'P'); // portrait untuk bukti
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                // Embed gambar sebagai halaman baru
                $this->addImagePage($pdf, $buktiFilePath, $ext);
            }

            return $pdf->Output('S'); // S = return as string
        } finally {
            // Hapus temp file
            if (file_exists($tempKwitansi)) {
                unlink($tempKwitansi);
            }
        }
    }

    /**
     * Import semua halaman dari satu PDF ke FPDI instance.
     */
    private function importAllPages(Fpdi $pdf, string $filePath, string $orientation = 'L'): void
    {
        $pageCount = $pdf->setSourceFile($filePath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $size       = $pdf->getTemplateSize($templateId);

            // Deteksi orientasi dari ukuran halaman asli
            $isLandscape = $size['width'] > $size['height'];
            $pageOrient  = $isLandscape ? 'L' : 'P';

            $pdf->AddPage($pageOrient, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
        }
    }

    /**
     * Tambah halaman baru berisi gambar (JPG/PNG) full-page.
     */
    private function addImagePage(Fpdi $pdf, string $imagePath, string $ext): void
    {
        // A4 portrait dalam mm: 210 x 297
        $pdf->AddPage('P', 'A4');

        $pageW = $pdf->GetPageWidth();
        $pageH = $pdf->GetPageHeight();
        $margin = 10; // mm padding

        $imgType = strtoupper($ext === 'jpg' ? 'JPEG' : $ext);

        // Hitung dimensi proporsional
        [$imgW, $imgH] = getimagesize($imagePath);
        $ratio   = $imgW / $imgH;
        $maxW    = $pageW - ($margin * 2);
        $maxH    = $pageH - ($margin * 2);

        if ($imgW / $maxW > $imgH / $maxH) {
            $drawW = $maxW;
            $drawH = $maxW / $ratio;
        } else {
            $drawH = $maxH;
            $drawW = $maxH * $ratio;
        }

        $x = ($pageW - $drawW) / 2;
        $y = ($pageH - $drawH) / 2;

        $pdf->Image($imagePath, $x, $y, $drawW, $drawH, $imgType);
    }
}