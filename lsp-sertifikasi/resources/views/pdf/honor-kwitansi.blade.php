<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
    @page {
        margin: 0;
        size: A4 landscape;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        color: #000;
        margin: 0;
        padding: 28px 36px;
    }

    .page {
        page-break-after: always;
    }

    .page-last {
        page-break-after: auto;
    }

    .kwitansi-frame {
        border: 2px solid #000;
        padding: 20px 24px;
    }

    .main-table {
        width: 100%;
        border-collapse: collapse;
    }

    .col-logo {
        width: 90px;
        vertical-align: top;
        padding-top: 2px;
        padding-right: 18px;
    }

    .col-logo img {
        width: 150px;
        height: 150px;
        object-fit: contain;
    }

    .col-content {
        vertical-align: top;
    }

    .body-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
    }

    .body-table td {
        padding: 4px 3px;
        vertical-align: top;
        font-size: 11pt;
    }

    .col-label {
        width: 148px;
    }

    .col-colon {
        width: 12px;
    }

    .terbilang-box {
        display: inline-block;
        border: 1.5px solid #555;
        background-color: #d7e7f1;
        padding: 3px 10px;
        font-style: italic;
        font-weight: bold;
        font-size: 11pt;
    }

    .detail-list {
        width: 100%;
        border-collapse: collapse;
        margin: 5px 0 3px 0;
    }

    .detail-list td {
        padding: 2px 3px;
        font-size: 11pt;
        vertical-align: top;
    }

    .detail-list .no-col {
        width: 22px;
    }

    .tuk-line {
        font-size: 11pt;
        margin-top: 4px;
    }

    .footer-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 32px;
    }

    .footer-table td {
        vertical-align: top;
    }

    .col-jumlah {
        width: 46%;
        padding-top: 10px;
    }

    .col-ttd {
        width: 54%;
        text-align: center;
    }

    .jumlah-label {
        font-style: italic;
        font-size: 11pt;
        margin-bottom: 5px;
    }

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

    .bukti-judul {
        font-size: 13pt;
        font-weight: bold;
        margin-bottom: 16px;
    }

    .bukti-img {
        max-width: 100%;
        max-height: 500px;
        display: block;
        margin: 0 auto;
    }

    .bukti-footer {
        text-align: right;
        margin-top: 32px;
        font-size: 11pt;
    }
    </style>
</head>

<body>

    @php
    $grouped = [];
    foreach ($honor->details as $detail) {
    $sid = $detail->schedule->skema_id;
    if (!isset($grouped[$sid])) {
    $grouped[$sid] = [
    'nama' => $detail->schedule->skema->name,
    'honor_per_asesi' => $detail->honor_per_asesi,
    'jumlah_asesi' => 0,
    'tanggals' => [],
    'tuks' => [],
    ];
    }
    $grouped[$sid]['jumlah_asesi'] += $detail->jumlah_asesi;

    $tgl = optional($detail->schedule->assessment_date)->translatedFormat('d F Y');
    if ($tgl && !in_array($tgl, $grouped[$sid]['tanggals'])) {
    $grouped[$sid]['tanggals'][] = $tgl;
    }
    $tuk = $detail->schedule->tuk->name ?? '-';
    if (!in_array($tuk, $grouped[$sid]['tuks'])) {
    $grouped[$sid]['tuks'][] = $tuk;
    }
    }
    $allTuks = collect($grouped)->pluck('tuks')->flatten()->unique()->implode(', ');
    $allTanggals = collect($grouped)->pluck('tanggals')->flatten()->unique()->implode(', ');
    @endphp

    <div class="{{ ($honor->isDikonfirmasi() && $honor->bukti_transfer_path) ? 'page' : 'page-last' }}">
        <div class="kwitansi-frame">
            <table class="main-table">
                <tr>
                    {{-- LOGO --}}
                    <td class="col-logo">
                        @php $icon = public_path('images/icon-lsp.png'); @endphp
                        @if(file_exists($icon))<img src="{{ $icon }}">@endif
                    </td>

                    {{-- KONTEN --}}
                    <td class="col-content">
                        <table class="body-table">
                            <tr>
                                <td class="col-label">No.</td>
                                <td class="col-colon">:</td>
                                <td>{{ $honor->nomor_kwitansi }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Telah diterima dari</td>
                                <td class="col-colon">:</td>
                                <td><strong>LSP Kompetensi Administrasi Perkantoran</strong></td>
                            </tr>
                            <tr>
                                <td class="col-label" style="padding-top:5px;">Uang sejumlah</td>
                                <td class="col-colon" style="padding-top:5px;">:</td>
                                <td style="padding-top:5px;">
                                    <span class="terbilang-box">
                                        {{ ucwords(\App\Helpers\Terbilang::convert($honor->total)) }} Rupiah
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="col-label" style="padding-top:8px;">Untuk pembayaran</td>
                                <td class="col-colon" style="padding-top:8px;">:</td>
                                <td style="padding-top:8px;">
                                    Honor Asesor, dengan skema sebagai berikut:
                                    <table class="detail-list">
                                        @foreach(array_values($grouped) as $i => $g)
                                        <tr>
                                            <td class="no-col">{{ $i + 1 }}.</td>
                                            <td>
                                                {{ $g['nama'] }}, sebanyak {{ $g['jumlah_asesi'] }} asesi
                                                @Rp. {{ number_format($g['honor_per_asesi'], 0, ',', '.') }}
                                                = Rp.
                                                {{ number_format($g['jumlah_asesi'] * $g['honor_per_asesi'], 0, ',', '.') }},-
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                    <div class="tuk-line">di TUK {{ $allTuks }}, {{ $allTanggals }}</div>
                                </td>
                            </tr>
                        </table>

                        <table class="footer-table">
                            <tr>
                                <td class="col-jumlah">
                                    <div class="jumlah-label"><em>Jumlah :</em></div>
                                    <div class="jumlah-box">Rp {{ number_format($honor->total, 0, ',', '.') }}</div>
                                </td>
                                <td class="col-ttd">
                                    <div style="font-size:11pt;margin-bottom:3px;">
                                        Jakarta,
                                        {{ optional($honor->tanggal_kwitansi)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
                                    </div>
                                    <div style="font-size:11pt;margin-bottom:2px;">Penerima</div>
                                    @if($honor->isDikonfirmasi() && !empty($ttdAsesor ?? null))
                                    <img src="{{ $ttdAsesor }}" style="height:68px;margin:6px 0 2px;"><br>
                                    @else
                                    <div style="height:76px;"></div>
                                    @endif
                                    <div style="font-size:11pt;">{{ $honor->asesor->nama }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if($honor->isDikonfirmasi() && $honor->bukti_transfer_path)
    <div class="page-last">
        <div class="bukti-judul">Bukti Pembayaran</div>
        @php
        $ext = strtolower(pathinfo($honor->bukti_transfer_name ?? '', PATHINFO_EXTENSION));
        $buktiFull = storage_path('app/private/' . $honor->bukti_transfer_path);
        @endphp

        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:60%;vertical-align:top;padding-right:20px;">
                    @if(in_array($ext, ['jpg', 'jpeg', 'png']) && file_exists($buktiFull))
                    <img src="{{ $buktiFull }}"
                        style="max-width:100%;max-height:460px;width:auto;height:auto;display:block;">
                    @elseif($ext === 'pdf')
                    <p><em>Bukti transfer disimpan dalam format PDF.</em></p>
                    @else
                    <p><em>Bukti transfer tidak dapat ditampilkan.</em></p>
                    @endif
                </td>
                <td style="width:40%; text-align:center; vertical-align:bottom;">
                    <div style="font-size:11pt;margin-bottom:6px;">
                        Jakarta,
                        {{ optional($honor->dikonfirmasi_at)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
                    </div>
                    <div style="font-size:11pt;margin-bottom:2px;">Penerima</div>
                    @if(!empty($ttdAsesor ?? null))
                    <img src="{{ $ttdAsesor }}" style="height:68px;margin:6px 0;">
                    @else
                    <div style="height:76px;"></div>
                    @endif
                    <div style="font-size:11pt;">{{ $honor->asesor->nama }}</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

</body>

</html>