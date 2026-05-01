<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrAk03UmpanBalik extends Model
{
    protected $table = 'fr_ak03_umpan_balik';

    protected $fillable = [
        'asesmen_id',
        'schedule_id',
        'jawaban',
        'catatan_lain',
        'submitted_at',
    ];

    protected $casts = [
        'jawaban'      => 'array',
        'submitted_at' => 'datetime',
    ];

    public function asesmen(): BelongsTo
    {
        return $this->belongsTo(Asesmen::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * Jawaban untuk pertanyaan index tertentu (0-based).
     */
    public function getJawabanItem(int $index): array
    {
        $jawaban = $this->jawaban ?? [];
        return $jawaban[$index] ?? ['jawaban' => null, 'catatan' => null];
    }
}