<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Elemen extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_kompetensi_id', 'kode', 'judul', 'urutan', 'hint_bukti',
    ];

    public function unitKompetensi()
    {
        return $this->belongsTo(UnitKompetensi::class);
    }

    public function kuks()
    {
        return $this->hasMany(Kuk::class)->orderBy('urutan');
    }
}