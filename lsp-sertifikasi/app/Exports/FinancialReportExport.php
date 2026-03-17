<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $data;
    protected $summary;

    public function __construct($data, $summary)
    {
        $this->data = $data;
        $this->summary = $summary;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No Registrasi',
            'Nama Asesi',
            'TUK',
            'Skema',
            'Jenis',
            'Fase',
            'Pemasukan (Rp)',
            'Status',
        ];
    }

    public function map($item): array
    {
        return [
            $item->verified_at ? $item->verified_at->format('d/m/Y') : $item->created_at->format('d/m/Y'),
            $item->asesmen->id,
            $item->asesmen->full_name ?? $item->asesmen->user->name,
            $item->asesmen->tuk->name ?? '-',
            $item->asesmen->skema->name ?? '-',
            $item->asesmen->is_collective ? 'Kolektif' : 'Mandiri',
            $this->getPhaseLabel($item->payment_phase),
            number_format($item->amount, 0, ',', '.'),
            $item->status === 'verified' ? 'Terverifikasi' : 'Pending',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFC107']]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Keuangan';
    }

    private function getPhaseLabel($phase)
    {
        $labels = [
            'full' => 'Full',
            'phase_1' => 'Fase 1',
            'phase_2' => 'Fase 2',
        ];
        return $labels[$phase] ?? $phase;
    }
}