<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanObservasiAsesi extends Model
{
    use HasFactory;

    protected $table = 'jawaban_observasi_asesi';

    protected $fillable = [
        'asesmen_id',
        'distribusi_soal_observasi_id',
        'paket_soal_observasi_id',
        'gdrive_link',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class);
    }

    public function distribusiSoalObservasi(): BelongsTo
    {
        return $this->belongsTo(DistribusiSoalObservasi::class);
    }

    public function paketSoalObservasi(): BelongsTo
    {
        return $this->belongsTo(PaketSoalObservasi::class);
    }

    public function hasLink(): bool
    {
        return !empty($this->gdrive_link);
    }
}