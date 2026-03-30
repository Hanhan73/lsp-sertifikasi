<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoalTeoriAsesi extends Model
{
    use HasFactory;

    protected $table = 'soal_teori_asesi';

    protected $fillable = [
        'distribusi_soal_teori_id',
        'asesmen_id',
        'soal_teori_id',
        'urutan',
        'jawaban',
    ];

    public function distribusiSoalTeori(): BelongsTo
    {
        return $this->belongsTo(DistribusiSoalTeori::class);
    }

    /**
     * Relasi ke Asesmen (bukan langsung ke User),
     * karena satu User bisa punya banyak Asesmen.
     */
    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class);
    }

    public function soalTeori(): BelongsTo
    {
        return $this->belongsTo(SoalTeori::class);
    }

    public function isBenar(): bool
    {
        return $this->jawaban !== null
            && $this->jawaban === $this->soalTeori->jawaban_benar;
    }
}