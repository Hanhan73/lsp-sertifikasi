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

    /* Tabel rincian jadwal */
    .detail-list {
        width: 100%;
        border-collapse: collapse;
        margin: 6px 0 3px 0;
    }

    .detail-list th {
        background: #f0f4f8;
        font-size: 9pt;
        font-weight: bold;
        padding: 3px 4px;
        border-bottom: 1px solid #ccc;
        vertical-align: top;
    }

    .detail-list td {
        padding: 3px 4px;
        font-size: 9.5pt;
        vertical-align: top;
        border-bottom: 1px solid #eee;
    }

    .detail-list .no-col  { width: 20px; }
    .detail-list .skema-col { width: 140px; }
    .detail-list .tgl-col { width: 90px; }
    .detail-list .lok-col { }
    .detail-list .asesi-col { width: 40px; text-align: center; }
    .detail-list .honor-col { width: 90px; text-align: right; }
    .detail-list .sub-col  { width: 100px; text-align: right; font-weight: bold; }

    .sub-info {
        font-size: 8.5pt;
        color: #555;
        margin-top: 1px;
    }

    .footer-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 24px;
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

    .bukti-judul {
        font-size: 13pt;
        font-weight: bold;
        margin-bottom: 16px;
    }
    </style>
    @if($isDraft)
        <style>
        .watermark-draft {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 80px;
            font-weight: 900;
            color: rgba(200, 0, 0, 0.08);
            letter-spacing: 8px;
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }
        </style>
    @endif
</head>

<body>
    @if($isDraft)
    <div class="watermark-draft">DRAFT</div>
    @endif

    @php
    /*
     * Susun per-JADWAL — setiap HonorPaymentDetail → satu baris.
     * Lokasi diambil dari nama sekolah dalam collective_batch_id:
     * Format batch: NAMA-SEKOLAH-TUKCODE-SUFFIX6CHAR
     * → buang 2 segmen terakhir, replace '-' jadi spasi, ucwords
     */
    $rows = [];
    foreach ($honor->details as $detail) {
        $schedule = $detail->schedule;
        $tgl      = optional($schedule->assessment_date)->translatedFormat('d F Y') ?? '-';
        $waktu    = trim(($schedule->start_time ?? '') . ($schedule->end_time ? ' – ' . $schedule->end_time : ''));
        $skema    = $schedule->skema->name ?? '-';
        $sub      = $detail->jumlah_asesi * $detail->honor_per_asesi;

        // Ambil nama sekolah dari batch ID asesi pertama di jadwal ini
        $batchId     = $schedule->asesmens->first()?->collective_batch_id ?? '';
        $namaSekolah = '-';
        if ($batchId) {
            // Format: NAMA-SEKOLAH-TUKCODE-SUFFIX → buang 2 segmen terakhir
            $parts = explode('-', $batchId);
            if (count($parts) > 2) {
                $filtered = array_filter(
                    array_slice($parts, 0, count($parts) - 2),
                    function ($word) {
                        return strtoupper($word) !== 'TUK';
                    }
                );

                $namaSekolah = ucwords(strtoupper(implode(' ', $filtered)));
            } else {
                // fallback jika format tidak standard
                $namaSekolah = ucwords(strtoupper(str_replace('-', ' ', $batchId)));
            }
        }

        $rows[] = compact('tgl', 'waktu', 'namaSekolah', 'skema', 'sub', 'detail');
    }

    // Halaman kwitansi: jika sudah dikonfirmasi DAN ada bukti transfer, beri page-break
    $hasBukti = $honor->isDikonfirmasi() && $honor->bukti_transfer_path;
    @endphp

    <div class="{{ $hasBukti ? 'page' : 'page-last' }}">
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
                                    Honor Asesor, dengan rincian jadwal sebagai berikut:

                                    <table class="detail-list">
                                        <tr>
                                            <th class="no-col">No</th>
                                            <th class="skema-col">Skema</th>
                                            <th class="tgl-col">Tanggal</th>
                                            <th class="lok-col">Sekolah / Instansi</th>
                                            <th class="asesi-col">Asesi</th>
                                            <th class="honor-col">Honor/Asesi</th>
                                            <th class="sub-col">Subtotal</th>
                                        </tr>
                                        @foreach($rows as $i => $row)
                                        <tr style="{{ $i % 2 === 1 ? 'background:#f9fbfd;' : '' }}">
                                            <td class="no-col">{{ $i + 1 }}.</td>
                                            <td class="skema-col">{{ $row['skema'] }}</td>
                                            <td class="tgl-col">
                                                {{ $row['tgl'] }}
                                            </td>
                                            <td class="lok-col">{{ $row['namaSekolah'] }}</td>
                                            <td class="asesi-col">{{ $row['detail']->jumlah_asesi }}</td>
                                            <td class="honor-col">
                                                Rp {{ number_format($row['detail']->honor_per_asesi, 0, ',', '.') }}
                                            </td>
                                            <td class="sub-col">
                                                Rp {{ number_format($row['sub'], 0, ',', '.') }},-
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="footer-table">
                            <tr>
                                <td class="col-jumlah">
                                    <div style="font-style:italic;font-size:11pt;margin-bottom:5px;">Jumlah :</div>
                                    <div class="jumlah-box">Rp {{ number_format($honor->total, 0, ',', '.') }}</div>
                                </td>
                                <td class="col-ttd">
                                    <div style="font-size:11pt;margin-bottom:3px;">
                                        Jakarta,
                                        {{ optional($honor->tanggal_kwitansi)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
                                    </div>
                                    <div style="font-size:11pt;margin-bottom:2px;">Penerima</div>
                                    @if($isDraft)
                                        <div style="height:60px;border-bottom:1px solid #999;width:150px;margin-top:4px;"></div>
                                        <div style="font-size:9pt;color:#999;margin-top:2px;font-style:italic;">
                                            (Belum ditandatangani)
                                        </div>
                                    @elseif(!$isDraft && !empty($ttdAsesor))
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

    @if($hasBukti)
    <div class="page-last">
        <div class="bukti-judul">Bukti Pembayaran</div>
        @php
        $ext       = strtolower(pathinfo($honor->bukti_transfer_name ?? '', PATHINFO_EXTENSION));
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
                <td style="width:40%;text-align:center;vertical-align:bottom;">
                    <div style="font-size:11pt;margin-bottom:6px;">
                        Jakarta,
                        {{ optional($honor->dibayar_at)->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
                    </div>
                    <div style="font-size:11pt;margin-bottom:2px;">Penerima</div>
                    @if(!empty($ttdAsesor))
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