<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaketSoalTeori extends Model
{
    use HasFactory;

    protected $table = 'paket_soal_teori';

    protected $fillable = [
        'skema_id',
        'kode_paket',
        'nama_paket',
        'tahun',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function soalTeori(): HasMany
    {
        return $this->hasMany(SoalTeori::class);
    }

    public function distribusi(): HasMany
    {
        return $this->hasMany(DistribusiSoalTeori::class);
    }

    // Label lengkap: "Paket A 2025"
    public function getLabelAttribute(): string
    {
        $label = "Paket {$this->kode_paket}";
        if ($this->nama_paket) $label .= " — {$this->nama_paket}";
        if ($this->tahun) $label .= " ({$this->tahun})";
        return $label;
    }

    public function getJumlahSoalAttribute(): int
    {
        return $this->soalTeori()->count();
    }
}