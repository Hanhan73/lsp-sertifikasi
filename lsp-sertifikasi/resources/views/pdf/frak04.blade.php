<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>FR.AK.04 - Banding Asesmen - {{ $frak04->nama_asesi ?? $asesmen->full_name }}</title>
    <style>
    /* ─────────────────────────────
   PAGE SETUP (ikut Word)
───────────────────────────── */
    @page {
        size: A4;
        margin: 0;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    /* ─────────────────────────────
   BODY (INI PALING PENTING)
───────────────────────────── */
    body {
        font-family: Arial, sans-serif;
        font-size: 10.5pt;
        line-height: 1.15;
        color: #000;

        /* margin Word-like */
        padding: 2.3cm 0.9cm 0.4cm 1.8cm;

        letter-spacing: 0;
        word-spacing: -0.3px;
    }

    /* global text density */
    td {
        line-height: 1.1;
    }

    /* ─────────────────────────────
   HEADER
───────────────────────────── */
    .doc-header-tbl {
        width: 100%;
        border-collapse: collapse;
        margin-left: 0.2cm;
    }

    .doc-header-tbl td {
        border: none;
        padding: 0 4pt;
    }

    .doc-code {
        font-weight: bold;
        font-size: 11pt;
        width: 14%;
    }

    .doc-title-cell {
        font-weight: bold;
        font-size: 11pt;
        text-align: left;
    }

    /* ─────────────────────────────
   MAIN TABLE
───────────────────────────── */
    .main-tbl {
        width: calc(100% - 0.65cm);
        margin-left: 0.65cm;

        border-collapse: collapse;
        border: 1.3pt solid #000;

        font-size: 10.5pt;
        margin-top: 5pt;
    }

    .main-tbl td {
        border: 1pt solid #000;
        padding: 2pt 4pt;
        vertical-align: middle;
    }

    /* ─────────────────────────────
   HEADER PERTANYAAN
───────────────────────────── */
    .pertanyaan-header td {
        height: 22pt;
        font-weight: normal;
        padding: 2pt 4pt;
    }

    .pertanyaan-header td:first-child {
        width: 80%;
    }

    .col-ya,
    .col-tidak {
        width: 10%;
        text-align: center;
        font-weight: bold;
    }

    /* ─────────────────────────────
   ROW PERTANYAAN
───────────────────────────── */
    .pertanyaan-row td {
        height: 26pt;
        padding: 2pt 4pt;
    }

    .col-cb {
        text-align: center;
        vertical-align: middle;
    }

    /* ─────────────────────────────
   CHECKBOX (WORD STYLE)
───────────────────────────── */
    .cb {
        display: inline-block;
        width: 10pt;
        height: 10pt;
        border: 0.75pt solid #000;
        text-align: center;
        line-height: 10pt;
        font-size: 8pt;
        font-weight: bold;
        vertical-align: middle;
    }

    /* ─────────────────────────────
   TEXT BLOCKS
───────────────────────────── */
    .block-text {
        padding: 2pt 4pt;
        line-height: 1.2;
    }

    /* skema info */
    .block-skema {
        height: 60pt;
        line-height: 1.4;
    }

    /* alasan */
    .block-alasan {
        height: 60pt;
        vertical-align: top;
    }

    /* hak */
    .block-hak {
        height: 36pt;
    }

    /* ─────────────────────────────
   SIGNATURE (SUPER IMPORTANT)
───────────────────────────── */
    .ttd-cell {
        padding: 4pt;
    }

    .sig-row {
        display: flex;
        align-items: center;
        margin-bottom: 16pt;
    }

    .sig-label {
        font-size: 11pt;
        line-height: normal;
        margin-right: 4pt;
        vertical-align: bottom;
        display: inline-block;
    }

    /* garis tanda tangan */
    .sig-line-wrap {
        display: inline-block;
        position: relative;
        width: 130pt;
        height: 50pt;
        /* lebih tinggi */

        border-bottom: 1pt dotted #000;
        margin-right: 12pt;

        vertical-align: bottom;
        align-items: flex-end;
    }

    /* gambar tanda tangan */
    .sig-img-overlay {
        position: absolute;
        bottom: 2pt;
        /* dari sebelumnya 4pt → turun */
        left: 0;

        width: 100%;
        height: 85%;
        /* bikin lebih gede */

        object-fit: contain;
        object-position: center bottom;
    }


    /* nama kecil di bawah */
    .sig-name-below {
        position: absolute;
        bottom: 0;
        width: 100%;
        text-align: center;
        font-size: 7pt;
    }

    /* tanggal */
    .sig-tanggal {

        font-size: 11pt;
        line-height: normal;
        margin-right: 4pt;
        vertical-align: bottom;
        display: inline-block;

        min-width: 80pt;
        align-items: center;
        border-bottom: 1pt dotted #000;
    }

    /* ─────────────────────────────
   FINAL TOUCH
───────────────────────────── */
    table {
        border-spacing: 0;
    }

    strong {
        font-weight: bold;
    }

    .cross-out {
        text-decoration: line-through;
    }
    </style>
</head>

<body>

    {{-- ═══════════════ HEADER ═══════════════ --}}
    <table class="doc-header-tbl">
        <tr>
            <td class="doc-code">&nbsp;&nbsp;&nbsp;FR.AK.04.</td>
            <td class="doc-title-cell">BANDING ASESMEN</td>
        </tr>
    </table>

    {{-- ═══════════════ MAIN TABLE ═══════════════ --}}
    <table class="main-tbl">

        {{-- Nama Asesi — height 411 twips = 21pt --}}
        <tr style="height: 21pt;">
            <td colspan="3" style="padding: 3pt 6pt;">
                Nama Asesi: {{ $frak04->nama_asesi ?? $asesmen->full_name ?? '-' }}
            </td>
        </tr>

        {{-- Nama Asesor --}}
        <tr style="height: 21pt;">
            <td colspan="3" style="padding: 3pt 6pt;">
                Nama Asesor: {{ $frak04->nama_asesor ?? $asesmen->schedule?->asesor?->nama ?? '-' }}
            </td>
        </tr>

        {{-- Tanggal Asesmen --}}
        <tr style="height: 21pt;">
            <td colspan="3" style="padding: 3pt 6pt;">
                Tanggal Asesmen:
                {{ $frak04->tanggal_asesmen ?? ($asesmen->schedule?->assessment_date?->translatedFormat('l, d F Y') ?? '-') }}
            </td>
        </tr>

        {{-- Header pertanyaan —  height 548 twips = 27pt --}}
        <tr class="pertanyaan-header" style="height: 27pt;">
            <td>Jawablah dengan Ya atau Tidak pertanyaan-pertanyaan berikut ini :</td>
            <td class="col-ya">YA</td>
            <td class="col-tidak">TIDAK</td>
        </tr>

        {{-- Pertanyaan 1 — height 680 twips = 34pt --}}
        <tr class="pertanyaan-row" style="height: 34pt;">
            <td>Apakah Proses Banding telah dijelaskan kepada Anda?</td>
            <td class="col-cb">
                <span class="cb">{{ $frak04->proses_banding_dijelaskan === true ? 'V' : '' }}</span>
            </td>
            <td class="col-cb">
                <span class="cb">{{ $frak04->proses_banding_dijelaskan === false ? 'V' : '' }}</span>
            </td>
        </tr>

        {{-- Pertanyaan 2 — height 679 twips ≈ 34pt --}}
        <tr class="pertanyaan-row" style="height: 34pt;">
            <td>Apakah Anda telah mendiskusikan Banding dengan Asesor?</td>
            <td class="col-cb">
                <span class="cb">{{ $frak04->sudah_diskusi_dengan_asesor === true ? 'V' : '' }}</span>
            </td>
            <td class="col-cb">
                <span class="cb">{{ $frak04->sudah_diskusi_dengan_asesor === false ? 'V' : '' }}</span>
            </td>
        </tr>

        {{-- Pertanyaan 3 — height 683 twips ≈ 34pt --}}
        <tr class="pertanyaan-row" style="height: 34pt;">
            <td>Apakah Anda mau melibatkan &#x201C;orang lain&#x201D; membantu Anda dalam Proses Banding?</td>
            <td class="col-cb">
                <span class="cb">{{ $frak04->melibatkan_orang_lain === true ? 'V' : '' }}</span>
            </td>
            <td class="col-cb">
                <span class="cb">{{ $frak04->melibatkan_orang_lain === false ? 'V' : '' }}</span>
            </td>
        </tr>

        {{-- Info Skema — height 1463 twips = 73pt --}}
        <tr>
            <td colspan="3" style="height: 73pt; padding: 3pt 6pt; vertical-align: middle; line-height: 1.6;">
                @php $jenis = $asesmen->skema?->jenis_skema; @endphp
                Banding ini diajukan atas Keputusan Asesmen yang dibuat terhadap Skema Sertifikasi
                @if($jenis === 'kkni')
                (<span>KKNI</span>/<span class="cross-out">Okupasi</span>/<span class="cross-out">Klaster</span>)
                @elseif($jenis === 'okupasi')
                (<span class="cross-out">KKNI</span>/<span>Okupasi</span>/<span class="cross-out">Klaster</span>)
                @elseif($jenis === 'klaster')
                (<span class="cross-out">KKNI</span>/<span class="cross-out">Okupasi</span>/<span>Klaster</span>)
                @else
                (KKNI/Okupasi/Klaster)
                @endif berikut :<br>
                Skema Sertifikasi :
                &nbsp;<strong>{{ $frak04->skema_sertifikasi ?? $asesmen->skema?->name ?? '-' }}</strong><br>
                No. Skema Sertifikasi : {{ $frak04->no_skema_sertifikasi ?? $asesmen->skema?->nomor_skema ?? '-' }}
            </td>
        </tr>

        {{-- Alasan Banding — height 1629 twips = 81pt --}}
        <tr>
            <td colspan="3" style="height: 81pt; padding: 3pt 6pt; vertical-align: top;">
                Banding ini diajukan atas alasan sebagai berikut :<br><br>
                {{ $frak04->alasan_banding ?? '' }}
            </td>
        </tr>

        {{-- Hak Banding — height 968 twips = 48pt --}}
        <tr>
            <td colspan="3" class="hak-area">
                Anda mempunyai hak mengajukan banding jika Anda menilai Proses Asesmen tidak sesuai SOP
                dan tidak memenuhi Prinsip Asesmen.
            </td>
        </tr>

        {{-- Tanda Tangan — height 1559 twips = 78pt — gaya sama persis FR.AK.01 ── --}}
        <tr>
            <td colspan="3" class="ttd-cell">

                <div class="sig-row">
                    <span class="sig-label">Tanda tangan Asesi :</span>
                    <span class="sig-line-wrap">
                        @if($frak04->ttd_asesi)
                        <img src="{{ $frak04->ttd_asesi_image }}" class="sig-img-overlay" alt="TTD Asesi">
                        @endif
                        <span
                            class="sig-name-below">{{ $frak04->nama_ttd_asesi ?? $frak04->nama_asesi ?? $asesmen->full_name ?? '' }}</span>
                    </span>
                    <span class="sig-label">Tanggal :&nbsp;</span>
                    <span class="sig-tanggal">
                        @if($frak04->tanggal_ttd_asesi){{ $frak04->tanggal_ttd_asesi->format('d-m-Y') }}@endif
                    </span>
                </div>

            </td>
        </tr>

    </table>

</body>

</html>