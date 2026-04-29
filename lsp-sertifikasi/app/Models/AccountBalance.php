<?php
// app/Models/AccountBalance.php

namespace App\Models;

use App\Models\Payment;
use App\Models\HonorPayment;
use App\Models\BiayaOperasional;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccountBalance extends Model
{
    protected $fillable = [
        'tahun', 'kas', 'bank', 'perlengkapan',
        'utang_operasional', 'saldo_dana',
        'distribusi_yayasan', 'hutang_distribusi',
        'tanggal_distribusi', 'catatan_distribusi',
        'jurnal_balik_done', 'dibuat_oleh', 'diupdate_oleh',
    ];

    protected $casts = [
        'tanggal_distribusi' => 'date',
        'jurnal_balik_done'  => 'boolean',
    ];

    // ── Auto-computed dari sistem ─────────────────────────────────────────

    /** Piutang = payments yang belum verified */
    public function getPiutangAsesiAttribute(): int
    {
        return (int) Payment::whereIn('status', ['pending', 'uploaded'])
            ->whereYear('created_at', $this->tahun)
            ->sum('amount');
    }

    /** Utang honor = honor_payments menunggu pembayaran */
    public function getUtangHonorAttribute(): int
    {
        return (int) HonorPayment::where('status', 'menunggu_pembayaran')
            ->whereYear('created_at', $this->tahun)
            ->sum('total');
    }

    /** Pendapatan = payments verified */
    public function getPendapatanAttribute(): int
    {
        return (int) Payment::where('status', 'verified')
            ->whereYear('verified_at', $this->tahun)
            ->sum('amount');
    }

    /** Beban honor = honor_payments dibayar */
    public function getBebanHonorAttribute(): int
    {
        return (int) HonorPayment::whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])
            ->whereYear('dibayar_at', $this->tahun)
            ->whereNotNull('dibayar_at')
            ->sum('total');
    }

    /** Beban operasional = biaya_operasional */
    public function getBebanOperasionalAttribute(): int
    {
        return (int) BiayaOperasional::whereYear('tanggal', $this->tahun)->sum('total');
    }

    /** Surplus/Defisit = Pendapatan − Beban Honor − Beban Ops */
    public function getSurplusAttribute(): int
    {
        return $this->pendapatan - $this->beban_honor - $this->beban_operasional;
    }

    /** Total Aset */
    public function getTotalAsetAttribute(): int
    {
        return $this->kas + $this->bank + $this->perlengkapan
             + $this->piutang_asesi;
    }

    /** Total Kewajiban */
    public function getTotalKewajibanAttribute(): int
    {
        return $this->utang_honor + $this->utang_operasional + $this->hutang_distribusi;
    }

    /** Total Ekuitas */
    public function getTotalEkuitasAttribute(): int
    {
        return $this->saldo_dana + $this->surplus - $this->distribusi_yayasan;
    }

    /** Total Kewajiban + Ekuitas */
    public function getTotalKewajiban_EkuitasAttribute(): int
    {
        return $this->total_kewajiban + $this->total_ekuitas;
    }

    // ── Helper static ─────────────────────────────────────────────────────

    public static function forTahun(int $tahun): self
    {
        return static::firstOrCreate(
            ['tahun' => $tahun],
            ['dibuat_oleh' => auth()->id()]
        );
    }
}