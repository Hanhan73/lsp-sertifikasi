<?php

namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ScheduleExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $scheduleIds;
    protected $groupInfo;
    protected $rowNumber = 0;

    public function __construct($scheduleIds, $groupInfo = [])
    {
        $this->scheduleIds = $scheduleIds;
        $this->groupInfo = $groupInfo;
    }

    /**
     * ✅ FIXED: Get asesmens from schedules (not schedules themselves)
     */
    public function collection()
    {
        // Get all schedules with their asesmens
        $schedules = Schedule::with(['asesmens.user', 'asesmens.skema', 'tuk', 'skema'])
            ->whereIn('id', $this->scheduleIds)
            ->orderBy('assessment_date', 'asc')
            ->get();

        // ✅ FIXED: Flatten to get all asesmens from all schedules
        $asesmens = $schedules->flatMap(function($schedule) {
            // Attach schedule data to each asesmen for mapping
            return $schedule->asesmens->map(function($asesmen) use ($schedule) {
                $asesmen->schedule_data = [
                    'assessment_date' => $schedule->assessment_date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'location' => $schedule->location,
                    'notes' => $schedule->notes,
                    'tuk_name' => $schedule->tuk->name ?? '-',
                ];
                return $asesmen;
            });
        });

        return $asesmens->sortBy('full_name')->values();
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            'NO',
            'NO REGISTRASI',
            'NIK',
            'NAMA LENGKAP',
            'TEMPAT LAHIR',
            'TANGGAL LAHIR',
            'JENIS KELAMIN',
            'ALAMAT',
            'KOTA/KAB',
            'PROVINSI',
            'NO. TELEPON',
            'EMAIL',
            'PENDIDIKAN',
            'PEKERJAAN',
            'SUMBER BIAYA',
            'INSTITUSI',
            'SKEMA SERTIFIKASI',
            'TUK',
            'TANGGAL ASESMEN',
            'WAKTU',
            'LOKASI',
            'JENIS PENDAFTARAN',
            'BATCH ID',
            'STATUS',
            'TRAINING',
            'BIAYA',
            'CATATAN',
        ];
    }

    /**
     * ✅ FIXED: Map data for each asesmen (not schedule)
     */
    public function map($asesmen): array
    {
        $this->rowNumber++;
        
        return [
            $this->rowNumber,
            $asesmen->id,
            $asesmen->nik ?? '-',
            $asesmen->full_name ?? $asesmen->user->name ?? '-',
            $asesmen->birth_place ?? '-',
            $asesmen->birth_date ? $asesmen->birth_date->format('d/m/Y') : '-',
            $asesmen->gender == 'L' ? 'Laki-laki' : ($asesmen->gender == 'P' ? 'Perempuan' : '-'),
            $asesmen->address ?? '-',
            $this->getCityName($asesmen->city_code),
            $this->getProvinceName($asesmen->province_code),
            $asesmen->phone ?? '-',
            $asesmen->user->email ?? $asesmen->email ?? '-',
            $asesmen->education ?? '-',
            $asesmen->occupation ?? '-',
            $asesmen->budget_source ?? '-',
            $asesmen->institution ?? '-',
            $asesmen->skema->name ?? '-',
            $asesmen->schedule_data['tuk_name'] ?? '-',
            $asesmen->schedule_data['assessment_date']->format('d F Y'),
            $asesmen->schedule_data['start_time'] . ' - ' . $asesmen->schedule_data['end_time'],
            $asesmen->schedule_data['location'],
            $asesmen->is_collective ? 'Kolektif' : 'Mandiri',
            $asesmen->is_collective ? $asesmen->collective_batch_id : '-',
            $asesmen->status_label,
            $asesmen->training_flag ? 'Ya' : 'Tidak',
            'Rp ' . number_format($asesmen->fee_amount, 0, ',', '.'),
            $asesmen->schedule_data['notes'] ?? '-',
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Title row (if group info exists)
        if (!empty($this->groupInfo)) {
            $sheet->insertNewRowBefore(1, 3);
            
            $sheet->setCellValue('A1', 'DAFTAR HADIR ASESMEN');
            $sheet->mergeCells('A1:AA1');
            
            $sheet->setCellValue('A2', 'Tanggal: ' . ($this->groupInfo['date'] ?? '-'));
            $sheet->setCellValue('A3', 'Waktu: ' . ($this->groupInfo['time'] ?? '-') . ' | Lokasi: ' . ($this->groupInfo['location'] ?? '-'));
            
            // Style for title
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            
            $sheet->getStyle('A2:A3')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
            ]);
        }

        // Header row styling
        $headerRow = !empty($this->groupInfo) ? 4 : 1;
        
        return [
            $headerRow => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // NO
            'B' => 12,  // NO REG
            'C' => 18,  // NIK
            'D' => 25,  // NAMA
            'E' => 15,  // TEMPAT LAHIR
            'F' => 15,  // TGL LAHIR
            'G' => 12,  // GENDER
            'H' => 35,  // ALAMAT
            'I' => 20,  // KOTA
            'J' => 15,  // PROVINSI
            'K' => 15,  // TELEPON
            'L' => 25,  // EMAIL
            'M' => 15,  // PENDIDIKAN
            'N' => 20,  // PEKERJAAN
            'O' => 15,  // SUMBER BIAYA
            'P' => 20,  // INSTITUSI
            'Q' => 30,  // SKEMA
            'R' => 20,  // TUK
            'S' => 15,  // TGL ASESMEN
            'T' => 15,  // WAKTU
            'U' => 25,  // LOKASI
            'V' => 12,  // JENIS
            'W' => 20,  // BATCH ID
            'X' => 15,  // STATUS
            'Y' => 10,  // TRAINING
            'Z' => 15,  // BIAYA
            'AA' => 30, // CATATAN
        ];
    }

    /**
     * Helper: Get city name from code
     */
    private function getCityName($cityCode)
    {
        // You can implement proper city lookup here
        // For now, return the code
        return $cityCode ?? '-';
    }

    /**
     * Helper: Get province name from code
     */
    private function getProvinceName($provinceCode)
    {
        // You can implement proper province lookup here
        // For now, return the code
        return $provinceCode ?? '-';
    }
}