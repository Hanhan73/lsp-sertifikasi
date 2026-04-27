<?php

namespace App\Helpers;

class Terbilang
{
    private static array $satuan = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima',
        'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
        'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
        'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas',
    ];

    public static function convert(int $angka): string
    {
        if ($angka < 0) return 'minus ' . static::convert(abs($angka));
        if ($angka === 0) return 'nol';
        if ($angka < 20) return static::$satuan[$angka];
        if ($angka < 100) {
            $puluhan = intdiv($angka, 10);
            $sisa    = $angka % 10;
            return static::$satuan[$puluhan] . ' puluh' . ($sisa ? ' ' . static::$satuan[$sisa] : '');
        }
        if ($angka < 200) {
            $sisa = $angka - 100;
            return 'seratus' . ($sisa ? ' ' . static::convert($sisa) : '');
        }
        if ($angka < 1000) {
            $ratus = intdiv($angka, 100);
            $sisa  = $angka % 100;
            return static::$satuan[$ratus] . ' ratus' . ($sisa ? ' ' . static::convert($sisa) : '');
        }
        if ($angka < 2000) {
            $sisa = $angka - 1000;
            return 'seribu' . ($sisa ? ' ' . static::convert($sisa) : '');
        }
        if ($angka < 1_000_000) {
            $ribu = intdiv($angka, 1000);
            $sisa = $angka % 1000;
            return static::convert($ribu) . ' ribu' . ($sisa ? ' ' . static::convert($sisa) : '');
        }
        if ($angka < 1_000_000_000) {
            $juta = intdiv($angka, 1_000_000);
            $sisa = $angka % 1_000_000;
            return static::convert($juta) . ' juta' . ($sisa ? ' ' . static::convert($sisa) : '');
        }
        $miliar = intdiv($angka, 1_000_000_000);
        $sisa   = $angka % 1_000_000_000;
        return static::convert($miliar) . ' miliar' . ($sisa ? ' ' . static::convert($sisa) : '');
    }

    /**
     * Output dengan huruf kapital di setiap kata.
     */
    public static function convertTitle(int $angka): string
    {
        return ucwords(static::convert($angka));
    }
}