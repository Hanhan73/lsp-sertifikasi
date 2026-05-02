<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    protected $fillable = [
        'nomor_urut', 'tanggal_agenda', 'nomor_surat', 'tanggal_surat',
        'kepada', 'kode_klasifikasi', 'isi_ringkas', 'file_path', 'created_by',
    ];

    protected $casts = [
        'tanggal_agenda' => 'date',
        'tanggal_surat'  => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}