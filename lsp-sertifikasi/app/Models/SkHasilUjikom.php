<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkHasilUjikom extends Model
{
    protected $fillable = [
        'collective_batch_id',
        'nomor_sk',
        'tanggal_pleno',
        'tempat_dikeluarkan',
        'status',
        'catatan_direktur',
        'approved_by',
        'approved_at',
        'rejected_at',
        'sk_path',
        'submitted_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_pleno' => 'date',
        'approved_at'   => 'datetime',
        'rejected_at'   => 'datetime',
        'submitted_at'  => 'datetime',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // Batch Data Helpers
    // =========================================================================

    /**
     * Ambil semua asesmen dalam batch ini.
     */
    public function getAsesmensAttribute()
    {
        return Asesmen::with(['skema', 'tuk', 'schedule'])
            ->where('collective_batch_id', $this->collective_batch_id)
            ->get();
    }

    /**
     * Ambil semua jadwal (schedule) yang terkait dengan batch ini.
     */
    public function getSchedulesAttribute()
    {
        return Schedule::with(['tuk', 'skema', 'asesor.user', 'beritaAcara'])
            ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $this->collective_batch_id))
            ->get();
    }

    /**
     * Ambil semua asesi yang K (kompeten) dari semua berita acara batch ini.
     * Return: Collection of Asesmen with 'rekomendasi' => 'K'
     */
    public function getPesertaKompetenAttribute()
    {
        $scheduleIds = Schedule::whereHas(
            'asesmens',
            fn($q) => $q->where('collective_batch_id', $this->collective_batch_id)
        )->pluck('id');

        return BeritaAcaraAsesi::with(['asesmen'])
            ->whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
            ->where('rekomendasi', 'K')
            ->get()
            ->pluck('asesmen')
            ->filter();
    }

    /**
     * Info ringkas batch (TUK, Skema, total peserta).
     */
    public function getBatchInfoAttribute(): array
    {
        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $this->collective_batch_id)
            ->first();

        if (!$first) return [];

        return [
            'tuk'   => $first->tuk,
            'skema' => $first->skema,
            'total' => Asesmen::where('collective_batch_id', $this->collective_batch_id)->count(),
        ];
    }

    // =========================================================================
    // Status Helpers
    // =========================================================================

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isSubmitted(): bool  { return $this->status === 'submitted'; }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }
    public function hasSk(): bool        { return !is_null($this->sk_path); }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'submitted' => 'Menunggu Persetujuan',
            'approved'  => 'Disetujui',
            'rejected'  => 'Ditolak',
            default     => '-',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'secondary',
            'submitted' => 'warning',
            'approved'  => 'success',
            'rejected'  => 'danger',
            default     => 'secondary',
        };
    }

    public static function generateNomorSk(): string
    {
        $months = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
    
        $now   = now();
        $bulan = $months[$now->month];
        $tahun = $now->year;
    
        // Hitung SK yang sudah ada di bulan & tahun ini
        $count = self::whereYear('submitted_at', $tahun)
            ->whereMonth('submitted_at', $now->month)
            ->count() + 1;
    
        $nomor = str_pad($count, 3, '0', STR_PAD_LEFT);
    
        return "{$nomor}/LSP-KAP/SER.20.06/{$bulan}/{$tahun}";
    }
}