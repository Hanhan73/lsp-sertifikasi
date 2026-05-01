<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#333; padding:24px; }
h2 { font-size:13px; text-align:center; margin-bottom:2px; }
.sub { text-align:center; color:#666; font-size:9px; margin-bottom:16px; }
table { width:100%; border-collapse:collapse; margin-bottom:12px; }
th, td { padding:6px 10px; border:1px solid #ddd; }
.section-header td { background:#1a5276; color:#fff; font-weight:bold; }
.plus-row td { }
.minus-row td { }
.total-row td { background:#f7dc6f; font-weight:bold; font-size:11px; }
.text-right { text-align:right; }
.green { color:#27AE60; font-weight:bold; }
.red   { color:#C0392B; font-weight:bold; }
.indent { padding-left:24px; }
</style>
</head>
<body>

<h2>LSP-KAP</h2>
<h2>LAPORAN PERUBAHAN MODAL (EKUITAS)</h2>
<p class="sub">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }} &nbsp;|&nbsp; Dicetak {{ now()->translatedFormat('d F Y') }}</p>

<table>
    <tr class="section-header">
        <td colspan="2">SALDO DANA (MODAL)</td>
    </tr>
    <tr>
        <td>Saldo Dana Awal (1 Januari {{ $tahun }})</td>
        <td class="text-right">Rp {{ number_format($saldoAwal,0,',','.') }}</td>
    </tr>
    <tr class="plus-row">
        <td class="indent">Ditambah: Surplus Tahun {{ $tahun }}</td>
        <td class="text-right green">+ Rp {{ number_format($surplus,0,',','.') }}</td>
    </tr>
    @if($distribusi > 0)
    <tr class="minus-row">
        <td class="indent">Dikurangi: Distribusi ke Yayasan</td>
        <td class="text-right red">− Rp {{ number_format($distribusi,0,',','.') }}</td>
    </tr>
    @endif
    <tr class="total-row">
        <td>Saldo Dana Akhir (31 Desember {{ $tahun }})</td>
        <td class="text-right">Rp {{ number_format($saldoAkhir,0,',','.') }}</td>
    </tr>
</table>

{{-- Ringkasan --}}
<table style="margin-top:16px;">
    <tr class="section-header">
        <td colspan="2">RINGKASAN EKUITAS</td>
    </tr>
    <tr>
        <td>Saldo Dana</td>
        <td class="text-right">Rp {{ number_format($balance->saldo_dana,0,',','.') }}</td>
    </tr>
    <tr>
        <td>Surplus Tahun Berjalan</td>
        <td class="text-right {{ $surplus >= 0 ? 'green' : 'red' }}">
            Rp {{ number_format($surplus,0,',','.') }}
        </td>
    </tr>
    @if($distribusi > 0)
    <tr>
        <td>Dikurangi: Distribusi ke Yayasan</td>
        <td class="text-right red">(Rp {{ number_format($distribusi,0,',','.') }})</td>
    </tr>
    @endif
    <tr class="total-row">
        <td><strong>Total Ekuitas</strong></td>
        <td class="text-right"><strong>Rp {{ number_format($saldoAkhir,0,',','.') }}</strong></td>
    </tr>
</table>

</body>
</html>