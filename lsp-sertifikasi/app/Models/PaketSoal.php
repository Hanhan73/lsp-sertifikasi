<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaketSoal extends Model
{
    use HasFactory;

    protected $table = 'paket_soal';

    protected $fillable = [
        'skema_id',
        'judul',
        'file_path',
        'file_name',
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

    public function distribusi(): HasMany
    {
        return $this->hasMany(DistribusiPaketSoal::class);
    }
}