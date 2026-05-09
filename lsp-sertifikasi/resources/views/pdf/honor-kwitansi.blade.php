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

    .detail-list .no-col    { width: 20px; }
    .detail-list .skema-col { width: 140px; }
    .detail-list .tgl-col   { width: 90px; }
    .detail-list .lok-col   { }
    .detail-list .asesi-col { width: 40px; text-align: center; }
    .detail-list .honor-col { width: 90px; text-align: right; }
    .detail-list .sub-col   { width: 100px; text-align: right; font-weight: bold; }

    .deduction-row {
        background: #fff3cd;
        font-size: 10pt;
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

    .transfer-box {
        display: inline-block;
        border: 2px solid #155724;
        background-color: #d1e7dd;
        padding: 5px 16px;
        font-weight: bold;
        font-size: 12.5pt;
        min-width: 150px;
        text-align: center;
        margin-top: 6px;
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
    $rows = [];
    foreach ($honor->details as $detail) {
        $schedule    = $detail->schedule;
        $tgl         = optional($schedule->assessment_date)->translatedFormat('d F Y') ?? '-';
        $skema       = $schedule->skema->name ?? '-';
        $sub         = $detail->jumlah_asesi * $detail->honor_per_asesi;
        $batchId     = $schedule->asesmens->first()?->collective_batch_id ?? '';
        $namaSekolah = '-';

        if ($batchId) {
            $parts = explode('-', $batchId);
            if (count($parts) > 2) {
                $filtered    = array_filter(
                    array_slice($parts, 0, count($parts) - 2),
                    fn($word) => strtoupper($word) !== 'TUK'
                );
                $namaSekolah = ucwords(strtoupper(implode(' ', $filtered)));
            } else {
                $namaSekolah = ucwords(strtoupper(str_replace('-', ' ', $batchId)));
            }
        }

        $rows[] = compact('tgl', 'namaSekolah', 'skema', 'sub', 'detail');
    }

    $hasDeduct = !is_null($honor->deduction_amount) && $honor->deduction_amount > 0;

    $jumlahTerbilang = $hasDeduct
        ? (float) $honor->total - (float) $honor->deduction_amount
        : (float) $honor->total;

    // Bukti hanya tampil di blade kalau:
    // - Sudah dikonfirmasi
    // - Ada file bukti
    // - File-nya gambar (jpg/png) — karena DomPDF bisa render gambar
    // - FPDI TIDAK tersedia (kalau FPDI ada, merge dilakukan di controller, bukan di sini)
    $buktiPath   = $honor->bukti_transfer_path
        ? storage_path('app/private/' . $honor->bukti_transfer_path)
        : null;
    $buktiExt    = $buktiPath ? strtolower(pathinfo($buktiPath, PATHINFO_EXTENSION)) : null;
    $fpdiAda     = class_exists(\setasign\Fpdi\Fpdi::class);

    // Tampilkan halaman bukti di DomPDF hanya kalau FPDI tidak ada
    // (kalau FPDI ada, controller sudah merge via PdfMergeService)
    $hasBuktiDomPdf = !$isDraft
        && $buktiPath
        && file_exists($buktiPath)
        && !$fpdiAda;
    @endphp

    {{-- ═══ HALAMAN KWITANSI ═══ --}}
    <div class="{{ $hasBuktiDomPdf ? 'page' : 'page-last' }}">
        <div class="kwitansi-frame">
            <table class="main-table">
                <tr>
                    {{-- LOGO --}}
                    <td class="col-logo">
                        @php $icon = public_path('images/icon-lsp.png'); @endphp
                        @if(file_exists($icon))
                        <img src="{{ $icon }}">
                        @endif
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
                                        {{ ucwords(\App\Helpers\Terbilang::convert($jumlahTerbilang)) }} Rupiah
                                    </span>
                                    @if($hasDeduct)
                                    <div style="font-size:8.5pt;color:#666;margin-top:3px;font-style:italic;">
                                        (setelah potongan cicilan hutang
                                        Rp {{ number_format($honor->deduction_amount, 0, ',', '.') }}
                                        dari total honor Rp {{ number_format($honor->total, 0, ',', '.') }})
                                    </div>
                                    @endif
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
                                            <td class="tgl-col">{{ $row['tgl'] }}</td>
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

                                        {{-- Total honor --}}
                                        <tr style="border-top:2px solid #ccc;">
                                            <td colspan="6" style="text-align:right;padding:3px 4px;font-weight:bold;">
                                                Total Honor
                                            </td>
                                            <td class="sub-col">
                                                Rp {{ number_format($honor->total, 0, ',', '.') }},-
                                            </td>
                                        </tr>

                                        {{-- Baris cicilan hutang --}}
                                        @if($hasDeduct)
                                        <tr class="deduction-row">
                                            <td colspan="6" style="text-align:right;padding:3px 4px;color:#dc3545;">
                                                Potongan Cicilan Hutang
                                                @if($honor->deductionReceivable)
                                                ({{ $honor->deductionReceivable->uraian ?? $honor->deductionReceivable->jenis_label }})
                                                @endif
                                            </td>
                                            <td class="sub-col" style="color:#dc3545;">
                                                - Rp {{ number_format($honor->deduction_amount, 0, ',', '.') }},-
                                            </td>
                                        </tr>
                                        <tr style="background:#d1e7dd;border-top:1.5px solid #155724;">
                                            <td colspan="6" style="text-align:right;padding:3px 4px;font-weight:bold;color:#155724;">
                                                Jumlah Transfer Bersih
                                            </td>
                                            <td class="sub-col" style="color:#155724;">
                                                Rp {{ number_format($jumlahTerbilang, 0, ',', '.') }},-
                                            </td>
                                        </tr>
                                        @if($honor->deduction_note)
                                        <tr>
                                            <td colspan="7" style="font-size:8.5pt;color:#666;padding:2px 4px;font-style:italic;">
                                                Ket: {{ $honor->deduction_note }}
                                            </td>
                                        </tr>
                                        @endif
                                        @endif

                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="footer-table">
                            <tr>
                                <td class="col-jumlah">
                                    <div style="font-style:italic;font-size:11pt;margin-bottom:5px;">
                                        {{ $hasDeduct ? 'Transfer Bersih :' : 'Jumlah :' }}
                                    </div>
                                    <div class="{{ $hasDeduct ? 'transfer-box' : 'jumlah-box' }}">
                                        Rp {{ number_format($jumlahTerbilang, 0, ',', '.') }}
                                    </div>
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
                                    @elseif(!empty($ttdAsesor))
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

    {{--
        ═══ HALAMAN BUKTI (FALLBACK — hanya tampil kalau FPDI tidak terinstall) ═══

        Kalau FPDI sudah terinstall:
          → Controller (PdfMergeService) yang handle penggabungan
          → Blok ini tidak akan pernah dirender ($hasBuktiDomPdf = false)

        Kalau FPDI belum terinstall:
          → Gambar (JPG/PNG): tampil langsung di sini via DomPDF
          → PDF: tampil keterangan singkat (DomPDF tidak bisa embed PDF lain)
    --}}
    @if($hasBuktiDomPdf)
    <div class="page-last">
        <div class="bukti-judul">Bukti Pembayaran</div>

        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:60%;vertical-align:top;padding-right:20px;">

                    @if(in_array($buktiExt, ['jpg', 'jpeg', 'png']))
                    <img src="{{ $buktiPath }}"
                         style="max-width:100%;max-height:460px;width:auto;height:auto;display:block;">

                    @elseif($buktiExt === 'pdf')
                    {{-- PDF tidak bisa dirender inline oleh DomPDF --}}
                    <div style="border:1px dashed #ccc;padding:24px;text-align:center;border-radius:4px;background:#f8f9fa;">
                        <div style="font-size:32pt;color:#dc3545;">&#128196;</div>
                        <div style="font-size:11pt;font-weight:bold;margin-top:8px;">Bukti Transfer (PDF)</div>
                        <div style="font-size:9pt;color:#666;margin-top:4px;">
                            {{ $honor->bukti_transfer_name }}
                        </div>
                        <div style="font-size:8.5pt;color:#888;margin-top:8px;font-style:italic;">
                            Install library <strong>setasign/fpdi</strong> agar bukti PDF
                            dapat digabungkan otomatis ke dalam kwitansi ini.
                        </div>
                    </div>

                    @else
                    <p style="color:#888;font-style:italic;">Bukti transfer tidak dapat ditampilkan.</p>
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