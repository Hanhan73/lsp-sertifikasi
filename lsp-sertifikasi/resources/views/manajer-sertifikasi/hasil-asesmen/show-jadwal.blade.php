@extends('layouts.app')

@section('title', 'Hasil Asesmen — ' . $schedule->skema->name)
@section('breadcrumb', 'Hasil Asesmen › Per Jadwal')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

{{-- ===== HEADER ===== --}}
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-2">
        <a href="{{ route('manajer-sertifikasi.hasil-asesmen.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Hasil Asesmen
        </a>
    </div>

    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Hasil Asesmen</h4>
            <div class="d-flex flex-wrap gap-3 text-muted small">
                <span><i class="bi bi-patch-check me-1"></i>{{ $schedule->skema->name }}</span>
                <span><i
                        class="bi bi-calendar3 me-1"></i>{{ $schedule->assessment_date->translatedFormat('d F Y') }}</span>
                <span><i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}</span>
                <span><i class="bi bi-building me-1"></i>{{ $schedule->tuk->name ?? '-' }}</span>
                @if($schedule->asesor)
                <span><i class="bi bi-person-badge me-1"></i>{{ $schedule->asesor->user->name ?? '-' }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-start">
            @php
            $semua = $schedule->asesmens->count();
            $hadir = $schedule->asesmens->where('hadir', true)->count();
            @endphp
            <div class="text-center px-3 py-2 rounded-3"
                style="background:#f0fdf4;border:1px solid #bbf7d0;min-width:70px">
                <div class="fw-bold" style="font-size:1.2rem;color:#16a34a">{{ $hadir }}/{{ $semua }}</div>
                <div style="font-size:.65rem;color:#6b7280;font-weight:600">HADIR</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                style="background:#eff6ff;border:1px solid #bfdbfe;min-width:70px">
                <div class="fw-bold" style="font-size:1.2rem;color:#2563eb">
                    {{ $hasilObservasi->count() }}/{{ $totalObservasi }}</div>
                <div style="font-size:.65rem;color:#6b7280;font-weight:600">OBSERVASI</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                style="background:#fdf4ff;border:1px solid #e9d5ff;min-width:70px">
                <div class="fw-bold" style="font-size:1.2rem;color:#7c3aed">
                    {{ $hasilPortofolio->count() }}/{{ $totalPortofolio }}</div>
                <div style="font-size:.65rem;color:#6b7280;font-weight:600">PORTOFOLIO</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                style="background:{{ $beritaAcara ? '#f0fdf4' : '#fef9c3' }};border:1px solid {{ $beritaAcara ? '#bbf7d0' : '#fde68a' }};min-width:70px">
                <div class="fw-bold" style="font-size:1.2rem;color:{{ $beritaAcara ? '#16a34a' : '#b45309' }}">
                    {{ $beritaAcara ? '✓' : '—' }}
                </div>
                <div style="font-size:.65rem;color:#6b7280;font-weight:600">BERITA ACARA</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== REKAP NILAI ===== --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <div class="fw-semibold">
            <i class="bi bi-table text-primary me-2"></i>Rekap Nilai Seluruh Asesi
        </div>
        @if($beritaAcara)
        <span class="badge bg-success px-3 py-2" style="font-size:.75rem;">
            <i class="bi bi-check-circle me-1"></i>Berita Acara Tersedia
        </span>
        @else
        <span class="badge bg-warning text-dark px-3 py-2" style="font-size:.75rem;">
            <i class="bi bi-hourglass me-1"></i>Berita Acara Belum Ada
        </span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:36px;">#</th>
                        <th>Nama Asesi</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Nilai Teori</th>
                        @foreach($schedule->distribusiSoalObservasi as $distObs)
                        <th class="text-center text-truncate" style="max-width:120px;"
                            title="{{ $distObs->soalObservasi->judul ?? '-' }}">
                            {{ Str::limit($distObs->soalObservasi->judul ?? '-', 20) }}
                        </th>
                        @endforeach
                        @foreach($schedule->distribusiPortofolio as $distPorto)
                        <th class="text-center text-truncate" style="max-width:120px;"
                            title="{{ $distPorto->portofolio->judul ?? '-' }}">
                            {{ Str::limit($distPorto->portofolio->judul ?? '-', 20) }}
                        </th>
                        @endforeach
                        <th class="text-center pe-4">Rekomendasi</th>
                    </tr>
                    <tr class="table-light border-top-0" style="font-size:.7rem;color:#9ca3af;">
                        <th class="ps-4"></th>
                        <th></th>
                        <th class="text-center fw-normal">Kehadiran</th>
                        <th class="text-center fw-normal">PG (Benar/Total)</th>
                        @foreach($schedule->distribusiSoalObservasi as $d)
                        <th class="text-center fw-normal">Link GDrive</th>
                        @endforeach
                        @foreach($schedule->distribusiPortofolio as $d)
                        <th class="text-center fw-normal">Dokumen</th>
                        @endforeach
                        <th class="text-center pe-4 fw-normal">Hasil BA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule->asesmens as $i => $asesmen)
                    @php
                    $soalAsesi = $distribusiTeori ? $distribusiTeori->soalAsesi->where('asesmen_id', $asesmen->id) :
                    collect();
                    $totalSoal = $soalAsesi->count();
                    $benarSoal = $soalAsesi->filter(fn($s) => $s->jawaban && $s->jawaban ===
                    $s->soalTeori?->jawaban_benar)->count();
                    $sudahSubmit = $soalAsesi->whereNotNull('submitted_at')->isNotEmpty();
                    $nilaiPct = $totalSoal > 0 ? round($benarSoal / $totalSoal * 100) : 0;
                    $rek = $rekomendasiMap[$asesmen->id] ?? null;
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $asesmen->full_name }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $asesmen->nik ?? '-' }}</div>
                        </td>
                        <td class="text-center">
                            @if($asesmen->hadir)
                            <span class="badge bg-success">Hadir</span>
                            @else
                            <span class="badge bg-secondary">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($totalSoal === 0)
                            <span class="text-muted small">Belum ada soal</span>
                            @elseif(!$sudahSubmit)
                            <span class="badge bg-warning text-dark" style="font-size:.7rem;">Belum Submit</span>
                            @else
                            <div class="fw-bold {{ $nilaiPct >= 60 ? 'text-success' : 'text-danger' }}">
                                {{ $nilaiPct }}</div>
                            <div class="progress mt-1" style="height:4px;width:60px;margin:0 auto;">
                                <div class="progress-bar {{ $nilaiPct >= 60 ? 'bg-success' : 'bg-danger' }}"
                                    style="width:{{ $nilaiPct }}%"></div>
                            </div>
                            <div style="font-size:.68rem;color:#6b7280;">{{ $benarSoal }}/{{ $totalSoal }}</div>
                            @endif
                        </td>
                        @foreach($schedule->distribusiSoalObservasi as $distObs)
                        @php
                        $jwb = $jawabanObservasi->where('asesmen_id', $asesmen->id)
                        ->where('distribusi_soal_observasi_id', $distObs->id)->first();
                        @endphp
                        <td class="text-center">
                            @if($jwb && $jwb->gdrive_link)
                            <a href="{{ $jwb->gdrive_link }}" target="_blank"
                                class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.72rem;">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Drive
                            </a>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        @endforeach
                        @foreach($schedule->distribusiPortofolio as $distPorto)
                        @php $hasil = $hasilPortofolio->where('portofolio_id', $distPorto->portofolio_id)->first();
                        @endphp
                        <td class="text-center">
                            @if($hasil)
                            <span class="badge bg-success-subtle text-success border border-success-subtle"
                                style="font-size:.68rem;">
                                <i class="bi bi-check-lg me-1"></i>Diupload
                            </span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="text-center pe-4">
                            @if($rek === 'K')
                            <span class="badge bg-success px-3">Kompeten</span>
                            @elseif($rek === 'BK')
                            <span class="badge bg-danger px-3">Belum Kompeten</span>
                            @else
                            <span class="badge bg-secondary">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Kiri --}}
    <div class="col-lg-6">
        {{-- Daftar Hadir --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div class="fw-semibold">
                    <i class="bi bi-person-check text-success me-2"></i>Daftar Hadir
                    <span class="badge bg-secondary ms-1">{{ $hadir }}/{{ $semua }}</span>
                </div>
                @if($schedule->asesor?->user?->signature)
                <a href="{{ route('manajer-sertifikasi.jadwal.daftar-hadir', $schedule) }}" target="_blank"
                    class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-pdf me-1"></i>PDF
                </a>
                @else
                <span class="badge bg-secondary">Belum TTD</span>
                @endif
            </div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0" style="font-size:.83rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th class="text-center">Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule->asesmens as $i => $asesmen)
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $asesmen->full_name }}</td>
                            <td class="text-muted">{{ $asesmen->nik ?? '-' }}</td>
                            <td class="text-center">
                                @if($asesmen->hadir)
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                <i class="bi bi-x-circle text-muted"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Foto Dokumentasi --}}
        @if($schedule->foto_dokumentasi_1 || $schedule->foto_dokumentasi_2)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="fw-semibold">
                    <i class="bi bi-camera text-info me-2"></i>Foto Dokumentasi
                    @if($schedule->hasFotoDokumentasi())
                    <span class="badge bg-success ms-1" style="font-size:.65rem;">Lengkap</span>
                    @else
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Sebagian</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach([1, 2] as $slot)
                    @php $col = "foto_dokumentasi_{$slot}"; @endphp
                    @if($schedule->$col)
                    <div class="col-6">
                        <div class="border rounded-3 overflow-hidden">
                            <img src="{{ route('manajer-sertifikasi.hasil-asesmen.foto', [$schedule, $slot]) }}"
                                class="w-100" style="max-height:200px;object-fit:cover;" alt="Foto {{ $slot }}">
                            <div class="px-2 py-1 bg-light text-muted" style="font-size:.75rem;">
                                <i class="bi bi-image me-1"></i>Foto {{ $slot }}
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @if($schedule->foto_uploaded_at)
                <div class="text-muted mt-2" style="font-size:.75rem;">
                    <i class="bi bi-clock me-1"></i>Diupload
                    {{ \Carbon\Carbon::parse($schedule->foto_uploaded_at)->translatedFormat('d M Y, H:i') }}
                </div>
                @endif
            </div>
        </div>
        @endif
        {{-- Catatan Asesor --}}
        @if($schedule->catatan_asesor)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="fw-semibold">
                    <i class="bi bi-pencil-square text-secondary me-2"></i>Catatan Asesor
                </div>
            </div>
            <div class="card-body">
                <p class="mb-0 small" style="white-space:pre-wrap;">{{ $schedule->catatan_asesor }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Kanan --}}
    <div class="col-lg-6">

        {{-- Hasil Observasi --}}
        @if($totalObservasi > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="fw-semibold">
                    <i class="bi bi-eye text-primary me-2"></i>Hasil Observasi
                    <span class="badge bg-secondary ms-1">{{ $hasilObservasi->count() }}/{{ $totalObservasi }}</span>
                </div>
            </div>
            <div class="list-group list-group-flush">
                @foreach($schedule->distribusiSoalObservasi as $dist)
                @php
                $obs = $dist->soalObservasi;
                $hasil = $hasilObservasi->where('soal_observasi_id', $obs->id)->first();
                $paket = $dist->paketSoalObservasi;
                $linkAsesi = $jawabanObservasi->where('distribusi_soal_observasi_id', $dist->id);
                @endphp
                <div class="list-group-item px-4 py-3">
                    <div class="d-flex align-items-start gap-3">
                        <i
                            class="bi {{ $hasil ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} fs-5 flex-shrink-0 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $obs->judul }}</div>
                            @if($paket)
                            <div class="text-muted" style="font-size:.75rem;"><i class="bi bi-collection me-1"></i>Paket
                                {{ $paket->kode_paket }}</div>
                            @endif
                            @if($hasil)
                            <div class="text-muted mt-1" style="font-size:.75rem;">
                                <i class="bi bi-file-earmark me-1"></i>{{ $hasil->file_name }}
                                · {{ $hasil->uploaded_at->translatedFormat('d M Y, H:i') }}
                            </div>
                            @else
                            <div class="text-muted small mt-1 fst-italic">Belum diupload asesor</div>
                            @endif
                            @if($linkAsesi->isNotEmpty())
                            <div class="mt-2">
                                <div class="text-muted"
                                    style="font-size:.72rem;font-weight:600;text-transform:uppercase;">Link GDrive
                                    Asesi:</div>
                                @foreach($linkAsesi as $jwb)
                                @php $namaAsesi = $schedule->asesmens->find($jwb->asesmen_id); @endphp
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="text-muted small flex-shrink-0"
                                        style="min-width:120px;">{{ $namaAsesi?->full_name ?? '—' }}</span>
                                    <a href="{{ $jwb->gdrive_link }}" target="_blank"
                                        class="btn btn-xs btn-outline-primary py-0 px-2 flex-shrink-0"
                                        style="font-size:.72rem;">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Buka Drive
                                    </a>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @if($hasil)
                        <a href="{{ route('manajer-sertifikasi.jadwal.rekap.download-observasi', [$schedule, $obs]) }}"
                            class="btn btn-sm btn-outline-primary flex-shrink-0">
                            <i class="bi bi-download"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Hasil Portofolio --}}
        @if($totalPortofolio > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="fw-semibold">
                    <i class="bi bi-briefcase me-2" style="color:#7c3aed"></i>Hasil Portofolio
                    <span class="badge bg-secondary ms-1">{{ $hasilPortofolio->count() }}/{{ $totalPortofolio }}</span>
                </div>
            </div>
            <div class="list-group list-group-flush">
                @foreach($schedule->distribusiPortofolio as $dist)
                @php
                $porto = $dist->portofolio;
                $hasil = $hasilPortofolio->where('portofolio_id', $porto->id)->first();
                @endphp
                <div class="list-group-item px-4 py-3">
                    <div class="d-flex align-items-start gap-3">
                        <i
                            class="bi {{ $hasil ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} fs-5 flex-shrink-0 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $porto->judul }}</div>
                            @if($hasil)
                            <div class="text-muted mt-1" style="font-size:.75rem;">
                                <i class="bi bi-file-earmark me-1"></i>{{ $hasil->file_name }}
                                · {{ $hasil->uploaded_at->translatedFormat('d M Y, H:i') }}
                            </div>
                            @else
                            <div class="text-muted small mt-1 fst-italic">Belum diupload asesor</div>
                            @endif
                        </div>
                        @if($hasil)
                        <a href="{{ route('manajer-sertifikasi.jadwal.rekap.download-portofolio', [$schedule, $porto]) }}"
                            class="btn btn-sm btn-outline-primary flex-shrink-0">
                            <i class="bi bi-download"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Berita Acara --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div class="fw-semibold">
                    <i class="bi bi-file-text text-warning me-2"></i>Berita Acara
                </div>
                <div class="d-flex gap-2">
                    @if($beritaAcara)
                    <a href="{{ route('manajer-sertifikasi.jadwal.berita-acara.pdf', $schedule) }}" target="_blank"
                        class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-file-pdf me-1"></i>PDF BA
                    </a>
                    @endif
                    @if($beritaAcara && $beritaAcara->file_path)
                    <a href="{{ route('manajer-sertifikasi.jadwal.rekap.download-ba', $schedule) }}"
                        class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download me-1"></i>File BA
                    </a>
                    @endif
                </div>
            </div>

            @if(!$beritaAcara)
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-hourglass-split"
                    style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                <p class="fw-semibold mb-0">Berita acara belum diisi asesor</p>
            </div>
            @else
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 mb-3 p-3 rounded-3 bg-light border">
                    <div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase;">
                            Tanggal Pelaksanaan</div>
                        <div class="fw-semibold small">
                            {{ $beritaAcara->tanggal_pelaksanaan?->translatedFormat('d F Y') ?? '-' }}</div>
                    </div>
                    @if($beritaAcara->catatan)
                    <div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase;">
                            Catatan</div>
                        <div class="small">{{ $beritaAcara->catatan }}</div>
                    </div>
                    @endif
                </div>

                <div class="fw-semibold small mb-2">Hasil Rekomendasi Asesor:</div>
                <div class="d-flex flex-column gap-2">
                    @foreach($schedule->asesmens as $asesmen)
                    @php $rek = $rekomendasiMap[$asesmen->id] ?? null; @endphp
                    <div
                        class="d-flex align-items-center gap-3 p-2 rounded-3 border
                        {{ $rek === 'K' ? 'border-success bg-success-subtle' : ($rek === 'BK' ? 'border-danger bg-danger-subtle' : 'bg-light border') }}">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $asesmen->full_name }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $asesmen->nik ?? '-' }}</div>
                        </div>
                        @if($rek === 'K')
                        <span class="badge bg-success px-3 py-2"><i class="bi bi-award me-1"></i>Kompeten</span>
                        @elseif($rek === 'BK')
                        <span class="badge bg-danger px-3 py-2"><i class="bi bi-x-circle me-1"></i>Belum Kompeten</span>
                        @else
                        <span class="badge bg-secondary px-3 py-2">—</span>
                        @endif
                    </div>
                    @endforeach
                </div>

                @php
                $totalK = collect($rekomendasiMap)->filter(fn($v) => $v === 'K')->count();
                $totalBK = collect($rekomendasiMap)->filter(fn($v) => $v === 'BK')->count();
                @endphp
                @if($totalK + $totalBK > 0)
                <div class="d-flex gap-3 mt-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="text-center flex-fill">
                        <div class="fw-bold text-success" style="font-size:1.5rem;">{{ $totalK }}</div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;">KOMPETEN</div>
                    </div>
                    <div class="border-start"></div>
                    <div class="text-center flex-fill">
                        <div class="fw-bold text-danger" style="font-size:1.5rem;">{{ $totalBK }}</div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;">BELUM KOMPETEN</div>
                    </div>
                    <div class="border-start"></div>
                    <div class="text-center flex-fill">
                        <div class="fw-bold" style="font-size:1.5rem;color:#2563eb;">{{ $semua }}</div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600;">TOTAL</div>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>

    </div>
</div>

@endsection