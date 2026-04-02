<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Times New Roman', Times, serif;
        font-size: 11pt;
        color: #000;
        padding: 20px 30px;
    }

    .header {
        text-align: center;
        margin-bottom: 18px;
    }

    .header h1 {
        font-size: 13pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .header-line {
        border-top: 2.5px solid #000;
        border-bottom: 1px solid #000;
        margin: 6px 0;
        height: 4px;
    }

    .info-block {
        margin-bottom: 14px;
    }

    .info-block table {
        border: none;
        width: auto;
    }

    .info-block td {
        padding: 2px 0;
        font-size: 10.5pt;
        border: none;
    }

    .info-block td:first-child {
        width: 130px;
    }

    .info-block td.colon {
        width: 12px;
    }

    .info-block .val {
        font-weight: bold;
    }

    .tabel-peserta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
        font-size: 10.5pt;
    }

    .tabel-peserta th {
        background: #e8e8e8;
        border: 1px solid #333;
        padding: 6px 8px;
        text-align: center;
        font-weight: bold;
        font-size: 10pt;
    }

    .tabel-peserta td {
        border: 1px solid #555;
        vertical-align: top;
    }

    .tabel-peserta td.no {
        text-align: center;
        padding: 7px 4px;
        width: 30px;
    }

    .tabel-peserta td.nama {
        padding: 7px 8px;
        width: 34%;
    }

    .tabel-peserta td.lembaga {
        padding: 7px 8px;
    }

    .tabel-peserta td.ttd {
        width: 110px;
        height: 44px;
    }

    .footer-wrap {
        width: 100%;
        margin-top: 10px;
    }

    .ttd-block {
        float: right;
        text-align: center;
        width: 200px;
    }

    .ttd-label {
        font-size: 10.5pt;
        margin-bottom: 4px;
    }

    .ttd-box {
        height: 72px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ttd-box img {
        max-height: 68px;
        max-width: 180px;
    }

    .ttd-line {
        border-top: 1px solid #000;
        padding-top: 3px;
        font-size: 10pt;
        font-weight: bold;
    }

    .ttd-reg {
        font-size: 9.5pt;
        color: #333;
    }

    .clearfix::after {
        content: '';
        display: table;
        clear: both;
    }
    </style>
</head>

<body>

    <div class="header">
        <h1>Daftar Hadir Peserta Uji Sertifikasi Kompetensi</h1>
        <div class="header-line"></div>
    </div>

    <div class="info-block">
        <table>
            <tr>
                <td>Skema Sertifikasi</td>
                <td class="colon">:</td>
                <td class="val">{{ $schedule->skema->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>TUK</td>
                <td class="colon">:</td>
                <td class="val">{{ $schedule->tuk->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Uji</td>
                <td class="colon">:</td>
                <td class="val">{{ $schedule->assessment_date->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td class="colon">:</td>
                <td class="val">{{ $schedule->start_time }} – {{ $schedule->end_time }}</td>
            </tr>
            <tr>
                <td>Asesor</td>
                <td class="colon">:</td>
                <td class="val">{{ $asesor->nama ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="tabel-peserta">
        <thead>
            <tr>
                <th style="width:30px;">NO</th>
                <th style="width:34%;">NAMA PESERTA</th>
                <th>ASAL LEMBAGA / INSTITUSI</th>
                <th style="width:110px;">TANDA TANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asesmens as $i => $asesmen)
            <tr>
                <td class="no">{{ $i + 1 }}</td>
                <td class="nama">
                    {{ $asesmen->full_name }}
                    <div style="font-size:8.5pt;color:#555;margin-top:2px;">NIK: {{ $asesmen->nik }}</div>
                </td>
                <td class="lembaga">{{ $asesmen->institution ?? '-' }}</td>
                <td class="ttd"></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;padding:12px;color:#888;">Belum ada peserta terdaftar</td>
            </tr>
            @endforelse
            @for($x = $asesmens->count(); $x < 10; $x++) <tr>
                <td class="no">{{ $x + 1 }}</td>
                <td class="nama">&nbsp;</td>
                <td class="lembaga">&nbsp;</td>
                <td class="ttd"></td>
                </tr>
                @endfor
        </tbody>
    </table>

    <div class="footer-wrap clearfix">
        <div class="ttd-block">
            <div class="ttd-label">Asesor,</div>
            <div class="ttd-box">
                @if($ttdAsesor)
                <img src="{{ $ttdAsesor }}" alt="TTD Asesor">
                @endif
            </div>
            <div class="ttd-line">{{ $asesor->nama ?? '___________________' }}</div>
            <div class="ttd-reg">No. Reg: {{ $asesor->no_reg_met ?? '___________________' }}</div>
        </div>
    </div>

</body>

</html>