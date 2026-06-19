<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistribusiSoalTeori extends Model
{
    use HasFactory;

    protected $table = 'distribusi_soal_teori';

    protected $fillable = [
        'schedule_id',
        'paket_soal_teori_id',
        'jumlah_soal',
        'durasi_menit',
        'didistribusikan_oleh',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function paketSoalTeori(): BelongsTo
    {
        return $this->belongsTo(PaketSoalTeori::class);
    }

    public function didistribusikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'didistribusikan_oleh');
    }

    public function soalAsesi(): HasMany
    {
        return $this->hasMany(SoalTeoriAsesi::class);
    }
}