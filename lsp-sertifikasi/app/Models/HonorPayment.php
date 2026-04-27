<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorPayment extends Model
{
    protected $fillable = [
        'asesor_id',
        'nomor_kwitansi',
        'tanggal_kwitansi',
        'total',
        'status',
        'bukti_transfer_path',
        'bukti_transfer_name',
        'dibayar_at',
        'dibayar_oleh',
        'dikonfirmasi_at',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_kwitansi' => 'date',
        'dibayar_at'       => 'datetime',
        'dikonfirmasi_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function asesor()
    {
        return $this->belongsTo(Asesor::class);
    }

    public function details()
    {
        return $this->hasMany(HonorPaymentDetail::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function dibayarOleh()
    {
        return $this->belongsTo(User::class, 'dibayar_oleh');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isMenunggu(): bool
    {
        return $this->status === 'menunggu_pembayaran';
    }

    public function isSudahDibayar(): bool
    {
        return $this->status === 'sudah_dibayar';
    }

    public function isDikonfirmasi(): bool
    {
        return $this->status === 'dikonfirmasi';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'sudah_dibayar'       => 'Sudah Dibayar',
            'dikonfirmasi'        => 'Dikonfirmasi',
            default               => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'menunggu_pembayaran' => 'warning',
            'sudah_dibayar'       => 'info',
            'dikonfirmasi'        => 'success',
            default               => 'secondary',
        };
    }

    /**
     * Generate nomor kwitansi otomatis.
     * Format: 001/LSP-KAP/KEU.KK/IV/2026
     */
    public static function generateNomor(): string
    {
        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        $bulan  = now()->month;
        $tahun  = now()->year;
        $urutan = static::whereYear('created_at', $tahun)->count() + 1;

        return sprintf('%03d/LSP-KAP/KEU.KK/%s/%d', $urutan, $bulanRomawi[$bulan], $tahun);
    }
}