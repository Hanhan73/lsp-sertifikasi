<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiayaOperasional extends Model
{
    protected $table = 'biaya_operasional';

    protected $fillable = [
        'nomor',
        'tanggal',
        'uraian',
        'nama_penerima',
        'asesor_id',
        'tarif',
        'jumlah',
        'total',
        'bukti_transaksi',
        'bukti_kegiatan',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tarif'   => 'integer',
        'jumlah'  => 'integer',
        'total'   => 'integer',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Generate nomor otomatis: BO-YYYY-NNN
     */
    public static function generateNomor(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'BO-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * URL bukti transaksi (public_html disk)
     */
    public function getBuktiTransaksiUrlAttribute(): ?string
    {
        if (!$this->bukti_transaksi) return null;
        return asset('storage/' . $this->bukti_transaksi);
    }

    /**
     * URL bukti kegiatan (public_html disk)
     */
    public function getBuktiKegiatanUrlAttribute(): ?string
    {
        if (!$this->bukti_kegiatan) return null;
        return asset('storage/' . $this->bukti_kegiatan);
    }

    public function asesor(): BelongsTo
{
    return $this->belongsTo(\App\Models\Asesor::class, 'asesor_id');
}
}