<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FR.APL.02 - {{ $apldua->asesmen->full_name ?? '' }}</title>
    <style>
    @page {
        size: A4;
        margin: 0;
    }
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.4;
        color: #000;
        padding: 1.8cm 2cm;
    }
    .doc-title {
        font-size: 13pt;
        font-weight: bold;
        margin-bottom: 8pt;
    }
    /* Skema table */
    .skema-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
        margin-bottom: 8pt;
    }
    .skema-tbl td {
        border: 1pt solid #000;
        padding: 4pt 6pt;
        vertical-align: middle;
    }
    /* Panduan box */
    .panduan-tbl {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10pt;
    }
    .panduan-tbl td {
        border: 1pt solid #000;
        padding: 5pt 7pt;
        vertical-align: top;
        font-size: 9.5pt;
    }
    .panduan-tbl td.header {
        font-weight: bold;
        background: #f2f2f2;
    }
    /* Unit header */
    .unit-header-tbl {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    .unit-header-tbl td {
        border: 1pt solid #000;
        padding: 3pt 6pt;
        font-size: 9.5pt;
        vertical-align: middle;
        line-height: 1.3;
    }
    /* Elemen table */
    .elemen-tbl {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8pt;
    }
    .elemen-tbl th {
        border: 1pt solid #000;
        padding: 4pt 5pt;
        text-align: center;
        font-size: 9pt;
        background: #f9f9f9;
        font-weight: bold;
    }
    .elemen-tbl td {
        border: 1pt solid #000;
        padding: 5pt 6pt;
        vertical-align: top;
        font-size: 9pt;
    }
    .elemen-tbl td.col-dapatkah {
        width: 52%;
    }
    .elemen-tbl td.col-k {
        width: 8%;
        text-align: center;
        vertical-align: middle;
    }
    .elemen-tbl td.col-bk {
        width: 8%;
        text-align: center;
        vertical-align: middle;
    }
    .elemen-tbl td.col-bukti {
        width: 32%;
    }
    /* Jangan potong baris elemen di tengah-tengah */
    .elemen-tbl tr {
        page-break-inside: avoid;
    }
    /* Header unit jangan terpisah dari tabel elemennya */
    .unit-block {
        page-break-inside: avoid;
    }
    .cb-wrap {
        display: inline-block;
        width: 12pt;
        height: 12pt;
        border: 1pt solid #000;
        text-align: center;
        line-height: 12pt;
        font-size: 9pt;
        font-weight: bold;
        vertical-align: middle;
    }
    .elemen-judul {
        font-weight: bold;
        margin-bottom: 3pt;
    }
    .kuk-label {
        font-style: italic;
        font-size: 8.5pt;
        color: #333;
    }
    .kuk-item {
        font-size: 8.5pt;
        padding-left: 8pt;
        color: #222;
    }
    .page-break {
        page-break-after: always;
    }
    /* Signature table */
    .sig-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
        margin-top: 10pt;
        page-break-inside: avoid;
    }
    .sig-tbl td {
        border: 1pt solid #000;
        padding: 5pt 7pt;
        vertical-align: top;
    }
    .sig-cell-img {
        height: 65pt;
        text-align: center;
        vertical-align: middle;
        padding: 4pt 6pt;
    }
    .sig-img {
        max-width: 160pt;
        max-height: 55pt;
        display: block;
        margin: 0 auto;
    }
    .sig-date {
        font-size: 8.5pt;
        text-align: center;
        padding: 3pt 6pt 4pt;
    }
    .rekomendasi-box {
        background: #f9f9f9;
        border: 1pt solid #000;
        padding: 5pt 8pt;
        font-size: 10pt;
        margin-bottom: 6pt;
    }
    </style>
</head>
<body>

{{-- ─── HALAMAN 1: Header + Panduan + Unit 1-dst ─── --}}
<div class="doc-title">FR.APL.02. ASESMEN MANDIRI</div>

{{-- Skema table --}}
<table class="skema-tbl">
    <tr>
        <td style="width:40%;" rowspan="2">Skema Sertifikasi
            @if($asesmen->skema?->jenis_skema === 'kkni')
            (KKNI/Okupasi/Klaster)
            @elseif($asesmen->skema?->jenis_skema === 'okupasi')
            (KKNI/Okupasi/Klaster)
            @else
            (KKNI/Okupasi/Klaster)
            @endif
        </td>
        <td style="width:8%;">Judul</td>
        <td style="width:3%; text-align:center; font-weight:bold;">:</td>
        <td style="width:49%; font-weight:bold;">{{ $asesmen->skema?->name ?? '-' }}</td>
    </tr>
    <tr>
        <td>Nomor</td>
        <td style="text-align:center; font-weight:bold;">:</td>
        <td>{{ $asesmen->skema?->nomor_skema ?? '-' }}</td>
    </tr>
</table>

{{-- Panduan --}}
<table class="panduan-tbl">
    <tr>
        <td class="header">PANDUAN ASESMEN MANDIRI</td>
    </tr>
    <tr>
        <td>
            <strong>Instruksi:</strong><br>
            &bull; Baca setiap pertanyaan di kolom sebelah kiri<br>
            &bull; Beri tanda centang (&#10004;) pada kotak jika Anda yakin dapat melakukan tugas yang dijelaskan.<br>
            &bull; Isi kolom di sebelah kanan dengan menuliskan bukti yang Anda miliki, dengan panduan pertanyaan &quot;Apa&quot;, &quot;Bagaimana&quot;, &quot;Kapan&quot; dan &quot;Dimana&quot; terkait bukti tersebut.
        </td>
    </tr>
</table>

{{-- Units --}}
@php
    $jawMap = $apldua->jawabans->keyBy('elemen_id');
    $units  = $asesmen->skema?->unitKompetensis ?? collect();
@endphp

@foreach($units as $uIdx => $unit)

{{-- Wrapper: header + kolom header tidak orphan ── --}}
<div style="page-break-inside: avoid;">
<table style="width:100%; border-collapse:collapse; margin-bottom:0; border-bottom:none;">
    <tr>
        <td style="white-space:nowrap; font-weight:bold; background:#f2f2f2; vertical-align:middle; border:1pt solid #000; padding:3pt 3pt; font-size:9.5pt;" rowspan="2">Unit Kompetensi {{ $uIdx + 1 }}</td>
        <td style="border:1pt solid #000; padding:3pt 6pt; font-size:9pt;"><span style="color:#666;">Kode Unit : </span>{{ $unit->kode_unit }}</td>
    </tr>
    <tr>
        <td style="border:1pt solid #000; padding:3pt 6pt; font-size:9pt;"><span style="color:#666; font-weight:normal;">Judul Unit : </span><strong>{{ $unit->judul_unit }}</strong></td>
    </tr>
</table>
<table style="width:100%; border-collapse:collapse; margin-bottom:0; border-top:none;">
    <tr style="background:#f9f9f9;">
        <td style="border:1pt solid #000; padding:4pt 5pt; text-align:center; font-weight:bold; font-size:9pt; width:52%;">Dapatkah Saya...?</td>
        <td style="border:1pt solid #000; padding:4pt 5pt; text-align:center; font-weight:bold; font-size:9pt; width:8%;">K</td>
        <td style="border:1pt solid #000; padding:4pt 5pt; text-align:center; font-weight:bold; font-size:9pt; width:8%;">BK</td>
        <td style="border:1pt solid #000; padding:4pt 5pt; text-align:center; font-weight:bold; font-size:9pt; width:32%;">Bukti yang relevan</td>
    </tr>
</table>
</div>

{{-- Elemen rows ── --}}
<table class="elemen-tbl" style="margin-bottom:8pt; margin-top:0;">
    @foreach($unit->elemens as $eIdx => $elemen)
    @php $jaw = $jawMap[$elemen->id] ?? null; @endphp
    <tr>
        <td class="col-dapatkah">
            <div class="elemen-judul">{{ $eIdx + 1 }}. Elemen: {{ $elemen->judul }}</div>
            @if($elemen->kuks->isNotEmpty())
            <div class="kuk-label">&bull;&nbsp;Kriteria Unjuk Kerja:</div>
            @foreach($elemen->kuks as $kIdx => $kuk)
            <div class="kuk-item">{{ $eIdx + 1 }}.{{ $kIdx + 1 }}. {{ $kuk->deskripsi }}</div>
            @endforeach
            @endif
        </td>
        <td class="col-k">
            <span class="cb-wrap">{{ $jaw?->jawaban === 'K' ? 'V' : '' }}</span>
        </td>
        <td class="col-bk">
            <span class="cb-wrap">{{ $jaw?->jawaban === 'BK' ? 'V' : '' }}</span>
        </td>
        <td class="col-bukti" style="word-break:break-word;">
            {{ $jaw?->bukti ?? '' }}
        </td>
    </tr>
    @endforeach
</table>

@endforeach

{{-- TTD Table --}}
<table class="sig-tbl">
    {{-- Row 1: Asesi header --}}
    <tr>
        <td style="width:40%; vertical-align:top; border-right:1pt solid #000;" rowspan="4">
            <strong>Rekomendasi Untuk Asesi:</strong><br><br>
            @if($apldua->rekomendasi_asesor === 'lanjut')
            Asesmen <strong>dapat</strong> / <span style="text-decoration:line-through;">tidak dapat</span> dilanjutkan
            @elseif($apldua->rekomendasi_asesor === 'tidak_lanjut')
            Asesmen <span style="text-decoration:line-through;">dapat</span> / <strong>tidak dapat</strong> dilanjutkan
            @else
            Asesmen dapat / tidak dapat dilanjutkan
            @endif
            @if($apldua->catatan_asesor)
            <br><br><em style="font-size:8.5pt;">Catatan: {{ $apldua->catatan_asesor }}</em>
            @endif
        </td>
        <td colspan="2" style="text-align:center; font-weight:bold;">Asesi :</td>
    </tr>
    <tr>
        <td style="width:22%;">Nama</td>
        <td style="width:38%;"><strong>{{ $apldua->nama_ttd_asesi ?? $asesmen->full_name }}</strong></td>
    </tr>
    <tr>
        <td style="vertical-align:top; padding-top:4pt;">Tanda tangan/<br>Tanggal</td>
        <td class="sig-cell-img">
            @if($apldua->ttd_asesi)
            <img src="{{ $apldua->ttd_asesi_image }}" class="sig-img" alt="TTD Asesi">
            @endif
            <div class="sig-date">
                {{ $apldua->tanggal_ttd_asesi?->format('d-m-Y') ?? '' }}
            </div>
        </td>
    </tr>
    <tr style="border-top-style: none;">
        <td style="border-top-style: none;"></td>
        <td style="border-top-style: none;"></td>
    </tr>

    {{-- Asesor section --}}
    <tr>
        <td style="vertical-align:top;" rowspan="4">
            <strong>Catatan :</strong>
            <div style="min-height:20pt; margin-top:4pt; font-size:9pt;">
                {{ $apldua->catatan_asesor ?? '' }}
            </div>
        </td>
        <td colspan="2" style="text-align:center; font-weight:bold;">Ditinjau Oleh Asesor :</td>
    </tr>
    <tr>
        <td>Nama :</td>
        <td><strong>{{ $apldua->nama_ttd_asesor ?? '' }}</strong></td>
    </tr>
    <tr>
        <td>No. Reg:</td>
        <td>{{ $asesor_no_reg ?? $apldua->asesmen?->schedule?->asesor?->no_reg_met ?? '' }}</td>
    </tr>
    <tr>
        <td style="vertical-align:top; padding-top:4pt;">Tanda tangan/<br>Tanggal</td>
        <td class="sig-cell-img">
            @if($apldua->ttd_asesor)
            <img src="{{ $apldua->ttd_asesor_image }}" class="sig-img" alt="TTD Asesor">
            @endif
            <div class="sig-date">
                {{ $apldua->tanggal_ttd_asesor?->format('d-m-Y') ?? '' }}
            </div>
        </td>
    </tr>
</table>

</body>
</html>