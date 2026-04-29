<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kwitansi {{ $kwitansiNumber }}</title>
<style>
@page { size: A4; margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 10.5pt;
    color: #000;
    padding: 1cm 1.8cm 1.5cm 1.8cm;
}
.kop-wrap { text-align: center; margin-bottom: 4pt; }
.kop-wrap img { height: 65pt; width: auto; }
.kop-border { border-bottom: 3pt solid #000; margin-bottom: 10pt; }
.header-table {
    width: 100%; border: none; border-collapse: collapse;
    margin-bottom: 6pt; font-size: 10pt; line-height: 1.5;
}
.header-left  { width: 60%; vertical-align: top; }
.header-right { width: 40%; vertical-align: top; text-align: right; }

/* Kotak kwitansi */
.kwitansi-box {
    border: 1.5pt solid #000;
    padding: 18pt 22pt;
    margin-top: 8pt;
}
.no-kwitansi { font-size: 10pt; margin-bottom: 12pt; }
.data-table { width: 100%; border: none; border-collapse: collapse; }
.data-table td { padding: 3pt 0; font-size: 10.5pt; vertical-align: top; }
.data-label  { width: 120pt; }
.data-colon  { width: 10pt; }
.jumlah-box {
    border: 1pt solid #000; padding: 4pt 10pt;
    font-style: italic; font-weight: bold; font-size: 11pt;
    display: inline-block; min-width: 240pt;
}
.ttd-table { width: 100%; border: none; border-collapse: collapse; margin-top: 20pt; }
.ttd-left  { width: 50%; vertical-align: top; font-size: 10.5pt; }
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
    min-width: 150pt; padding-top: 2pt;
    font-weight: bold; font-size: 10.5pt;
}
</style>
</head>
<body>

@php
    \Carbon\Carbon::setLocale('id');
    $kopPath     = public_path('images/icon-lsp.png');
    $kopSrc      = file_exists($kopPath)     ? 'data:image/png;base64,'.base64_encode(file_get_contents($kopPath))     : null;
    $nominal     = $payment->amount;
    $terbilang   = \App\Helpers\TerbilangHelper::convert((int) $nominal);
    $tanggal     = $payment->verified_at ?? now();

    // TTD & stempel hanya tampil kalau versi berisi
    $ttdSrc     = null;
    $stempelSrc = null;
    if (($versi ?? 'berisi') === 'berisi') {
        $ttdPath     = storage_path('app/private/mankeu/ttd.png');
        $stempelPath = storage_path('app/private/mankeu/stempel.png');
        $ttdSrc      = file_exists($ttdPath)     ? 'data:image/png;base64,'.base64_encode(file_get_contents($ttdPath))     : null;
        $stempelSrc  = file_exists($stempelPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($stempelPath)) : null;
    }
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

{{-- Kotak Kwitansi --}}
<div class="kwitansi-box">

    <div class="no-kwitansi">No. : {{ $kwitansiNumber }}</div>

    <table class="data-table">
        <tr>
            <td class="data-label">Telah diterima dari</td>
            <td class="data-colon">:</td>
            <td><strong>{{ $asesmen->full_name }}</strong></td>
        </tr>
        <tr>
            <td class="data-label">Lembaga</td>
            <td class="data-colon">:</td>
            <td>{{ $asesmen->institution ?? ($asesmen->tuk->name ?? '-') }}</td>
        </tr>
        <tr>
            <td class="data-label" style="padding-top:6pt;">Uang sejumlah</td>
            <td class="data-colon" style="padding-top:6pt;">:</td>
            <td style="padding-top:6pt;">
                <div class="jumlah-box">{{ ucfirst($terbilang) }}</div>
            </td>
        </tr>
        <tr>
            <td class="data-label" style="padding-top:8pt;">Untuk pembayaran</td>
            <td class="data-colon" style="padding-top:8pt;">:</td>
            <td style="padding-top:8pt;">
                Biaya Asesmen Kompetensi Skema
                <strong>{{ $asesmen->skema->name ?? '-' }}</strong>
                atas nama <strong>{{ $asesmen->full_name }}</strong>
                @if($asesmen->institution), {{ $asesmen->institution }}@endif
            </td>
        </tr>
    </table>

    {{-- TTD --}}
    <table class="ttd-table">
        <tr>
            <td class="ttd-left">
                <strong>Jumlah :</strong>
                <div style="border:1pt solid #000;display:inline-block;padding:4pt 12pt;font-weight:bold;font-style:italic;font-size:11pt;min-width:130pt;">
                    Rp {{ number_format($nominal, 0, ',', '.') }}
                </div>
            </td>
            <td class="ttd-right">
                Jakarta, {{ $tanggal->translatedFormat('d F Y') }}<br>
                Penerima<br>
                <div class="ttd-img-wrap">
                    @if($stempelSrc)<img src="{{ $stempelSrc }}" class="stempel" alt="Stempel">@endif
                    @if($ttdSrc)<img src="{{ $ttdSrc }}" class="ttd" alt="TTD">@endif
                </div>
                <span class="ttd-name">Dr. Marsofiyati, S.Pd., M.Pd.</span><br>
                <span style="font-size:10pt;">Manajer Keuangan</span>
            </td>
        </tr>
    </table>

</div>

</body>
</html>