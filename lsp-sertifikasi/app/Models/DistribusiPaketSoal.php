<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiPaketSoal extends Model
{
    use HasFactory;

    protected $table = 'distribusi_paket_soal';

    protected $fillable = [
        'schedule_id',
        'paket_soal_id',
        'didistribusikan_oleh',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function paketSoal(): BelongsTo
    {
        return $this->belongsTo(PaketSoal::class);
    }

    public function didistribusikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'didistribusikan_oleh');
    }
}