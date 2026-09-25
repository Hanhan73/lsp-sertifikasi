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

    /**
     * Komponen umpan balik FR.AK.03 — urutan harus sama dengan form asesi.
     */
    public const PERTANYAAN = [
        'Saya mendapatkan penjelasan yang cukup memadai mengenai proses asesmen/uji kompetensi',
        'Saya diberikan kesempatan untuk mempelajari standar kompetensi yang akan diujikan dan menilai diri sendiri terhadap pencapaiannya',
        'Asesor memberikan kesempatan untuk mendiskusikan/menegosiasikan metoda, instrumen dan sumber asesmen serta jadwal asesmen',
        'Asesor berusaha menggali seluruh bukti pendukung yang sesuai dengan latar belakang pelatihan dan pengalaman yang saya miliki',
        'Saya sepenuhnya diberikan kesempatan untuk mendemonstrasikan kompetensi yang saya miliki selama asesmen',
        'Saya mendapatkan penjelasan yang memadai mengenai keputusan asesmen',
        'Asesor memberikan umpan balik yang mendukung setelah asesmen serta tindak lanjutnya',
        'Asesor bersama saya mempelajari semua dokumen asesmen serta menandatanganinya',
        'Saya mendapatkan jaminan kerahasiaan hasil asesmen serta penjelasan penanganan dokumen asesmen',
        'Asesor menggunakan keterampilan komunikasi yang efektif selama asesmen',
    ];
}