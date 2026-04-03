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
        font-family: "Calibri", sans-serif;
        font-size: 11pt;
        color: #000;
        padding: 1cm 2.5cm 1.5cm 2.5cm;
    }

    /* Kop */
    .kop-img {
        width: 100%;
        height: auto;
        display: block;
    }

    .kop-border {
        margin-bottom: 16pt;
    }

    /* Judul */
    .judul {
        text-align: center;
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 18pt;
    }

    .judul-line {
        border-top: 2.5pt solid #000;
        border-bottom: 1pt solid #000;
        height: 4pt;
        margin-bottom: 12pt;
    }

    /* Info block */
    .info-table {
        border: none;
        border-collapse: collapse;
        margin-bottom: 12pt;
        margin-left: 40pt;
    }

    .info-table td {
        padding: 2pt 0;
        font-size: 10.5pt;
        border: none;
        vertical-align: top;
    }

    .info-table td.label {
        width: 160pt;
        font-weight: bold;
    }

    .info-table td.colon {
        width: 12pt;
        text-align: center;
    }

    .info-table td.val {
        font-weight: bold;
    }

    /* Tabel peserta */
    .tabel-peserta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20pt;
        font-size: 10.5pt;
        padding-left: 0;
    }

    .tabel-peserta th {
        background: #B8CCE4;
        border: 1pt solid #333;
        padding: 12pt 7pt;
        text-align: center;
        font-weight: bold;
        font-size: 10pt;
    }

    .tabel-peserta td {
        border: 1pt solid #555;
        vertical-align: top;
    }

    .tabel-peserta td.no {
        text-align: center;
        padding: 6pt 4pt;
        width: 28pt;
    }

    .tabel-peserta td.nama {
        padding: 6pt 7pt;
        width: 34%;
    }

    .tabel-peserta td.lembaga {
        padding: 6pt 7pt;
    }

    .tabel-peserta td.ttd {
        width: 100pt;
        height: 60pt;
        vertical-align: middle;
        /* penting */
    }

    /* TTD Asesor */
    .footer-table {
        width: 100%;
        border: none;
        border-collapse: collapse;
        margin-top: 8pt;
    }

    .footer-left {
        width: 55%;
        vertical-align: top;
    }

    .footer-right {
        width: 45%;
        text-align: center;
        vertical-align: top;
        font-size: 10.5pt;
    }

    .ttd-box {
        height: 65pt;
        text-align: center;
    }

    .ttd-box img {
        max-height: 60pt;
        max-width: 160pt;
    }

    .ttd-name {
        border-top: 1pt solid #000;
        padding-top: 3pt;
        font-weight: bold;
        font-size: 10pt;
        display: inline-block;
        min-width: 140pt;
    }

    .ttd-reg {
        font-size: 9.5pt;
        color: #333;
    }
    </style>
</head>

<body>

    {{-- KOP: gunakan kop_lsp.png (gambar kop lengkap dengan BNSP + logo + teks) --}}
    @php
    \Carbon\Carbon::setLocale('id');
    $kopPath = public_path('images/kop_surat.png');
    $kopSrc = file_exists($kopPath)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath))
    : null;
    @endphp
    @if($kopSrc)
    <div class="kop-border">
        <img src="{{ $kopSrc }}" class="kop-img" alt="Kop LSP">
    </div>
    @endif

    {{-- JUDUL --}}
    <div class="judul">Daftar Hadir Peserta Uji Sertifikasi Kompetensi</div>

    {{-- INFO JADWAL --}}
    <table class="info-table">
        <tr>
            <td class="label">1. Skema Sertifikasi</td>
            <td class="colon">:</td>
            <td class="val">{{ $schedule->skema->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">2. TUK</td>
            <td class="colon">:</td>
            <td class="val">{{ $schedule->tuk->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">3. Tanggal Uji</td>
            <td class="colon">:</td>
            <td class="val">{{ $schedule->assessment_date->translatedFormat('l, d F Y') }}</td>
        </tr>
    </table>

    {{-- TABEL PESERTA --}}
    <table class="tabel-peserta">
        <thead>
            <tr>
                <th style="width:18pt;">NO</th>
                <th style="width:34%;">NAMA PESERTA</th>
                <th>ASAL LEMBAGA</th>
                <th style="width:100pt;">TANDA TANGAN</th>
            </tr>
        </thead>
        <tbody>
            @php
            $ttdAse = $asesmen?->user?->signature_image ?? null;
            @endphp
            @forelse($asesmens as $i => $asesmen)
            <tr>
                <td class="no">{{ $i + 1 }}</td>
                <td class="nama">
                    {{ $asesmen->full_name }}
                </td>
                <td class="lembaga">{{ $asesmen->institution ?? '-' }}</td>
                <td class="ttd">
                    @php
                    $ttdAsesi = $asesmen->user?->signature_image ?? null;
                    $hadirAsesi = $asesmen->hadir;
                    @endphp
                    @if($ttdAsesi && $hadirAsesi)
                    <img src="{{ $ttdAsesi }}" style="max-height:45pt; max-width:85pt; display:block; margin:auto;">
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;padding:10pt;color:#888;">Belum ada peserta</td>
            </tr>
            @endforelse
            @php
            $minRows = 5;
            $total = $asesmens->count();
            @endphp

            @for($x = $total; $x < $minRows; $x++) <tr>
                <td class="no">{{ $x + 1 }}</td>
                <td class="nama">&nbsp;</td>
                <td class="lembaga">&nbsp;</td>
                <td class="ttd">-</td>
                </tr>
                @endfor
        </tbody>
    </table>

    {{-- TTD ASESOR --}}
    @php
    $ttdSrc = $asesor?->user?->signature_image ?? null;
    @endphp
    <table class="footer-table">
        <tr>
            <td class="footer-left"></td>
            <td class="footer-right">
                <div style="margin-bottom:4pt;">Asesor,</div>
                <div class="ttd-box">
                    @if($ttdSrc)
                    <img src="{{ $ttdSrc }}" alt="TTD Asesor">
                    @endif
                </div>
                <div>
                    <span class="ttd-name">{{ $asesor->nama ?? '______________________' }}</span>
                </div>
                <div class="ttd-reg">No. Reg: {{ $asesor->no_reg_met ?? '-' }}</div>
            </td>
        </tr>
    </table>

</body>

</html>