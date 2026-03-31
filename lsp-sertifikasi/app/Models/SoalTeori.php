<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoalTeori extends Model
{
    use HasFactory;

    protected $table = 'soal_teori';

    protected $fillable = [
        'skema_id',
        'pertanyaan',
        'pilihan_a',
        'pilihan_b',
        'pilihan_c',
        'pilihan_d',
        'pilihan_e',
        'jawaban_benar',
        'dibuat_oleh',
    ];

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}