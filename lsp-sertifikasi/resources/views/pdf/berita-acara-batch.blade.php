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

        /*
     * Sel normal: border kiri, kanan, bawah hitam. Border atas diatur per-baris via inline.
     * Kita TIDAK set border-top di sini — akan diatur inline per baris.
     */
        .tabel-peserta td {
            border-left: 1pt solid #000;
            border-right: 1pt solid #000;
            border-bottom: 1pt solid #000;
            border-top: 1pt solid #000;
            padding: 3pt 5pt;
            vertical-align: middle;
        }

        .td-center {
            text-align: center;
        }

        .ttd-section {
            margin-top: 12pt;
            font-size: 10.5pt;
        }

        .ttd-tanggal {
            text-align: right;
            margin-bottom: 6pt;
        }

        .ttd-wrap {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .ttd-wrap td {
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

    @php
    $bnspPath = public_path('images/bnsp.png');
    $lspPath = public_path('images/icon-lsp.png');
    $bnspSrc = file_exists($bnspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bnspPath)) : '';
    $lspSrc = file_exists($lspPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($lspPath)) : '';
    @endphp

    <table style="width:100%; border:none; border-collapse:collapse;">
        <tr>
            <td style="width:25%; text-align:left; vertical-align:middle; border:none;">
                @if($bnspSrc)<img src="{{ $bnspSrc }}" style="height:20pt; width:auto;" alt="BNSP">@endif
            </td>
            <td style="width:50%; text-align:center; vertical-align:middle; border:none;">
                @if($lspSrc)<img src="{{ $lspSrc }}" style="height:90pt; width:auto;" alt="LSP-KAP">@endif
            </td>
            <td style="width:25%; border:none;"></td>
        </tr>
    </table>

    <div class="kop-garis"></div>

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

    /**
    * Strategi border kolom ASESOR:
    *
    * Yang kita kontrol hanya kolom asesor (td ke-3).
    * Kolom lain (No, Nama, Hasil) tetap border normal semua sisi.
    *
    * Kolom asesor:
    * border-left : hitam (selalu)
    * border-right : hitam (selalu)
    * border-top : hitam di baris PERTAMA grup, PUTIH di baris berikutnya
    * border-bottom: hitam di baris TERAKHIR grup, PUTIH di baris sebelumnya
    *
    * Page break di DomPDF:
    * Saat baris terpotong di halaman, DomPDF menggambar border-bottom dari <TR>
        * Caranya: kita set border-bottom pada
    <TR> = hitam hanya untuk baris tengah grup
        * Tapi DomPDF tidak selalu render TR border...
        *
        * Cara yang PASTI bekerja di DomPDF:
        * Sel asesor di tengah grup: border-top & border-bottom PUTIH (tidak kelihatan)
        * Tapi ketika DomPDF page-break, dia memotong sel dan menggambar sisi terbuka
        * dengan border yang ada. Karena border putih = tidak kelihatan, kita tidak bisa
        * mengandalkan ini.
        *
        * Solusi FINAL yang benar-benar bekerja:
        * Jadikan border-bottom kolom asesor SELALU HITAM.
        * Tapi border-top PUTIH di baris non-pertama.
        * Dengan border-collapse:collapse, border-bottom hitam baris sebelumnya
        * "menang" atas border-top putih baris ini → tidak ada garis DOBEL.
        * Yang tersisa: garis bawah tiap baris hitam, garis atas hanya di baris pertama grup.
        * Efek visual: kolom asesor punya garis di bawah setiap baris (termasuk page break),
        * tapi tidak ada garis di antara baris satu asesor (karena border-top putih kalah).
        *
        * WAIT — dengan border-collapse, "kalah menang" border:
        * border-top putih (#fff) vs border-bottom hitam (#000) dari baris di atasnya
        * → yang MENANG adalah yang lebih "visible" = hitam
        * Jadi ini tidak akan bekerja — tetap kelihatan garis di tengah.
        *
        * Solusi BENAR-BENAR FINAL:
        * Gunakan border-collapse:collapse DAN override dengan !important tidak bisa.
        * Satu-satunya cara: TIDAK set border-bottom pada sel asesor baris tengah,
        * dan TIDAK set border-top pada sel asesor baris non-pertama.
        * Tapi set border-bottom pada
    <TR> itu sendiri — DomPDF render TR border
        * di page break meski sel tidak punya border.
        *
        * Implementasi:
        * - td asesor tengah: border-top:none; border-bottom:none (hanya L/R)
        * - tr tengah: border-bottom: 1pt solid #000 → DomPDF pakai ini saat page break
        */
        $baris = collect();
        $noUrut = 1;
        foreach ($jadwalData as $item) {
        $schedule = $item['schedule'];
        $rekMap = $item['rekMap'];
        $asesor = $schedule->asesor;
        $asesmens = $item['asesmens'];
        $grupTotal = $asesmens->count();
        $idx = 0;

        foreach ($asesmens as $asesmen) {
        $isFirst = $idx === 0;
        $isLast = $idx === $grupTotal - 1;

        $baris->push([
        'no' => $noUrut++,
        'nama' => $asesmen->full_name,
        'asesor' => $isFirst ? $asesor : null,
        'is_first'=> $isFirst,
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
                @php
                $isMid = !$b['is_first'] && !$b['is_last']; // tengah grup
                $isFirstOnly = $b['is_first'] && $b['is_last']; // satu-satunya di grup

                // Border asesor td:
                // - Baris pertama grup: semua hitam (normal)
                // - Baris terakhir grup: L/R hitam, top putih, bottom hitam
                // - Baris tengah: L/R hitam, top & bottom TIDAK ADA (none)
                // → TR punya border-bottom hitam untuk page-break coverage

                if ($b['is_first'] && $b['is_last']) {
                // satu-satunya: normal
                $asesorTdStyle = 'border:1pt solid #000;';
                } elseif ($b['is_first']) {
                // pertama dari grup: atas+kiri+kanan hitam, bawah tidak ada
                $asesorTdStyle = 'border-top:1pt solid #000;border-left:1pt solid #000;border-right:1pt solid
                #000;border-bottom:none;';
                } elseif ($b['is_last']) {
                // terakhir dari grup: kiri+kanan+bawah hitam, atas tidak ada
                $asesorTdStyle = 'border-top:none;border-left:1pt solid #000;border-right:1pt solid
                #000;border-bottom:1pt solid #000;';
                } else {
                // tengah: hanya kiri+kanan, atas+bawah tidak ada
                $asesorTdStyle = 'border-top:none;border-left:1pt solid #000;border-right:1pt solid
                #000;border-bottom:none;';
                }

                // TR border-bottom untuk page-break di tengah grup:
                // Baris tengah dan baris pertama (yang belum selesai grupnya):
                // set border-bottom pada TR → DomPDF render ini saat page break
                $trStyle = (!$b['is_last']) ? 'border-bottom:1pt solid #000;' : '';

                // Nama asesor
                $asesorHtml = '';
                if ($b['asesor']) {
                $asesorHtml = e($b['asesor']->nama);
                if ($b['asesor']->no_reg_met) {
                $asesorHtml .= '<br>' . e($b['asesor']->no_reg_met);
                }
                }
                @endphp
                <tr style="{{ $trStyle }}">
                    <td class="td-center">{{ $b['no'] }}</td>
                    <td>{{ $b['nama'] }}</td>
                    <td
                        style="{{ $asesorTdStyle }} padding:3pt 5pt;vertical-align:middle;text-align:center;font-size:9.5pt;line-height:1.3;">
                        {!! $asesorHtml !!}</td>
                    <td class="td-center">{{ $b['rek'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="text-align:justify; margin-top:8pt; margin-bottom:6pt; font-size:10.5pt;">
            Demikian berita acara asesmen/uji kompetensi ini dibuat sebagai pengambil keputusan oleh LSP-KAP.
        </div>

        {{-- ══ TTD ══ --}}
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
                        <table class="ttd-wrap">
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