<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; padding: 20px; }
h2 { font-size: 14px; text-align: center; margin-bottom: 2px; }
.sub { text-align: center; color: #666; font-size: 9px; margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th { background: #2C3E50; color: #fff; padding: 6px 8px; text-align: center; font-size: 9px; }
td { padding: 5px 8px; border: 1px solid #ddd; font-size: 9px; }
tr:nth-child(even) td { background: #f9f9f9; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.total-row td { background: #2C3E50 !important; color: #fff; font-weight: bold; }
.green { color: #27AE60; font-weight: bold; }
.red   { color: #C0392B; font-weight: bold; }
.muted { color: #999; }
.summary { display: table; width: 100%; margin-bottom: 16px; }
.summary-item { display: table-cell; width: 25%; padding: 10px 8px; border-radius: 6px; text-align: center; }
.s1 { background: #d4edda; }
.s2 { background: #f8d7da; }
.s3 { background: #d1ecf1; }
.s4 { background: #fff3cd; }
.summary-item .label { font-size: 8px; color: #555; margin-bottom: 3px; }
.summary-item .val   { font-size: 13px; font-weight: bold; }
</style>
</head>
<body>

<h2>REKAP PENDAPATAN & KEUANGAN</h2>
<p class="sub">Tahun {{ $tahun }} &mdash; Dicetak {{ now()->translatedFormat('d F Y') }}</p>

{{-- Summary --}}
<div class="summary">
    <div class="summary-item s1">
        <div class="label">Total Pemasukan</div>
        <div class="val green">Rp {{ number_format($totalPemasukan,0,',','.') }}</div>
    </div>
    <div class="summary-item s2">
        <div class="label">Honor Asesor</div>
        <div class="val red">Rp {{ number_format($totalHonor,0,',','.') }}</div>
    </div>
    <div class="summary-item s3">
        <div class="label">Biaya Operasional</div>
        <div class="val" style="color:#0c5460">Rp {{ number_format($totalBiayaOps,0,',','.') }}</div>
    </div>
    <div class="summary-item s4">
        <div class="label">Saldo Bersih</div>
        <div class="val {{ $totalSaldo >= 0 ? 'green' : 'red' }}">Rp {{ number_format($totalSaldo,0,',','.') }}</div>
    </div>
</div>

{{-- Tabel per bulan --}}
<table>
    <thead>
        <tr>
            <th style="width:12%">Bulan</th>
            <th style="width:20%">Pemasukan (Rp)</th>
            <th style="width:18%">Honor Asesor (Rp)</th>
            <th style="width:18%">Biaya Ops (Rp)</th>
            <th style="width:16%">Total Keluar (Rp)</th>
            <th style="width:16%">Saldo Bersih (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bulanLabels as $i => $bln)
        @php
            $in  = $dataPemasukan[$i];
            $hon = $dataHonor[$i];
            $ops = $dataBiayaOps[$i];
            $sal = $dataSaldo[$i];
            $empty = $in == 0 && $hon == 0 && $ops == 0;
        @endphp
        <tr class="{{ $empty ? 'muted' : '' }}">
            <td>{{ $bln }}</td>
            <td class="text-right {{ $in > 0 ? 'green' : '' }}">
                {{ $in > 0 ? number_format($in,0,',','.') : '-' }}
            </td>
            <td class="text-right {{ $hon > 0 ? 'red' : '' }}">
                {{ $hon > 0 ? number_format($hon,0,',','.') : '-' }}
            </td>
            <td class="text-right">
                {{ $ops > 0 ? number_format($ops,0,',','.') : '-' }}
            </td>
            <td class="text-right">
                {{ ($hon+$ops) > 0 ? number_format($hon+$ops,0,',','.') : '-' }}
            </td>
            <td class="text-right {{ $sal >= 0 ? 'green' : 'red' }}">
                {{ number_format($sal,0,',','.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td><strong>TOTAL</strong></td>
            <td class="text-right">{{ number_format($totalPemasukan,0,',','.') }}</td>
            <td class="text-right">{{ number_format($totalHonor,0,',','.') }}</td>
            <td class="text-right">{{ number_format($totalBiayaOps,0,',','.') }}</td>
            <td class="text-right">{{ number_format($totalHonor+$totalBiayaOps,0,',','.') }}</td>
            <td class="text-right">{{ number_format($totalSaldo,0,',','.') }}</td>
        </tr>
    </tfoot>
</table>

</body>
</html>