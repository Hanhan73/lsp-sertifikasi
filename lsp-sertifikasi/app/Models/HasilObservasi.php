<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class HasilObservasi extends Model
{
    protected $table = 'hasil_observasi';
 
    protected $fillable = [
        'schedule_id', 'soal_observasi_id',
        'file_path', 'file_name',
        'uploaded_by', 'uploaded_at', 'catatan',
    ];
 
    protected $casts = ['uploaded_at' => 'datetime'];
 
    public function schedule()      { return $this->belongsTo(Schedule::class); }
    public function soalObservasi() { return $this->belongsTo(SoalObservasi::class); }
    public function uploader()      { return $this->belongsTo(User::class, 'uploaded_by'); }
}