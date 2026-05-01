<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>FR.AK.03 - Umpan Balik</title>
    <style>
@page {
    margin: 0;
    size: A4 portrait;
}

body {
    font-family: Arial, sans-serif;
    font-size: 9pt;
    color: #000;
    padding: 0mm 15mm 8mm 15mm;
    margin: 0;
}

hr.kop-line {
    border: none;
    border-top: 2px solid #000;
    margin: 1px 0 5px 0;
}

.judul-wrapper {
    text-align: center;
    margin-bottom: 5px;
}
.judul-wrapper .judul {
    font-size: 11pt;
    font-weight: bold;
    text-transform: uppercase;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5px;
}
.info-table td {
    padding: 1px 3px;
    font-size: 8.5pt;
    vertical-align: top;
}
.info-table .label {
    width: 110px;
    font-weight: bold;
}
.info-table .sep {
    width: 8px;
}

.tbl {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5px;
}
.tbl th, .tbl td {
    border: 1px solid #555;
    padding: 2px 4px;
    font-size: 8pt;
    vertical-align: middle;
}
.tbl thead th {
    background-color: #dce6f1;
    font-weight: bold;
    text-align: center;
}
.tbl .no {
    text-align: center;
    width: 22px;
}
.tbl .col-hasil {
    text-align: center;
    width: 30px;
}
.tbl .col-catatan {
    width: 110px;
}
.tbl .checked {
    text-align: center;
    font-size: 11pt;
    font-weight: bold;
}

.catatan-box {
    border: 1px solid #555;
    padding: 4px 6px;
    min-height: 20px;
    font-size: 8.5pt;
    margin-bottom: 6px;
}

.ttd-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6px;
}
.ttd-table td {
    width: 50%;
    text-align: center;
    padding: 2px;
    font-size: 8.5pt;
    vertical-align: top;
}
.ttd-space {
    height: 40px;
}
.ttd-line {
    border-top: 1px solid #000;
    padding-top: 3px;
}
    </style>
</head>
<body>

{{-- KOP SURAT --}}
@php
    $kopPath = storage_path('app/public/images/kop_surat.png');
    $kopSrc  = file_exists($kopPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath))
        : null;
@endphp

@if($kopSrc)
    <img src="{{ $kopSrc }}" style="width:100%;height:auto;display:block;object-fit:contain;" alt="Kop Surat">
@endif


    {{-- JUDUL --}}
    <div class="judul-wrapper">
        <div class="judul">Umpan Balik dan Catatan Asesmen</div>
    </div>

    {{-- INFO HEADER --}}
    <table class="info-table">
        <tr>
            <td class="label">Skema Sertifikasi</td>
            <td class="sep">:</td>
            <td>{{ $asesmen->skema->name ?? '-' }} ({{ $asesmen->skema->jenis_skema ?? '' }})</td>
            <td class="label">Judul</td>
            <td class="sep">:</td>
            <td>{{ $asesmen->skema->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label"></td>
            <td></td>
            <td></td>
            <td class="label">Nomor</td>
            <td class="sep">:</td>
            <td>{{ $asesmen->skema->nomor_skema ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">TUK</td>
            <td class="sep">:</td>
            <td colspan="4">{{ $schedule->tuk->name ?? '-' }} ({{ $schedule->tuk->jenis_tuk ?? 'Sewaktu' }})</td>
        </tr>
        <tr>
            <td class="label">Nama Asesor</td>
            <td class="sep">:</td>
            <td colspan="4">{{ $schedule->asesor->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Asesi</td>
            <td class="sep">:</td>
            <td colspan="4">{{ $asesmen->full_name }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Asesmen</td>
            <td class="sep">:</td>
            <td>{{ $schedule->assessment_date?->translatedFormat('d F Y') ?? '-' }}</td>
            <td class="label">Mulai</td>
            <td class="sep">:</td>
            <td>{{ $schedule->start_time ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td class="label">Selesai</td>
            <td class="sep">:</td>
            <td>{{ $schedule->end_time ?? '-' }}</td>
        </tr>
    </table>

    {{-- TABEL PERTANYAAN --}}
    <p style="font-size:9pt; margin-bottom:4px;">
        <em>Umpan balik dari Asesi (diisi oleh Asesi setelah pengambilan keputusan):</em>
    </p>

    <table class="tbl">
        <thead>
            <tr>
                <th class="no">No</th>
                <th>KOMPONEN</th>
                <th class="col-hasil">Ya</th>
                <th class="col-hasil">Tidak</th>
                <th class="col-catatan">Catatan/Komentar Asesi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pertanyaan as $i => $p)
                @php $item = $frAk03->getJawabanItem($i); @endphp
                <tr>
                    <td class="no">{{ $i + 1 }}</td>
                    <td>{{ $p }}</td>
                    <td class="col-hasil checked">{{ ($item['jawaban'] ?? '') === 'ya' ? 'V' : '' }}</td>
                    <td class="col-hasil checked">{{ ($item['jawaban'] ?? '') === 'tidak' ? 'V' : '' }}</td>
                    <td class="col-catatan" style="font-size:8.5pt">{{ $item['catatan'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- CATATAN LAIN --}}
    <p style="font-size:9pt; margin-bottom:3px;"><strong>Catatan/komentar lainnya (apabila ada):</strong></p>
    <div class="catatan-box">{{ $frAk03->catatan_lain ?? '' }}</div>

</body>
</html>