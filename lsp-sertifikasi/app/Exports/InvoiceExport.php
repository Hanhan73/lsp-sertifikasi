<?php
// app/Exports/InvoiceExport.php

namespace App\Exports;

use App\Models\Payment;
use App\Models\Asesmen;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class InvoiceExport implements WithStyles, WithColumnWidths, WithEvents
{
    protected $payment;
    protected $asesmens;
    protected $isCollective;
    
    public function __construct($payment, $asesmens = null, $isCollective = false)
    {
        $this->payment = $payment;
        $this->asesmens = $asesmens;
        $this->isCollective = $isCollective;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header styles
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '2196F3']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 20,
            'D' => 15,
            'E' => 20,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                if ($this->isCollective && $this->asesmens) {
                    $this->generateCollectiveInvoice($sheet);
                } else {
                    $this->generateIndividualInvoice($sheet);
                }
            },
        ];
    }

    private function generateIndividualInvoice($sheet)
    {
        $asesmen = $this->payment->asesmen;
        $currentRow = 1;
        
        // Header
        $sheet->setCellValue('A' . $currentRow, 'INVOICE PEMBAYARAN SERTIFIKASI');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $currentRow += 2;
        
        $sheet->setCellValue('A' . $currentRow, 'SIKAP LSP');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $currentRow += 2;
        
        // Invoice Info
        $sheet->setCellValue('A' . $currentRow, 'No. Invoice:');
        $sheet->setCellValue('B' . $currentRow, 'INV-' . $this->payment->id . '-' . date('Ymd'));
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Tanggal:');
        $sheet->setCellValue('B' . $currentRow, $this->payment->verified_at ? $this->payment->verified_at->format('d F Y') : date('d F Y'));
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Status:');
        $sheet->setCellValue('B' . $currentRow, strtoupper($this->payment->status));
        $currentRow += 2;
        
        // Customer Info
        $sheet->setCellValue('A' . $currentRow, 'KEPADA:');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Nama:');
        $sheet->setCellValue('B' . $currentRow, $asesmen->full_name);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Email:');
        $sheet->setCellValue('B' . $currentRow, $asesmen->email ?? $asesmen->user->email);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'No. Registrasi:');
        $sheet->setCellValue('B' . $currentRow, '#' . $asesmen->id);
        $currentRow += 2;
        
        // Items Header
        $itemHeaderRow = $currentRow;
        $sheet->setCellValue('A' . $currentRow, 'No');
        $sheet->setCellValue('B' . $currentRow, 'Deskripsi');
        $sheet->setCellValue('C' . $currentRow, 'Skema');
        $sheet->setCellValue('D' . $currentRow, 'Qty');
        $sheet->setCellValue('E' . $currentRow, 'Harga');
        
        $sheet->getStyle('A' . $currentRow . ':E' . $currentRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2196F3']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $currentRow++;
        
        // Items
        $itemStartRow = $currentRow;
        $sheet->setCellValue('A' . $currentRow, '1');
        $sheet->setCellValue('B' . $currentRow, 'Sertifikasi Profesi');
        $sheet->setCellValue('C' . $currentRow, $asesmen->skema->name ?? '-');
        $sheet->setCellValue('D' . $currentRow, '1');
        
        $certFee = $asesmen->training_flag ? ($this->payment->amount - 1500000) : $this->payment->amount;
        $sheet->setCellValue('E' . $currentRow, 'Rp ' . number_format($certFee, 0, ',', '.'));
        $currentRow++;
        
        // Training if applicable
        if ($asesmen->training_flag) {
            $sheet->setCellValue('A' . $currentRow, '2');
            $sheet->setCellValue('B' . $currentRow, 'Pelatihan Sertifikasi');
            $sheet->setCellValue('C' . $currentRow, '-');
            $sheet->setCellValue('D' . $currentRow, '1');
            $sheet->setCellValue('E' . $currentRow, 'Rp ' . number_format(1500000, 0, ',', '.'));
            $currentRow++;
        }
        
        $itemEndRow = $currentRow - 1;
        
        // Apply border to items
        $sheet->getStyle('A' . $itemHeaderRow . ':E' . $itemEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        
        $currentRow++;
        
        // Total
        $sheet->setCellValue('D' . $currentRow, 'TOTAL:');
        $sheet->setCellValue('E' . $currentRow, 'Rp ' . number_format($this->payment->amount, 0, ',', '.'));
        $sheet->getStyle('D' . $currentRow . ':E' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('D' . $currentRow . ':E' . $currentRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD']
            ]
        ]);
        
        $currentRow += 2;
        
        // Payment Info
        $sheet->setCellValue('A' . $currentRow, 'INFORMASI PEMBAYARAN:');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Metode:');
        $sheet->setCellValue('B' . $currentRow, strtoupper($this->payment->method));
        $currentRow++;
        
        if ($this->payment->transaction_id) {
            $sheet->setCellValue('A' . $currentRow, 'Transaction ID:');
            $sheet->setCellValue('B' . $currentRow, $this->payment->transaction_id);
            $currentRow++;
        }
        
        if ($this->payment->verified_at) {
            $sheet->setCellValue('A' . $currentRow, 'Tanggal Verifikasi:');
            $sheet->setCellValue('B' . $currentRow, $this->payment->verified_at->format('d F Y H:i'));
            $currentRow++;
        }
        
        $currentRow += 2;
        
        // Footer
        $sheet->setCellValue('A' . $currentRow, 'Terima kasih atas kepercayaan Anda menggunakan layanan SIKAP LSP');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true);
    }

    private function generateCollectiveInvoice($sheet)
    {
        $firstAsesmen = $this->asesmens->first();
        $tuk = $firstAsesmen->tuk;
        $phase = $this->payment->payment_phase ?? 'full';
        $currentRow = 1;
        
        // Header
        $sheet->setCellValue('A' . $currentRow, 'INVOICE PEMBAYARAN KOLEKTIF');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $currentRow += 2;
        
        $sheet->setCellValue('A' . $currentRow, 'SIKAP LSP');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $currentRow += 2;
        
        // Invoice Info
        $sheet->setCellValue('A' . $currentRow, 'No. Invoice:');
        $sheet->setCellValue('B' . $currentRow, 'INV-BATCH-' . $this->payment->order_id);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Tanggal:');
        $sheet->setCellValue('B' . $currentRow, $this->payment->verified_at ? $this->payment->verified_at->format('d F Y') : date('d F Y'));
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Fase Pembayaran:');
        $sheet->setCellValue('B' . $currentRow, strtoupper($phase));
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Status:');
        $sheet->setCellValue('B' . $currentRow, strtoupper($this->payment->status));
        $currentRow += 2;
        
        // TUK Info
        $sheet->setCellValue('A' . $currentRow, 'KEPADA:');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'TUK:');
        $sheet->setCellValue('B' . $currentRow, $tuk->name);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Email:');
        $sheet->setCellValue('B' . $currentRow, $tuk->email);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Batch ID:');
        $sheet->setCellValue('B' . $currentRow, $firstAsesmen->collective_batch_id);
        $currentRow += 2;
        
        // Items Header
        $itemHeaderRow = $currentRow;
        $sheet->setCellValue('A' . $currentRow, 'No');
        $sheet->setCellValue('B' . $currentRow, 'Nama Peserta');
        $sheet->setCellValue('C' . $currentRow, 'Skema');
        $sheet->setCellValue('D' . $currentRow, 'Fase');
        $sheet->setCellValue('E' . $currentRow, 'Harga');
        
        $sheet->getStyle('A' . $currentRow . ':E' . $currentRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2196F3']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $currentRow++;
        
        // Items - Participants
        $itemStartRow = $currentRow;
        $total = 0;
        foreach ($this->asesmens as $index => $asesmen) {
            $amount = $this->payment->amount / $this->asesmens->count(); // Split equally
            
            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $asesmen->full_name);
            $sheet->setCellValue('C' . $currentRow, $asesmen->skema->name ?? '-');
            $sheet->setCellValue('D' . $currentRow, strtoupper($phase));
            $sheet->setCellValue('E' . $currentRow, 'Rp ' . number_format($amount, 0, ',', '.'));
            
            $total += $amount;
            $currentRow++;
        }
        
        $itemEndRow = $currentRow - 1;
        
        // Apply border to items
        $sheet->getStyle('A' . $itemHeaderRow . ':E' . $itemEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        
        $currentRow++;
        
        // Summary
        $sheet->setCellValue('D' . $currentRow, 'Jumlah Peserta:');
        $sheet->setCellValue('E' . $currentRow, $this->asesmens->count() . ' orang');
        $currentRow++;
        
        $sheet->setCellValue('D' . $currentRow, 'TOTAL:');
        $sheet->setCellValue('E' . $currentRow, 'Rp ' . number_format($total, 0, ',', '.'));
        $sheet->getStyle('D' . $currentRow . ':E' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('D' . $currentRow . ':E' . $currentRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD']
            ]
        ]);
        
        $currentRow += 2;
        
        // Payment Info
        $sheet->setCellValue('A' . $currentRow, 'INFORMASI PEMBAYARAN:');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;
        
        $sheet->setCellValue('A' . $currentRow, 'Metode:');
        $sheet->setCellValue('B' . $currentRow, strtoupper($this->payment->method));
        $currentRow++;
        
        if ($this->payment->transaction_id) {
            $sheet->setCellValue('A' . $currentRow, 'Transaction ID:');
            $sheet->setCellValue('B' . $currentRow, $this->payment->transaction_id);
            $currentRow++;
        }
        
        if ($this->payment->verified_at) {
            $sheet->setCellValue('A' . $currentRow, 'Tanggal Verifikasi:');
            $sheet->setCellValue('B' . $currentRow, $this->payment->verified_at->format('d F Y H:i'));
            $currentRow++;
        }
        
        $currentRow += 2;
        
        // Footer
        $sheet->setCellValue('A' . $currentRow, 'Terima kasih atas kepercayaan Anda menggunakan layanan SIKAP LSP');
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true);
    }
}