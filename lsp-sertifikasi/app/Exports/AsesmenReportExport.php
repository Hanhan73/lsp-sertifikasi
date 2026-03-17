<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsesmenReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $asesmens;
    protected $reportType;

    public function __construct($asesmens, $reportType = 'general')
    {
        $this->asesmens = $asesmens;
        $this->reportType = $reportType;
    }

    public function collection()
    {
        return $this->asesmens;
    }

    public function headings(): array
    {
        return [
            'No Registrasi',
            'Nama Lengkap',
            'Email',
            'NIK',
            'Jenis Kelamin',
            'TUK',
            'Skema',
            'Jenis Pendaftaran',
            'Tanggal Daftar',
            'Status',
            'Biaya (Rp)',
            'Pelatihan',
            'Tanggal Verifikasi',
            'Tanggal Bayar',
            'Tanggal Asesmen',
            'Hasil',
            'Tanggal Sertifikat',
            'No Sertifikat',
        ];
    }

    public function map($asesmen): array
    {
        return [
            $asesmen->id,
            $asesmen->full_name ?? $asesmen->user->name,
            $asesmen->user->email,
            $asesmen->nik ?? '-',
            $asesmen->gender === 'L' ? 'Laki-laki' : ($asesmen->gender === 'P' ? 'Perempuan' : '-'),
            $asesmen->tuk->name ?? '-',
            $asesmen->skema->name ?? '-',
            $asesmen->is_collective ? 'Kolektif' : 'Mandiri',
            $asesmen->registration_date ? $asesmen->registration_date->format('d/m/Y') : '-',
            $this->getStatusLabel($asesmen->status),
            $asesmen->fee_amount ? number_format($asesmen->fee_amount, 0, ',', '.') : '-',
            $asesmen->training_flag ? 'Ya' : 'Tidak',
            $asesmen->admin_verified_at ? $asesmen->admin_verified_at->format('d/m/Y') : '-',
            $asesmen->payment && $asesmen->payment->verified_at ? $asesmen->payment->verified_at->format('d/m/Y') : '-',
            $asesmen->schedule ? $asesmen->schedule->assessment_date->format('d/m/Y') : '-',
            $asesmen->result ? ucfirst($asesmen->result) : '-',
            $asesmen->certificate ? $asesmen->certificate->issued_date->format('d/m/Y') : '-',
            $asesmen->certificate ? $asesmen->certificate->certificate_number : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']]],
        ];
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'registered' => 'Terdaftar',
            'data_completed' => 'Data Lengkap',
            'verified' => 'Terverifikasi',
            'paid' => 'Sudah Bayar',
            'scheduled' => 'Terjadwal',
            'pre_assessment_completed' => 'Pra-Asesmen Selesai',
            'assessed' => 'Sudah Diases',
            'certified' => 'Tersertifikasi',
        ];
        return $labels[$status] ?? $status;
    }
}