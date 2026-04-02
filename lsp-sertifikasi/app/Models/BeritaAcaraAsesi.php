<?php

namespace App\Models;
  
use Illuminate\Database\Eloquent\Model;
 
class BeritaAcaraAsesi extends Model
{
    protected $table = 'berita_acara_asesi';
 
    protected $fillable = ['berita_acara_id', 'asesmen_id', 'rekomendasi', 'catatan'];
 
    public function beritaAcara() { return $this->belongsTo(BeritaAcara::class); }
    public function asesmen()     { return $this->belongsTo(Asesmen::class); }
 
    public function getIsKompetenAttribute(): bool { return $this->rekomendasi === 'K'; }
}