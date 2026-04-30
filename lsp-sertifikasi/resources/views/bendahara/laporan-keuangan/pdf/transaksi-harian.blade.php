<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',sans-serif;font-size:9px;color:#333;padding:16px}
h2{font-size:12px;text-align:center;margin-bottom:2px}
.sub{text-align:center;color:#666;font-size:8px;margin-bottom:12px}
table{width:100%;border-collapse:collapse}
th,td{padding:4px 6px;border:1px solid #ddd;font-size:8px}
thead th{background:#2C3E50;color:#fff;font-weight:bold;text-align:center}
tr:nth-child(even) td{background:#f9f9f9}
.text-right{text-align:right}
.green{color:#27AE60;font-weight:bold}
.red{color:#C0392B;font-weight:bold}
.total-row td{background:#2C3E50;color:#fff;font-weight:bold}
.badge-pemasukan{background:#d4edda;color:#155724;padding:1px 4px;border-radius:3px}
.badge-honor{background:#f8d7da;color:#721c24;padding:1px 4px;border-radius:3px}
.badge-ops{background:#d1ecf1;color:#0c5460;padding:1px 4px;border-radius:3px}
</style>
</head>
<body>
<h2>LSP-KAP — JURNAL TRANSAKSI HARIAN</h2>
<p class="sub">
    Tanggal: {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
    &nbsp;|&nbsp; Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
</p>

<table>
    <thead>
        <tr>
            <th style="width:5%">Waktu</th>
            <th style="width:12%">No. Jurnal</th>
            <th style="width:30%">Keterangan</th>
            <th style="width:8%">Tipe</th>
            <th style="width:15%">Akun Debit</th>
            <th style="width:15%">Akun Kredit</th>
            <th style="width:7%">Debit</th>
            <th style="width:8%">Kredit</th>
        </tr>
    </thead>
    <tbody>
    @forelse($transaksi as $t)
    <tr>
        <td class="text-center">{{ $t['waktu'] }}</td>
        <td>{{ $t['nomor'] }}</td>
        <td>{{ $t['keterangan'] }}</td>
        <td class="text-center">
            @if($t['tipe']==='pemasukan')
            <span class="badge-pemasukan">Masuk</span>
            @elseif($t['tipe']==='honor')
            <span class="badge-honor">Honor</span>
            @else
            <span class="badge-ops">Ops</span>
            @endif
        </td>
        <td>{{ $t['akun_debit'] }}</td>
        <td>{{ $t['akun_kredit'] }}</td>
        <td class="text-right green">{{ $t['debit']>0 ? number_format($t['debit'],0,',','.') : '-' }}</td>
        <td class="text-right red">{{ $t['kredit']>0 ? number_format($t['kredit'],0,',','.') : '-' }}</td>
    </tr>
    @empty
    <tr><td colspan="8" style="text-align:center;color:#999">Tidak ada transaksi</td></tr>
    @endforelse
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="6" class="text-right">TOTAL</td>
            <td class="text-right">{{ number_format($totalDebit,0,',','.') }}</td>
            <td class="text-right">{{ number_format($totalKredit,0,',','.') }}</td>
        </tr>
    </tfoot>
</table>
</body>
</html>