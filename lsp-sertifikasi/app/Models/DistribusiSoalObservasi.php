<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiSoalObservasi extends Model
{
    use HasFactory;

    protected $table = 'distribusi_soal_observasi';

    protected $fillable = [
        'schedule_id',
        'soal_observasi_id',
        'didistribusikan_oleh',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function soalObservasi(): BelongsTo
    {
        return $this->belongsTo(SoalObservasi::class);
    }

    public function didistribusikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'didistribusikan_oleh');
    }
}