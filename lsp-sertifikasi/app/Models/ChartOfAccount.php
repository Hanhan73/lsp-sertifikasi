<?php
// app/Models/ChartOfAccount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'kode', 'nama', 'tipe', 'sub_tipe',
        'keterangan', 'is_active', 'is_system', 'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($q)       { return $q->where('is_active', true); }
    public function scopeTipe($q, $tipe)  { return $q->where('tipe', $tipe); }

    // ── Helpers ───────────────────────────────────────────────────────────

    public static function tipeList(): array
    {
        return [
            'aset'       => 'Aset',
            'kewajiban'  => 'Kewajiban',
            'ekuitas'    => 'Ekuitas',
            'pendapatan' => 'Pendapatan',
            'beban'      => 'Beban',
        ];
    }

    public static function subTipeList(): array
    {
        return [
            'aset_lancar'      => 'Aset Lancar',
            'aset_tetap'       => 'Aset Tetap',
            'utang_lancar'     => 'Utang Lancar',
            'utang_jangka_panjang' => 'Utang Jangka Panjang',
            'beban_personalia' => 'Beban Personalia',
            'beban_operasional'=> 'Beban Operasional',
            'beban_administrasi'=> 'Beban Administrasi',
        ];
    }

    public function getTipeLabelAttribute(): string
    {
        return self::tipeList()[$this->tipe] ?? $this->tipe;
    }

    public function getTipeBadgeAttribute(): string
    {
        return match($this->tipe) {
            'aset'       => 'success',
            'kewajiban'  => 'danger',
            'ekuitas'    => 'warning',
            'pendapatan' => 'info',
            'beban'      => 'secondary',
            default      => 'light',
        };
    }

    public function getSubTipeLabelAttribute(): string
    {
        return self::subTipeList()[$this->sub_tipe] ?? '-';
    }
    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class, 'chart_of_account_id');
    }
}