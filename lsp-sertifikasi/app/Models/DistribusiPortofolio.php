<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiPortofolio extends Model
{
    use HasFactory;

    protected $table = 'distribusi_portofolio';

    protected $fillable = [
        'schedule_id',
        'portofolio_id',
        'didistribusikan_oleh',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function portofolio(): BelongsTo
    {
        return $this->belongsTo(Portofolio::class);
    }

    public function didistribusikanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'didistribusikan_oleh');
    }
}