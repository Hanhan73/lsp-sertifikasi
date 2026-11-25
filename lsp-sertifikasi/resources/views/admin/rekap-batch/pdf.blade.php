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
table.data tr.even td { background: #f6f6f6; }
table.data tr.grp td { background: #DCE6F1; font-weight: bold; }
table.data tr.total td { background: #2C3E50; color: #fff; font-weight: bold; }
.c { text-align: center; }
.muted { color: #555; font-weight: normal; }
.note { font-size: 7.5px; color: #666; font-style: italic; margin-top: 3px; }
.page-break { page-break-before: always; }
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

{{-- Ringkasan --}}
<table class="data" style="margin-top:10px;">
    <tr>
        <th>Jumlah Batch</th><th>Batch Selesai</th><th>Batch Masih Proses</th><th>Asesi dalam Batch</th><th>Asesi Mandiri (tidak termasuk)</th>
    </tr>
    <tr>
        <td class="c">{{ $total['n_batch'] }}</td>
        <td class="c">{{ $total['selesai'] }} ({{ $pct($total['selesai'], $total['n_batch']) }})</td>
        <td class="c">{{ $total['proses'] }} ({{ $pct($total['proses'], $total['n_batch']) }})</td>
        <td class="c">{{ $total['asesi'] }}</td>
        <td class="c">{{ $total['mandiri'] }}</td>
    </tr>
</table>

{{-- ── Tabel ringkasan per bagian ── --}}
@foreach($tabs as $key => $tab)
@php $isAsesor = $key === 'asesor'; @endphp
<h3>{{ count($tabs) > 1 ? $loop->iteration . '. ' : '' }}Rekap Batch {{ $tab['title'] }}</h3>
<table class="data">
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th>{{ $tab['label'] }}</th>
            <th style="width:10%">Jumlah Batch</th>
            <th style="width:10%">Batch Selesai</th>
            <th style="width:10%">Batch Masih Proses</th>
            <th style="width:9%">% Selesai</th>
            <th style="width:10%">Jumlah Asesi</th>
            @unless($isAsesor)<th style="width:10%">Jumlah Asesor</th>@endunless
        </tr>
    </thead>
    <tbody>
        @forelse($rekap['rows'][$key] as $r)
        <tr class="{{ $loop->even ? 'even' : '' }}">
            <td class="c">{{ $loop->iteration }}</td>
            <td>{{ $r['label'] }}</td>
            <td class="c">{{ $r['n_batch'] }}</td>
            <td class="c">{{ $r['selesai'] }}</td>
            <td class="c">{{ $r['proses'] }}</td>
            <td class="c">{{ $pct($r['selesai'], $r['n_batch']) }}</td>
            <td class="c">{{ $r['asesi'] }}</td>
            @unless($isAsesor)<td class="c">{{ $r['asesor'] }}</td>@endunless
        </tr>
        @empty
        <tr><td colspan="{{ $isAsesor ? 7 : 8 }}" class="c">Tidak ada data</td></tr>
        @endforelse
        <tr class="total">
            <td></td>
            <td>TOTAL</td>
            <td class="c">{{ $total['n_batch'] }}*</td>
            <td class="c">{{ $total['selesai'] }}*</td>
            <td class="c">{{ $total['proses'] }}*</td>
            <td class="c">{{ $pct($total['selesai'], $total['n_batch']) }}</td>
            <td class="c">{{ $total['asesi'] }}</td>
            @unless($isAsesor)<td class="c">{{ $total['asesor'] }}*</td>@endunless
        </tr>
    </tbody>
</table>
<p class="note">* Total = jumlah berbeda secara keseluruhan, bukan penjumlahan kolom. Batch Selesai = semua asesi di batch sudah punya hasil K/BK.</p>
@endforeach

{{-- ── Lampiran: daftar batch per kelompok ── --}}
@foreach($tabs as $key => $tab)
@php $cols = \App\Http\Controllers\Admin\RekapBatchController::columnsFor($key); $nText = count($cols) + 1; @endphp
<div class="page-break"></div>
<h3>Lampiran{{ count($tabs) > 1 ? ' ' . $loop->iteration : '' }}: Daftar Batch {{ $tab['title'] }}</h3>
<table class="data">
    <thead>
        <tr>
            <th style="width:3%">No</th>
            @foreach($cols as $label)
            <th>{{ $label }}</th>
            @endforeach
            <th style="width:5%">Asesi</th>
            <th style="width:4%">K</th>
            <th style="width:4%">BK</th>
            <th style="width:5%">Belum</th>
            <th style="width:5%">% K</th>
            <th style="width:7%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rekap['rows'][$key] as $g)
        <tr class="grp">
            <td colspan="{{ $nText + 6 }}">
                {{ $g['label'] }}
                <span class="muted">&mdash; {{ $g['n_batch'] }} batch ({{ $g['selesai'] }} selesai, {{ $g['proses'] }} masih proses)</span>
            </td>
        </tr>
        @foreach(($lampiran[$key][$g['key']] ?? collect())->values() as $b)
        <tr>
            <td class="c">{{ $loop->iteration }}</td>
            @foreach(array_keys($cols) as $c)
            @switch($c)
                @case('batch')<td>{{ $b['batch_id'] }}</td>@break
                @case('lembaga')<td>{{ $b['lembaga'] }}{{ $b['lembaga_src'] === 'asesi' ? ' *' : '' }}</td>@break
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
            <td>{{ $b['selesai'] ? 'Selesai' : 'Masih proses' }}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>
<p class="note">* Asal lembaga diambil dari isian asesi karena nama lembaga di jadwal belum diisi.
    Batch yang punya beberapa jadwal dapat muncul di lebih dari satu kelompok dengan jumlah asesi bagiannya saja.</p>
@endforeach

<p class="note" style="color:#888; font-style:normal;">Dicetak {{ now()->translatedFormat('d F Y H:i') }} — data akun simulasi/dummy tidak disertakan.</p>

</body>
</html>
