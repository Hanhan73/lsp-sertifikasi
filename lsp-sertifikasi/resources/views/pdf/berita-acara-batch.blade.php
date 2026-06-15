<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
    @page {
        margin: 0;
        size: A4 portrait;
    }
    * { box-sizing: border-box; }
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
        font-size: 10.5pt;
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
        padding: 3pt 5pt;
        font-weight: bold;
    }
    .tabel-peserta td {
        border: 1pt solid #000;
        padding: 2.5pt 5pt;
        vertical-align: middle;
    }
    .td-center { text-align: center; }

    .section-jadwal {
        margin-top: 10pt;
        margin-bottom: 4pt;
        font-size: 10pt;
        font-weight: bold;
    }

    .closing {
        text-align: justify;
        margin-top: 8pt;
        margin-bottom: 6pt;
        font-size: 10.5pt;
    }

    /* ── TTD ── */
    .ttd-table {
        width: 100%;
        border: none;
        border-collapse: collapse;
        margin-top: 10pt;
    }
    .ttd-table td { border: none; vertical-align: top; }
    .ttd-left  { width: 50%; }
    .ttd-right { width: 50%; text-align: center; }
    .ttd-sig   { height: 55pt; }
    .ttd-sig img { max-height: 55pt; max-width: 120pt; }
    .ttd-name  { font-weight: bold; text-decoration: underline; }
    .ttd-reg   { font-size: 9.5pt; margin-top: 1pt; }

    /* Separator antar jadwal */
    .jadwal-separator {
        border-top: 1pt dashed #999;
        margin: 12pt 0 8pt 0;
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
    $tuk   = $first?->tuk;
    @endphp

    <div class="doc-title">Berita Acara Asesmen/Uji Kompetensi</div>
    <div class="doc-subtitle">
        Pelaksanaan Sertifikasi Tanggal
        {{ $tanggalTerakhir->translatedFormat('d F Y') }}<br>
        LSP KOMPETENSI ADMINISTRASI PERKANTORAN
    </div>

    {{-- ══ PEMBUKA ══ --}}
    @php
    $totalPeserta = $totalK + $totalBK;
    // Kumpulkan range tanggal dari semua jadwal
    $semuaTanggal = $jadwalData->map(fn($d) => \Carbon\Carbon::parse($d['schedule']->assessment_date));
    $tglMin = $semuaTanggal->min();
    $tglMax = $semuaTanggal->max();
    $tglStr = $tglMin->eq($tglMax)
        ? $tglMin->translatedFormat('l, d F Y')
        : $tglMin->translatedFormat('d F Y') . ' s.d. ' . $tglMax->translatedFormat('d F Y');
    @endphp

    <div class="opening">
        Pada {{ $tglStr }}, bertempat di {{ $tuk?->name ?? '-' }} telah dilakukan
        Uji Kompetensi Keahlian Administrasi Perkantoran untuk Skema
        <strong>{{ $skema?->name ?? '-' }}</strong>
        yang diikuti sebanyak {{ $totalPeserta }} orang peserta dengan penjelasan sebagai berikut:
    </div>

    {{-- ══ TABEL PESERTA PER JADWAL ══ --}}
    @php $noUrut = 1; @endphp

    @foreach($jadwalData as $idx => $item)
    @php
        $schedule = $item['schedule'];
        $rekMap   = $item['rekMap'];
        $asesmens = $item['asesmens'];
        $asesor   = $schedule->asesor;
        $tglJadwal = \Carbon\Carbon::parse($schedule->assessment_date)->translatedFormat('d F Y');
    @endphp

    @if($idx > 0)
    <div class="jadwal-separator"></div>
    @endif

    <div class="section-jadwal">
        Jadwal {{ $idx + 1 }}: {{ $tglJadwal }}
        &nbsp;|&nbsp; Asesor: {{ $asesor?->nama ?? '-' }}
        @if($asesor?->no_reg_met)
        ({{ $asesor->no_reg_met }})
        @endif
    </div>

    <table class="tabel-peserta">
        <thead>
            <tr>
                <th style="width:18pt;" rowspan="2">No</th>
                <th rowspan="2">Nama</th>
                <th style="width:130pt;" rowspan="2">Organisasi</th>
                <th colspan="2" style="width:60pt;">Rekomendasi</th>
            </tr>
            <tr>
                <th style="width:35pt;">K</th>
                <th style="width:35pt;">BK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asesmens as $asesmen)
            @php $rek = $rekMap[$asesmen->id] ?? null; @endphp
            <tr>
                <td class="td-center">{{ $noUrut++ }}.</td>
                <td>{{ $asesmen->full_name }}</td>
                <td class="td-center">{{ $asesmen->institution ?? $tuk?->name ?? '-' }}</td>
                <td class="td-center" style="font-size:13pt;">{{ $rek === 'K'  ? 'V' : '-' }}</td>
                <td class="td-center" style="font-size:13pt;">{{ $rek === 'BK' ? 'V' : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

    {{-- ══ PENUTUP ══ --}}
    <div class="closing">
        Demikian berita acara asesmen/uji kompetensi ini dibuat sebagai pengambil keputusan oleh LSP-KAP.
    </div>

    {{-- ══ TTD — SATU PER ASESOR ══ --}}
    @php
    // Kumpulkan asesor unik dari semua jadwal
    $asesorUnik = $jadwalData
        ->map(fn($d) => $d['schedule']->asesor)
        ->filter()
        ->unique('id')
        ->values();
    @endphp

    <table style="width:100%; border:none; border-collapse:collapse; margin-top:10pt;">
        <tr>
            <td style="border:none; width:40%;"></td>
            <td style="border:none; width:60%; text-align:center; vertical-align:top; font-size:10.5pt; line-height:1.6;">
                <div>Bandung, {{ $tanggalSurat->translatedFormat('d F Y') }}</div>
                @if($asesorUnik->count() === 1)
                    @php $asesor = $asesorUnik->first(); @endphp
                    <div>Asesor</div>
                    <div class="ttd-sig">
                        @php $sigUri = $asesor?->user?->signature_image ?? null; @endphp
                        @if($sigUri)
                        <img src="{{ $sigUri }}" alt="TTD">
                        @endif
                    </div>
                    <div><span class="ttd-name">{{ $asesor?->nama ?? '______________________' }}</span></div>
                    @if($asesor?->no_reg_met)
                    <div class="ttd-reg">MET. {{ $asesor->no_reg_met }}</div>
                    @endif
                @else
                    {{-- Multiple asesor: tampilkan dalam satu baris --}}
                    <div>Para Asesor</div>
                    <table style="width:100%; border:none; border-collapse:collapse; margin-top:6pt;">
                        <tr>
                        @foreach($asesorUnik as $asesor)
                        <td style="border:none; text-align:center; vertical-align:top; width:{{ round(100/$asesorUnik->count()) }}%;">
                            <div class="ttd-sig">
                                @php $sigUri = $asesor?->user?->signature_image ?? null; @endphp
                                @if($sigUri)
                                <img src="{{ $sigUri }}" alt="TTD">
                                @endif
                            </div>
                            <div><span class="ttd-name" style="font-size:10pt;">{{ $asesor->nama }}</span></div>
                            @if($asesor->no_reg_met)
                            <div class="ttd-reg">MET. {{ $asesor->no_reg_met }}</div>
                            @endif
                        </td>
                        @endforeach
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
