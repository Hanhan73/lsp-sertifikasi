<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelService
{
    // Sheet yang tidak punya kolom Nama sama sekali
    private array $skipSheets = ['Cover', 'Asesor', 'Berita Acara'];

    // =========================================================================
    // INJECT NAMA ASESI KE TEMPLATE
    // =========================================================================

    public function injectNamaAsesi(string $inputPath, string $outputPath, array $names): bool
    {
        try {
            $reader      = $this->createReader($inputPath);
            $spreadsheet = $reader->load($inputPath);

            $dataSheets = array_values(array_filter(
                $spreadsheet->getSheetNames(),
                fn($name) => !in_array($name, $this->skipSheets)
            ));

            if (empty($dataSheets)) {
                \Log::warning('[ExcelService] Tidak ada sheet data ditemukan.');
                return false;
            }

            $injectedCount = 0;

            foreach ($dataSheets as $sheetName) {
                $ws = $spreadsheet->getSheetByName($sheetName);
                if (!$ws) continue;

                // Cari header 'Nama' — scan baris 1-30, kolom 1-30
                [$headerRow, $namaCol] = $this->findNamaHeader($ws);
                if (!$headerRow) {
                    \Log::info("[ExcelService] SKIP [{$sheetName}]: header 'Nama' tidak ditemukan.");
                    continue;
                }

                $dataStartRow = $this->detectDataStartRow($ws, $headerRow, $namaCol);

                \Log::info("[ExcelService] [{$sheetName}] headerRow={$headerRow}, namaCol={$namaCol}, dataStart={$dataStartRow}");

                // Unmerge merged cells yang overlap kolom Nama di baris data
                $this->unmergeNamaColumn($ws, $namaCol, $dataStartRow, count($names));

                // Inject nama + formula per sheet
                foreach ($names as $i => $nama) {
                    $row = $dataStartRow + $i;
                    $ws->setCellValueByColumnAndRow($namaCol, $row, $nama);

                    $this->injectRowFormulas($sheetName, $ws, $row, $i + 1, $namaCol);
                }

                \Log::info("[ExcelService] Injected " . count($names) . " nama ke [{$sheetName}]");
                $injectedCount++;
            }

            if ($injectedCount === 0) {
                \Log::warning('[ExcelService] Tidak ada sheet yang berhasil diinjeksi.');
                return false;
            }

            // Inject Berita Acara secara terpisah
            $this->injectBeritaAcara($spreadsheet, $names);

            $writer = IOFactory::createWriter($spreadsheet, $this->detectFormat($inputPath));
            $writer->save($outputPath);

            if (!file_exists($outputPath) || filesize($outputPath) < 1000) {
                \Log::warning('[ExcelService] Output file suspiciously small, possible corruption');
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            \Log::error('[ExcelService] injectNamaAsesi error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    // =========================================================================
    // INJECT FORMULA PER BARIS SESUAI SHEET
    // =========================================================================

    /**
     * Inject nomor urut dan formula yang relevan untuk baris yang baru diisi nama.
     *
     * - Pencapaian  : col A = nomor, col N = formula Pencapaian (K/BK)
     * - Hasil Asesmen: col C = nomor, col E-O = formula UK per UK, col P = Keputusan Asesmen
     */
    private function injectRowFormulas(string $sheetName, $ws, int $row, int $no, int $namaCol): void
    {
        switch ($sheetName) {

            case 'Pencapaian':
                // Col A = No urut
                $ws->setCellValueByColumnAndRow(1, $row, $no);

                // Col N (14) = formula Pencapaian K/BK berdasarkan UK 01-11 (C-M)
                $ws->setCellValueByColumnAndRow(14, $row,
                    "=IF(AND(C{$row}>70,D{$row}>70,E{$row}>70,F{$row}>70,G{$row}>70,"
                    . "H{$row}>70,I{$row}>70,J{$row}>70,K{$row}>70,L{$row}>70,M{$row}>70)=TRUE,\"K\",\"BK\")"
                );
                break;

            case 'Hasil Asesmen':
                // Col C (3) = No urut
                $ws->setCellValueByColumnAndRow(3, $row, $no);

                // Col E-O (5-15) = formula K/BK per UK, referensi ke Pencapaian C-M
                $pencapaianCols = ['C','D','E','F','G','H','I','J','K','L','M'];
                foreach ($pencapaianCols as $i => $pc) {
                    $col = 5 + $i; // E=5, F=6, ..., O=15
                    $ws->setCellValueByColumnAndRow($col, $row,
                        "=IF(Pencapaian!{$pc}{$row}>70,\"K\",\"BK\")"
                    );
                }

                // Col P (16) = Keputusan Asesmen (K hanya jika semua UK = K)
                $ws->setCellValueByColumnAndRow(16, $row,
                    "=IF(AND(E{$row}=\"K\",F{$row}=\"K\",G{$row}=\"K\",H{$row}=\"K\","
                    . "I{$row}=\"K\",J{$row}=\"K\",K{$row}=\"K\",L{$row}=\"K\","
                    . "M{$row}=\"K\",N{$row}=\"K\",O{$row}=\"K\")=TRUE,\"K\",\"BK\")"
                );
                break;
        }
    }

    // =========================================================================
    // INJECT BERITA ACARA
    // =========================================================================

    /**
     * Inject data peserta ke sheet Berita Acara.
     *
     * Struktur per baris (mulai row 19):
     *   C = No urut
     *   D = =Pencapaian!B{pencapaian_row}   (nama dari sheet Pencapaian, mulai B4)
     *   J = ='Hasil Asesmen'!P{ha_row}       (keputusan dari sheet Hasil Asesmen, mulai P4)
     *
     * Row offset: Pencapaian/Hasil Asesmen data mulai row 4,
     *             Berita Acara data mulai row 19 → pencapaian_row = ba_row - 15
     */
    private function injectBeritaAcara($spreadsheet, array $names): void
    {
        if (!in_array('Berita Acara', $spreadsheet->getSheetNames())) {
            \Log::warning('[ExcelService] Sheet Berita Acara tidak ditemukan, skip.');
            return;
        }

        $ws = $spreadsheet->getSheetByName('Berita Acara');

        $baStartRow   = 19;  // baris pertama data di Berita Acara
        $srcStartRow  = 4;   // baris pertama data di Pencapaian & Hasil Asesmen

        foreach ($names as $i => $nama) {
            $baRow  = $baStartRow + $i;
            $srcRow = $srcStartRow + $i;  // row di Pencapaian & Hasil Asesmen

            // C = No urut
            $ws->setCellValueByColumnAndRow(3, $baRow, $i + 1);

            // D = nama dari Pencapaian (agar konsisten dengan sheet lain)
            $ws->setCellValueByColumnAndRow(4, $baRow, "=Pencapaian!B{$srcRow}");

            // J (col 10) = Keputusan dari Hasil Asesmen kolom P
            $ws->setCellValueByColumnAndRow(10, $baRow, "='Hasil Asesmen'!P{$srcRow}");
        }

        \Log::info('[ExcelService] Berita Acara injected for ' . count($names) . ' peserta.');
    }

    // =========================================================================
    // PARSE BERITA ACARA
    // =========================================================================

    public function parseBeritaAcara(string $filePath): ?array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xlsm', 'xls'])) {
            return null;
        }

        try {
            $reader      = $this->createReader($filePath);
            $spreadsheet = $reader->load($filePath);
        } catch (\Throwable $e) {
            \Log::error('[ExcelService] Gagal membuka file: ' . $e->getMessage());
            return ['error' => 'Gagal membuka file: ' . $e->getMessage()];
        }

        if (!in_array('Berita Acara', $spreadsheet->getSheetNames())) {
            return ['error' => "Sheet 'Berita Acara' tidak ditemukan di file ini"];
        }

        $ws = $spreadsheet->getSheetByName('Berita Acara');

        // Cari teks 'Pada tanggal'
        $tanggalRaw = null;
        for ($r = 1; $r <= 20; $r++) {
            $cell = $ws->getCellByColumnAndRow(3, $r)->getValue();
            if ($cell && str_contains((string) $cell, 'Pada tanggal')) {
                $tanggalRaw = trim((string) $cell);
                break;
            }
        }

        // Deteksi mode 2-kolom: col K row 18 = 'BK'
        $row18ColK  = strtoupper(trim((string) ($ws->getCellByColumnAndRow(11, 18)->getValue() ?? '')));
        $twoColMode = $row18ColK === 'BK';

        $peserta = [];
        $maxRow  = $ws->getHighestRow();

        for ($r = 19; $r <= $maxRow; $r++) {
            $noVal   = $ws->getCellByColumnAndRow(3, $r)->getValue();
            $namaVal = $ws->getCellByColumnAndRow(4, $r)->getValue();

            if (!$noVal || !ctype_digit((string) trim((string) $noVal))) break;
            $namaStr = trim((string) ($namaVal ?? ''));
            if (in_array($namaStr, ['', '0', '-'])) break;

            $valJ = strtoupper(trim((string) ($ws->getCellByColumnAndRow(10, $r)->getCalculatedValue() ?? '')));
            $valK = strtoupper(trim((string) ($ws->getCellByColumnAndRow(11, $r)->getCalculatedValue() ?? '')));

            if ($twoColMode) {
                $checkVals = ['K', 'V', '✓', 'X', '1', 'YA', 'Y'];
                $rek = in_array($valJ, $checkVals) ? 'K'
                     : (in_array($valK, $checkVals) ? 'BK' : null);
            } else {
                $rek = in_array($valJ, ['K', 'BK']) ? $valJ : null;
            }

            $peserta[] = ['nama' => $namaStr, 'rekomendasi' => $rek];
        }

        return [
            'tanggal_raw' => $tanggalRaw,
            'peserta'     => $peserta,
            'total'       => count($peserta),
            'filled'      => count(array_filter($peserta, fn($p) => $p['rekomendasi'] !== null)),
        ];
    }

    public function parseHasilObservasi(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xlsm', 'xls'])) {
            return ['sheets' => [], 'error' => 'Format file tidak didukung: ' . $ext];
        }

        try {
            $reader      = $this->createReader($filePath);
            $spreadsheet = $reader->load($filePath);
        } catch (\Throwable $e) {
            \Log::error('[ExcelService][parseHasilObservasi] Gagal buka file: ' . $e->getMessage());
            return ['sheets' => [], 'error' => 'Gagal membuka file: ' . $e->getMessage()];
        }

        $result = [];

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (in_array($sheetName, $this->skipSheets)) continue;

            $ws = $spreadsheet->getSheetByName($sheetName);
            if (!$ws) continue;

            [$headerRow, $namaCol] = $this->findNamaHeader($ws);
            if (!$headerRow) continue;

            $maxCol    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ws->getHighestColumn());
            $headerMap = [];

            for ($c = 1; $c <= $maxCol; $c++) {
                try {
                    $val = trim((string) $ws->getCellByColumnAndRow($c, $headerRow)->getCalculatedValue());
                } catch (\Throwable $e) {
                    $val = trim((string) $ws->getCellByColumnAndRow($c, $headerRow)->getValue());
                }
                if ($val !== '') $headerMap[$c] = $val;
            }

            if (empty($headerMap)) continue;

            $dataStartRow = $this->detectDataStartRow($ws, $headerRow, $namaCol);
            $maxRow       = $ws->getHighestRow();
            $rows         = [];

            for ($r = $dataStartRow; $r <= $maxRow; $r++) {
                try {
                    $namaVal = trim((string) $ws->getCellByColumnAndRow($namaCol, $r)->getCalculatedValue());
                } catch (\Throwable $e) {
                    $namaVal = trim((string) $ws->getCellByColumnAndRow($namaCol, $r)->getValue());
                }
                if (in_array($namaVal, ['', '0', '-'])) break;

                $row = [];
                foreach ($headerMap as $c => $label) {
                    try {
                        $cell = $ws->getCellByColumnAndRow($c, $r)->getCalculatedValue();
                    } catch (\Throwable $e) {
                        $cell = $ws->getCellByColumnAndRow($c, $r)->getValue();
                    }
                    $row[$label] = $cell !== null ? (string) $cell : '';
                }
                $rows[] = $row;
            }

            if (!empty($rows)) {
                $result[$sheetName] = [
                    'headers' => array_values($headerMap),
                    'rows'    => $rows,
                ];
            }
        }

        return ['sheets' => $result, 'error' => null];
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Cari cell header 'Nama' (case-insensitive).
     * Kembalikan [row, col] atau [null, null].
     */
    private function findNamaHeader($ws): array
    {
        $maxRow = min($ws->getHighestRow(), 30);
        $maxCol = min(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
            $ws->getHighestColumn()), 30);

        for ($r = 1; $r <= $maxRow; $r++) {
            for ($c = 1; $c <= $maxCol; $c++) {
                $cell = $ws->getCellByColumnAndRow($c, $r);

                try {
                    $val = strtolower(trim((string) $cell->getCalculatedValue()));
                } catch (\Throwable $e) {
                    $val = strtolower(trim((string) $cell->getValue()));
                }

                if (str_contains($val, 'nama')) {
                    return [$r, $c];
                }
            }
        }
        return [null, null];
    }

    /**
     * Deteksi baris data mulai.
     */
    private function detectDataStartRow($ws, int $headerRow, int $namaCol): int
    {
        $nextRow  = $headerRow + 1;
        $nextCell = $ws->getCellByColumnAndRow($namaCol, $nextRow);
        $nextVal  = $nextCell->getValue();

        if ($nextVal === null || trim((string) $nextVal) === '') {
            return $headerRow + 2;
        }

        if (is_numeric($nextVal)) {
            return $nextRow;
        }

        $lower = strtolower(trim((string) $nextVal));
        if (!in_array($lower, ['no', 'nama', 'nomor', 'unit kompetensi'])) {
            return $nextRow;
        }

        return $headerRow + 2;
    }


    /**
     * Unmerge merged cells yang overlap dengan kolom Nama di baris data.
     */
    private function unmergeNamaColumn($ws, int $namaCol, int $dataStartRow, int $count): void
    {
        $rowsNeeded = range($dataStartRow, $dataStartRow + $count - 1);

        foreach (array_keys($ws->getMergeCells()) as $rangeStr) {
            if (!preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/i', $rangeStr, $m)) continue;

            $colStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($m[1]);
            $colEnd   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($m[3]);
            $rowStart = (int) $m[2];
            $rowEnd   = (int) $m[4];

            if ($colStart > $namaCol || $colEnd < $namaCol) continue;

            $mergeRows = range($rowStart, $rowEnd);
            if (!array_intersect($mergeRows, $rowsNeeded)) continue;

            $ws->unmergeCells($rangeStr);
            \Log::info("[ExcelService] Unmerged: {$rangeStr}");
        }
    }

    private function detectFormat(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return match($ext) {
            'xlsm'  => 'Xlsx',
            'xls'   => 'Xls',
            'ods'   => 'Ods',
            default => 'Xlsx',
        };
    }

    private function createReader(string $filePath): \PhpOffice\PhpSpreadsheet\Reader\IReader
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $readerType = match($ext) {
            'xlsx', 'xlsm' => 'Xlsx',
            'xls'          => 'Xls',
            'ods'          => 'Ods',
            'csv'          => 'Csv',
            default        => throw new \RuntimeException("Format file tidak didukung: {$ext}"),
        };

        return IOFactory::createReader($readerType);
    }

    
}