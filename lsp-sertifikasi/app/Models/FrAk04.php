<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrAk04 extends Model
{
    protected $table = 'fr_ak_04';

    protected $fillable = [
        'asesmen_id',
        'nama_asesi',
        'nama_asesor',
        'tanggal_asesmen',
        'skema_sertifikasi',
        'no_skema_sertifikasi',
        'proses_banding_dijelaskan',
        'sudah_diskusi_dengan_asesor',
        'melibatkan_orang_lain',
        'alasan_banding',
        'ttd_asesi',
        'nama_ttd_asesi',
        'tanggal_ttd_asesi',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'proses_banding_dijelaskan'   => 'boolean',
        'sudah_diskusi_dengan_asesor' => 'boolean',
        'melibatkan_orang_lain'       => 'boolean',
        'tanggal_ttd_asesi'           => 'datetime',
        'submitted_at'                => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class);
    }

    // ── Accessors ──────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'submitted' => 'Sudah Disubmit',
            default     => '-',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'warning',
            'submitted' => 'success',
            default     => 'secondary',
        };
    }

    public function getIsEditableAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getTtdAsesiImageAttribute(): ?string
    {
        if (!$this->ttd_asesi) return null;
        return str_starts_with($this->ttd_asesi, 'data:image')
            ? $this->ttd_asesi
            : 'data:image/png;base64,' . $this->ttd_asesi;
    }
}