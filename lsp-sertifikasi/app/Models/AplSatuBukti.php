<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AplSatu;

class AplSatuBukti extends Model
{
    protected $table = 'apl_01_bukti';

    protected $fillable = [
        'apl_01_id', 'kategori', 'nama_dokumen', 'deskripsi',
        'gdrive_file_id', 'gdrive_file_url', 'original_filename',
        'status', 'catatan',
        'uploaded_by', 'uploaded_at', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'uploaded_at'  => 'datetime',
        'verified_at'  => 'datetime',
    ];

    public function aplsatu()
    {
        return $this->belongsTo(AplSatu::class, 'apl_01_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'Ada Memenuhi Syarat'        => 'success',
            'Ada Tidak Memenuhi Syarat'  => 'warning',
            'Tidak Ada'                  => 'secondary',
            default                      => 'secondary',
        };
    }

    public function getKategoriLabelAttribute(): string
    {
        return match($this->kategori) {
            'persyaratan_dasar' => 'Persyaratan Dasar',
            'administratif'     => 'Administratif',
            default             => '-',
        };
    }
}