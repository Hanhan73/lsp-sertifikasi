<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AplSatu extends Model
{
    protected $table = 'apl_01';
    
    protected $fillable = [
        'asesmen_id',
        // Data Pribadi
        'nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'kebangsaan', 'alamat_rumah', 'kode_pos',
        'telp_rumah', 'telp_kantor', 'hp', 'email', 'kualifikasi_pendidikan',
        // Data Pekerjaan
        'nama_institusi', 'jabatan', 'alamat_kantor', 'kode_pos_kantor',
        'telp_kantor_detail', 'fax_kantor', 'email_kantor',
        // Data Sertifikasi
        'tujuan_asesmen', 'tujuan_asesmen_lainnya',
        // Rekomendasi LSP
        'rekomendasi', 
        // Tanda Tangan
        'ttd_pemohon', 'tanggal_ttd_pemohon', 'nama_ttd_pemohon',
        'ttd_admin', 'tanggal_ttd_admin', 'nama_ttd_admin',
        // Status
        'status', 'submitted_at', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'tanggal_lahir'        => 'date',
        'tanggal_ttd_pemohon'  => 'datetime',
        'tanggal_ttd_admin'    => 'datetime',
        'submitted_at'         => 'datetime',
        'verified_at'          => 'datetime',
    ];

    // ── Relations ──
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class);
    }

    public function buktiKelengkapan()
    {
        return $this->hasMany(AplSatuBukti::class, 'apl_01_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Accessors ──
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'Draft',
            'submitted' => 'Sudah Disubmit',
            'verified'  => 'Sudah Diverifikasi',
            'approved'  => 'Disetujui',
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

    /**
     * Whether the form is still editable by the asesi
     * Only 'draft' status is editable; once submitted it is locked.
     */
    public function getIsEditableAttribute(): bool
    {
        return $this->status === 'draft';
    }

    // ── Image helpers ──
    public function getTtdPemohonImageAttribute(): ?string
    {
        if (!$this->ttd_pemohon) return null;
        // Already has data URI prefix
        if (str_starts_with($this->ttd_pemohon, 'data:image')) {
            return $this->ttd_pemohon;
        }
        return 'data:image/png;base64,' . $this->ttd_pemohon;
    }

    public function getTtdAdminImageAttribute(): ?string
    {
        if (!$this->ttd_admin) return null;
        if (str_starts_with($this->ttd_admin, 'data:image')) {
            return $this->ttd_admin;
        }
        return 'data:image/png;base64,' . $this->ttd_admin;
    }
}