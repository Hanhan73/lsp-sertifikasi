<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrAk01 extends Model
{
    protected $table = 'fr_ak_01';

    protected $fillable = [
        'asesmen_id',
        // Data skema & jadwal
        'skema_judul',
        'skema_nomor',
        'tuk_nama',
        'waktu_asesmen',
        'hari_tanggal',
        'nama_asesor',
        'nama_asesi',
        // Bukti yang akan dikumpulkan (checkbox)
        'bukti_verifikasi_portofolio',
        'bukti_observasi_langsung',
        'bukti_pertanyaan_lisan',
        'bukti_lainnya',
        'bukti_lainnya_keterangan',
        'bukti_hasil_review_produk',
        'bukti_hasil_kegiatan_terstruktur',
        'bukti_pertanyaan_tertulis',
        'bukti_pertanyaan_wawancara',
        // Status
        'status',
        // Tanda tangan Asesi
        'ttd_asesi',
        'nama_ttd_asesi',
        'tanggal_ttd_asesi',
        // Tanda tangan Asesor
        'ttd_asesor',
        'nama_ttd_asesor',
        'tanggal_ttd_asesor',
        // Timestamps submit/verify
        'submitted_at',
        'verified_at',
        'verified_by',
        // Rejection (jika returned)
        'rejection_notes',
        'returned_at',
        'returned_by',
    ];

    protected $casts = [
        'bukti_verifikasi_portofolio'    => 'boolean',
        'bukti_observasi_langsung'       => 'boolean',
        'bukti_pertanyaan_lisan'         => 'boolean',
        'bukti_lainnya'                  => 'boolean',
        'bukti_hasil_review_produk'      => 'boolean',
        'bukti_hasil_kegiatan_terstruktur' => 'boolean',
        'bukti_pertanyaan_tertulis'      => 'boolean',
        'bukti_pertanyaan_wawancara'     => 'boolean',
        'tanggal_ttd_asesi'              => 'datetime',
        'tanggal_ttd_asesor'             => 'datetime',
        'submitted_at'                   => 'datetime',
        'verified_at'                    => 'datetime',
        'returned_at'                    => 'datetime',
    ];

    // ── Relations ──
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Accessors ──
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'submitted' => 'Sudah Disubmit',
            'verified'  => 'Sudah Diverifikasi',
            'approved'  => 'Disetujui',
            ';returned'  => 'Ditolak / Dikembalikan',
            default     => '-',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'secondary',
            'submitted' => 'info',
            'verified'  => 'warning',
            'approved'  => 'success',
            'returned'  => 'danger',
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
}