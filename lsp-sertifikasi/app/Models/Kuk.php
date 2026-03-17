<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kuk extends Model
{
    use HasFactory;

    protected $table = 'kuks';

    protected $fillable = [
        'elemen_id', 'kode', 'deskripsi', 'urutan',
    ];

    public function elemen()
    {
        return $this->belongsTo(Elemen::class);
    }
}