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
        // Deduction (cicilan hutang)
        'deduction_receivable_id',
        'deduction_amount',
        'deduction_note',
    ];

    protected $casts = [
        'tanggal_kwitansi'  => 'date',
        'dibayar_at'        => 'datetime',
        'dikonfirmasi_at'   => 'datetime',
        'total'             => 'decimal:2',
        'deduction_amount'  => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

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

    public function deductionReceivable()
    {
        return $this->belongsTo(OtherReceivable::class, 'deduction_receivable_id');
    }

    // ── Status helpers (dipakai di views dan controller) ───────────────────

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

    // ── Accessors ──────────────────────────────────────────────────────────

    /** Jumlah bersih yang benar-benar ditransfer ke asesor setelah potong cicilan */
    public function getJumlahTransferAttribute(): float
    {
        return (float) $this->total - (float) ($this->deduction_amount ?? 0);
    }

    /** Apakah ada potongan cicilan hutang */
    public function getHasDeductionAttribute(): bool
    {
        return !is_null($this->deduction_amount) && $this->deduction_amount > 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'sudah_dibayar'       => 'Sudah Dibayar',
            'dikonfirmasi'        => 'Dikonfirmasi',
            default               => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'menunggu_pembayaran' => 'warning',
            'sudah_dibayar'       => 'info',
            'dikonfirmasi'        => 'success',
            default               => 'secondary',
        };
    }

    /**
     * Kwitansi bisa di-reset hanya kalau:
     * - Status masih menunggu_pembayaran
     * - Belum ada bukti transfer diupload
     */
    public function getCanResetAttribute(): bool
    {
        return $this->isMenunggu() && is_null($this->bukti_transfer_path);
    }

    /**
     * Bukti bisa diganti kalau:
     * - Sudah upload (sudah_dibayar) tapi asesor belum konfirmasi
     */
    public function getCanReplaceBuktiAttribute(): bool
    {
        return $this->isSudahDibayar() && !is_null($this->bukti_transfer_path);
    }

    /**
     * Asesor bisa lihat kwitansi & nominal hanya setelah sudah_dibayar
     */
    public function getAsesorCanViewAttribute(): bool
    {
        return in_array($this->status, ['sudah_dibayar', 'dikonfirmasi']);
    }

    /** Auto-generate nomor kwitansi */
    public static function generateNomor(): string
    {
        $bulan  = now()->format('m');
        $tahun  = now()->format('Y');
        $roman  = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][(int) $bulan];
        $urutan = static::whereYear('created_at', $tahun)->whereMonth('created_at', $bulan)->count() + 1;

        return sprintf('%03d/LSP-KAP/KEU.KK/%s/%s', $urutan, $roman, $tahun);
    }
}