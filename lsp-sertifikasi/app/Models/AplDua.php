<?php
// File: app/Models/AplDua.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AplDua extends Model
{
    protected $table = 'apl_02';

    protected $fillable = [
        'asesmen_id',
        'status',
        'ttd_asesi', 'nama_ttd_asesi', 'tanggal_ttd_asesi',
        'ttd_asesor', 'nama_ttd_asesor', 'tanggal_ttd_asesor',
        'rekomendasi_asesor', 'catatan_asesor',
        'submitted_at', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'tanggal_ttd_asesi'  => 'datetime',
        'tanggal_ttd_asesor' => 'datetime',
        'submitted_at'       => 'datetime',
        'verified_at'        => 'datetime',
    ];

    // ── Relations ──
    public function asesmen()   { return $this->belongsTo(Asesmen::class); }
    public function verifier()  { return $this->belongsTo(User::class, 'verified_by'); }
    public function jawabans()  { return $this->hasMany(AplDuaJawaban::class, 'apl_02_id'); }

    // ── Accessors ──
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'Draft',
            'submitted' => 'Sudah Disubmit',
            'verified'  => 'Sudah Diverifikasi',
            'approved'  => 'Disetujui',
            'returned'  => 'Perlu Revisi',
            default     => '-',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'secondary',
            'submitted' => 'info',
            'verified'  => 'warning',
            'approved'  => 'success',
            default     => 'secondary',
        };
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, ['draft', 'returned']);
    }

    public function getTtdAsesiImageAttribute(): ?string
    {
        if (!$this->ttd_asesi) return null;
        return str_starts_with($this->ttd_asesi, 'data:image')
            ? $this->ttd_asesi
            : 'data:image/png;base64,' . $this->ttd_asesi;
    }

    public function getTtdAsesorImageAttribute(): ?string
    {
        if (!$this->ttd_asesor) return null;
        return str_starts_with($this->ttd_asesor, 'data:image')
            ? $this->ttd_asesor
            : 'data:image/png;base64,' . $this->ttd_asesor;
    }

    // Hitung progress pengisian: berapa elemen sudah dijawab
    public function getProgressAttribute(): array
    {
        $total    = $this->jawabans()->count();
        $answered = $this->jawabans()->whereNotNull('jawaban')->count();
        $k        = $this->jawabans()->where('jawaban', 'K')->count();
        $bk       = $this->jawabans()->where('jawaban', 'BK')->count();
        return compact('total', 'answered', 'k', 'bk');
    }
}