<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesorRekening extends Model
{
    protected $fillable = [
        'asesor_id',
        'nama_bank',
        'nomor_rekening',
        'nama_pemilik',
        'cabang',
        'is_utama',
    ];

    protected $casts = [
        'is_utama' => 'boolean',
    ];

    public function asesor()
    {
        return $this->belongsTo(Asesor::class);
    }
}