<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoalObservasi extends Model
{
    use HasFactory;

    protected $table = 'soal_observasi';

    protected $fillable = [
        'skema_id',
        'judul',        // nama kelompok observasi, e.g. "Observasi Kompetensi Teknis"
        'dibuat_oleh',
        // TIDAK ada file_path di sini — file ada di paket_soal_observasi
    ];

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Paket-paket di dalam observasi ini (A, B, C, D, dst)
     */
    public function paket(): HasMany
    {
        return $this->hasMany(PaketSoalObservasi::class)->orderBy('kode_paket');
    }

    /**
     * Distribusi ke jadwal asesmen
     */
    public function distribusi(): HasMany
    {
        return $this->hasMany(DistribusiSoalObservasi::class);
    }

    /**
     * Jumlah paket yang sudah diupload
     */
    public function getTotalPaketAttribute(): int
    {
        return $this->paket()->count();
    }
}