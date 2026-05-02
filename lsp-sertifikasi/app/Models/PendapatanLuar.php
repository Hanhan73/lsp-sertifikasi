<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendapatanLuar extends Model
{
    protected $table = 'pendapatan_luar';

    protected $fillable = [
        'tanggal', 'uraian', 'kategori', 'jumlah',
        'coa_id', 'bukti_path', 'bukti_name', 'catatan', 'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah'  => 'integer',
    ];

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntry()
    {
        return $this->hasOne(JournalEntry::class, 'ref_id')
            ->where('ref_type', self::class);
    }
}