<?php
// app/Models/JournalEntryLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'chart_of_account_id',
        'debit',
        'kredit',
        'keterangan',
    ];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function akun()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
