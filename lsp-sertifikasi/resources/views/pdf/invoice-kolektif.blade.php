<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->invoice_number }}</title>
<style>
@page { size: A4; margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 11pt;
    color: #000;
    padding: 1.2cm 2cm 1.5cm 2cm;
}

/* ── KOP ─────────────────────────────────────────────────────── */
.kop-img { width: 100%; height: auto; display: block; }
.kop-border { border-bottom: 3pt solid #000; margin-bottom: 10pt; }

/* ── Header info kiri-kanan ──────────────────────────────────── */
.header-table { width: 100%; border: none; border-collapse: collapse; margin-bottom: 12pt; }
.header-left  { width: 55%; vertical-align: top; font-size: 10pt; line-height: 1.7; }
.header-right {
    width: 40%;
    vertical-align: top;
    text-align: right;
    font-size: 10pt;
    line-height: 1.7;
}

/* ── Judul INVOICE ───────────────────────────────────────────── */
.invoice-title {
    text-align: center;
    font-size: 16pt;
    font-weight: bold;
    letter-spacing: 3pt;
    margin: 10pt 0 2pt;
}
.invoice-nomor {
    text-align: center;
    font-size: 10pt;
    margin-bottom: 12pt;
}

/* ── Yth. ────────────────────────────────────────────────────── */
.yth { font-size: 10.5pt; line-height: 1.7; margin-bottom: 14pt; }
.yth b { font-size: 11pt; }

/* ── Tabel Items ─────────────────────────────────────────────── */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
.items-table th {
    background: #000;
    color: #fff;
    padding: 5pt 7pt;
    font-size: 10pt;
    text-align: center;
    border: 1pt solid #000;
}
.items-table td {
    padding: 5pt 7pt;
    font-size: 10pt;
    border: 1pt solid #555;
    vertical-align: top;
}
.items-table .no    { text-align: center; width: 25pt; }
.items-table .right { text-align: right; }
.items-table .center { text-align: center; }

/* Baris total */
.total-row td {
    font-weight: bold;
    background: #f0f0f0;
}

/* ── Terbilang + Info bayar ──────────────────────────────────── */
.terbilang-box {
    font-size: 10.5pt;
    margin: 10pt 0 16pt;
    line-height: 1.8;
}

/* ── TTD ─────────────────────────────────────────────────────── */
.ttd-table { width: 100%; border: none; border-collapse: collapse; margin-top: 20pt; }
.ttd-left  { width: 55%; vertical-align: top; font-size: 10pt; line-height: 1.7; }
.ttd-right {
    width: 45%;
    text-align: center;
    vertical-align: top;
    font-size: 10pt;
}
.ttd-box { height: 70pt; text-align: center; }
.ttd-box img { max-height: 65pt; max-width: 160pt; }
.ttd-name {
    border-top: 1pt solid #000;
    display: inline-block;
    min-width: 160pt;
    padding-top: 3pt;
    font-weight: bold;
    font-size: 10.5pt;
}
.ttd-jabatan { font-size: 10pt; }
</style>
</head>
<body>

{{-- ── KOP SURAT ─────────────────────────────────────────────── --}}
@php
    \Carbon\Carbon::setLocale('id');
    $kopPath = public_path('images/kop_surat.png');
    $kopSrc  = file_exists($kopPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath))
        : null;

    // TTD Manajer Keuangan (simpan di storage/app/private/mankeu/ttd.png)
    $ttdPath = storage_path('app/private/mankeu/ttd.png');
    $ttdSrc  = file_exists($ttdPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($ttdPath))
        : null;

    // Stempel (storage/app/private/mankeu/stempel.png)
    $stempelPath = storage_path('app/private/mankeu/stempel.png');
    $stempelSrc  = file_exists($stempelPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath))
        : null;

    $romans = [
        1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
        7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII',
    ];
    $issuedAt = $invoice->issued_at ?? now();
@endphp

@if($kopSrc)
<div class="kop-border">
    <img src="{{ $kopSrc }}" class="kop-img" alt="Kop LSP KAP">
</div>
@else
{{-- Fallback kop surat --}}
<div class="kop-border" style="padding-bottom:8pt;">
    <table style="width:100%;border:none;border-collapse:collapse;">
        <tr>
            <td style="width:70pt;vertical-align:middle;text-align:center;">
                <div style="width:55pt;height:55pt;border:2pt solid #cc0000;border-radius:28pt;text-align:center;font-size:8pt;font-weight:bold;color:#cc0000;padding-top:16pt;">LSP<br>KAP</div>
            </td>
            <td style="vertical-align:middle;border-left:2pt solid #333;padding-left:10pt;">
                <div style="font-size:14pt;font-weight:bold;">LSP Kompetensi Administrasi Perkantoran</div>
                <div style="font-size:9pt;margin-top:2pt;">
                    Graha DLA Lt. 2 Suite 06, Jl. Oto Iskandar Dinata, Nyengseret Astana Anyar, Kota Bandung – Jawa Barat<br>
                    NPWP: 50.599.365.9-422.000
                </div>
            </td>
        </tr>
    </table>
</div>
@endif

{{-- ── Header kiri-kanan ───────────────────────────────────────── --}}
<table class="header-table">
    <tr>
        <td class="header-left">
            Jakarta, {{ $issuedAt->translatedFormat('d F Y') }}<br>
            Manajer Keuangan,<br>
            <b>Dr Marsofiyati S Pd M Pd</b><br>
            LSP Kompetensi Administrasi Perkantoran<br>
            <br>
            NPWP &nbsp;&nbsp;: 50.599.365.9-422.000<br>
            Alamat &nbsp;: Graha DLA Lt. 2 Suite 06<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jl. Oto Iskandar Dinata, Nyengseret Astana Anyar<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kota Bandung – Jawa Barat
        </td>
        <td class="header-right">
            <b>BSI An. LSP KAP</b><br>
            No.Rek: 1619161919<br>
            KCP Bandung UPI
        </td>
    </tr>
</table>

{{-- ── Judul ───────────────────────────────────────────────────── --}}
<div class="invoice-title">INVOICE</div>
<div class="invoice-nomor">No.: {{ $invoice->invoice_number }}</div>

{{-- ── Yth. ────────────────────────────────────────────────────── --}}
<div class="yth">
    Yth.<br>
    Pimpinan<br>
    <b>{{ $invoice->recipient_name }}</b><br>
    @if($invoice->recipient_address)
        {!! nl2br(e($invoice->recipient_address)) !!}
    @endif
</div>

{{-- ── Tabel Items ─────────────────────────────────────────────── --}}
<table class="items-table">
    <thead>
        <tr>
            <th class="no">No</th>
            <th>Nama Skema</th>
            <th style="width:60pt;">Jumlah Asesi</th>
            <th style="width:90pt;">Harga Satuan</th>
            <th style="width:100pt;">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $i => $item)
        <tr>
            <td class="no">{{ $i + 1 }}</td>
            <td>{{ $item['skema_name'] }}</td>
            <td class="center">{{ $item['jumlah'] }} Orang</td>
            <td class="right">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
            <td class="right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        {{-- Baris total asesi --}}
        <tr>
            <td colspan="2" class="right" style="font-weight:bold;">
                Total Asesi = {{ collect($invoice->items)->sum('jumlah') }} Orang
            </td>
            <td colspan="2" class="right" style="font-weight:bold;">Sub Total</td>
            <td class="right" style="font-weight:bold;">
                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
            </td>
        </tr>
        {{-- Baris Total besar --}}
        <tr class="total-row">
            <td colspan="4" class="right">Total</td>
            <td class="right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

{{-- ── Terbilang ───────────────────────────────────────────────── --}}
<div class="terbilang-box">
    Total biaya yang dibayarkan <b>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
    ({{ \App\Helpers\TerbilangHelper::convert((int) $invoice->total_amount) }})</b>.
    Pembayaran dapat dilakukan melalui Rekening Bank BSI No. 1619161919 atas nama LSP-KAP.
    @if($invoice->notes)
        <br><br>Catatan: {{ $invoice->notes }}
    @endif
</div>

{{-- ── TTD ─────────────────────────────────────────────────────── --}}
<table class="ttd-table">
    <tr>
        <td class="ttd-left"></td>
        <td class="ttd-right">
            Jakarta, {{ $issuedAt->translatedFormat('d F Y') }}<br>
            Manajer Keuangan,<br>
            <div class="ttd-box">
                @if($ttdSrc)
                    <img src="{{ $ttdSrc }}" alt="TTD">
                @endif
                @if($stempelSrc)
                    <img src="{{ $stempelSrc }}" alt="Stempel" style="max-height:60pt;max-width:60pt;position:relative;margin-left:-30pt;">
                @endif
            </div>
            <span class="ttd-name">Dr. Marsofiyati, S.Pd., M.Pd.</span><br>
            <span class="ttd-jabatan">Manajer Keuangan</span>
        </td>
    </tr>
</table>

</body>
</html>