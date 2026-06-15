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
        padding: 1.5cm 2cm 1.5cm 2cm;
        line-height: 1.4;
    }

    .kop-garis {
        border-top: 3pt solid #000;
        border-bottom: 1pt solid #000;
        height: 4pt;
        margin-bottom: 12pt;
    }

    .doc-title {
        text-align: center;
        font-size: 13pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 2pt;
    }

    .doc-subtitle {
        text-align: center;
        font-size: 11pt;
        font-weight: bold;
        margin-bottom: 10pt;
    }

    .opening {
        text-align: justify;
        margin-bottom: 8pt;
        font-size: 10.5pt;
    }

    /* ── TABEL PESERTA ── */
    .tabel-peserta {
        width: 100%;
        border-collapse: collapse;
        margin: 8pt 0 10pt 0;
        font-size: 10pt;
    }

    .tabel-peserta th {
        border: 1pt solid #000;
        background: #f0f0f0;
        text-align: center;
        padding: 4pt 5pt;
        font-weight: bold;
    }

    .tabel-peserta td {
        border: 1pt solid #000;
        padding: 3pt 5pt;
        vertical-align: middle;
    }

    .td-center {
        text-align: center;
    }

    .td-asesor {
        text-align: center;
        vertical-align: middle;
        font-size: 10pt;
        line-height: 1.4;
    }

    .closing {
        text-align: justify;
        margin-top: 8pt;
        margin-bottom: 6pt;
        font-size: 10.5pt;
    }

    /* ── TTD ── */
    .ttd-wrap {
        width: 100%;
        border: none;
        border-collapse: collapse;
        margin-top: 12pt;
        font-size: 10.5pt;
    }

    .ttd-wrap td {
        border: none;
        text-align: center;
        vertical-align: top;
    }

    .ttd-sig {
        height: 60pt;
    }

    .ttd-sig img {
        max-height: 60pt;
        max-width: 130pt;
    }

    .ttd-name {
        font-weight: bold;
        text-decoration: underline;
    }

    .ttd-reg {
        font-size: 9.5pt;
        margin-top: 1pt;
    }
    </style>
</head>

<body>

    {{-- ══ KOP SURAT ══ --}}
    @php
    $bnspPath = public_path('images/bnsp.png');
    $lspPath = public_path('images/icon-lsp.png');
    $bnspSrc = file_exists($bnspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bnspPath)) : '';
    $lspSrc = file_exists($lspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($lspPath)) : '';
    @endphp

    <table style="width:100%; border:none; border-collapse:collapse;">
        <tr>
            <td style="width:25%; text-align:left; vertical-align:middle;">
                @if($bnspSrc)<img src="{{ $bnspSrc }}" style="height:20pt; width:auto;" alt="BNSP">@endif
            </td>
            <td style="width:50%; text-align:center; vertical-align:middle;">
                @if($lspSrc)<img src="{{ $lspSrc }}" style="height:90pt; width:auto;" alt="LSP-KAP">@endif
            </td>
            <td style="width:25%;"></td>
        </tr>
    </table>

    <div class="kop-garis"></div>

    {{-- ══ JUDUL ══ --}}
    @php
    $skema = $first?->skema;
    $tuk = $first?->tuk;

    $semuaTanggal = $jadwalData->map(fn($d) => \Carbon\Carbon::parse($d['schedule']->assessment_date));
    $tglMin = $semuaTanggal->min();
    $tglMax = $semuaTanggal->max();
    $tglStr = $tglMin->eq($tglMax)
    ? $tglMin->translatedFormat('d F Y')
    : $tglMin->translatedFormat('d') . ' dan ' . $tglMax->translatedFormat('d F Y');
    $totalPeserta = $totalK + $totalBK;
    @endphp

    <div class="doc-title">BERITA ACARA ASESMEN/UJI KOMPETENSI SERTIFIKASI</div>
    <div class="doc-subtitle">LSP KOMPETENSI ADMINISTRASI PERKANTORAN</div>

    {{-- ══ PEMBUKA ══ --}}
    <div class="opening">
        Pada tanggal {{ $tglStr }}, bertempat di {{ $tuk?->name ?? '-' }} telah dilakukan
        Uji Kompetensi Keahlian Administrasi Perkantoran untuk Skema
        <strong>{{ $skema?->name ?? '-' }}</strong>
        yang diikuti sebanyak {{ $totalPeserta }} orang peserta dengan penjelasan sebagai berikut:
    </div>

    {{-- ══ TABEL PESERTA — 4 kolom, asesor rowspan per grup ══ --}}
    @php
    // Bangun array baris dengan rowspan asesor
    // Struktur: kumpulkan semua peserta dari semua jadwal, kelompokkan per asesor
    $semuaBaris = collect();
    $noUrut = 1;

    foreach ($jadwalData as $item) {
    $schedule = $item['schedule'];
    $rekMap = $item['rekMap'];
    $asesor = $schedule->asesor;
    $asesmens = $item['asesmens'];

    $firstOfGroup = true;
    $groupCount = $asesmens->count();

    foreach ($asesmens as $asesmen) {
    $semuaBaris->push([
    'no' => $noUrut++,
    'nama' => $asesmen->full_name,
    'asesor' => $asesor,
    'rowspan' => $firstOfGroup ? $groupCount : 0,
    'show_asesor' => $firstOfGroup,
    'rek' => $rekMap[$asesmen->id] ?? null,
    ]);
    $firstOfGroup = false;
    }
    }
    @endphp

    <table class="tabel-peserta">
        <thead>
            <tr>
                <th style="width:28pt;">NO</th>
                <th>NAMA ASESI</th>
                <th style="width:150pt;">NAMA ASESOR</th>
                <th style="width:80pt;">HASIL UJIKOM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($semuaBaris as $baris)
            <tr>
                <td class="td-center">{{ $baris['no'] }}</td>
                <td>{{ $baris['nama'] }}</td>
                @if($baris['show_asesor'])
                <td class="td-asesor" rowspan="{{ $baris['rowspan'] }}">
                    @if($baris['asesor'])
                    {{ $baris['asesor']->nama }}
                    @if($baris['asesor']->no_reg_met)
                    <br>{{ $baris['asesor']->no_reg_met }}
                    @endif
                    @else
                    -
                    @endif
                </td>
                @endif
                <td class="td-center">{{ $baris['rek'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ══ PENUTUP ══ --}}
    <div class="closing">
        Demikian berita acara asesmen/uji kompetensi ini dibuat sebagai pengambil keputusan oleh LSP-KAP.
    </div>

    {{-- ══ TTD — SEMUA ASESOR BERDAMPINGAN ══ --}}
    @php
    $asesorUnik = $jadwalData
    ->map(fn($d) => $d['schedule']->asesor)
    ->filter()
    ->unique('id')
    ->values();
    $colPct = $asesorUnik->count() > 0 ? round(100 / $asesorUnik->count()) : 100;
    @endphp

    <p style="text-align:right; margin-top:10pt; margin-bottom:4pt; font-size:10.5pt;">
        Bandung, {{ $tanggalSurat->translatedFormat('d F Y') }}
    </p>

    <table class="ttd-wrap">
        <tr>
            @foreach($asesorUnik as $idx => $asesor)
            <td style="width:{{ $colPct }}%;">
                <div>Asesor {{ $asesorUnik->count() > 1 ? ($idx + 1) : '' }}</div>
                <div class="ttd-sig">
                    @php $sigUri = $asesor?->user?->signature_image ?? null; @endphp
                    @if($sigUri)
                    <img src="{{ $sigUri }}" alt="TTD">
                    @endif
                </div>
                <div><span class="ttd-name">{{ $asesor->nama }}</span></div>
                @if($asesor->no_reg_met)
                <div class="ttd-reg">{{ $asesor->no_reg_met }}</div>
                @endif
            </td>
            @endforeach
        </tr>
    </table>

</body>

</html>