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
.val{text-align:right;color:#e67e22;font-weight:600}
.total-aset{background:#1a5276;color:#fff;font-weight:bold}
.total-val{background:#1a5276;color:#fff;font-weight:bold;text-align:right}
.ekuitas-bg{background:#fef9e7}
.piutang-bg{background:#eaf4fb}
.sub-item{font-size:8.5px;color:#555;padding-left:10px}
.text-muted-sm{color:#888;font-size:8px}
.text-danger{color:#C0392B}
</style>
</head>
<body>
<h2>LEMBAGA SERTIFIKASI PROFESI (LSP) ADMINISTRASI PERKANTORAN</h2>
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

{{-- Kas | Utang Honor --}}
<tr>
    <td>Kas</td>
    <td class="val">{{ number_format($balance->kas,0,',','.') }}</td>
    <td>Utang Honor Asesor</td>
    <td class="val">{{ number_format($utangHonor,0,',','.') }}</td>
</tr>

{{-- Bank | Utang Operasional --}}
<tr>
    <td>Bank</td>
    <td class="val">{{ number_format($bank,0,',','.') }}</td>
    <td>Utang Operasional</td>
    <td class="val">{{ number_format($balance->utang_operasional,0,',','.') }}</td>
</tr>

{{-- Piutang Asesi | Saldo Dana --}}
<tr>
    <td>Piutang Asesi</td>
    <td class="val">{{ number_format($piutangAsesi,0,',','.') }}</td>
    <td class="ekuitas-bg">Saldo Dana</td>
    <td class="val ekuitas-bg">{{ number_format($balance->saldo_dana,0,',','.') }}</td>
</tr>

{{-- Piutang Lainnya — selalu tampil | Surplus --}}
@php $adaPiutangLainnya = isset($piutangLainnya) && $piutangLainnya->count() > 0; @endphp
<tr class="{{ $adaPiutangLainnya ? 'piutang-bg' : '' }}">
    <td>
        Piutang Lainnya
        @if($adaPiutangLainnya)
        <br>
        @foreach($piutangLainnya as $pr)
        <span class="sub-item">↳ {{ $pr->nama_pihak }}: {{ $pr->uraian }}
            ({{ number_format($pr->jumlah,0,',','.') }})</span><br>
        @endforeach
        @else
        <br><span class="text-muted-sm">Tidak ada</span>
        @endif
    </td>
    <td class="val {{ $adaPiutangLainnya ? 'piutang-bg' : '' }}">
        {{ number_format($totalPiutangLainnya ?? 0,0,',','.') }}
    </td>
    <td class="ekuitas-bg">
        Surplus Tahun Berjalan
        @if($summary['distribusi'] > 0)
        <br><span class="text-muted-sm">setelah distribusi Rp {{ number_format($summary['distribusi'],0,',','.') }}</span>
        @endif
    </td>
    <td class="val ekuitas-bg {{ ($surplus - $summary['distribusi']) < 0 ? 'text-danger' : '' }}">
        {{ number_format($surplus - $summary['distribusi'],0,',','.') }}
    </td>
</tr>

{{-- Perlengkapan | kosong --}}
<tr>
    <td>Perlengkapan</td>
    <td class="val">{{ number_format($balance->perlengkapan,0,',','.') }}</td>
    <td class="ekuitas-bg"></td>
    <td class="ekuitas-bg"></td>
</tr>

{{-- Total --}}
<tr>
    <td class="total-aset">Total Aset</td>
    <td class="total-val">{{ number_format($totalAset,0,',','.') }}</td>
    <td class="total-aset">Total Kewajiban + Ekuitas</td>
    <td class="total-val">{{ number_format($totalKewEkuitas,0,',','.') }}</td>
</tr>

</tbody>
</table>
</body>
</html>