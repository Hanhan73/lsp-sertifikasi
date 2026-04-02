<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class BeritaAcara extends Model
{
    protected $table = 'berita_acara';
 
    protected $fillable = [
        'schedule_id', 'tanggal_pelaksanaan', 'catatan',
        'file_path', 'file_name', 'dibuat_oleh',
    ];
 
    protected $casts = ['tanggal_pelaksanaan' => 'date'];
 
    public function schedule()  { return $this->belongsTo(Schedule::class); }
    public function pembuat()   { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function asesis()    { return $this->hasMany(BeritaAcaraAsesi::class); }
 
    public function hasFile(): bool { return (bool) $this->file_path; }
}