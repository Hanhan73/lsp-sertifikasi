<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>FR.APL.01 - {{ $aplsatu->nama_lengkap }}</title>
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
        font-size: 11pt;
        line-height: 1.4;
        color: #000;
        padding: 2.54cm;
    }

    .doc-title {
        font-size: 12pt;
        font-weight: bold;
        margin-bottom: 10pt;
    }

    .section-heading {
        font-size: 11pt;
        font-weight: bold;
        margin-top: 0;
        margin-bottom: 4pt;
    }

    .body-text {
        font-size: 11pt;
        font-style: italic;
        margin-bottom: 6pt;
        text-align: justify;
    }

    .sub-heading {
        font-size: 11pt;
        font-weight: bold;
        margin-top: 8pt;
        margin-bottom: 4pt;
        padding-left: 18pt;
    }

    .small-note {
        font-size: 9pt;
        margin-top: 3pt;
        margin-bottom: 8pt;
    }

    /* DATA TABLE */
    .data-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 11pt;
        margin-bottom: 2pt;
    }

    .data-tbl td {
        padding: 4pt 3pt;
        vertical-align: bottom;
        border: none;
    }

    .data-tbl td.lbl {
        width: 30%;
        white-space: nowrap;
    }

    .data-tbl td.sep {
        width: 3%;
        text-align: center;
    }

    .data-tbl td.val {
        width: 67%;
        border-bottom: 0.5pt solid #000;
    }

    .cross-out {
        text-decoration: line-through;
    }

    .page-break {
        page-break-after: always;
    }

    /* SKEMA TABLE */
    .skema-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 11pt;
        margin-bottom: 6pt;
    }

    .skema-tbl td {
        border: 1pt solid #000;
        padding: 4pt 6pt;
        vertical-align: middle;
    }

    /* CHECKBOX */
    .cb-wrap {
        display: inline-block;
        width: 11pt;
        height: 11pt;
        border: 1pt solid #000;
        text-align: center;
        line-height: 11pt;
        font-size: 9pt;
        font-weight: bold;
        vertical-align: middle;
        margin-right: 3pt;
    }

    /* UNIT KOMPETENSI TABLE */
    .unit-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
        margin-bottom: 0;
    }

    .unit-tbl th,
    .unit-tbl td {
        border: 1pt solid #000;
        padding: 2pt 4pt;
        vertical-align: middle;
    }

    .unit-tbl th {
        background: #fff;
        font-weight: bold;
        text-align: center;
        font-size: 9.5pt;
    }

    .unit-tbl td.no-col  { text-align: center; }
    .unit-tbl td.std-col { text-align: center; }

    /* BUKTI KELENGKAPAN TABLE */
    .bukti-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
        margin-bottom: 6pt;
    }

    .bukti-tbl th,
    .bukti-tbl td {
        border: 1pt solid #000;
        padding: 3pt 4pt;
        vertical-align: middle;
    }

    .bukti-tbl th {
        background: #fff;
        font-weight: bold;
        text-align: center;
        font-size: 10pt;
    }

    .bukti-tbl td.bno    { text-align: center; width: 5%; }
    .bukti-tbl td.bnama  { width: 46%; text-align: left; }
    .bukti-tbl td.bcheck { text-align: center; width: 12%; vertical-align: middle; padding: 3pt 2pt; }

    /* ═══════════════════════════════════════
       SIGNATURE TABLE — diperbaiki
    ═══════════════════════════════════════ */
    .sig-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 11pt;
        margin-top: 6pt;
        page-break-inside: avoid;
    }

    .sig-tbl td {
        border: 1pt solid #000;
        padding: 6pt 8pt;
        vertical-align: top;
    }

    .col-left  { width: 49%; }
    .col-mid   { width: 17%; }
    .col-right { width: 34%; }

    /* Cell yang menampung area TTD */
    .sig-cell {
        padding: 0 !important;
        border: 1pt solid #000;
    }

    /* Area gambar TTD — tinggi diperbesar, konten di tengah */
    .sig-img-area {
        height: 80pt;
        text-align: center;
        vertical-align: middle;
        /* Trik DomPDF: gunakan padding atas untuk mendorong gambar ke tengah
           karena vertical-align tidak selalu bekerja di DomPDF */
        padding-top: 10pt;
        padding-bottom: 0;
        padding-left: 6pt;
        padding-right: 6pt;
    }

    .sig-img {
        /* Lebih besar dari sebelumnya (160×50 → 180×62) */
        max-width: 180pt;
        max-height: 62pt;
        display: block;
        margin: 0 auto;
    }

    /* Baris tanggal di bawah TTD */
    .sig-date {
        font-size: 9pt;
        text-align: center;
        padding: 4pt 6pt 6pt 6pt;
        border-top: 0.5pt solid #ccc;
    }
    </style>
</head>

<body>

    <div class="doc-title">FR.APL.01. PERMOHONAN SERTIFIKASI KOMPETENSI</div>

    <div class="section-heading">Bagian 1 : Rincian Data Pemohon Sertifikasi</div>
    <div class="body-text">Pada bagian ini, cantumkan data pribadi, data pendidikan formal serta data pekerjaan anda
        pada saat ini.</div>

    <div class="sub-heading">a. Data Pribadi</div>

    <table class="data-tbl">
        <tr>
            <td class="lbl">Nama lengkap</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->nama_lengkap }}</td>
        </tr>
        <tr>
            <td class="lbl">No. KTP/NIK/Paspor</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->nik }}</td>
        </tr>
        <tr>
            <td class="lbl">Tempat / tgl. Lahir</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->tempat_lahir }}, {{ $aplsatu->tanggal_lahir?->translatedFormat('d-m-Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Jenis kelamin</td>
            <td class="sep">:</td>
            <td class="val">
                @if($aplsatu->jenis_kelamin === 'Laki-laki')
                Laki-laki / <span class="cross-out">Wanita</span> <span style="font-size:9pt;">*)</span>
                @elseif($aplsatu->jenis_kelamin === 'Perempuan')
                <span class="cross-out">Laki-laki</span> / Wanita <span style="font-size:9pt;">*)</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Kebangsaan</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->kebangsaan ?? 'Indonesia' }}</td>
        </tr>
        <tr>
            <td class="lbl">Alamat rumah</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->alamat_rumah }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Kode pos : {{ $aplsatu->kode_pos ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">No. Telepon/E-mail</td>
            <td class="sep">:</td>
            <td class="val">Rumah : {{ $aplsatu->telp_rumah ?? '-' }} &nbsp;&nbsp;&nbsp; Kantor :
                {{ $aplsatu->telp_kantor_detail ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl"></td>
            <td class="sep"></td>
            <td class="val">HP : {{ $aplsatu->hp }} &nbsp;&nbsp;&nbsp; E-mail : {{ $aplsatu->email }}</td>
        </tr>
        <tr>
            <td class="lbl">Kualifikasi Pendidikan</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->kualifikasi_pendidikan ?? '-' }}</td>
        </tr>
    </table>

    <div class="small-note">*Coret yang tidak perlu</div>

    <div class="sub-heading">b. Data Pekerjaan Sekarang</div>

    <table class="data-tbl">
        <tr>
            <td class="lbl">Nama Institusi / Perusahaan</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->nama_institusi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Jabatan</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Alamat Kantor</td>
            <td class="sep">:</td>
            <td class="val">{{ $aplsatu->alamat_kantor ?? '-' }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Kode pos : {{ $aplsatu->kode_pos_kantor ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">No. Telp/Fax/E-mail</td>
            <td class="sep">:</td>
            <td class="val">Telp : {{ $aplsatu->telp_kantor_detail ?? '-' }} &nbsp;&nbsp;&nbsp; Fax :
                {{ $aplsatu->fax_kantor ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl"></td>
            <td class="sep"></td>
            <td class="val">E-mail : {{ $aplsatu->email_kantor ?? '-' }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <div class="section-heading">Bagian 2 : Data Sertifikasi</div>
    <div class="body-text">Tuliskan Judul dan Nomor Skema Sertifikasi yang anda ajukan berikut Daftar Unit
        Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai dengan latar belakang
        pendidikan, pelatihan kerja dan pengalaman kerja yang anda miliki.</div>

    <table class="skema-tbl">
        <tr>
            <td style="width:29%" rowspan="2">Skema Sertifikasi<br>
                @if($asesmen->skema?->jenis_skema === 'kkni')
                <span>(KKNI/</span><span class="cross-out">Okupasi</span><span>/</span><span class="cross-out">Klaster</span><span>)</span>
                @elseif ($asesmen->skema?->jenis_skema === 'okupasi')
                <span class="cross-out">(KKNI</span>/<span>Okupasi/</span><span class="cross-out">Klaster</span><span>)</span>
                @elseif ($asesmen->skema?->jenis_skema === 'klaster')
                <span class="cross-out">KKNI</span>/<span class="cross-out">Okupasi</span>/<span>(Klaster)</span>
                @else
                <span class="cross-out">KKNI</span>/<span class="cross-out">Okupasi</span>/<span class="cross-out">Klaster</span>
                @endif
            </td>
            <td style="width:10%">Judul</td>
            <td style="width:3%; text-align:center; font-weight:bold;">:</td>
            <td style="width:58%; font-weight:bold;">{{ $asesmen->skema?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nomor</td>
            <td style="text-align:center; font-weight:bold;">:</td>
            <td>{{ $asesmen->skema?->nomor_skema ?? '-' }}</td>
        </tr>
        <tr>
            <td rowspan="4" colspan="2">Tujuan Asesmen</td>
            <td style="border-top:0; border-bottom:1pt solid #000; border-left:0; border-right:1pt solid #000;">:</td>
            <td><span class="cb-wrap">{{ $aplsatu->tujuan_asesmen === 'Sertifikasi' ? 'V' : '' }}</span> Sertifikasi</td>
        </tr>
        <tr>
            <td style="border-top:0; border-bottom:1pt solid #000; border-left:0; border-right:1pt solid #000;"></td>
            <td><span class="cb-wrap">{{ $aplsatu->tujuan_asesmen === 'PKT' ? 'V' : '' }}</span> Pengakuan Kompetensi Terkini (PKT)</td>
        </tr>
        <tr>
            <td style="border-top:0; border-bottom:1pt solid #000; border-left:0; border-right:1pt solid #000;"></td>
            <td><span class="cb-wrap">{{ $aplsatu->tujuan_asesmen === 'RPL' ? 'V' : '' }}</span> Rekognisi Pembelajaran Lampau (RPL)</td>
        </tr>
        <tr>
            <td style="border-top:0; border-bottom:1pt solid #000; border-left:0; border-right:1pt solid #000;"></td>
            <td><span class="cb-wrap">{{ $aplsatu->tujuan_asesmen === 'Lainnya' ? 'V' : '' }}</span>
                Lainnya{{ $aplsatu->tujuan_asesmen === 'Lainnya' && $aplsatu->tujuan_asesmen_lainnya ? ' : ' . $aplsatu->tujuan_asesmen_lainnya : '' }}</td>
        </tr>
    </table>

    <div style="font-size:11pt; font-weight:bold; margin-top:5pt; margin-bottom:3pt;">Daftar Unit Kompetensi sesuai kemasan:</div>

    <table class="unit-tbl">
        <thead>
            <tr>
                <th style="width:5%">No.</th>
                <th style="width:22%">Kode Unit</th>
                <th style="width:53%">Judul Unit</th>
                <th style="width:20%">Standar Kompetensi Kerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asesmen->skema?->unitKompetensis ?? [] as $i => $unit)
            <tr>
                <td class="no-col">{{ $i + 1 }}</td>
                <td style="font-size:9pt">{{ $unit->kode_unit }}</td>
                <td>{{ $unit->judul_unit }}</td>
                <td class="std-col">{{ $unit->standar_kompetensi ?? 'SKKNI' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:10pt;">

        <div class="section-heading">Bagian 3 : Bukti Kelengkapan Pemohon</div>

        <div style="font-size:11pt; font-weight:bold; margin-top:4pt; margin-bottom:3pt;">3.1 Bukti Persyaratan Dasar Pemohon</div>

        <table class="bukti-tbl">
            <thead>
                <tr>
                    <th class="bno" rowspan="2">No.</th>
                    <th class="bnama" rowspan="2" style="text-align:center;">Bukti Persyaratan Dasar</th>
                    <th colspan="2" style="border-bottom:0;">Ada</th>
                    <th class="bcheck" rowspan="2">Tidak Ada</th>
                </tr>
                <tr>
                    <th class="bcheck">Memenuhi Syarat</th>
                    <th class="bcheck">Tidak Memenuhi Syarat</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($aplsatu->buktiKelengkapan->where('kategori', 'persyaratan_dasar') as $bukti)
                <tr>
                    <td class="bno">{{ $no++ }}.</td>
                    <td class="bnama">{{ $bukti->nama_dokumen }}</td>
                    <td class="bcheck"><span class="cb-wrap">{{ $bukti->status === 'Ada Memenuhi Syarat'       ? 'V' : '' }}</span></td>
                    <td class="bcheck"><span class="cb-wrap">{{ $bukti->status === 'Ada Tidak Memenuhi Syarat' ? 'V' : '' }}</span></td>
                    <td class="bcheck"><span>{{ $bukti->status === 'Tidak Ada' ? 'V' : '' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="font-size:11pt; font-weight:bold; margin-top:4pt; margin-bottom:3pt;">3.2 Bukti Administratif</div>

        <table class="bukti-tbl">
            <thead>
                <tr>
                    <th class="bno" rowspan="2">No.</th>
                    <th class="bnama" rowspan="2" style="text-align:center;">Bukti Administratif</th>
                    <th colspan="2" style="border-bottom:0;">Ada</th>
                    <th class="bcheck" rowspan="2">Tidak Ada</th>
                </tr>
                <tr>
                    <th class="bcheck">Memenuhi Syarat</th>
                    <th class="bcheck">Tidak Memenuhi Syarat</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($aplsatu->buktiKelengkapan->where('kategori', 'administratif') as $bukti)
                <tr>
                    <td class="bno">{{ $no++ }}.</td>
                    <td class="bnama">{{ $bukti->nama_dokumen }}</td>
                    <td class="bcheck"><span class="cb-wrap">{{ $bukti->status === 'Ada Memenuhi Syarat'       ? 'V' : '' }}</span></td>
                    <td class="bcheck"><span class="cb-wrap">{{ $bukti->status === 'Ada Tidak Memenuhi Syarat' ? 'V' : '' }}</span></td>
                    <td class="bcheck"><span>{{ $bukti->status === 'Tidak Ada' ? 'V' : '' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ═══════════════════════════════════════
             TABEL TANDA TANGAN — APL-01
        ═══════════════════════════════════════ --}}
        <table class="sig-tbl">

            {{-- ── PEMOHON / KANDIDAT ── --}}
            <tr>
                <td class="col-left" rowspan="3">
                    <strong>Rekomendasi (diisi oleh LSP):</strong><br>
                    <span style="font-size:10pt;">Berdasarkan ketentuan persyaratan dasar, maka pemohon:</span>
                    <br><br>
                    @if($aplsatu->status === 'verified')
                    <strong>Layak</strong> / <span class="cross-out">Tidak Diterima</span>
                    @else
                    <span class="cross-out">Layak</span> / Tidak Diterima
                    @endif
                    <span style="font-size:10pt;"> *) sebagai peserta sertifikasi<br>* coret yang tidak perlu</span>
                </td>
                <td colspan="2" style="text-align:center; font-weight:bold; vertical-align:middle;">
                    Pemohon / Kandidat :
                </td>
            </tr>
            <tr>
                <td class="col-mid" style="vertical-align:middle;">Nama</td>
                <td class="col-right" style="vertical-align:middle;">
                    <strong>{{ $aplsatu->nama_ttd_pemohon ?? $aplsatu->nama_lengkap }}</strong>
                </td>
            </tr>
            <tr>
                <td class="col-mid" style="vertical-align:middle;">Tanda tangan/<br>Tanggal</td>
                {{-- Cell TTD Pemohon --}}
                <td class="sig-cell">
                    <div class="sig-img-area">
                        @if($aplsatu->ttd_pemohon)
                        <img class="sig-img" src="{{ $aplsatu->ttd_pemohon_image }}" alt="TTD Pemohon">
                        @endif
                    </div>
                    <div class="sig-date">
                        {{ $aplsatu->tanggal_ttd_pemohon
                            ? \Carbon\Carbon::parse($aplsatu->tanggal_ttd_pemohon)->translatedFormat('d-m-Y')
                            : '' }}
                    </div>
                </td>
            </tr>

            {{-- ── ADMIN LSP ── --}}
            <tr>
                <td class="col-left" rowspan="3" style="vertical-align:top;">
                    <strong>Catatan :</strong>
                    <div style="min-height:20pt; margin-top:3pt; font-size:10pt;">
                        {{ $aplsatu->catatan_rekomendasi ?? '' }}
                    </div>
                </td>
                <td colspan="2" style="text-align:center; font-weight:bold; vertical-align:middle;">
                    Admin LSP :
                </td>
            </tr>
            <tr>
                <td class="col-mid" style="vertical-align:middle;">Nama</td>
                <td class="col-right" style="vertical-align:middle;">
                    <strong>{{ $aplsatu->nama_ttd_admin ?? '' }}</strong>
                </td>
            </tr>
            <tr>
                <td class="col-mid" style="vertical-align:middle;">Tanda tangan/<br>Tanggal</td>
                {{-- Cell TTD Admin --}}
                <td class="sig-cell">
                    <div class="sig-img-area">
                        @if($aplsatu->ttd_admin)
                        <img class="sig-img" src="{{ $aplsatu->ttd_admin_image }}" alt="TTD Admin">
                        @endif
                    </div>
                    <div class="sig-date">
                        {{ $aplsatu->tanggal_ttd_admin
                            ? \Carbon\Carbon::parse($aplsatu->tanggal_ttd_admin)->translatedFormat('d-m-Y')
                            : '' }}
                    </div>
                </td>
            </tr>

        </table>

    </div>

</body>

</html>