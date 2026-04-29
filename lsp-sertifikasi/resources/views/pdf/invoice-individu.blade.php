<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoiceNumber }}</title>
<style>
@page { size: A4; margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 10.5pt;
    color: #000;
    padding: 0.8cm 1.8cm 1.2cm 1.8cm;
}
.kop-wrap { text-align: center; margin-bottom: 4pt; }
.kop-wrap img { height: 65pt; width: auto; }
.kop-border { border-bottom: 3pt solid #000; margin-bottom: 6pt; }
.header-table {
    width: 100%; border: none; border-collapse: collapse;
    margin-bottom: 6pt; font-size: 10pt; line-height: 1.5;
}
.header-left  { width: 60%; vertical-align: top; }
.header-right { width: 40%; vertical-align: top; text-align: right; }
.invoice-title {
    text-align: center; font-size: 15pt; font-weight: bold;
    letter-spacing: 4pt; margin: 6pt 0 2pt;
}
.invoice-nomor { text-align: center; font-size: 10pt; margin-bottom: 8pt; }
.yth { font-size: 10.5pt; line-height: 1.6; margin-bottom: 8pt; }
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 8pt; }
.items-table th {
    background: #000; color: #fff;
    padding: 4pt 6pt; font-size: 10pt;
    text-align: center; border: 1pt solid #000;
}
.items-table td {
    padding: 4pt 6pt; font-size: 10pt;
    border: 1pt solid #555; vertical-align: top;
}
.no     { text-align: center; width: 20pt; }
.right  { text-align: right; }
.center { text-align: center; }
.total-row td { font-weight: bold; background: #f0f0f0; }
.terbilang-box { font-size: 10.5pt; margin: 6pt 0 10pt; line-height: 1.7; }
.ttd-table { width: 100%; border: none; border-collapse: collapse; margin-top: 10pt; }
.ttd-left  { width: 50%; vertical-align: top; }
.ttd-right { width: 50%; text-align: center; vertical-align: top; font-size: 10pt; }
.ttd-img-wrap { position: relative; height: 65pt; text-align: center; }
.ttd-img-wrap img.ttd {
    position: absolute; max-height: 58pt; max-width: 135pt;
    left: 50%; transform: translateX(-50%); top: 3pt;
}
.ttd-img-wrap img.stempel {
    position: absolute; max-height: 52pt; max-width: 52pt;
    left: 55%; top: 6pt; opacity: 0.85;
}
.ttd-name {
    border-top: 1pt solid #000; display: inline-block;
    min-width: 155pt; padding-top: 2pt;
    font-weight: bold; font-size: 10.5pt;
}
</style>
</head>
<body>

@php
    \Carbon\Carbon::setLocale('id');
    $kopPath     = public_path('images/icon-lsp.png');
    $kopSrc      = file_exists($kopPath)     ? 'data:image/png;base64,'.base64_encode(file_get_contents($kopPath))     : null;
    $ttdPath     = storage_path('app/private/mankeu/ttd.png');
    $ttdSrc      = file_exists($ttdPath)     ? 'data:image/png;base64,'.base64_encode(file_get_contents($ttdPath))     : null;
    $stempelPath = storage_path('app/private/mankeu/stempel.png');
    $stempelSrc  = file_exists($stempelPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($stempelPath)) : null;
    $issuedAt    = $payment->verified_at ?? now();
    $nominal     = $payment->amount;
@endphp

{{-- KOP --}}
<div class="kop-border">
    @if($kopSrc)
    <div class="kop-wrap"><img src="{{ $kopSrc }}" alt="Logo LSP KAP"></div>
    @endif
    <table class="header-table">
        <tr>
            <td class="header-left">
                <strong>LSP Kompetensi Administrasi Perkantoran</strong><br>
                NPWP &nbsp;&nbsp;&nbsp;: 50.599.365.9-422.000<br>
                Alamat &nbsp;: Graha DLA Lt. 2 Suite 06<br>
                <span style="margin-left:42pt;">&nbsp;Jl. Oto Iskandar Dinata, Nyengseret Astana Anyar</span><br>
                <span style="margin-left:42pt;">&nbsp;Kota Bandung &ndash; Jawa Barat</span>
            </td>
            <td class="header-right">
                <strong>BSI An. LSP KAP</strong><br>
                No.Rek: 1619161919<br>
                KCP Bandung UPI
            </td>
        </tr>
    </table>
</div>

{{-- Judul --}}
<div class="invoice-title">INVOICE</div>
<div class="invoice-nomor">No.: {{ $invoiceNumber }}</div>

{{-- Yth --}}
<div class="yth">
    Yth.<br>
    <strong>{{ $asesmen->full_name }}</strong><br>
    {{ $asesmen->institution ?? ($asesmen->tuk->name ?? '') }}<br>
    @if($asesmen->address){{ $asesmen->address }}@endif
</div>

{{-- Tabel --}}
<table class="items-table">
    <thead>
        <tr>
            <th class="no">No</th>
            <th>Untuk Pembayaran</th>
            <th>Atas Nama</th>
            <th style="width:65pt;" class="center">Jumlah</th>
            <th style="width:110pt;" class="right">Nominal</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="no">1</td>
            <td>
                Biaya Asesmen Kompetensi<br>
                Skema: <strong>{{ $asesmen->skema->name ?? '-' }}</strong>
            </td>
            <td>{{ $asesmen->full_name }}</td>
            <td class="center">1 Orang</td>
            <td class="right">Rp {{ number_format($nominal, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="4" class="right">Total</td>
            <td class="right">Rp {{ number_format($nominal, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

{{-- Terbilang --}}
<div class="terbilang-box">
    Total biaya yang dibayarkan
    <strong>Rp {{ number_format($nominal, 0, ',', '.') }}
    ({{ \App\Helpers\TerbilangHelper::convert((int) $nominal) }})</strong>.
    Pembayaran dapat dilakukan melalui Rekening Bank BSI No. 1619161919 atas nama LSP-KAP.
</div>

{{-- TTD --}}
<table class="ttd-table">
    <tr>
        <td class="ttd-left"></td>
        <td class="ttd-right">
            Jakarta, {{ $issuedAt->translatedFormat('d F Y') }}<br>
            Manajer Keuangan,<br>
            <div class="ttd-img-wrap">
                @if($stempelSrc)<img src="{{ $stempelSrc }}" class="stempel" alt="Stempel">@endif
                @if($ttdSrc)<img src="{{ $ttdSrc }}" class="ttd" alt="TTD">@endif
            </div>
            <span class="ttd-name">Dr. Marsofiyati, S.Pd., M.Pd.</span><br>
            <span style="font-size:10pt;">Manajer Keuangan</span>
        </td>
    </tr>
</table>

</body>
</html>