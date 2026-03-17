<?php
// File: app/Models/AplDuaJawaban.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AplDuaJawaban extends Model
{
    protected $table = 'apl_02_jawaban';

    protected $fillable = [
        'apl_02_id', 'elemen_id', 'jawaban', 'bukti',
    ];

    public function aplDua()  { return $this->belongsTo(AplDua::class, 'apl_02_id'); }
    public function elemen()  { return $this->belongsTo(Elemen::class); }

    public function getJawabanLabelAttribute(): string
    {
        return match($this->jawaban) {
            'K'  => 'Kompeten',
            'BK' => 'Belum Kompeten',
            default => '-',
        };
    }

    public function getJawabanBadgeAttribute(): string
    {
        return match($this->jawaban) {
            'K'  => 'success',
            'BK' => 'danger',
            default => 'secondary',
        };
    }
}