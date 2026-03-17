<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $payments;

    public function __construct($payments)
    {
        $this->payments = $payments;
    }

    public function collection()
    {
        return $this->payments;
    }

    public function headings(): array
    {
        return [
            'No Registrasi',
            'Nama Asesi',
            'TUK',
            'Skema',
            'Jenis',
            'Fase',
            'Jumlah (Rp)',
            'Metode',
            'Status',
            'Tanggal Bayar',
            'Tanggal Verifikasi',
            'Verifikasi Oleh',
            'Transaction ID',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->asesmen->id,
            $payment->asesmen->full_name ?? $payment->asesmen->user->name,
            $payment->asesmen->tuk->name ?? '-',
            $payment->asesmen->skema->name ?? '-',
            $payment->asesmen->is_collective ? 'Kolektif' : 'Mandiri',
            $this->getPhaseLabel($payment->payment_phase),
            number_format($payment->amount, 0, ',', '.'),
            ucfirst($payment->method),
            $this->getStatusLabel($payment->status),
            $payment->created_at->format('d/m/Y H:i'),
            $payment->verified_at ? $payment->verified_at->format('d/m/Y H:i') : '-',
            $payment->is_auto_verified ? 'Otomatis (Midtrans)' : ($payment->verifier ? $payment->verifier->name : '-'),
            $payment->transaction_id ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '198754']]],
        ];
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

    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Pending',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
        ];
        return $labels[$status] ?? $status;
    }
}