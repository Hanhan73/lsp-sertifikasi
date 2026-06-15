<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
    @page {
        margin: 0;
        size: A4 portrait;
    }

    * {
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        color: #000;
        margin: 0;
        padding: 2cm 2.5cm 2cm 2.5cm;
        line-height: 1.5;
    }

    .kop-garis {
        border-top: 3pt solid #000;
        border-bottom: 1pt solid #000;
        height: 4pt;
        margin-bottom: 14pt;
    }

    .judul-wrap {
        text-align: center;
        margin-bottom: 12pt;
    }

    .judul-wrap p {
        margin: 0;
        padding: 0;
    }

    .lbl-sk {
        font-size: 12pt;
        font-weight: bold;
    }

    .lbl-tentang {
        font-size: 11pt;
        font-weight: bold;
        margin-top: 6pt;
        line-height: 1.5;
    }

    .body-text {
        text-align: justify;
        margin-bottom: 8pt;
    }

    .dasar-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10pt;
    }

    .dasar-table td {
        border: none;
        vertical-align: top;
        padding: 1pt 0;
    }

    .dasar-no {
        width: 20pt;
    }

    /* ── TABEL PESERTA ── */
    .tbl-peserta {
        width: 100%;
        border-collapse: collapse;
        margin: 10pt 0 12pt 0;
        font-size: 10.5pt;
    }

    .tbl-peserta th {
        border: 1pt solid #000;
        background: #f0f0f0;
        text-align: center;
        padding: 4pt 6pt;
        font-weight: bold;
    }

    .tbl-peserta td {
        border: 1pt solid #000;
        padding: 3pt 6pt;
        vertical-align: middle;
    }

    .tbl-peserta td.tc {
        text-align: center;
    }

    .tbl-peserta td.asesor-cell {
        text-align: center;
        vertical-align: middle;
        font-size: 10pt;
        line-height: 1.4;
    }

    .penutup {
        text-align: justify;
        margin-bottom: 10pt;
    }

    .tembusan {
        margin-top: 20pt;
        font-size: 10.5pt;
    }
    </style>
</head>

<body>

    {{-- ══ KOP SURAT ══ --}}
    @php
    $bnspPath = public_path('images/bnsp.png');
    $lspPath  = public_path('images/icon-lsp.png');
    $bnspSrc  = file_exists($bnspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bnspPath)) : '';
    $lspSrc   = file_exists($lspPath)  ? 'data:image/png;base64,' . base64_encode(file_get_contents($lspPath))  : '';
    @endphp

    <table style="width:100%; border:none; border-collapse:collapse;">
        <tr>
            <td style="width:25%; text-align:left; vertical-align:middle;">
                @if($bnspSrc)
                <img src="{{ $bnspSrc }}" style="height:20pt; width:auto;" alt="BNSP">
                @endif
            </td>
            <td style="width:50%; text-align:center; vertical-align:middle;">
                @if($lspSrc)
                <img src="{{ $lspSrc }}" style="height:95pt; width:auto;" alt="LSP-KAP">
                @endif
            </td>
            <td style="width:25%;"></td>
        </tr>
    </table>

    <div class="kop-garis"></div>

    {{-- ══ JUDUL SK ══ --}}
    @php
    $first    = $first ?? $schedules->first()?->asesmens->first();
    $skema    = $first?->skema;
    $tuk      = $first?->tuk;

    $tanggalList = $schedules->map(fn($s) => \Carbon\Carbon::parse($s->assessment_date));
    $tanggalMin  = $tanggalList->min();
    $tanggalMax  = $tanggalList->max();
    $tanggalStr  = $tanggalMin->eq($tanggalMax)
        ? $tanggalMin->translatedFormat('d F Y')
        : $tanggalMin->translatedFormat('d') . '-' . $tanggalMax->translatedFormat('d F Y');
    @endphp

    <div class="judul-wrap">
        <p class="lbl-sk">SURAT KEPUTUSAN</p>
        <p class="lbl-sk">DIREKTUR LSP KOMPETENSI ADMINISTRASI PERKANTORAN</p>
        <p style="margin:4pt 0 0 0;">Nomor : {{ $skUjikom->nomor_sk }}</p>
        <p class="lbl-tentang" style="margin-top:12pt;">
            TENTANG<br>
            PENETAPAN HASIL PENILAIAN UJI KOMPETENSI UNTUK<br>
            SKEMA {{ strtoupper($skema?->name ?? '-') }}<br>
            DI {{ strtoupper($tuk?->name ?? '-') }} PADA TANGGAL {{ strtoupper($tanggalStr) }}
        </p>
    </div>

    {{-- ══ PEMBUKAAN ══ --}}
    <p class="body-text">
        Direktur LSP Kompetensi Administrasi Perkantoran berdasarkan:
    </p>

    <table class="dasar-table">
        <tr>
            <td class="dasar-no">1.</td>
            <td>
                Hasil pleno Panitia Teknis uji Kompetensi LSP Kompetensi Administrasi Perkantoran,
                tanggal {{ $skUjikom->tanggal_pleno->translatedFormat('d F Y') }}.
            </td>
        </tr>
        <tr>
            <td class="dasar-no">2.</td>
            <td>
                Bukti-bukti pendukung yang dikumpulkan dalam proses asesmen peserta uji kompetensi
                Skema {{ $skema?->name ?? '-' }} yang dilaksanakan pada tanggal {{ $tanggalStr }}
                bertempat di {{ $tuk?->name ?? '-' }}.
            </td>
        </tr>
        <tr>
            <td class="dasar-no">3.</td>
            <td>
                Rekomendasi keputusan penilaian dari asesor kompetensi yang ditugaskan untuk
                melaksanakan penilaian sesuai dengan skema sertifikasi yang diujikan.
            </td>
        </tr>
    </table>

    <p class="body-text">
        Dengan ini memutuskan, bahwa peserta uji kompetensi dengan daftar nama sebagai berikut:
    </p>

    {{-- ══ TABEL PESERTA — dengan kolom Asesor (rowspan per grup) ══ --}}
    @php
    /*
     * $pesertaPerAsesor = array of:
     *   ['asesor' => Asesor|null, 'asesis' => Collection, 'count' => int]
     *
     * Jika variable tidak tersedia (misal dari manajer flow lama), fallback ke loop biasa tanpa asesor.
     */
    $hasAsesorData = isset($pesertaPerAsesor) && !empty($pesertaPerAsesor);
    $noUrut = 1;
    @endphp

    <table class="tbl-peserta">
        <thead>
            <tr>
                <th style="width:28pt;">No</th>
                <th>Nama Asesi</th>
                <th style="width:150pt;">Nama Asesor</th>
                <th style="width:80pt;">Hasil Ujikom</th>
            </tr>
        </thead>
        <tbody>
            @if($hasAsesorData)
                @foreach($pesertaPerAsesor as $grup)
                    @php
                        $asesor    = $grup['asesor'];
                        $asesis    = $grup['asesis'];
                        $rowspan   = $grup['count'];
                        $firstRow  = true;
                        // Format asesor cell: nama + no reg + tahun (jika ada)
                        $asesorNama   = $asesor?->nama ?? '-';
                        $asesorRegMet = $asesor?->no_reg_met ?? '';
                        // no_reg_met format: "000.010993 2018" — tampilkan apa adanya
                    @endphp
                    @foreach($asesis as $asesi)
                    <tr>
                        <td class="tc">{{ $noUrut++ }}.</td>
                        <td>{{ $asesi->full_name }}</td>
                        @if($firstRow)
                        <td class="asesor-cell" rowspan="{{ $rowspan }}">
                            {{ $asesorNama }}
                            @if($asesorRegMet)
                            <br>{{ $asesorRegMet }}
                            @endif
                        </td>
                        @php $firstRow = false; @endphp
                        @endif
                        <td class="tc">K</td>
                    </tr>
                    @endforeach
                @endforeach
            @else
                {{-- Fallback: tanpa kolom asesor (kompatibel dengan view lama) --}}
                @foreach($pesertaKompeten as $i => $asesi)
                <tr>
                    <td class="tc">{{ $i + 1 }}.</td>
                    <td>{{ $asesi->full_name }}</td>
                    <td class="tc">-</td>
                    <td class="tc">K</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    {{-- ══ PENUTUP ══ --}}
    <p class="penutup">
        Menetapkan bahwa peserta Uji Kompetensi dinyatakan Kompeten dan Belum Kompeten berdasarkan
        hasil asesmen terhadap unit-unit kompetensi yang diujikan, sesuai dengan skema yang tercantum
        di atas dan sebagaimana tercantum dalam daftar nama peserta pada Surat Keputusan ini.
    </p>

    {{-- ══ TTD ══ --}}
    @php
    $isPreview = $preview ?? false;
    $sigPath   = storage_path('app/private/direktur/ttd.png');
    $sigSrc    = (!$isPreview && file_exists($sigPath))
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigPath))
        : '';
    @endphp

    @if($isPreview)
    <div style="position:fixed; top:45%; left:50%; transform:translate(-50%,-50%) rotate(-30deg);
                font-size:80pt; font-weight:bold; color:rgba(180,0,0,0.07);
                letter-spacing:6pt; white-space:nowrap;">
        DRAFT
    </div>
    @endif

    <table style="width:100%; border:none; border-collapse:collapse; margin-top:10pt;">
        <tr>
            <td style="width:40%; border:none;"></td>
            <td style="width:60%; border:none; vertical-align:top; font-size:11pt; line-height:1.6;">
                <p style="text-align:left; margin-bottom:0; padding-left:60pt;">
                    Dikeluarkan di &nbsp;: {{ $skUjikom->tempat_dikeluarkan }}<br>
                    Pada tanggal &nbsp;&nbsp;&nbsp;:
                    {{ $skUjikom->approved_at?->translatedFormat('d F Y') ?? \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Direktur LSP-KAP,
                </p>
                @if($sigSrc)
                <img src="{{ $sigSrc }}" style="width:100%; height:auto; display:block; margin-top:-34pt;" alt="TTD">
                @else
                <div style="height:70pt;"></div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ TEMBUSAN ══ --}}
    <div class="tembusan">
        <p style="margin:0;">Tembusan:</p>
        <p style="margin:2pt 0 0 0;">Yth. Dewan Pengarah LSP Kompetensi Administrasi Perkantoran</p>
    </div>

</body>
</html>