<?php
// database/seeders/ChartOfAccountSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $akuns = [
            // ── ASET ──────────────────────────────────────────────────────
            ['kode'=>'1-001','nama'=>'Kas',                       'tipe'=>'aset',       'sub_tipe'=>'aset_lancar',    'urutan'=>1,  'is_system'=>true],
            ['kode'=>'1-002','nama'=>'Bank',                      'tipe'=>'aset',       'sub_tipe'=>'aset_lancar',    'urutan'=>2,  'is_system'=>true],
            ['kode'=>'1-003','nama'=>'Piutang Asesi',             'tipe'=>'aset',       'sub_tipe'=>'aset_lancar',    'urutan'=>3,  'is_system'=>true],
            ['kode'=>'1-004','nama'=>'Perlengkapan',              'tipe'=>'aset',       'sub_tipe'=>'aset_tetap',     'urutan'=>4,  'is_system'=>true],

            // ── KEWAJIBAN ─────────────────────────────────────────────────
            ['kode'=>'2-001','nama'=>'Utang Honor Asesor',        'tipe'=>'kewajiban',  'sub_tipe'=>'utang_lancar',   'urutan'=>10, 'is_system'=>true],
            ['kode'=>'2-002','nama'=>'Utang Operasional',         'tipe'=>'kewajiban',  'sub_tipe'=>'utang_lancar',   'urutan'=>11, 'is_system'=>true],
            ['kode'=>'2-003','nama'=>'Hutang Distribusi Yayasan', 'tipe'=>'kewajiban',  'sub_tipe'=>'utang_lancar',   'urutan'=>12, 'is_system'=>true],

            // ── EKUITAS ───────────────────────────────────────────────────
            ['kode'=>'3-001','nama'=>'Saldo Dana',                'tipe'=>'ekuitas',    'sub_tipe'=>null,             'urutan'=>20, 'is_system'=>true],
            ['kode'=>'3-002','nama'=>'Surplus Tahun Berjalan',    'tipe'=>'ekuitas',    'sub_tipe'=>null,             'urutan'=>21, 'is_system'=>true],

            // ── PENDAPATAN ────────────────────────────────────────────────
            ['kode'=>'4-001','nama'=>'Pendapatan Sertifikasi',    'tipe'=>'pendapatan', 'sub_tipe'=>null,             'urutan'=>30, 'is_system'=>true],

            // ── BEBAN ─────────────────────────────────────────────────────
            ['kode'=>'5-001','nama'=>'Beban Honor Asesor',        'tipe'=>'beban',      'sub_tipe'=>'beban_personalia','urutan'=>40,'is_system'=>true],
            ['kode'=>'5-002','nama'=>'Beban Operasional',         'tipe'=>'beban',      'sub_tipe'=>'beban_operasional','urutan'=>41,'is_system'=>true],
        ];

        foreach ($akuns as $akun) {
            DB::table('chart_of_accounts')->insertOrIgnore(array_merge($akun, [
                'is_active'  => true,
                'keterangan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}