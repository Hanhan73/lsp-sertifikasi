<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portofolio extends Model
{
    use HasFactory;

    protected $table = 'portofolio';

    protected $fillable = [
        'skema_id',
        'judul',
        'deskripsi',
        'file_path',
        'file_name',
        'tipe_file',
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
        return $this->hasMany(DistribusiPortofolio::class);
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }
}