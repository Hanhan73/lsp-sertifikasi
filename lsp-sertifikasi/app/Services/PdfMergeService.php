<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

/**
 * PdfMergeService
 *
 * Requires:
 *   composer require setasign/fpdi setasign/fpdf
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
        // Simpan kwitansi ke temp file (FPDI butuh path)
        $tempKwitansi = tempnam(sys_get_temp_dir(), 'kwitansi_') . '.pdf';
        file_put_contents($tempKwitansi, $kwitansiPdfString);

        try {
            $pdf = new Fpdi();
            $pdf->SetAutoPageBreak(false);

            // ── Import semua halaman kwitansi ─────────────────────────────
            $this->importAllPages($pdf, $tempKwitansi);

            // ── Append bukti transfer ─────────────────────────────────────
            $ext = strtolower(pathinfo($buktiFilePath, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                $this->importAllPages($pdf, $buktiFilePath);
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $this->addImagePage($pdf, $buktiFilePath, $ext);
            }

            // Output sebagai string
            return $pdf->Output('S');
        } finally {
            if (file_exists($tempKwitansi)) {
                unlink($tempKwitansi);
            }
        }
    }

    /**
     * Import semua halaman dari satu PDF ke FPDI instance.
     * Deteksi orientasi otomatis dari ukuran halaman asli.
     */
    private function importAllPages(Fpdi $pdf, string $filePath): void
    {
        $pageCount = $pdf->setSourceFile($filePath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $size       = $pdf->getTemplateSize($templateId);

            // Deteksi orientasi
            $isLandscape = $size['width'] > $size['height'];
            $orientation = $isLandscape ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
        }
    }

    /**
     * Tambah halaman baru berisi gambar full-page (A4 portrait).
     */
    private function addImagePage(Fpdi $pdf, string $imagePath, string $ext): void
    {
        $pdf->AddPage('P', 'A4');

        $pageW  = $pdf->GetPageWidth();
        $pageH  = $pdf->GetPageHeight();
        $margin = 10; // mm

        $imgType = strtoupper($ext === 'jpg' ? 'JPEG' : $ext);

        [$imgW, $imgH] = getimagesize($imagePath);
        $ratio  = $imgW / $imgH;
        $maxW   = $pageW - ($margin * 2);
        $maxH   = $pageH - ($margin * 2);

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