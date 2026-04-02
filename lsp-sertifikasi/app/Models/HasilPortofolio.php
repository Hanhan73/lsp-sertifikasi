<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class HasilPortofolio extends Model
{
    protected $table = 'hasil_portofolio';
 
    protected $fillable = [
        'schedule_id', 'portofolio_id',
        'file_path', 'file_name',
        'uploaded_by', 'uploaded_at', 'catatan',
    ];
 
    protected $casts = ['uploaded_at' => 'datetime'];
 
    public function schedule()   { return $this->belongsTo(Schedule::class); }
    public function portofolio() { return $this->belongsTo(Portofolio::class); }
    public function uploader()   { return $this->belongsTo(User::class, 'uploaded_by'); }
}
 