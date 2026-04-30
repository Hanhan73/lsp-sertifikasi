<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kwitansi {{ $invoice->invoice_number }}</title>
<style>
@page { size: A4 landscape; margin: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 11pt;
    color: #000;
    padding: 28px 36px;
}

.page      { page-break-after: always; }
.page-last { page-break-after: auto; }

/* ── Frame kwitansi ──────────────────────────────────────────── */
.kwitansi-frame {
    border: 2px solid #000;
    padding: 20px 24px;
}

/* ── Layout utama: logo | konten ─────────────────────────────── */
.main-table { width: 100%; border-collapse: collapse; }
.col-logo {
    width: 90px;
    vertical-align: top;
    padding-top: 2px;
    padding-right: 18px;
}
.col-logo img {
    width: 130px;
    height: 130px;
    object-fit: contain;
}
.col-content { vertical-align: top; }

/* ── Baris data ──────────────────────────────────────────────── */
.body-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
.body-table td { padding: 4px 3px; vertical-align: top; font-size: 11pt; }
.col-label  { width: 148px; }
.col-colon  { width: 12px; }

/* ── Terbilang box ───────────────────────────────────────────── */
.terbilang-box {
    display: inline-block;
    border: 1.5px solid #555;
    background-color: #d7e7f1;
    padding: 3px 10px;
    font-style: italic;
    font-weight: bold;
    font-size: 11pt;
}

/* ── Item list ───────────────────────────────────────────────── */
.detail-list { width: 100%; border-collapse: collapse; margin: 5px 0 3px 0; }
.detail-list td { padding: 2px 3px; font-size: 11pt; vertical-align: top; }
.detail-list .no-col { width: 22px; }

/* ── Footer TTD ──────────────────────────────────────────────── */
.footer-table { width: 100%; border-collapse: collapse; margin-top: 28px; }
.footer-table td { vertical-align: top; }
.col-jumlah { width: 46%; padding-top: 10px; }
.col-ttd    { width: 54%; text-align: center; }

.jumlah-label { font-style: italic; font-size: 11pt; margin-bottom: 5px; }
.jumlah-box {
    display: inline-block;
    border: 2px solid #000;
    background-color: #d7e7f1;
    padding: 5px 16px;
    font-weight: bold;
    font-size: 12.5pt;
    min-width: 150px;
    text-align: center;
}

.ttd-garis {
    border-bottom: 1px solid #000;
    width: 200px;
    margin: 0 auto 4px;
}

/* ── Halaman 2 ───────────────────────────────────────────────── */
.bukti-judul { font-size: 13pt; font-weight: bold; margin-bottom: 16px; }
</style>
</head>
<body>

@php
\Carbon\Carbon::setLocale('id');

$kopPath = public_path('images/icon-lsp.png');
$kopSrc  = file_exists($kopPath) ? $kopPath : null;

// TTD + stempel hanya untuk versi berisi
$ttdSrc     = null;
$stempelSrc = null;
if ($versi === 'berisi') {
    $ttdPath = storage_path('app/private/mankeu/ttd.png');
    if (file_exists($ttdPath)) {
        $ttdSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($ttdPath));
    }
    $stempelPath = storage_path('app/private/mankeu/stempel.png');
    if (file_exists($stempelPath)) {
        $stempelSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath));
    }
}

// Nominal & tanggal
if ($collectivePayment) {
    $nominal      = $collectivePayment->amount;
    $angsuranInfo = ' (Angsuran ke-' . $collectivePayment->installment_number . ')';
    $tanggal      = $collectivePayment->verified_at ?? $collectivePayment->updated_at ?? now();
} else {
    $nominal      = $invoice->total_amount;
    $angsuranInfo = '';
    $tanggal      = $invoice->issued_at ?? now();
}

$terbilang = ucwords(\App\Helpers\TerbilangHelper::convert((int) $nominal)) . ' Rupiah';
$adaBukti  = $collectivePayment && $collectivePayment->proof_path;
@endphp

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- HALAMAN 1 — Kwitansi                                       --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="{{ $adaBukti ? 'page' : 'page-last' }}">
<div class="kwitansi-frame">
<table class="main-table">
<tr>

    {{-- Logo --}}
    <td class="col-logo">
        @if($kopSrc)
        <img src="{{ $kopSrc }}" alt="Logo LSP KAP">
        @endif
    </td>

    {{-- Konten --}}
    <td class="col-content">
        <table class="body-table">
            <tr>
                <td class="col-label">No.</td>
                <td class="col-colon">:</td>
                <td>{{ $invoice->invoice_number }}{{ $angsuranInfo }}</td>
            </tr>
            <tr>
                <td class="col-label">Telah diterima dari</td>
                <td class="col-colon">:</td>
                <td><strong>{{ $invoice->tuk->name ?? $invoice->recipient_name }}</strong></td>
            </tr>
            <tr>
                <td class="col-label" style="padding-top:5px;">Uang sejumlah</td>
                <td class="col-colon" style="padding-top:5px;">:</td>
                <td style="padding-top:5px;">
                    <span class="terbilang-box">{{ $terbilang }}</span>
                </td>
            </tr>
            <tr>
                <td class="col-label" style="padding-top:8px;">Untuk pembayaran</td>
                <td class="col-colon" style="padding-top:8px;">:</td>
                <td style="padding-top:8px;">
                    @if($invoice->notes_kwitansi)
                    <div style="margin-bottom:6px;">{{ $invoice->notes_kwitansi }}</div>
                    @endif
                    <table class="detail-list">
                        @foreach($invoice->items as $idx => $item)
                        <tr>
                            <td class="no-col">{{ $idx + 1 }}.</td>
                            <td>
                                {{ $item['skema_name'] }},
                                sebanyak {{ $item['jumlah'] }} orang
                                @Rp. {{ number_format($item['harga_satuan'], 0, ',', '.') }}
                                = Rp. {{ number_format($item['subtotal'], 0, ',', '.') }},-
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>

        {{-- Footer: Jumlah + TTD --}}
        <table class="footer-table">
            <tr>
                <td class="col-jumlah">
                    <div class="jumlah-label"><em>Jumlah :</em></div>
                    <div class="jumlah-box">Rp {{ number_format($nominal, 0, ',', '.') }}</div>
                </td>
                <td class="col-ttd">
                    <div style="font-size:11pt;margin-bottom:3px;">
                        Jakarta, {{ $tanggal->translatedFormat('d F Y') }}
                    </div>
                    <div style="font-size:11pt;margin-bottom:2px;">Penerima</div>

                    {{-- TTD + Stempel --}}
                    <div style="height:72px;text-align:center;position:relative;">
                        @if($ttdSrc)
                        <img src="{{ $ttdSrc }}"
                             style="max-height:68px;max-width:160px;position:absolute;left:50%;transform:translateX(-50%);">
                        @endif
                        @if($stempelSrc)
                        <img src="{{ $stempelSrc }}"
                             style="max-height:60px;max-width:60px;position:absolute;left:55%;top:4px;">
                        @endif
                    </div>

                    <div style="font-size:11pt;font-weight:bold;">Dr. Marsofiyati, S.Pd., M.Pd.</div>
                    <div style="font-size:10pt;">Manajer Keuangan</div>
                </td>
            </tr>
        </table>
    </td>

</tr>
</table>
</div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- HALAMAN 2 — Bukti Transfer (kiri) + TTD (kanan)           --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($adaBukti)
@php
$proofPath = storage_path('app/private/' . $collectivePayment->proof_path);
$proofExt  = strtolower(pathinfo($collectivePayment->proof_path, PATHINFO_EXTENSION));
$isImage   = in_array($proofExt, ['jpg','jpeg','png']);
$proofSrc  = null;
if ($isImage && file_exists($proofPath)) {
    $mime    = $proofExt === 'jpg' ? 'jpeg' : $proofExt;
    $proofSrc = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($proofPath));
}
@endphp

<div class="page-last">
    <div class="bukti-judul">Bukti Pembayaran</div>

    <table style="width:100%;border-collapse:collapse;">
        <tr>
            {{-- Kiri: Foto bukti transfer --}}
            <td style="width:60%;vertical-align:top;padding-right:20px;">
                @if($proofSrc)
                <img src="{{ $proofSrc }}"
                     style="max-width:100%;max-height:460px;width:auto;height:auto;display:block;">
                @elseif($proofExt === 'pdf')
                <div style="border:1px solid #ccc;padding:16px;color:#666;font-style:italic;">
                    Bukti transfer disimpan dalam format PDF.
                </div>
                @else
                <div style="border:1px solid #ccc;padding:16px;color:#666;font-style:italic;">
                    Bukti transfer tidak dapat ditampilkan.
                </div>
                @endif
            </td>

            {{-- Kanan: TTD --}}
            <td style="width:40%;text-align:center;vertical-align:bottom;">
                <div style="font-size:11pt;margin-bottom:6px;">
                    Jakarta, {{ ($collectivePayment->verified_at ?? now())->translatedFormat('d F Y') }}
                </div>
                <div style="font-size:11pt;margin-bottom:2px;">Penerima</div>

                <div style="height:72px;text-align:center;position:relative;">
                    @if($ttdSrc)
                    <img src="{{ $ttdSrc }}"
                         style="max-height:68px;max-width:160px;position:absolute;left:50%;transform:translateX(-50%);">
                    @endif
                    @if($stempelSrc)
                    <img src="{{ $stempelSrc }}"
                         style="max-height:60px;max-width:60px;position:absolute;left:55%;top:4px;">
                    @endif
                </div>

                <div style="font-size:11pt;font-weight:bold;">Dr. Marsofiyati, S.Pd., M.Pd.</div>
                <div style="font-size:10pt;">Manajer Keuangan</div>
            </td>
        </tr>
    </table>
</div>
@endif

</body>
</html>