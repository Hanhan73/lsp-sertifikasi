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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MukImport implements WithMultipleSheets
{
    public int $unitCount = 0;
    public int $kukCount  = 0;

    private Skema $skema;

    public function __construct(Skema $skema)
    {
        $this->skema = $skema;
    }

    public function sheets(): array
    {
        return [
            'FR.APL.02' => new MukApl02Sheet($this->skema, $this),
        ];
    }
}