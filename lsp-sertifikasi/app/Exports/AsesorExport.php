<?php

namespace App\Exports;

use App\Models\Asesor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsesorExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCustomValueBinder, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Paksa kolom NIK (kolom C) selalu tersimpan sebagai string —
     * mencegah Excel menampilkannya dalam notasi ilmiah (3.2E+15).
     */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'C') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return (new DefaultValueBinder())->bindValue($cell, $value);
    }

    /**
     * Set format kolom NIK jadi Text supaya tampilannya juga rapi.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()
                    ->getStyle('C:C')
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
            },
        ];
    }

    public function collection(): Collection
    {
        $query = Asesor::with(['user', 'rekeningUtama']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_reg_met', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['status_reg'])) {
            $query->where('status_reg', $this->filters['status_reg']);
        }

        if (!empty($this->filters['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $this->filters['jenis_kelamin']);
        }

        return $query->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIK',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Umur',
            'Alamat',
            'Kota',
            'Provinsi',
            'Telepon',
            'Email',
            'No. Reg. Metodologi',
            'No. Blanko',
            'SIAPKerja',
            'Status Registrasi',
            'Tanggal Expire',
            'Punya Akun Login',
            'SK Pengangkatan',
            'Berlaku Hingga SK',
            'Rekening Utama',
            'No. Rekening',
            'Keterangan',
        ];
    }

    public function map($asesor): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $asesor->nama,
            $asesor->nik,
            $asesor->jenis_kelamin_label,
            $asesor->tempat_lahir,
            $asesor->tanggal_lahir?->translatedFormat('d-m-Y'),
            $asesor->umur,
            $asesor->alamat,
            $asesor->kota,
            $asesor->provinsi,
            $asesor->telepon,
            $asesor->email,
            $asesor->no_reg_met,
            $asesor->no_blanko,
            $asesor->siap_kerja,
            $asesor->status_label,
            $asesor->expire_date?->translatedFormat('d-m-Y'),
            $asesor->user_id ? 'Ya' : 'Tidak',
            $asesor->sk_pengangkatan_path ? 'Ada' : 'Belum Ada',
            $asesor->sk_pengangkatan_valid_until?->translatedFormat('d-m-Y'),
            $asesor->rekeningUtama?->nama_bank,
            $asesor->rekeningUtama?->nomor_rekening,
            $asesor->keterangan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}