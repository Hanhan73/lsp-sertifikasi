<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherReceivable extends Model
{
    protected $fillable = [
        'coa_id', 'jenis', 'nama_pihak', 'asesor_id', 'uraian', 'jumlah',
        'tanggal', 'jatuh_tempo', 'status',
        'tanggal_lunas', 'jumlah_lunas',
        'bukti_path', 'bukti_name', 'catatan',
        'created_by', 'updated_by', 'coa_lawan_id',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'jatuh_tempo'   => 'date',
        'tanggal_lunas' => 'date',
        'jumlah'        => 'decimal:2',
        'jumlah_lunas'  => 'decimal:2',
    ];

    public function coa()      { return $this->belongsTo(ChartOfAccount::class, 'coa_id'); }
    public function coaLawan() { return $this->belongsTo(ChartOfAccount::class, 'coa_lawan_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }

    /** Relasi ke asesor (opsional — untuk hutang asesor) */
    public function asesor()   { return $this->belongsTo(Asesor::class, 'asesor_id'); }

    public function getSisaAttribute(): float
    {
        return (float) $this->jumlah - (float) ($this->jumlah_lunas ?? 0);
    }

    public function getJenisLabelAttribute(): string
    {
        return $this->jenis === 'pinjaman' ? 'Pinjaman/Kasbon' : 'Tagihan';
    }
}