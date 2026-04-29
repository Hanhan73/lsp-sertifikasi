<?php

namespace App\Helpers;

class TerbilangHelper
{
    private static array $satuan = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima',
        'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
        'sebelas',
    ];

    public static function convert(int $angka): string
    {
        if ($angka < 0) {
            return 'minus ' . self::convert(abs($angka));
        }
        if ($angka < 12) {
            return self::$satuan[$angka];
        }
        if ($angka < 20) {
            return self::convert($angka - 10) . ' belas';
        }
        if ($angka < 100) {
            return self::convert((int) ($angka / 10)) . ' puluh' .
                ($angka % 10 ? ' ' . self::convert($angka % 10) : '');
        }
        if ($angka < 200) {
            return 'seratus' . ($angka - 100 ? ' ' . self::convert($angka - 100) : '');
        }
        if ($angka < 1000) {
            return self::convert((int) ($angka / 100)) . ' ratus' .
                ($angka % 100 ? ' ' . self::convert($angka % 100) : '');
        }
        if ($angka < 2000) {
            return 'seribu' . ($angka - 1000 ? ' ' . self::convert($angka - 1000) : '');
        }
        if ($angka < 1_000_000) {
            return self::convert((int) ($angka / 1000)) . ' ribu' .
                ($angka % 1000 ? ' ' . self::convert($angka % 1000) : '');
        }
        if ($angka < 1_000_000_000) {
            return self::convert((int) ($angka / 1_000_000)) . ' juta' .
                ($angka % 1_000_000 ? ' ' . self::convert($angka % 1_000_000) : '');
        }
        if ($angka < 1_000_000_000_000) {
            return self::convert((int) ($angka / 1_000_000_000)) . ' miliar' .
                ($angka % 1_000_000_000 ? ' ' . self::convert($angka % 1_000_000_000) : '');
        }
        return (string) $angka;
    }
}