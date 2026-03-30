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
        'jumlah_soal',
        'didistribusikan_oleh',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
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