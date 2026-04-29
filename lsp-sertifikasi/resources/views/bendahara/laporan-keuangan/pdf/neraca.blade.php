{{-- pdf/neraca.blade.php --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',sans-serif;font-size:10px;color:#333;padding:20px}
h2{font-size:13px;text-align:center;margin-bottom:2px}
.sub{text-align:center;color:#666;font-size:9px;margin-bottom:14px}
table{width:100%;border-collapse:collapse}
th,td{padding:6px 8px;border:1px solid #ccc}
.h-aset{background:#1a5276;color:#fff;font-weight:bold;text-align:center}
.h-kewajiban{background:#1a5276;color:#fff;font-weight:bold;text-align:center}
.sub-h{background:#2e86c1;color:#fff;font-size:9px;text-align:center}
.val{text-align:right;color:#e67e22;font-weight:600}
.total-aset{background:#1a5276;color:#fff;font-weight:bold}
.total-val{background:#1a5276;color:#fff;font-weight:bold;text-align:right}
.ekuitas-bg{background:#fef9e7}
</style>
</head>
<body>
<h2>LSP-KAP</h2>
<h2>NERACA BENTUK T</h2>
<p class="sub">Per 31 Desember {{ $tahun }} &nbsp;|&nbsp; Dicetak {{ now()->translatedFormat('d F Y') }}</p>

<table>
<thead>
<tr>
    <th class="h-aset" style="width:25%">ASET</th>
    <th class="h-aset" style="width:25%">Rp</th>
    <th class="h-kewajiban" style="width:25%">KEWAJIBAN &amp; EKUITAS</th>
    <th class="h-kewajiban" style="width:25%">Rp</th>
</tr>
</thead>
<tbody>
<tr>
    <td>Kas</td><td class="val">{{ number_format($balance->kas,0,',','.') }}</td>
    <td>Utang Honor Asesor</td><td class="val">{{ number_format($balance->utang_honor,0,',','.') }}</td>
</tr>
<tr>
    <td>Bank</td><td class="val">{{ number_format($balance->bank,0,',','.') }}</td>
    <td>Utang Operasional</td><td class="val">{{ number_format($balance->utang_operasional,0,',','.') }}</td>
</tr>
<tr>
    <td>Piutang Asesi</td><td class="val">{{ number_format($balance->piutang_asesi,0,',','.') }}</td>
    <td>Hutang Distribusi Yayasan</td><td class="val">{{ number_format($balance->hutang_distribusi,0,',','.') }}</td>
</tr>
<tr>
    <td>Perlengkapan</td><td class="val">{{ number_format($balance->perlengkapan,0,',','.') }}</td>
    <td class="ekuitas-bg">Saldo Dana</td><td class="val ekuitas-bg">{{ number_format($balance->saldo_dana,0,',','.') }}</td>
</tr>
<tr>
    <td></td><td></td>
    <td class="ekuitas-bg">Surplus Tahun Berjalan</td>
    <td class="val ekuitas-bg">{{ number_format($balance->surplus - $balance->distribusi_yayasan,0,',','.') }}</td>
</tr>
<tr>
    <td class="total-aset">Total Aset</td>
    <td class="total-val">{{ number_format($balance->total_aset,0,',','.') }}</td>
    <td class="total-aset">Total Kewajiban + Ekuitas</td>
    <td class="total-val">{{ number_format($balance->total_kewajiban_ekuitas,0,',','.') }}</td>
</tr>
</tbody>
</table>
</body>
</html>