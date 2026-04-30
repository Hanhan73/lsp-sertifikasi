<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#333; padding:24px; }
h2 { font-size:14px; text-align:center; margin-bottom:2px; }
.sub { text-align:center; color:#666; font-size:9px; margin-bottom:16px; }
table { width:100%; border-collapse:collapse; margin-bottom:12px; }
th,td { padding:5px 8px; border:1px solid #ddd; }
.section-header td { background:#2C3E50; color:#fff; font-weight:bold; }
.total-row td { background:#f0f0f0; font-weight:bold; }
.surplus-row td { background:#2C3E50; color:#fff; font-weight:bold; font-size:11px; }
.text-right { text-align:right; }
.green { color:#27AE60; } .red { color:#C0392B; }
.indent1 { padding-left:20px; }
.indent2 { padding-left:36px; color:#666; font-size:9px; }
</style>
</head>
<body>
<h2>LSP-KAP</h2>
<h2>LAPORAN LABA RUGI (SURPLUS/DEFISIT)</h2>
<p class="sub">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }} &nbsp;|&nbsp; Dicetak {{ now()->translatedFormat('d F Y') }}</p>

<table>
    <tr class="section-header"><td colspan="2">PENDAPATAN</td></tr>
    <tr>
        <td class="indent1">Pendapatan Sertifikasi Kompetensi</td>
        <td class="text-right green">Rp {{ number_format($summary['pendapatan'],0,',','.') }}</td>
    </tr>
    @foreach($pendapatanSkema as $s)
    <tr>
        <td class="indent2">— {{ $s->skema }}</td>
        <td class="text-right">Rp {{ number_format($s->total,0,',','.') }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
        <td>Total Pendapatan</td>
        <td class="text-right green">Rp {{ number_format($summary['pendapatan'],0,',','.') }}</td>
    </tr>

    <tr><td colspan="2" style="border:0;height:8px;background:#fff"></td></tr>

    <tr class="section-header"><td colspan="2">BEBAN</td></tr>
    <tr>
        <td class="indent1">Beban Honor Asesor</td>
        <td class="text-right red">Rp {{ number_format($summary['beban_honor'],0,',','.') }}</td>
    </tr>
    <tr>
        <td class="indent1">Beban Operasional</td>
        <td class="text-right red">Rp {{ number_format($summary['beban_ops'],0,',','.') }}</td>
    </tr>
    @foreach($bebanOpsDetail as $b)
    <tr>
        <td class="indent2">— {{ $b->uraian }} ({{ $b->nama_penerima }})</td>
        <td class="text-right">Rp {{ number_format($b->total,0,',','.') }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
        <td>Total Beban</td>
        <td class="text-right red">
            Rp {{ number_format($summary['beban_honor'] + $summary['beban_ops'],0,',','.') }}
        </td>
    </tr>

    <tr><td colspan="2" style="border:0;height:8px;background:#fff"></td></tr>

    <tr class="surplus-row">
        <td>{{ $summary['surplus'] >= 0 ? 'SURPLUS' : 'DEFISIT' }} TAHUN BERJALAN</td>
        <td class="text-right {{ $summary['surplus'] >= 0 ? 'green' : 'red' }}">
            Rp {{ number_format(abs($summary['surplus']),0,',','.') }}
        </td>
    </tr>
    @if($summary['distribusi'] > 0)
    <tr>
        <td class="indent1">Distribusi ke Yayasan</td>
        <td class="text-right red">
            (Rp {{ number_format($summary['distribusi'],0,',','.') }})
        </td>
    </tr>
    <tr class="total-row">
        <td>Surplus Setelah Distribusi</td>
        <td class="text-right {{ ($summary['surplus'] - $summary['distribusi']) >= 0 ? 'green' : 'red' }}">
            Rp {{ number_format($summary['surplus'] - $summary['distribusi'],0,',','.') }}
        </td>
    </tr>
    @endif
</table>
</body>
</html>