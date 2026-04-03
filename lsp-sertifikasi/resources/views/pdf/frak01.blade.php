<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>FR.AK.01 - {{ $frak01->nama_asesi ?? $asesmen->full_name }}</title>
    <style>
    @page {
        size: A4;
        margin: 0;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.3;
        color: #000;
        padding: 1.8cm 2cm 1.5cm 2cm;
    }

    /* ── JUDUL ── */
    .doc-header {
        font-weight: bold;
        font-size: 10pt;
        margin-bottom: 6pt;
    }

    .doc-header span.code {
        margin-right: 8pt;
    }

    /* ── MAIN TABLE ── */
    .main-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
    }

    .main-tbl td {
        border: 0.75pt solid #000;
        padding: 3pt 5pt;
        vertical-align: middle;
    }

    .main-tbl td.lbl {
        width: 26%;
        vertical-align: middle;
    }

    .main-tbl td.sep {
        width: 8%;
        vertical-align: middle;
    }

    .main-tbl td.val {
        vertical-align: middle;
    }

    /* ── CHECKBOX ── */
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
        margin-right: 3pt;
    }

    .cb-row td {
        padding: 2pt 5pt;
        vertical-align: middle;
    }

    /* ── PERNYATAAN TABLE ── */
    .pernyataan-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
    }

    .pernyataan-tbl td {
        border: 0.75pt solid #000;
        padding: 3pt 5pt;
        vertical-align: top;
        line-height: 1.4;
    }

    .pernyataan-label {
        font-weight: bold;
    }

    /* ── SIGNATURE ── */
    .sig-cell {
        padding: 5pt 5pt 4pt 5pt;
    }

    /* CHANGED: align-items: center (was baseline) agar tanggal sejajar tengah seperti AK04 */
    .sig-row {
        display: flex;
        align-items: center;
        margin-bottom: 6pt;
    }

    .sig-row:last-child {
        margin-bottom: 8pt;
    }

    .sig-label {
        font-size: 10pt;
        line-height: normal;
        margin-right: 4pt;
        vertical-align: bottom;
        display: inline-block;
    }

    .sig-line-wrap {
        display: inline-block;
        position: relative;
        width: 120pt;
        border-bottom: 1pt dotted #000;
        height: 48pt;
        vertical-align: bottom;
        margin-right: 16pt;
    }

    .sig-img-overlay {
        position: absolute;
        bottom: 8pt;
        left: 0;
        width: 100%;
        height: calc(100% - 8pt);
        object-fit: contain;
        object-position: left bottom;
    }

    .sig-name-below {
        position: absolute;
        bottom: 0pt;
        left: 0;
        width: 100%;
        font-size: 7pt;
        text-align: center;
        line-height: 1;
        color: #000;
    }

    /* CHANGED: samakan dengan AK04 — tanpa height fixed, tanpa line-height fixed */
    .sig-tanggal {
        font-size: 10pt;
        line-height: normal;
        margin-right: 4pt;
        vertical-align: bottom;
        display: inline-block;
        min-width: 80pt;
        border-bottom: 1pt dotted #000;
    }

    .sig-tanggal-dots {
        display: none;
    }

    .note-text {
        font-size: 9pt;
        margin-top: 3pt;
        font-style: italic;
    }

    .cross-out {
        text-decoration: line-through;
    }
    </style>
</head>

<body>

    {{-- ═══════════════ HEADER ═══════════════ --}}
    <p class="doc-header">
        <span class="code">FR.AK.01.</span>
        <span>PERSETUJUAN ASESMEN DAN KERAHASIAAN</span>
    </p>

    {{-- ═══════════════ MAIN TABLE ═══════════════ --}}
    <table class="main-tbl">

        {{-- Intro --}}
        <tr>
            <td colspan="4" style="padding: 3pt 5pt;">
                Persetujuan Asesmen ini untuk menjamin bahwa Asesi telah diberi arahan secara rinci tentang perencanaan
                dan proses asesmen
            </td>
        </tr>

        {{-- Skema Sertifikasi --}}
        <tr>
            <td class="lbl" rowspan="2">
                Skema Sertifikasi
                @php $jenis = $asesmen->skema?->jenis_skema; @endphp
                @if($jenis === 'kkni')
                (<span>KKNI</span>/<span class="cross-out">Okupasi</span>/<span class="cross-out">Klaster</span>)
                @elseif($jenis === 'okupasi')
                (<span class="cross-out">KKNI</span>/<span>Okupasi</span>/<span class="cross-out">Klaster</span>)
                @elseif($jenis === 'klaster')
                (<span class="cross-out">KKNI</span>/<span class="cross-out">Okupasi</span>/<span>Klaster</span>)
                @else
                (KKNI/Okupasi/Klaster)
                @endif
            </td>
            <td class="sep">Judul</td>
            <td class="val" colspan="2">
                <strong>{{ $frak01->skema_judul ?? $asesmen->skema?->name ?? '-' }}</strong>
            </td>
        </tr>
        <tr>
            <td class="sep">Nomor</td>
            <td class="val" colspan="2">{{ $frak01->skema_nomor ?? $asesmen->skema?->nomor_skema ?? '-' }}</td>
        </tr>

        {{-- TUK --}}
        <tr>
            <td class="lbl" colspan="2">TUK</td>
            <td class="val" colspan="2">Sewaktu/Tempat Kerja/Mandiri *</td>
        </tr>

        {{-- Nama Asesor --}}
        <tr>
            <td class="lbl" colspan="2">Nama Asesor</td>
            <td class="val" colspan="2">{{ $frak01->nama_asesor ?? $asesmen->schedule?->asesor?->nama ?? '' }}</td>
        </tr>

        {{-- Nama Asesi --}}
        <tr>
            <td class="lbl" colspan="2">Nama Asesi</td>
            <td class="val" colspan="2">{{ $frak01->nama_asesi ?? $asesmen->full_name ?? '' }}</td>
        </tr>

        {{-- Bukti baris 1 --}}
        <tr class="cb-row">
            <td class="lbl" colspan="2" style="border-bottom: none;"></td>
            <td class="val">
                <span class="cb">{{ $frak01->bukti_verifikasi_portofolio ? 'V' : '' }}</span> Hasil Verifikasi
                Portofolio
            </td>
            <td class="val">
                <span class="cb">{{ $frak01->bukti_hasil_review_produk ? 'V' : '' }}</span> Hasil Review Produk
            </td>
        </tr>
        {{-- Bukti baris 2 --}}
        <tr class="cb-row">
            <td class="lbl" colspan="2" style="border-top: none; border-bottom: none;"></td>
            <td class="val">
                <span class="cb">{{ $frak01->bukti_observasi_langsung ? 'V' : '' }}</span> Hasil Observasi Langsung
            </td>
            <td class="val">
                <span class="cb">{{ $frak01->bukti_hasil_kegiatan_terstruktur ? 'V' : '' }}</span> Hasil Kegiatan
                Terstruktur
            </td>
        </tr>
        {{-- Bukti baris 3 — label sejajar di sini sesuai dokumen asli --}}
        <tr class="cb-row">
            <td class="lbl" colspan="2" style="border-top: none; border-bottom: none;">Bukti yang akan dikumpulkan :
            </td>
            <td class="val">
                <span class="cb">{{ $frak01->bukti_pertanyaan_lisan ? 'V' : '' }}</span> Hasil Pertanyaan Lisan
            </td>
            <td class="val">
                <span class="cb">{{ $frak01->bukti_pertanyaan_tertulis ? 'V' : '' }}</span> Hasil Pertanyaan Tertulis
            </td>
        </tr>
        {{-- Bukti baris 4 --}}
        <tr class="cb-row">
            <td class="lbl" colspan="2" style="border-top: none;"></td>
            <td class="val">
                <span class="cb">{{ $frak01->bukti_lainnya ? 'V' : '' }}</span> Lainnya
                @if($frak01->bukti_lainnya_keterangan) : {{ $frak01->bukti_lainnya_keterangan }} @else ...... @endif
            </td>
            <td class="val">
                <span class="cb">{{ $frak01->bukti_pertanyaan_wawancara ? 'V' : '' }}</span> Hasil Pertanyaan Wawancara
            </td>
        </tr>

        {{-- Pelaksanaan --}}
        <tr>
            <td class="lbl" colspan="2" rowspan="3" style="vertical-align: middle;">Pelaksanaan asesmen disepakati pada:
            </td>
            <td class="val" colspan="2">
                Hari / Tanggal &nbsp;:
                &nbsp;{{ $frak01->hari_tanggal ?? ($asesmen->schedule?->assessment_date?->translatedFormat('l, d F Y') ?? '') }}
            </td>
        </tr>
        <tr>
            <td class="val" colspan="2">
                Waktu &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                &nbsp;{{ $frak01->waktu_asesmen ?? ($asesmen->schedule ? ($asesmen->schedule->start_time . ($asesmen->schedule->end_time ? ' – ' . $asesmen->schedule->end_time : '')) : '') }}
            </td>
        </tr>
        <tr>
            <td class="val" colspan="2">
                TUK
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                &nbsp;{{ $frak01->tuk_nama ?? $asesmen->tuk?->name ?? '' }}
            </td>
        </tr>

    </table>

    {{-- ═══════════════ PERNYATAAN & TTD ═══════════════ --}}
    <table class="pernyataan-tbl">

        {{-- Asesi 1 --}}
        <tr>
            <td>
                <span class="pernyataan-label">Asesi:</span><br>
                Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor.
            </td>
        </tr>

        {{-- Asesor --}}
        <tr>
            <td>
                <span class="pernyataan-label">Asesor:</span><br>
                Menyatakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor
                dalam pekerjaan <em>Asesmen</em> kepada siapapun atau organisasi apapun selain kepada pihak yang
                berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.
            </td>
        </tr>

        {{-- Asesi 2 --}}
        <tr>
            <td>
                <span class="pernyataan-label">Asesi :</span><br>
                Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya
                <strong>digunakan</strong> untuk pengembangan profesional dan hanya dapat diakses oleh orang tertentu
                saja.
            </td>
        </tr>

        {{-- Tanda Tangan --}}
        <tr>
            <td class="sig-cell">

                {{-- TTD Asesor — urutan: asesor dulu --}}
                <div class="sig-row">
                    <span class="sig-label">Tanda tangan Asesor :</span>
                    <span class="sig-line-wrap">
                        @if($frak01->ttd_asesor)
                        <img src="{{ $frak01->ttd_asesor_image }}" class="sig-img-overlay" alt="TTD Asesor">
                        @endif
                        <span
                            class="sig-name-below">{{ $frak01->nama_asesor ?? $asesmen->schedule?->asesor?->nama ?? '' }}</span>
                    </span>
                    <span class="sig-label">Tanggal :&nbsp;</span>
                    <span class="sig-tanggal">
                        @if($frak01->tanggal_ttd_asesor){{ $frak01->tanggal_ttd_asesor->translatedFormat('d-m-Y') }}@endif
                    </span>
                </div>

                {{-- TTD Asesi --}}
                <div class="sig-row">
                    <span class="sig-label">Tanda tangan Asesi &nbsp;&nbsp;&nbsp;:</span>
                    <span class="sig-line-wrap">
                        @if($frak01->ttd_asesi)
                        <img src="{{ $frak01->ttd_asesi_image }}" class="sig-img-overlay" alt="TTD Asesi">
                        @endif
                        <span class="sig-name-below">{{ $frak01->nama_asesi ?? $asesmen->full_name ?? '' }}</span>
                    </span>
                    <span class="sig-label">Tanggal :&nbsp;</span>
                    <span class="sig-tanggal">
                        @if($frak01->tanggal_ttd_asesi){{ $frak01->tanggal_ttd_asesi->translatedFormat('d-m-Y') }}@endif
                    </span>
                </div>

            </td>
        </tr>
    </table>

    <p class="note-text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;* Coret yang tidak perlu</p>

</body>

</html>