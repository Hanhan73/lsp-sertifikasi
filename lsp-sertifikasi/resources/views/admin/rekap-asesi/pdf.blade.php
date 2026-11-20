<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #222; padding: 24px 28px; }
.kop-garis { border-top: 3pt solid #000; border-bottom: 1pt solid #000; height: 4pt; margin: 6px 0 12px; }
h2 { font-size: 13px; text-align: center; margin-bottom: 2px; }
.sub { text-align: center; color: #555; font-size: 9px; margin-bottom: 2px; }
h3 { font-size: 10.5px; margin: 14px 0 5px; }
table.data { width: 100%; border-collapse: collapse; }
table.data th { background: #2C3E50; color: #fff; padding: 5px 6px; text-align: center; font-size: 8.5px; border: 1px solid #2C3E50; }
table.data td { padding: 4px 6px; border: 1px solid #ccc; font-size: 8.5px; }
table.data tr.even td { background: #f6f6f6; }
table.data tr.total td { background: #2C3E50; color: #fff; font-weight: bold; }
.c { text-align: center; }
.note { font-size: 7.5px; color: #666; font-style: italic; margin-top: 3px; }
.foot { font-size: 7.5px; color: #888; margin-top: 16px; }
</style>
</head>
<body>

{{-- ══ KOP SURAT ══ --}}
@php
$bnspPath = public_path('images/bnsp.png');
$lspPath  = public_path('images/icon-lsp.png');
$bnspSrc  = file_exists($bnspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bnspPath)) : '';
$lspSrc   = file_exists($lspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($lspPath)) : '';
$total    = $rekap['total'];
@endphp
<table style="width:100%; border:none; border-collapse:collapse;">
    <tr>
        <td style="width:25%; text-align:left; vertical-align:middle;">
            @if($bnspSrc)<img src="{{ $bnspSrc }}" style="height:18pt; width:auto;" alt="BNSP">@endif
        </td>
        <td style="width:50%; text-align:center; vertical-align:middle;">
            @if($lspSrc)<img src="{{ $lspSrc }}" style="height:70pt; width:auto;" alt="LSP-KAP">@endif
        </td>
        <td style="width:25%;"></td>
    </tr>
</table>
<div class="kop-garis"></div>

<h2>LAPORAN REKAP ASESI</h2>
<p class="sub">Periode Asesmen: {{ $periodeText }}</p>
@if($filterText)
<p class="sub">{{ $filterText }}</p>
@endif

{{-- Ringkasan --}}
<table class="data" style="margin-top:10px;">
    <tr>
        <th>Total Daftar</th><th>Kompeten</th><th>Belum Kompeten</th><th>Belum Ada Hasil</th><th>Asesor Ditugaskan</th>
    </tr>
    <tr>
        <td class="c">{{ $total['daftar'] }}</td>
        <td class="c">{{ $total['kompeten'] }}</td>
        <td class="c">{{ $total['bk'] }}</td>
        <td class="c">{{ $total['belum'] }}</td>
        <td class="c">{{ $total['asesor'] }}</td>
    </tr>
</table>

@foreach($tabs as $key => $tab)
@php $isAsesor = $key === 'asesor'; @endphp
<h3>{{ $loop->iteration }}. Rekap {{ $tab['title'] }}</h3>
<table class="data">
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:{{ $isAsesor ? '43%' : '35%' }}">{{ $tab['label'] }}</th>
            <th style="width:13%">{{ $isAsesor ? 'Jml Asesi Ditangani' : 'Daftar' }}</th>
            <th style="width:11%">Kompeten</th>
            <th style="width:11%">Belum Kompeten</th>
            <th style="width:{{ $isAsesor ? '17%' : '12%' }}">Belum Ada Hasil</th>
            @unless($isAsesor)<th style="width:13%">Jml Asesor Ditugaskan</th>@endunless
        </tr>
    </thead>
    <tbody>
        @forelse($rekap['rows'][$key] as $r)
        <tr class="{{ $loop->even ? 'even' : '' }}">
            <td class="c">{{ $loop->iteration }}</td>
            <td>{{ $r['label'] }}</td>
            <td class="c">{{ $r['daftar'] }}</td>
            <td class="c">{{ $r['kompeten'] }}</td>
            <td class="c">{{ $r['bk'] }}</td>
            <td class="c">{{ $r['belum'] }}</td>
            @unless($isAsesor)<td class="c">{{ $r['asesor'] }}</td>@endunless
        </tr>
        @empty
        <tr><td colspan="{{ $isAsesor ? 6 : 7 }}" class="c">Tidak ada data</td></tr>
        @endforelse
        <tr class="total">
            <td></td>
            <td>TOTAL</td>
            <td class="c">{{ $total['daftar'] }}</td>
            <td class="c">{{ $total['kompeten'] }}</td>
            <td class="c">{{ $total['bk'] }}</td>
            <td class="c">{{ $total['belum'] }}</td>
            @unless($isAsesor)<td class="c">{{ $total['asesor'] }}*</td>@endunless
        </tr>
    </tbody>
</table>
@unless($isAsesor)
<p class="note">* Total jumlah asesor = asesor berbeda secara keseluruhan, bukan penjumlahan kolom.</p>
@endunless
@endforeach

<p class="foot">Dicetak {{ now()->translatedFormat('d F Y H:i') }} — data akun simulasi/dummy tidak disertakan.</p>

</body>
</html>
