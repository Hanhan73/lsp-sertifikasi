<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; color: #222; padding: 20px 26px; }
.kop-garis { border-top: 3pt solid #000; border-bottom: 1pt solid #000; height: 4pt; margin: 6px 0 10px; }
h2 { font-size: 13px; text-align: center; margin-bottom: 2px; }
h3 { font-size: 10.5px; margin: 14px 0 5px; }
.sub { text-align: center; color: #555; font-size: 9px; margin-bottom: 2px; }
table.data { width: 100%; border-collapse: collapse; }
table.data th { background: #2C3E50; color: #fff; padding: 5px 4px; text-align: center; font-size: 8px; border: 1px solid #2C3E50; }
table.data td { padding: 3px 4px; border: 1px solid #ccc; font-size: 8px; vertical-align: top; }
table.data tr.grp td { background: #DCE6F1; font-weight: bold; }
table.data tr.total td { background: #2C3E50; color: #fff; font-weight: bold; }
.c { text-align: center; }
.i { font-style: italic; color: #666; }
.muted { color: #555; font-weight: normal; }
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

<h2>LAPORAN REKAP PER BATCH{{ count($tabs) === 1 ? ' — ' . strtoupper(reset($tabs)['title']) : '' }}</h2>
<p class="sub">Periode Asesmen: {{ $periodeText }}</p>
@if($filterText)
<p class="sub">{{ $filterText }}</p>
@endif
<p class="sub">{{ $total['n_batch'] }} batch &middot; {{ $total['daftar'] }} asesi ({{ $total['mandiri'] }} mandiri) &middot; Kompeten {{ $total['kompeten'] }} ({{ $pct($total['kompeten'], $total['daftar']) }})</p>

@foreach($tabs as $key => $tab)
@php $cols = \App\Http\Controllers\Admin\RekapBatchController::columnsFor($key); $nText = count($cols) + 1; @endphp
<h3>{{ count($tabs) > 1 ? $loop->iteration . '. ' : '' }}Rekap {{ $tab['title'] }}</h3>
<table class="data">
    <thead>
        <tr>
            <th style="width:3%">No</th>
            @foreach($cols as $label)
            <th>{{ $label }}</th>
            @endforeach
            <th style="width:5%">Daftar</th>
            <th style="width:5%">K</th>
            <th style="width:5%">BK</th>
            <th style="width:6%">Belum Ada Hasil</th>
            <th style="width:5%">% K</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['tabs'][$key] as $g)
        <tr class="grp">
            <td colspan="{{ $nText }}">
                {{ $g['label'] }}
                <span class="muted">&mdash; {{ $g['n_batch'] ? $g['n_batch'] . ' batch' : 'hanya asesi mandiri' }}{{ $g['asesor'] !== null ? ', ' . $g['asesor'] . ' asesor' : '' }}</span>
            </td>
            <td class="c">{{ $g['daftar'] }}</td>
            <td class="c">{{ $g['kompeten'] }}</td>
            <td class="c">{{ $g['bk'] }}</td>
            <td class="c">{{ $g['belum'] }}</td>
            <td class="c">{{ $pct($g['kompeten'], $g['daftar']) }}</td>
        </tr>
        @foreach($g['batches'] as $b)
        <tr>
            <td class="c">{{ $loop->iteration }}</td>
            @foreach(array_keys($cols) as $c)
            @switch($c)
                @case('batch')<td class="{{ $b['is_mandiri'] ? 'i' : '' }}">{{ $b['label'] }}</td>@break
                @case('lembaga')<td>{{ $b['lembaga'] }}{{ $b['lembaga_src'] === 'asesi' && !$b['is_mandiri'] ? ' *' : '' }}</td>@break
                @case('tuk')<td>{{ $b['tuk_full'] }}</td>@break
                @case('skema')<td>{{ $b['skema_full'] }}</td>@break
                @case('asesor')<td>{{ $b['asesor_full'] }}</td>@break
                @case('tanggal')<td>{{ $b['tanggal'] }}</td>@break
            @endswitch
            @endforeach
            <td class="c">{{ $b['daftar'] }}</td>
            <td class="c">{{ $b['kompeten'] }}</td>
            <td class="c">{{ $b['bk'] }}</td>
            <td class="c">{{ $b['belum'] }}</td>
            <td class="c">{{ $pct($b['kompeten'], $b['daftar']) }}</td>
        </tr>
        @endforeach
        @empty
        <tr><td colspan="{{ $nText + 5 }}" class="c">Tidak ada data</td></tr>
        @endforelse
        <tr class="total">
            <td colspan="{{ $nText }}">TOTAL &mdash; {{ $total['n_batch'] }} batch</td>
            <td class="c">{{ $total['daftar'] }}</td>
            <td class="c">{{ $total['kompeten'] }}</td>
            <td class="c">{{ $total['bk'] }}</td>
            <td class="c">{{ $total['belum'] }}</td>
            <td class="c">{{ $pct($total['kompeten'], $total['daftar']) }}</td>
        </tr>
    </tbody>
</table>
@endforeach

<p class="note">* Asal lembaga diambil dari isian asesi karena nama lembaga di jadwal belum diisi.
    Batch yang punya beberapa jadwal dapat muncul di lebih dari satu kelompok dengan jumlah bagiannya saja.</p>
<p class="note" style="color:#888; font-style:normal;">Dicetak {{ now()->translatedFormat('d F Y H:i') }} — data akun simulasi/dummy tidak disertakan.</p>

</body>
</html>
