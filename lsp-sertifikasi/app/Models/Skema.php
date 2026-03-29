<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skema extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'jenis_skema', 'description',
        'dokumen_pengesahan_path', 'dokumen_pengesahan_name',
        'tanggal_pengesahan', 'nomor_skema',
        'fee', 'is_active',
    ];

    protected $casts = [
        'fee'               => 'decimal:2',
        'tanggal_pengesahan'=> 'date',
        'is_active'         => 'boolean',
    ];

    public function asesmens()
    {
        return $this->hasMany(Asesmen::class);
    }

    public function unitKompetensis()
    {
        return $this->hasMany(UnitKompetensi::class)->orderBy('urutan');
    }

    public function asesors()
    {
        return $this->belongsToMany(Asesor::class, 'asesor_skema')
            ->withTimestamps();
    }

    public function getJenisLabelAttribute()
    {
        return match($this->jenis_skema) {
            'okupasi' => 'Okupasi',
            'kkni'    => 'KKNI',
            'klaster' => 'Klaster',
            default   => '-',
        };
    }

    public function getJenisBadgeAttribute()
    {
        return match($this->jenis_skema) {
            'okupasi' => 'primary',
            'kkni'    => 'success',
            'klaster' => 'info',
            default   => 'secondary',
        };
    }

    public function getDokumenUrlAttribute()
    {
        return $this->dokumen_pengesahan_path
            ? asset('storage/' . $this->dokumen_pengesahan_path)
            : null;
    }
}