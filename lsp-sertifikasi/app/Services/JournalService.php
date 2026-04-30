<?php
// app/Services/JournalService.php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\HonorPayment;
use App\Models\BiayaOperasional;
use Illuminate\Support\Facades\DB;

class JournalService
{
    // ── Ambil akun by kode (cached per request) ───────────────────────────
    private array $akunCache = [];

    private function akun(string $kode): ChartOfAccount
    {
        if (!isset($this->akunCache[$kode])) {
            $akun = ChartOfAccount::where('kode', $kode)->first();
            abort_if(!$akun, 500, "Akun CoA [{$kode}] tidak ditemukan. Pastikan seeder sudah dijalankan.");
            $this->akunCache[$kode] = $akun;
        }
        return $this->akunCache[$kode];
    }

    // ── Buat jurnal generik ───────────────────────────────────────────────
    private function createJournal(
        string $tanggal,
        string $keterangan,
        array  $lines,         // [['akun'=>'1-002','debit'=>X,'kredit'=>0], ...]
        string $refType,
        int    $refId
    ): JournalEntry {
        // Guard: jangan duplikat
        if (JournalEntry::existsFor($refType, $refId)) {
            throw new \RuntimeException("Jurnal untuk {$refType}#{$refId} sudah ada.");
        }

        return DB::transaction(function () use ($tanggal, $keterangan, $lines, $refType, $refId) {
            $entry = JournalEntry::create([
                'tanggal'     => $tanggal,
                'nomor'       => JournalEntry::generateNomor(),
                'keterangan'  => $keterangan,
                'ref_type'    => $refType,
                'ref_id'      => $refId,
                'created_by'  => auth()->id(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id'    => $entry->id,
                    'chart_of_account_id' => $this->akun($line['akun'])->id,
                    'debit'               => $line['debit'],
                    'kredit'              => $line['kredit'],
                    'keterangan'          => $line['ket'] ?? null,
                ]);
            }

            // Validasi: total debit harus = total kredit
            $totalDebit  = collect($lines)->sum('debit');
            $totalKredit = collect($lines)->sum('kredit');
            if ($totalDebit !== $totalKredit) {
                throw new \RuntimeException("Jurnal tidak balance: debit={$totalDebit}, kredit={$totalKredit}");
            }

            return $entry;
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    // PUBLIC METHODS — dipanggil dari controller
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Payment asesi verified
     * Dr. 1-002 Bank
     *     Cr. 4-001 Pendapatan Sertifikasi
     */
    public function jurnalPaymentVerified(Payment $payment): JournalEntry
    {
        $nama = $payment->asesmen->full_name ?? 'Asesi';
        $skema = $payment->asesmen->skema->name ?? '-';

        return $this->createJournal(
            tanggal: now()->toDateString(),
            keterangan: "Penerimaan sertifikasi — {$nama} ({$skema})",
            lines: [
                [
                    'akun' => '1-002',
                    'debit' => $payment->amount,
                    'kredit' => 0,
                    'ket'  => "Pembayaran dari {$nama}"
                ],
                [
                    'akun' => '4-001',
                    'debit' => 0,
                    'kredit' => $payment->amount,
                    'ket'  => "Pendapatan sertifikasi {$skema}"
                ],
            ],
            refType: Payment::class,
            refId: $payment->id,
        );
    }

    /**
     * Piutang asesi timbul saat payment dibuat
     * Dr. 1-003 Piutang Asesi
     *     Cr. 4-001 Pendapatan Sertifikasi
     */
    public function jurnalPiutangAsesi(Payment $payment): JournalEntry
    {
        $nama  = $payment->asesmen->full_name ?? 'Asesi';
        $skema = $payment->asesmen->skema->name ?? '-';

        return $this->createJournal(
            tanggal:    now()->toDateString(),
            keterangan: "Piutang sertifikasi — {$nama} ({$skema})",
            lines: [
                ['akun' => '1-003', 'debit' => $payment->amount, 'kredit' => 0,
                'ket'  => "Piutang dari {$nama}"],
                ['akun' => '4-001', 'debit' => 0, 'kredit' => $payment->amount,
                'ket'  => "Pendapatan sertifikasi {$skema}"],
            ],
            refType: Payment::class . '_piutang',
            refId:   $payment->id,
        );
    }


/**
 * Piutang TUK timbul saat invoice kolektif dikirim
 * Dr. 1-003 Piutang Asesi (digunakan juga untuk piutang TUK)
 *     Cr. 4-001 Pendapatan Sertifikasi
 */
public function jurnalPiutangInvoice(\App\Models\Invoice $invoice): JournalEntry
{
    return $this->createJournal(
        tanggal:    now()->toDateString(),
        keterangan: "Piutang kolektif — {$invoice->recipient_name} ({$invoice->invoice_number})",
        lines: [
            ['akun' => '1-003', 'debit' => (int)$invoice->total_amount, 'kredit' => 0,
             'ket'  => "Piutang dari {$invoice->recipient_name}"],
            ['akun' => '4-001', 'debit' => 0, 'kredit' => (int)$invoice->total_amount,
             'ket'  => "Pendapatan sertifikasi kolektif {$invoice->invoice_number}"],
        ],
        refType: \App\Models\Invoice::class . '_piutang',
        refId:   $invoice->id,
    );
}

/**
 * Piutang TUK lunas saat angsuran diverifikasi
 * Dr. 1-002 Bank
 *     Cr. 1-003 Piutang Asesi
 */
public function jurnalPiutangInvoiceLunas(\App\Models\CollectivePayment $payment): JournalEntry
{
    $invoice = $payment->invoice;

    return $this->createJournal(
        tanggal:    now()->toDateString(),
        keterangan: "Pelunasan angsuran ke-{$payment->installment_number} — {$invoice->invoice_number}",
        lines: [
            ['akun' => '1-002', 'debit' => (int)$payment->amount, 'kredit' => 0,
             'ket'  => "Angsuran ke-{$payment->installment_number} {$invoice->invoice_number}"],
            ['akun' => '1-003', 'debit' => 0, 'kredit' => (int)$payment->amount,
             'ket'  => "Piutang lunas {$invoice->invoice_number}"],
        ],
        refType: \App\Models\CollectivePayment::class,
        refId:   $payment->id,
    );
}

    /**
     * Piutang asesi lunas saat payment verified
     * Dr. 1-002 Bank
     *     Cr. 1-003 Piutang Asesi
     */
    public function jurnalPiutangLunas(Payment $payment): JournalEntry
    {
        $nama = $payment->asesmen->full_name ?? 'Asesi';

        return $this->createJournal(
            tanggal:    now()->toDateString(),
            keterangan: "Pelunasan piutang sertifikasi — {$nama}",
            lines: [
                ['akun' => '1-002', 'debit' => $payment->amount, 'kredit' => 0,
                'ket'  => "Kas masuk dari {$nama}"],
                ['akun' => '1-003', 'debit' => 0, 'kredit' => $payment->amount,
                'ket'  => "Piutang lunas {$nama}"],
            ],
            refType: Payment::class,
            refId:   $payment->id,
        );
    }


    /**
     * Honor payment dibuat → catat utang honor
     * Dr. 5-001 Beban Honor
     *     Cr. 2-001 Utang Honor Asesor
     */
    public function jurnalHonorDibuat(HonorPayment $honor): JournalEntry
    {
        $nama = $honor->asesor->nama ?? 'Asesor';

        return $this->createJournal(
            tanggal:    now()->toDateString(),
            keterangan: "Pengakuan beban honor asesor — {$nama} ({$honor->nomor_kwitansi})",
            lines: [
                ['akun' => '5-001', 'debit' => $honor->total, 'kredit' => 0,
                'ket'  => "Beban honor {$nama}"],
                ['akun' => '2-001', 'debit' => 0, 'kredit' => $honor->total,
                'ket'  => "Utang honor {$honor->nomor_kwitansi}"],
            ],
            refType: HonorPayment::class . '_dibuat',
            refId:   $honor->id,
        );
    }

    /**
     * Honor dibayar → lunasi utang honor
     * Dr. 2-001 Utang Honor Asesor
     *     Cr. 1-002 Bank
     */
    public function jurnalHonorDibayar(HonorPayment $honor): JournalEntry
    {
        $nama = $honor->asesor->nama ?? 'Asesor';

        return $this->createJournal(
            tanggal:    $honor->dibayar_at->toDateString(),
            keterangan: "Pelunasan honor asesor — {$nama} ({$honor->nomor_kwitansi})",
            lines: [
                ['akun' => '2-001', 'debit' => $honor->total, 'kredit' => 0,
                'ket'  => "Lunasi utang honor {$nama}"],
                ['akun' => '1-002', 'debit' => 0, 'kredit' => $honor->total,
                'ket'  => "Transfer honor {$honor->nomor_kwitansi}"],
            ],
            refType: HonorPayment::class,
            refId:   $honor->id,
        );
    }

    /**
     * Biaya operasional dicatat
     * Dr. 5-002 Beban Operasional
     *     Cr. 1-002 Bank
     */
    public function jurnalBiayaOperasional(BiayaOperasional $biaya): JournalEntry
    {
        return $this->createJournal(
            tanggal: $biaya->tanggal->toDateString(),
            keterangan: "Biaya operasional — {$biaya->uraian} ({$biaya->nama_penerima})",
            lines: [
                [
                    'akun' => '5-002',
                    'debit' => $biaya->total,
                    'kredit' => 0,
                    'ket'  => $biaya->uraian
                ],
                [
                    'akun' => '1-002',
                    'debit' => 0,
                    'kredit' => $biaya->total,
                    'ket'  => "Dibayar ke {$biaya->nama_penerima}"
                ],
            ],
            refType: BiayaOperasional::class,
            refId: $biaya->id,
        );
    }

    /**
     * Distribusi ke yayasan
     * Dr. 3-002 Surplus Tahun Berjalan
     *     Cr. 2-003 Hutang Distribusi Yayasan
     */
    public function jurnalDistribusi(int $jumlah, int $tahun): JournalEntry
    {
        // Pakai ref_type khusus untuk distribusi (tidak ada model spesifik)
        if (JournalEntry::existsFor('distribusi_yayasan', $tahun)) {
            throw new \RuntimeException("Jurnal distribusi tahun {$tahun} sudah ada.");
        }

        return $this->createJournal(
            tanggal: now()->toDateString(),
            keterangan: "Distribusi surplus ke yayasan tahun {$tahun}",
            lines: [
                [
                    'akun' => '3-002',
                    'debit' => $jumlah,
                    'kredit' => 0,
                    'ket'  => "Distribusi surplus {$tahun}"
                ],
                [
                    'akun' => '2-003',
                    'debit' => 0,
                    'kredit' => $jumlah,
                    'ket'  => "Hutang distribusi yayasan"
                ],
            ],
            refType: 'distribusi_yayasan',
            refId: $tahun,
        );
    }

    /**
     * Jurnal balik distribusi (awal tahun berikutnya)
     * Dr. 2-003 Hutang Distribusi Yayasan
     *     Cr. 3-002 Surplus Tahun Berjalan
     */
    public function jurnalBalikDistribusi(int $jumlah, int $tahun): JournalEntry
    {
        return $this->createJournal(
            tanggal: now()->toDateString(),
            keterangan: "Jurnal balik distribusi yayasan tahun {$tahun}",
            lines: [
                [
                    'akun' => '2-003',
                    'debit' => $jumlah,
                    'kredit' => 0,
                    'ket'  => "Balik hutang distribusi {$tahun}"
                ],
                [
                    'akun' => '3-002',
                    'debit' => 0,
                    'kredit' => $jumlah,
                    'ket'  => "Balik surplus {$tahun}"
                ],
            ],
            refType: 'jurnal_balik_distribusi',
            refId: $tahun,
        );
    }
}