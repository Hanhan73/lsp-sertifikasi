<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
    @page {
        size: A4;
        margin: 0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 10.5pt;
        color: #000;
        padding: 1cm 1.8cm 1.5cm 1.8cm;
    }

    .kop-wrap {
        text-align: left;
        margin-bottom: 6pt;
        margin-left: 76pt;
    }

    .kop-wrap img {
        height: 120pt;
        width: auto;
    }

    .kop-border {
        margin-bottom: 8pt;
    }

    .header-table {
        width: 100%;
        border: none;
        border-collapse: collapse;
        margin-bottom: 10pt;
        font-size: 10pt;
        line-height: 1.0;
    }

    .header-left {
        width: 60%;
        vertical-align: top;
    }

    .header-right {
        width: 40%;
        vertical-align: top;
        text-align: right;
    }

    .invoice-title {
        text-align: center;
        font-size: 16pt;
        font-weight: bold;
        letter-spacing: 4pt;
        margin: 8pt 0 2pt;
    }

    .invoice-nomor {
        text-align: center;
        font-size: 10pt;
        margin-bottom: 10pt;
    }

    .yth {
        font-size: 10.5pt;
        line-height: 1.0;
        margin-bottom: 10pt;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10pt;
    }

    .items-table th {
        background: #bcd5ed;
        color: #000000;
        padding: 5pt 7pt;
        font-size: 10pt;
        text-align: center;
        border: 1pt solid #000;
    }

    .items-table td {
        padding: 5pt 7pt;
        font-size: 10pt;
        border: 1pt solid #555;
        vertical-align: top;
    }

    .no {
        text-align: center;
        width: 22pt;
    }

    .right {
        text-align: right;
    }

    .center {
        text-align: center;
    }

    .total-row td {
        font-weight: bold;
        background: #f0f0f0;
    }

    .terbilang-box {
        font-size: 10.5pt;
        margin: 8pt 0 14pt;
        line-height: 1.0;
    }

    .ttd-table {
        width: 100%;
        border: none;
        border-collapse: collapse;
        margin-top: 16pt;
    }

    .ttd-left {
        width: 50%;
        vertical-align: top;
    }

    .ttd-right {
        width: 50%;
        text-align: center;
        vertical-align: top;
        font-size: 10pt;
    }

    .ttd-img-wrap {
        position: relative;
        height: 68pt;
        text-align: center;
    }

    .ttd-img-wrap img.ttd {
        position: absolute;
        max-height: 60pt;
        max-width: 140pt;
        left: 50%;
        transform: translateX(-50%);
        top: 4pt;
    }

    .ttd-img-wrap img.stempel {
        position: absolute;
        max-height: 55pt;
        max-width: 55pt;
        left: 55%;
        top: 8pt;
        opacity: 0.85;
    }

    .ttd-name {
        border-top: 1pt solid #000;
        display: inline-block;
        min-width: 155pt;
        padding-top: 2pt;
        font-weight: bold;
        font-size: 10.5pt;
    }
    </style>
</head>

<body>

    @php
    \Carbon\Carbon::setLocale('id');
    $kopPath = public_path('images/icon-lsp.png');
    $kopSrc = file_exists($kopPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($kopPath)) : null;
    $ttdPath = storage_path('app/private/mankeu/ttd.png');
    $ttdSrc = file_exists($ttdPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($ttdPath)) : null;
    $stempelPath = storage_path('app/private/mankeu/stempel.png');
    $stempelSrc = file_exists($stempelPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($stempelPath)) :
    null;
    $issuedAt = $invoice->issued_at ?? now();
    @endphp

    {{-- KOP --}}
    <div class="kop-border">
        @if($kopSrc)
        <div class="kop-wrap"><img src="{{ $kopSrc }}" alt="Logo LSP KAP"></div>
        @endif
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <strong>LSP Kompetensi Administrasi Perkantoran</strong><br>
                    NPWP &nbsp;&nbsp;&nbsp;: 50.599.365.9-422.000<br>
                    Alamat &nbsp;: Graha DLA Lt. 2 Suite 06<br>
                    <span style="margin-left:42pt;">&nbsp;Jl. Oto Iskandar Dinata, Nyengseret Astana Anyar</span><br>
                    <span style="margin-left:42pt;">&nbsp;Kota Bandung &ndash; Jawa Barat</span>
                </td>
                <td class="header-right">
                    <strong>BSI An. LSP KAP</strong><br>
                    No.Rek: 1619161919<br>
                    KCP Bandung UPI
                </td>
            </tr>
        </table>
    </div>

    {{-- Judul --}}
    <div class="invoice-title">INVOICE</div>
    <div class="invoice-nomor">No.: {{ $invoice->invoice_number }}</div>

    {{-- Yth --}}
    <div class="yth">
        Yth.<br>
        Pimpinan<br>
        <strong>{{ $invoice->recipient_name }}</strong><br>
        @if($invoice->recipient_address)
        {!! nl2br(e($invoice->recipient_address)) !!}
        @endif
    </div>

    {{-- Tabel --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Nama Skema</th>
                <th style="width:65pt;">Jumlah Asesi</th>
                <th style="width:95pt;">Harga Satuan</th>
                <th style="width:105pt;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="no">{{ $i + 1 }}</td>
                <td>{{ $item['skema_name'] }}</td>
                <td class="center">{{ $item['jumlah'] }} Orang</td>
                <td class="right">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2" class="right" style="font-weight:bold;">
                    Total Asesi = {{ collect($invoice->items)->sum('jumlah') }} Orang
                </td>
                <td colspan="2" class="right" style="font-weight:bold;">Sub Total</td>
                <td class="right" style="font-weight:bold;">
                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                </td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="right">Total</td>
                <td class="right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Terbilang --}}
    <div class="terbilang-box">
        Total biaya yang dibayarkan
        <strong>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
            ({{ \App\Helpers\TerbilangHelper::convert((int) $invoice->total_amount) }})</strong>.
        Pembayaran dapat dilakukan melalui Rekening Bank BSI No. 1619161919 atas nama LSP-KAP.
        @if($invoice->notes)<br><br>Catatan: {{ $invoice->notes }}@endif
    </div>

    {{-- TTD --}}
    <table class="ttd-table">
        <tr>
            <td class="ttd-left"></td>
            <td class="ttd-right">
                Jakarta, {{ $issuedAt->translatedFormat('d F Y') }}<br>
                Manajer Keuangan,<br>
                <div class="ttd-img-wrap">
                    @if($stempelSrc)<img src="{{ $stempelSrc }}" class="stempel" alt="Stempel">@endif
                    @if($ttdSrc)<img src="{{ $ttdSrc }}" class="ttd" alt="TTD">@endif
                </div>
                <span class="ttd-name">Dr. Marsofiyati, S.Pd., M.Pd.</span><br>
                <span style="font-size:10pt;">Manajer Keuangan</span>
            </td>
        </tr>
    </table>

</body>

</html>