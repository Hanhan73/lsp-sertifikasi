<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; color: #222; padding: 20px 26px; }
.kop-garis { border-top: 3pt solid #000; border-bottom: 1pt solid #000; height: 4pt; margin: 6px 0 10px; }
h2 { font-size: 13px; text-align: center; margin-bottom: 2px; }
.sub { text-align: center; color: #555; font-size: 9px; margin-bottom: 2px; }
table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
table.data th { background: #2C3E50; color: #fff; padding: 5px 4px; text-align: center; font-size: 8px; border: 1px solid #2C3E50; }
table.data td { padding: 4px; border: 1px solid #ccc; font-size: 8px; vertical-align: top; }
table.data tr.even td { background: #f6f6f6; }
table.data tr.total td { background: #2C3E50; color: #fff; font-weight: bold; }
.c { text-align: center; }
.i { font-style: italic; color: #666; }
.note { font-size: 7.5px; color: #666; font-style: italic; margin-top: 4px; }
</style>
</head>
<body>

{{-- ══ KOP SURAT ══ --}}
@php
$bnspPath = public_path('images/bnsp.png');
$lspPath  = public_path('images/icon-lsp.png');
$bnspSrc  = file_exists($bnspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bnspPath)) : '';
$lspSrc   = file_exists($lspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($lspPath)) : '';
$total    = $data['total'];
$pct      = fn($n, $d) => $d > 0 ? number_format($n / $d * 100, 1, ',', '.') . '%' : '0%';
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

<h2>LAPORAN REKAP ASESI PER BATCH</h2>
<p class="sub">Periode Asesmen: {{ $periodeText }}</p>
@if($filterText)
<p class="sub">{{ $filterText }}</p>
@endif
<p class="sub">{{ $data['n_batch'] }} batch &middot; {{ $total['daftar'] }} asesi &middot; Kompeten {{ $total['kompeten'] }} ({{ $pct($total['kompeten'], $total['daftar']) }})</p>

<table class="data">
    <thead>
        <tr>
            <th style="width:3%">No</th>
            <th style="width:13%">Batch</th>
            <th style="width:17%">Asal Lembaga</th>
            <th style="width:15%">TUK</th>
            <th style="width:14%">Skema</th>
            <th style="width:10%">Tanggal Asesmen</th>
            <th style="width:5%">Daftar</th>
            <th style="width:5%">K</th>
            <th style="width:5%">BK</th>
            <th style="width:5%">Belum Ada Hasil</th>
            <th style="width:4%">% K</th>
            <th style="width:4%">Jml Asesor</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['rows'] as $r)
        <tr class="{{ $loop->even ? 'even' : '' }}">
            <td class="c">{{ $loop->iteration }}</td>
            <td class="{{ $r['is_mandiri'] ? 'i' : '' }}">{{ $r['label'] }}</td>
            <td>{{ $r['lembaga'] }}{{ $r['lembaga_src'] === 'asesi' && !$r['is_mandiri'] ? ' *' : '' }}</td>
            <td>{{ $r['tuk_full'] }}</td>
            <td>{{ $r['skema_full'] }}</td>
            <td>{{ $r['tanggal'] }}</td>
            <td class="c">{{ $r['daftar'] }}</td>
            <td class="c">{{ $r['kompeten'] }}</td>
            <td class="c">{{ $r['bk'] }}</td>
            <td class="c">{{ $r['belum'] }}</td>
            <td class="c">{{ $pct($r['kompeten'], $r['daftar']) }}</td>
            <td class="c">{{ $r['asesor'] }}</td>
        </tr>
        @empty
        <tr><td colspan="12" class="c">Tidak ada data</td></tr>
        @endforelse
        <tr class="total">
            <td></td>
            <td colspan="5">TOTAL</td>
            <td class="c">{{ $total['daftar'] }}</td>
            <td class="c">{{ $total['kompeten'] }}</td>
            <td class="c">{{ $total['bk'] }}</td>
            <td class="c">{{ $total['belum'] }}</td>
            <td class="c">{{ $pct($total['kompeten'], $total['daftar']) }}</td>
            <td class="c">{{ $total['asesor'] }}**</td>
        </tr>
    </tbody>
</table>
<p class="note">* Asal lembaga diambil dari isian asesi karena nama lembaga di jadwal belum diisi. &nbsp;
    ** Total jumlah asesor = asesor berbeda secara keseluruhan, bukan penjumlahan kolom.</p>
<p class="note" style="color:#888; font-style:normal;">Dicetak {{ now()->translatedFormat('d F Y H:i') }} — data akun simulasi/dummy tidak disertakan.</p>

</body>
</html>
