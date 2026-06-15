<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
    @page { margin: 0; size: A4 portrait; }
    * { box-sizing: border-box; }
    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        color: #000;
        margin: 0;
        padding: 1.5cm 2cm 1.5cm 2cm;
        line-height: 1.5;
    }
    .kop-garis {
        border-top: 3pt solid #000;
        border-bottom: 1pt solid #000;
        height: 4pt;
        margin-bottom: 14pt;
    }
    .doc-title {
        text-align: center;
        font-size: 13pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 2pt;
    }
    .doc-subtitle {
        text-align: center;
        font-size: 10.5pt;
        margin-bottom: 12pt;
    }
    .opening { text-align: justify; margin-bottom: 10pt; }
    .tabel-peserta {
        width: 100%;
        border-collapse: collapse;
        margin: 8pt 0 12pt 0;
        font-size: 10.5pt;
    }
    .tabel-peserta th {
        border: 1pt solid #000;
        background: #f0f0f0;
        text-align: center;
        padding: 4pt 6pt;
        font-weight: bold;
    }
    .tabel-peserta td {
        border: 1pt solid #000;
        padding: 3pt 6pt;
        vertical-align: middle;
    }
    .td-center { text-align: center; }
    .closing { text-align: justify; margin-bottom: 6pt; }
    .ttd-table { width: 100%; border: none; border-collapse: collapse; margin-top: 14pt; }
    .ttd-table td { border: none; vertical-align: top; }
    .ttd-left  { width: 50%; }
    .ttd-right { width: 50%; text-align: center; }
    .ttd-sig   { height: 55pt; }
    .ttd-sig img { max-height: 55pt; max-width: 120pt; }
    .ttd-name  { font-weight: bold; text-decoration: underline; }
    .ttd-reg   { font-size: 9.5pt; margin-top: 2pt; }
    </style>
</head>
<body>

    @php
    $bnspPath = public_path('images/bnsp.png');
    $lspPath  = public_path('images/icon-lsp.png');
    $bnspSrc  = file_exists($bnspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bnspPath)) : '';
    $lspSrc   = file_exists($lspPath)  ? 'data:image/png;base64,' . base64_encode(file_get_contents($lspPath))  : '';
    @endphp

    <table style="width:100%; border:none; border-collapse:collapse;">
        <tr>
            <td style="width:25%; text-align:left; vertical-align:middle;">
                @if($bnspSrc)<img src="{{ $bnspSrc }}" style="height:20pt; width:auto;" alt="BNSP">@endif
            </td>
            <td style="width:50%; text-align:center; vertical-align:middle;">
                @if($lspSrc)<img src="{{ $lspSrc }}" style="height:90pt; width:auto;" alt="LSP-KAP">@endif
            </td>
            <td style="width:25%;"></td>
        </tr>
    </table>

    <div class="kop-garis"></div>

    <div class="doc-title">Berita Acara Asesmen/Uji Kompetensi</div>
    <div class="doc-subtitle">
        Pelaksanaan Sertifikasi Tanggal
        {{ \Carbon\Carbon::parse($beritaAcara->tanggal_pelaksanaan)->translatedFormat('d F Y') }}<br>
        LSP KOMPETENSI ADMINISTRASI PERKANTORAN
    </div>

    @php
    $totalK  = collect($rekMap)->filter(fn($r) => $r === 'K')->count();
    $totalBK = collect($rekMap)->filter(fn($r) => $r === 'BK')->count();
    $total   = $totalK + $totalBK;
    $tglStr  = \Carbon\Carbon::parse($beritaAcara->tanggal_pelaksanaan)->translatedFormat('l, d F Y');
    @endphp

    <div class="opening">
        Pada {{ $tglStr }}, bertempat di {{ $schedule->tuk->name ?? '-' }} telah dilakukan
        Uji Kompetensi Keahlian Administrasi Perkantoran untuk Skema
        <strong>{{ $schedule->skema->name }}</strong>
        yang diikuti sebanyak {{ $total }} orang peserta dengan penjelasan sebagai berikut:
    </div>

    <table class="tabel-peserta">
        <thead>
            <tr>
                <th style="width:18pt;" rowspan="2">No</th>
                <th rowspan="2">Nama</th>
                <th style="width:130pt;" rowspan="2">Organisasi</th>
                <th colspan="2" style="width:60pt;">Rekomendasi</th>
            </tr>
            <tr>
                <th style="width:35pt;">K</th>
                <th style="width:35pt;">BK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedule->asesmens as $i => $asesiItem)
            @php $rek = $rekMap[$asesiItem->id] ?? null; @endphp
            <tr>
                <td class="td-center">{{ $i + 1 }}.</td>
                <td>{{ $asesiItem->full_name }}</td>
                <td class="td-center">{{ $asesiItem->institution ?? $schedule->tuk->name ?? '-' }}</td>
                <td class="td-center" style="font-size:14pt;">{{ $rek === 'K'  ? 'V' : '-' }}</td>
                <td class="td-center" style="font-size:14pt;">{{ $rek === 'BK' ? 'V' : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($beritaAcara->catatan)
    <div class="closing"><strong>Catatan:</strong> {{ $beritaAcara->catatan }}</div>
    @endif

    <div class="closing">
        Demikian berita acara asesmen/uji kompetensi ini dibuat sebagai pengambil keputusan oleh LSP-KAP.
    </div>

    @php
    $sigDataUri = $asesor?->user?->signature_image ?? null;
    @endphp

    <table class="ttd-table">
        <tr>
            <td class="ttd-left"></td>
            <td class="ttd-right">
                <div>Bandung, {{ $tanggalSurat->translatedFormat('d F Y') }}</div>
                <div>Asesor</div>
                <div class="ttd-sig">
                    @if($sigDataUri)
                    <img src="{{ $sigDataUri }}" alt="TTD Asesor">
                    @endif
                </div>
                <div><span class="ttd-name">{{ $asesor?->nama ?? '______________________' }}</span></div>
                @if($asesor?->no_reg_met)
                <div class="ttd-reg">MET. {{ $asesor->no_reg_met }}</div>
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
