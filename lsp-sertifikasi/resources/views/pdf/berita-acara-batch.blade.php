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

        /* Border normal untuk semua td */
        .tabel-peserta td {
            border: 1pt solid #000;
            padding: 3pt 5pt;
            vertical-align: middle;
        }

        /* Kolom asesor — baris non-pertama: hilangkan border atas & bawah,
       tapi pertahankan border kiri & kanan agar terlihat seperti rowspan */
        .td-asesor-empty {
            border-left: 1pt solid #000;
            border-right: 1pt solid #000;
            border-top: 0;
            border-bottom: 0;
            padding: 3pt 5pt;
            vertical-align: middle;
        }

        /* Baris terakhir per grup: border bawah dikembalikan */
        .td-asesor-last {
            border-left: 1pt solid #000;
            border-right: 1pt solid #000;
            border-top: 0;
            border-bottom: 1pt solid #000;
            padding: 3pt 5pt;
            vertical-align: middle;
        }

        /* Baris pertama per grup: border atas dikembalikan */
        .td-asesor-first {
            border: 1pt solid #000;
            padding: 3pt 5pt;
            vertical-align: middle;
            text-align: center;
            font-size: 9.5pt;
            line-height: 1.3;
        }

        .td-center {
            text-align: center;
        }

        .closing {
            text-align: justify;
            margin-top: 8pt;
            margin-bottom: 6pt;
            font-size: 10.5pt;
        }

        /* ── TTD ── */
        .ttd-section {
            margin-top: 12pt;
            font-size: 10.5pt;
        }

        .ttd-tanggal {
            text-align: right;
            margin-bottom: 6pt;
        }

        .ttd-asesor-wrap {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .ttd-asesor-wrap td {
            border: none;
            text-align: center;
            vertical-align: top;
            padding: 0 4pt;
        }

        .ttd-sig {
            height: 60pt;
            line-height: 60pt;
        }

        .ttd-sig img {
            max-height: 60pt;
            max-width: 130pt;
            vertical-align: middle;
        }

        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
            display: block;
            margin-top: 4pt;
        }

        .ttd-reg {
            font-size: 9.5pt;
            display: block;
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

    // Bangun baris dengan info grup asesor (untuk simulasi rowspan)
    $baris = collect();
    $noUrut = 1;
    foreach ($jadwalData as $item) {
    $schedule = $item['schedule'];
    $rekMap = $item['rekMap'];
    $asesor = $schedule->asesor;
    $asesmens = $item['asesmens'];
    $groupCount = $asesmens->count();
    $idx = 0;

    foreach ($asesmens as $asesmen) {
    $isFirst = $idx === 0;
    $isLast = $idx === $groupCount - 1;
    $baris->push([
    'no' => $noUrut++,
    'nama' => $asesmen->full_name,
    'asesor' => $asesor,
    'is_first' => $isFirst,
    'is_last' => $isLast,
    'rek' => $rekMap[$asesmen->id] ?? null,
    ]);
    $idx++;
    }
    }
    @endphp

    <div class="doc-title">BERITA ACARA ASESMEN/UJI KOMPETENSI SERTIFIKASI</div>
    <div class="doc-subtitle">LSP KOMPETENSI ADMINISTRASI PERKANTORAN</div>

    <div class="opening">
        Pada tanggal {{ $tglStr }}, bertempat di {{ $tuk?->name ?? '-' }} telah dilakukan
        Uji Kompetensi Keahlian Administrasi Perkantoran untuk Skema
        <strong>{{ $skema?->name ?? '-' }}</strong>
        yang diikuti sebanyak {{ $totalPeserta }} orang peserta dengan penjelasan sebagai berikut:
    </div>

    {{-- ══ TABEL PESERTA ══ --}}
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
            @foreach($baris as $b)
            <tr>
                <td class="td-center">{{ $b['no'] }}</td>
                <td>{{ $b['nama'] }}</td>

                @if($b['is_first'])
                {{-- Baris pertama grup: tampilkan nama asesor, border penuh --}}
                <td class="td-asesor-first">
                    @if($b['asesor'])
                    {{ $b['asesor']->nama }}
                    @if($b['asesor']->no_reg_met)
                    <br>{{ $b['asesor']->no_reg_met }}
                    @endif
                    @endif
                </td>

                @elseif($b['is_last'])
                {{-- Baris terakhir grup: kosong, border bawah dikembalikan --}}
                <td class="td-asesor-last"></td>

                @else
                {{-- Baris tengah: kosong, tanpa border atas & bawah --}}
                <td class="td-asesor-empty"></td>
                @endif

                <td class="td-center">{{ $b['rek'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="closing">
        Demikian berita acara asesmen/uji kompetensi ini dibuat sebagai pengambil keputusan oleh LSP-KAP.
    </div>

    {{-- ══ TTD — rata kanan, semua asesor berdampingan ══ --}}
    @php
    $asesorUnik = $jadwalData
    ->map(fn($d) => $d['schedule']->asesor)
    ->filter()
    ->unique('id')
    ->values();
    $ttdWidth = min(60, $asesorUnik->count() * 33);
    @endphp

    <div class="ttd-section">
        <div class="ttd-tanggal">Bandung, {{ $tanggalSurat->translatedFormat('d F Y') }}</div>
        <table style="width:100%; border:none; border-collapse:collapse;">
            <tr>
                <td style="border:none; width:{{ 100 - $ttdWidth }}%;"></td>
                <td style="border:none; width:{{ $ttdWidth }}%; vertical-align:top;">
                    <table class="ttd-asesor-wrap">
                        <tr>
                            @foreach($asesorUnik as $idx => $asesor)
                            <td>
                                <div>Asesor{{ $asesorUnik->count() > 1 ? ' ' . ($idx + 1) : '' }}</div>
                                <div class="ttd-sig">
                                    @php $sigUri = $asesor?->user?->signature_image ?? null; @endphp
                                    @if($sigUri)
                                    <img src="{{ $sigUri }}" alt="TTD">
                                    @endif
                                </div>
                                <span class="ttd-name">{{ $asesor->nama }}</span>
                                @if($asesor->no_reg_met)
                                <span class="ttd-reg">{{ $asesor->no_reg_met }}</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>