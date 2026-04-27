<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kwitansi {{ $invoice->invoice_number }}</title>
<style>
@page { size: A4; margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 11pt;
    color: #000;
    padding: 1.5cm 2cm;
}

/* ── KOP ─────────────────────────────────────────────────────── */
.kop-img    { width: 100%; height: auto; display: block; }
.kop-border { border-bottom: 3pt solid #000; margin-bottom: 16pt; }

/* ── Kotak kwitansi ──────────────────────────────────────────── */
.kwitansi-box {
    border: 1.5pt solid #000;
    padding: 20pt 24pt;
    margin-top: 10pt;
}

/* ── Nomor kwitansi ──────────────────────────────────────────── */
.no-kwitansi { font-size: 10pt; margin-bottom: 14pt; }

/* ── Baris data ──────────────────────────────────────────────── */
.data-table { width: 100%; border: none; border-collapse: collapse; }
.data-table td { padding: 3pt 0; font-size: 11pt; vertical-align: top; }
.data-label { width: 130pt; }
.data-colon { width: 12pt; }
.data-value { }

/* ── Jumlah uang box ─────────────────────────────────────────── */
.jumlah-box {
    border: 1pt solid #000;
    padding: 5pt 10pt;
    font-style: italic;
    font-weight: bold;
    font-size: 11.5pt;
    display: inline-block;
    min-width: 260pt;
}

/* ── TTD ─────────────────────────────────────────────────────── */
.ttd-table { width: 100%; border: none; border-collapse: collapse; margin-top: 24pt; }
.ttd-left  { width: 50%; vertical-align: top; font-size: 10.5pt; }
.ttd-right {
    width: 50%;
    text-align: center;
    vertical-align: top;
    font-size: 10.5pt;
}
.ttd-box  { height: 70pt; text-align: center; }
.ttd-box img { max-height: 65pt; max-width: 160pt; }
.ttd-name {
    border-top: 1pt solid #000;
    display: inline-block;
    min-width: 150pt;
    padding-top: 3pt;
    font-weight: bold;
}
</style>
</head>
<body>

@php
    \Carbon\Carbon::setLocale('id');

    $kopPath = public_path('images/kop_surat.png');
    $kopSrc  = file_exists($kopPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath))
        : null;

    // Versi berisi: tampilkan TTD + stempel
    $ttdSrc     = null;
    $stempelSrc = null;
    if ($versi === 'berisi') {
        $ttdPath = storage_path('app/private/mankeu/ttd.png');
        if (file_exists($ttdPath)) {
            $ttdSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($ttdPath));
        }
        $stempelPath = storage_path('app/private/mankeu/stempel.png');
        if (file_exists($stempelPath)) {
            $stempelSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath));
        }
    }

    // Tentukan nominal & keterangan angsuran
    if ($collectivePayment) {
        $nominal      = $collectivePayment->amount;
        $angsuranInfo = ' (Angsuran ke-' . $collectivePayment->installment_number . ')';
        $tanggal      = $collectivePayment->verified_at ?? $collectivePayment->updated_at ?? now();
    } else {
        $nominal      = $invoice->total_amount;
        $angsuranInfo = '';
        $tanggal      = $invoice->issued_at ?? now();
    }

    $terbilang = \App\Helpers\TerbilangHelper::convert((int) $nominal);
@endphp

{{-- ── KOP ────────────────────────────────────────────────────── --}}
@if($kopSrc)
<div class="kop-border">
    <img src="{{ $kopSrc }}" class="kop-img" alt="Kop LSP KAP">
</div>
@else
<div class="kop-border" style="padding-bottom:8pt;">
    <table style="width:100%;border:none;border-collapse:collapse;">
        <tr>
            <td style="width:70pt;vertical-align:middle;text-align:center;">
                <div style="width:55pt;height:55pt;border:2pt solid #cc0000;border-radius:28pt;text-align:center;font-size:8pt;font-weight:bold;color:#cc0000;padding-top:16pt;">LSP<br>KAP</div>
            </td>
            <td style="vertical-align:middle;border-left:2pt solid #333;padding-left:10pt;">
                <div style="font-size:14pt;font-weight:bold;">LSP Kompetensi Administrasi Perkantoran</div>
                <div style="font-size:9pt;margin-top:2pt;">
                    Graha DLA Lt. 2 Suite 06, Jl. Oto Iskandar Dinata, Nyengseret Astana Anyar, Kota Bandung – Jawa Barat
                </div>
            </td>
        </tr>
    </table>
</div>
@endif

{{-- ── Kotak Kwitansi ──────────────────────────────────────────── --}}
<div class="kwitansi-box">

    <div class="no-kwitansi">No. : {{ $invoice->invoice_number }}{{ $angsuranInfo ? str_replace('/', '-', $angsuranInfo) : '' }}</div>

    <table class="data-table">
        <tr>
            <td class="data-label">Telah diterima dari</td>
            <td class="data-colon">:</td>
            <td class="data-value"><b>{{ $invoice->tuk->name ?? $invoice->recipient_name }}</b></td>
        </tr>
        <tr>
            <td class="data-label">Uang sejumlah</td>
            <td class="data-colon">:</td>
            <td class="data-value">
                <div class="jumlah-box">{{ ucfirst($terbilang) }}</div>
            </td>
        </tr>
        <tr>
            <td class="data-label" style="padding-top:8pt;">Untuk pembayaran</td>
            <td class="data-colon" style="padding-top:8pt;">:</td>
            <td class="data-value" style="padding-top:8pt;">
                Biaya Asesmen Kompetensi
                @foreach($invoice->items as $idx => $item)
                    {{ $item['skema_name'] }}
                    ({{ $item['jumlah'] }} orang @ Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}){{ !$idx == count($invoice->items) - 1 ? ',' : '' }}
                @endforeach
                {{ $angsuranInfo }}
            </td>
        </tr>
    </table>

    {{-- ── TTD ───────────────────────────────────────────────────── --}}
    <table class="ttd-table">
        <tr>
            <td class="ttd-left">
                <b>Jumlah :</b>
                <div style="border:1pt solid #000;display:inline-block;padding:4pt 14pt;font-weight:bold;font-style:italic;font-size:11pt;min-width:140pt;">
                    Rp {{ number_format($nominal, 0, ',', '.') }}
                </div>
            </td>
            <td class="ttd-right">
                Jakarta, {{ $tanggal->translatedFormat('d F Y') }}<br>
                Penerima<br>
                <div class="ttd-box">
                    @if($ttdSrc)
                        <img src="{{ $ttdSrc }}" alt="TTD">
                    @endif
                    @if($stempelSrc)
                        <img src="{{ $stempelSrc }}" alt="Stempel"
                             style="max-height:60pt;max-width:60pt;position:relative;margin-left:-25pt;">
                    @endif
                </div>
                <span class="ttd-name">Dr. Marsofiyati, S.Pd., M.Pd.</span><br>
                <span style="font-size:10pt;">Manajer Keuangan</span>
            </td>
        </tr>
    </table>

</div>

</body>
</html>