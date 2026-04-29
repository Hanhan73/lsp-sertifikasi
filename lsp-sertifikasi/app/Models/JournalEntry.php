<?php
// app/Models/JournalEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'tanggal',
        'nomor',
        'keterangan',
        'ref_type',
        'ref_id',
        'created_by',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNomor(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'JRN-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Cek apakah jurnal sudah ada untuk transaksi ini
    public static function existsFor(string $refType, int $refId): bool
    {
        return static::where('ref_type', $refType)->where('ref_id', $refId)->exists();
    }
}
