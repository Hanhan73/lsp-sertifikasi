<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
    @page {
        size: A4;
        margin: 0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        color: #000;
        padding: 1.3cm 2cm 2cm 3cm;
    }

    .doc-title {
        text-align: center;
        font-size: 13pt;
        font-weight: bold;
        text-transform: uppercase;
        margin: 14pt 0 2pt;
        letter-spacing: .3pt;
    }

    .doc-subtitle {
        text-align: center;
        font-size: 13pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 12pt;
    }

    .opening {
        text-align: justify;
        line-height: 1.5;
        font-size: 12pt;
        margin-bottom: 8pt;
    }

    .tabel-peserta {
        width: 100%;
        border-collapse: collapse;
        font-size: 11pt;
        margin: 8pt 0;
    }

    .tabel-peserta th {
        border: 1pt solid #000;
        padding: 5pt 6pt;
        background-color: #D9D9D9;
        text-align: center;
        font-weight: bold;
    }

    .tabel-peserta td {
        border: 1pt solid #000;
        padding: 4pt 6pt;
        vertical-align: middle;
    }

    .td-center {
        text-align: center;
    }

    .closing {
        text-align: justify;
        line-height: 1.5;
        font-size: 12pt;
        margin: 10pt 0;
    }

    .ttd-table {
        width: 100%;
        border: none;
        border-collapse: collapse;
        margin-top: 16pt;
    }

    .ttd-right {
        width: 45%;
        text-align: center;
        vertical-align: top;
        font-size: 12pt;
        line-height: 1.5;
    }

    .ttd-left {
        width: 55%;
        vertical-align: top;
    }

    .ttd-sig {
        height: 70pt;
        text-align: center;
    }

    .ttd-sig img {
        max-height: 65pt;
        max-width: 150pt;
    }

    .ttd-name {
        font-weight: bold;
        border-top: 1pt solid #000;
        padding-top: 3pt;
        display: inline-block;
        min-width: 150pt;
    }

    .ttd-reg {
        font-size: 10pt;
    }
    </style>
</head>

<body>

    {{-- KOP: logo BNSP kiri, teks tengah, logo LSP-KAP kanan --}}
    @php
    \Carbon\Carbon::setLocale('id');
    $bnspPath = public_path('images/bnsp.png');
    $lspPath = public_path('images/icon-lsp.png');
    $bnspSrc = file_exists($bnspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bnspPath)) : '';
    $lspSrc = file_exists($lspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($lspPath)) : '';
    @endphp
    <div class="kop-border">
        <table style="width:100%; border:none; border-collapse:collapse;">
            <tr>
                <!-- KIRI -->
                <td style="width:25%; text-align:left; vertical-align:middle;">
                    @if($bnspSrc)
                    <img src="{{ $bnspSrc }}" style="height:20pt; width: auto;" alt="BNSP">
                    @endif
                </td>

                <!-- TENGAH -->
                <td style="width:50%; text-align:center;">
                    @if($lspSrc)
                    <img src="{{ $lspSrc }}" style="height:95pt; width: auto;" alt="LSP-KAP">
                    @endif
                </td>

                <!-- KANAN (kosong biar balance) -->
                <td style="width:25%;"></td>
            </tr>
        </table>
    </div>

    {{-- JUDUL --}}
    <div class="doc-title">Berita Acara Asesmen/Uji Kompetensi</div>
    <div class="doc-subtitle">
        Pelaksanaan Sertifikasi Tanggal
        {{ \Carbon\Carbon::parse($beritaAcara->tanggal_pelaksanaan)->translatedFormat('d F Y') }}<br>
        LSP KOMPETENSI ADMINISTRASI PERKANTORAN
    </div>

    {{-- PEMBUKA --}}
    @php
    $totalK = collect($rekMap)->filter(fn($r) => $r === 'K')->count();
    $totalBK = collect($rekMap)->filter(fn($r) => $r === 'BK')->count();
    $total = $totalK + $totalBK;
    $tglStr = \Carbon\Carbon::parse($beritaAcara->tanggal_pelaksanaan)->translatedFormat('l, d F Y');
    @endphp
    <div class="opening">
        Pada {{ $tglStr }}, bertempat di {{ $schedule->tuk->name ?? '-' }} telah dilakukan
        Uji Kompetensi Keahlian Administrasi Perkantoran untuk Skema
        {{ $schedule->skema->name }}
        yang diikuti sebanyak {{ $total }} orang peserta dengan penjelasan sebagai berikut:
    </div>

    {{-- TABEL PESERTA --}}
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
            @foreach($schedule->asesmens as $i => $asesmen)
            @php $rek = $rekMap[$asesmen->id] ?? null; @endphp
            <tr>
                <td class="td-center">{{ $i + 1 }}.</td>
                <td>{{ $asesmen->full_name }}</td>
                <td class="td-center">{{ $asesmen->institution ?? $schedule->tuk->name ?? '-' }}</td>
                <td class="td-center" style="font-size:14pt;">{{ $rek === 'K'  ? 'V' : '-' }}</td>
                <td class="td-center" style="font-size:14pt;">{{ $rek === 'BK' ? 'V' : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- CATATAN --}}
    @if($beritaAcara->catatan)
    <div class="closing"><strong>Catatan:</strong> {{ $beritaAcara->catatan }}</div>
    @endif

    <div class="closing">
        Demikian berita acara asesmen/uji kompetensi ini dibuat sebagai pengambil keputusan oleh LSP-KAP.
    </div>

    {{-- TTD ASESOR --}}
    @php
    $tanggalTtd = \Carbon\Carbon::parse($beritaAcara->tanggal_pelaksanaan)->translatedFormat('d F Y');
    $sigDataUri = $asesor?->user?->signature_image ?? null;
    @endphp
    <table class="ttd-table">
        <tr>
            <td class="ttd-left"></td>
            <td class="ttd-right">
                <div>Bandung, {{ $tanggalTtd }}</div>
                <div>Asesor</div>
                <div class="ttd-sig">
                    @if($sigDataUri)
                    <img src="{{ $sigDataUri }}" alt="TTD Asesor">
                    @endif
                </div>
                <div>
                    <span class="ttd-name">{{ $asesor?->nama ?? '______________________' }}</span>
                </div>
                @if($asesor?->no_reg_met)
                <div class="ttd-reg">MET. {{ $asesor->no_reg_met }}</div>
                @endif
            </td>
        </tr>
    </table>

</body>

</html>