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
.sub-header td { background:#d6eaf8; font-weight:bold; }
.total-row td { background:#f0f0f0; font-weight:bold; }
.grand-total td { background:#1a5276; color:#fff; font-weight:bold; font-size:11px; }
.text-right { text-align:right; }
.green { color:#27AE60; font-weight:bold; }
.red   { color:#C0392B; font-weight:bold; }
.indent { padding-left:24px; }
</style>
</head>
<body>

<h2>LSP-KAP</h2>
<h2>LAPORAN ARUS KAS</h2>
<p class="sub">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }} &nbsp;|&nbsp; Dicetak {{ now()->translatedFormat('d F Y') }}</p>

<table>
    {{-- Aktivitas Operasi --}}
    <tr class="section-header">
        <td colspan="2">AKTIVITAS OPERASI</td>
    </tr>

    <tr class="sub-header">
        <td colspan="2">Penerimaan</td>
    </tr>
    <tr>
        <td class="indent">Penerimaan Pembayaran Sertifikasi</td>
        <td class="text-right green">+ Rp {{ number_format($penerimaanSertifikasi,0,',','.') }}</td>
    </tr>

    <tr class="sub-header">
        <td colspan="2">Pengeluaran</td>
    </tr>
    <tr>
        <td class="indent">Pembayaran Honor Asesor</td>
        <td class="text-right red">− Rp {{ number_format($pembayaranHonor,0,',','.') }}</td>
    </tr>
    <tr>
        <td class="indent">Pembayaran Biaya Operasional</td>
        <td class="text-right red">− Rp {{ number_format($pembayaranOps,0,',','.') }}</td>
    </tr>
    @if($pembayaranDistr > 0)
    <tr>
        <td class="indent">Distribusi ke Yayasan</td>
        <td class="text-right red">− Rp {{ number_format($pembayaranDistr,0,',','.') }}</td>
    </tr>
    @endif

    <tr class="total-row">
        <td>Arus Kas Bersih dari Aktivitas Operasi</td>
        <td class="text-right {{ $kasOperasi >= 0 ? 'green' : 'red' }}">
            {{ $kasOperasi >= 0 ? '+' : '−' }} Rp {{ number_format(abs($kasOperasi),0,',','.') }}
        </td>
    </tr>

    {{-- Spacer --}}
    <tr><td colspan="2" style="border:0;height:10px;background:#fff"></td></tr>

    {{-- Saldo --}}
    <tr class="section-header">
        <td colspan="2">SALDO KAS DAN BANK</td>
    </tr>
    <tr>
        <td class="indent">Saldo Awal (Kas + Bank tahun {{ $tahun - 1 }})</td>
        <td class="text-right">Rp {{ number_format($kasAwal,0,',','.') }}</td>
    </tr>
    <tr>
        <td class="indent">Kenaikan / (Penurunan) Kas Bersih</td>
        <td class="text-right {{ $kasOperasi >= 0 ? 'green' : 'red' }}">
            {{ $kasOperasi >= 0 ? '+' : '' }} Rp {{ number_format($kasOperasi,0,',','.') }}
        </td>
    </tr>
    <tr class="grand-total">
        <td>Saldo Akhir Kas dan Bank per 31 Desember {{ $tahun }}</td>
        <td class="text-right {{ $kasAkhir >= 0 ? 'green' : 'red' }}">
            Rp {{ number_format($kasAkhir,0,',','.') }}
        </td>
    </tr>
</table>

<p style="font-size:8px;color:#888;text-align:center;margin-top:16px;">
    * Saldo awal diperoleh dari data kas dan bank tahun sebelumnya yang telah diinput manual.
</p>

</body>
</html>