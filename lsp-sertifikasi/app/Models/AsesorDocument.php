<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsesorDocument extends Model
{
    use HasFactory;

    public const JENIS_LABELS = [
        'sertifikat_bnsp'   => 'Sertifikat Asesor BNSP',
        'sertifikat_teknis' => 'Sertifikat Teknis',
        'ijazah'            => 'Ijazah / Transkrip Nilai',
        'cv'                => 'Curriculum Vitae',
        'ktp'               => 'KTP',
    ];

    protected $fillable = [
        'asesor_id', 'jenis_dokumen', 'file_path', 'file_name', 'file_size', 'uploaded_by',
    ];

    public function asesor()
    {
        return $this->belongsTo(Asesor::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getLabelAttribute(): string
    {
        return self::JENIS_LABELS[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }

    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return '-';
        return $this->file_size > 1048576
            ? round($this->file_size / 1048576, 1) . ' MB'
            : round($this->file_size / 1024) . ' KB';
    }
}