<?php

namespace App\Imports;

use App\Models\Skema;
use App\Models\UnitKompetensi;
use App\Models\Elemen;
use App\Models\Kuk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class MukApl02Sheet implements ToCollection
{
    private Skema     $skema;
    private MukImport $parent;

    public function __construct(Skema $skema, MukImport $parent)
    {
        $this->skema  = $skema;
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            // Hapus unit lama milik skema ini sebelum import ulang
            $this->skema->unitKompetensis()->delete();

            $curUnit   = null;
            $curElemen = null;

            foreach ($rows as $rowIndex => $row) {
                // Row adalah Collection, bukan array
                // Akses dengan ->get(index) atau dikonversi ke array
                $rowArray = $row->toArray();
                
                $c3 = $this->safe($rowArray, 2); // Col C (index 2)
                $c4 = $this->safe($rowArray, 3); // Col D
                $c5 = $this->safe($rowArray, 4); // Col E
                $c6 = $this->safe($rowArray, 5); // Col F
                $c7 = $this->safe($rowArray, 6); // Col G

                // ── Unit Kompetensi ──────────────────────────────
                // Col C = "Unit Kompetensi 01", Col F/G ada judul unit
                if (preg_match('/^Unit Kompetensi \d+$/i', $c3)) {
                    $unitFull = $c6 ?: $c7;
                    
                    if (!$unitFull) {
                        Log::warning("Baris {$rowIndex}: Unit tanpa judul, skip");
                        continue;
                    }

                    // Ekstrak kode dari "(N.82ADM00.001.3)"
                    $kode = '';
                    if (preg_match('/\(([^)]+)\)\s*$/', $unitFull, $m)) {
                        $kode     = trim($m[1]);
                        $unitFull = trim(preg_replace('/\s*\([^)]+\)\s*$/', '', $unitFull));
                    }

                    $curUnit = UnitKompetensi::create([
                        'skema_id'   => $this->skema->id,
                        'kode_unit'  => $kode,
                        'judul_unit' => $unitFull,
                        'urutan'     => ++$this->parent->unitCount,
                    ]);
                    $curElemen = null;
                    continue;
                }

                // ── Elemen ───────────────────────────────────────
                // Col C = "1.0" atau "1", Col D dimulai "Elemen"
                if ($curUnit && $c3 && preg_match('/^\d+\.?0?$/', $c3) && stripos($c4, 'Elemen') !== false) {
                    $judulEl = trim(preg_replace('/^Elemen\s*:\s*/i', '', $c4));
                    
                    if (!$judulEl || $judulEl === 'Kriteria Unjuk Kerja:') {
                        continue; // Skip baris header KUK
                    }

                    $urutan = $curUnit->elemens()->max('urutan') ?? 0;

                    $curElemen = Elemen::create([
                        'unit_kompetensi_id' => $curUnit->id,
                        'judul'  => $judulEl,
                        'urutan' => $urutan + 1,
                    ]);
                    continue;
                }

                // ── KUK ──────────────────────────────────────────
                // Col D = "1.1." atau "1.1", Col E ada deskripsi
                if ($curElemen && $c4 && preg_match('/^\d+\.\d+\.?\s*$/', $c4) && $c5) {
                    $urutan = $curElemen->kuks()->max('urutan') ?? 0;

                    Kuk::create([
                        'elemen_id' => $curElemen->id,
                        'kode'      => rtrim(trim($c4), '. '),
                        'deskripsi' => trim($c5),
                        'urutan'    => $urutan + 1,
                    ]);
                    $this->parent->kukCount++;
                }
            }

            DB::commit();
            
            Log::info("MUK Import success: {$this->parent->unitCount} units, {$this->parent->kukCount} KUKs");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MUK Import error: ' . $e->getMessage());
            Log::error('Stack: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    private function safe(array $row, int $idx): string
    {
        try {
            if (!isset($row[$idx]) || $row[$idx] === null) {
                return '';
            }
            return trim((string) $row[$idx]);
        } catch (\Exception $e) {
            return '';
        }
    }
}