<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #222; padding: 20px 28px; }
.kop-garis { border-top: 3pt solid #000; border-bottom: 1pt solid #000; height: 4pt; margin: 6px 0 10px; }
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
/* Bagan komposisi: tabel bersarang dengan lebar persen */
table.bar { width: 100%; border-collapse: collapse; height: 9px; }
table.bar td { border: none; padding: 0; height: 9px; font-size: 1px; }
/* Selektor dibuat lebih spesifik dari zebra `table.data tr.even td` */
table.data table.bar td.sep { width: 1px; background: #fff; }
table.data table.bar td.k  { background: #2a78d6; }
table.data table.bar td.bk { background: #eb6834; }
table.data table.bar td.bl { background: #1baf7a; }
.sw.k { background: #2a78d6; } .sw.bk { background: #eb6834; } .sw.bl { background: #1baf7a; }
table.legend td { border: none; padding: 0 10px 0 0; font-size: 8px; color: #444; }
.sw { display: inline-block; width: 9px; height: 9px; margin-right: 3px; }
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
$rate     = fn($n, $d) => $d > 0 ? $n / $d * 100 : 0;
$pctTxt   = fn($n, $d) => number_format($rate($n, $d), 1, ',', '.') . '%';
@endphp
<table style="width:100%; border:none; border-collapse:collapse;">
    <tr>
        <td style="width:25%; text-align:left; vertical-align:middle;">
            @if($bnspSrc)<img src="{{ $bnspSrc }}" style="height:18pt; width:auto;" alt="BNSP">@endif
        </td>
        <td style="width:50%; text-align:center; vertical-align:middle;">
            @if($lspSrc)<img src="{{ $lspSrc }}" style="height:60pt; width:auto;" alt="LSP-KAP">@endif
        </td>
        <td style="width:25%;"></td>
    </tr>
</table>
<div class="kop-garis"></div>

<h2>LAPORAN REKAP ASESI{{ count($tabs) === 1 ? ' — ' . strtoupper(reset($tabs)['title']) : '' }}</h2>
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
        <td class="c">{{ $total['kompeten'] }} ({{ $pctTxt($total['kompeten'], $total['daftar']) }})</td>
        <td class="c">{{ $total['bk'] }} ({{ $pctTxt($total['bk'], $total['daftar']) }})</td>
        <td class="c">{{ $total['belum'] }} ({{ $pctTxt($total['belum'], $total['daftar']) }})</td>
        <td class="c">{{ $total['asesor'] }}</td>
    </tr>
</table>

{{-- Legenda bagan --}}
<table class="legend" style="margin-top:8px; border-collapse:collapse;">
    <tr>
        <td>Bagan komposisi:</td>
        <td><span class="sw k"></span>Kompeten</td>
        <td><span class="sw bk"></span>Belum Kompeten</td>
        <td><span class="sw bl"></span>Belum Ada Hasil</td>
    </tr>
</table>

@foreach($tabs as $key => $tab)
@php $isAsesor = $key === 'asesor'; $cols = $isAsesor ? 8 : 9; @endphp
<h3>{{ count($tabs) > 1 ? $loop->iteration . '. ' : '' }}Rekap {{ $tab['title'] }}</h3>
<table class="data">
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th style="width:{{ $isAsesor ? '30%' : '26%' }}">{{ $tab['label'] }}</th>
            <th style="width:8%">{{ $isAsesor ? 'Jml Asesi Ditangani' : 'Daftar' }}</th>
            <th style="width:8%">Kompeten</th>
            <th style="width:8%">Belum Kompeten</th>
            <th style="width:8%">Belum Ada Hasil</th>
            <th style="width:8%">% Kompeten</th>
            @unless($isAsesor)<th style="width:8%">Jml Asesor Ditugaskan</th>@endunless
            <th style="width:{{ $isAsesor ? '26%' : '22%' }}">Komposisi Hasil</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rekap['rows'][$key] as $r)
        @php
            $parts = array_filter([
                'k'  => $rate($r['kompeten'], $r['daftar']),
                'bk' => $rate($r['bk'], $r['daftar']),
                'bl' => $rate($r['belum'], $r['daftar']),
            ], fn($w) => $w > 0);
        @endphp
        <tr class="{{ $loop->even ? 'even' : '' }}">
            <td class="c">{{ $loop->iteration }}</td>
            <td>{{ $r['label'] }}</td>
            <td class="c">{{ $r['daftar'] }}</td>
            <td class="c">{{ $r['kompeten'] }}</td>
            <td class="c">{{ $r['bk'] }}</td>
            <td class="c">{{ $r['belum'] }}</td>
            <td class="c">{{ $pctTxt($r['kompeten'], $r['daftar']) }}</td>
            @unless($isAsesor)<td class="c">{{ $r['asesor'] }}</td>@endunless
            <td>
                @if($parts)
                <table class="bar"><tr>
                    @foreach($parts as $cls => $w)
                    @if(!$loop->first)<td class="sep"></td>@endif
                    <td class="{{ $cls }}" style="width:{{ number_format($w, 2, '.', '') }}%;">&nbsp;</td>
                    @endforeach
                </tr></table>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="{{ $cols }}" class="c">Tidak ada data</td></tr>
        @endforelse
        <tr class="total">
            <td></td>
            <td>TOTAL</td>
            <td class="c">{{ $total['daftar'] }}</td>
            <td class="c">{{ $total['kompeten'] }}</td>
            <td class="c">{{ $total['bk'] }}</td>
            <td class="c">{{ $total['belum'] }}</td>
            <td class="c">{{ $pctTxt($total['kompeten'], $total['daftar']) }}</td>
            @unless($isAsesor)<td class="c">{{ $total['asesor'] }}*</td>@endunless
            <td></td>
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
