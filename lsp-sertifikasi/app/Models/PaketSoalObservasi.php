<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaketSoalObservasi extends Model
{
    use HasFactory;

    protected $table = 'paket_soal_observasi';

    protected $fillable = [
        'soal_observasi_id',
        'kode_paket',   // A, B, C, D, dst
        'judul',
        'file_path',
        'file_name',
        'dibuat_oleh',
    ];

    public function soalObservasi(): BelongsTo
    {
        return $this->belongsTo(SoalObservasi::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}