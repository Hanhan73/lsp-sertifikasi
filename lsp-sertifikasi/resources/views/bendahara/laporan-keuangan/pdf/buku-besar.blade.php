<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',sans-serif;font-size:9px;color:#333;padding:16px}
h2{font-size:12px;text-align:center;margin-bottom:2px}
.sub{text-align:center;color:#666;font-size:8px;margin-bottom:4px}
.akun-info{text-align:center;margin-bottom:12px;font-size:9px}
table{width:100%;border-collapse:collapse}
th,td{padding:4px 6px;border:1px solid #ddd;font-size:8.5px}
thead th{background:#2C3E50;color:#fff;font-weight:bold;text-align:center}
tr:nth-child(even) td{background:#f9f9f9}
.text-right{text-align:right}
.green{color:#27AE60;font-weight:bold}
.red{color:#C0392B;font-weight:bold}
.total-row td{background:#2C3E50;color:#fff;font-weight:bold}
</style>
</head>
<body>
<h2>LSP-KAP — BUKU BESAR</h2>
<p class="sub">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }}</p>
<p class="akun-info">
    <strong>{{ $selectedAkun->kode }} — {{ $selectedAkun->nama }}</strong>
    &nbsp;|&nbsp; Tipe: {{ $selectedAkun->tipe_label }}
    &nbsp;|&nbsp; Saldo Normal: {{ in_array($selectedAkun->tipe,['aset','beban']) ? 'Debit' : 'Kredit' }}
    &nbsp;|&nbsp; Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
</p>

<table>
    <thead>
        <tr>
            <th style="width:13%">No. Jurnal</th>
            <th style="width:10%">Tanggal</th>
            <th>Keterangan</th>
            <th style="width:13%">Debit (Rp)</th>
            <th style="width:13%">Kredit (Rp)</th>
            <th style="width:13%">Saldo (Rp)</th>
        </tr>
    </thead>
    <tbody>
    @forelse($entries as $e)
    <tr>
        <td>{{ $e['nomor'] }}</td>
        <td class="text-center">{{ $e['tanggal'] }}</td>
        <td>{{ $e['keterangan'] }}</td>
        <td class="text-right green">{{ $e['debit']>0 ? number_format($e['debit'],0,',','.') : '-' }}</td>
        <td class="text-right red">{{ $e['kredit']>0 ? number_format($e['kredit'],0,',','.') : '-' }}</td>
        <td class="text-right {{ $e['saldo']<0 ? 'red' : '' }}">{{ number_format($e['saldo'],0,',','.') }}</td>
    </tr>
    @empty
    <tr><td colspan="6" style="text-align:center;color:#999">Tidak ada transaksi untuk akun ini.</td></tr>
    @endforelse
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="3" class="text-right">TOTAL SALDO AKHIR</td>
            <td class="text-right">{{ number_format($totalDebit,0,',','.') }}</td>
            <td class="text-right">{{ number_format($totalKredit,0,',','.') }}</td>
            <td class="text-right">{{ number_format($saldoAkhir,0,',','.') }}</td>
        </tr>
    </tfoot>
</table>
</body>
</html>