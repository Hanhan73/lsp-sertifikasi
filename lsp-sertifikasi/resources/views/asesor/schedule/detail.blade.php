@extends('layouts.app')
@section('title', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal Asesmen')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

{{-- ── HEADER JADWAL ──────────────────────────────────────── --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex align-items-start gap-4 flex-wrap">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                    @if($schedule->assessment_date->isToday())
                        <span class="badge bg-warning text-dark">Hari Ini</span>
                    @elseif($schedule->assessment_date->isPast())
                        <span class="badge bg-secondary">Selesai</span>
                    @else
                        <span class="badge bg-primary">Mendatang</span>
                    @endif
                    @if($schedule->isApproved())
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Disetujui</span>
                    @endif
                </div>
                <h4 class="fw-bold mb-1">{{ $schedule->skema->name ?? '-' }}</h4>
                <div class="text-muted small d-flex flex-wrap gap-3">
                    <span><i class="bi bi-calendar3 me-1"></i>{{ $schedule->assessment_date->translatedFormat('l, d F Y') }}</span>
                    <span><i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}</span>
                    <span><i class="bi bi-building me-1"></i>{{ $schedule->tuk->name ?? '-' }}</span>
                    @if($schedule->location)
                        <span><i class="bi bi-geo-alt me-1"></i>{{ $schedule->location }}</span>
                    @endif
                    @if($schedule->sk_number)
                        <span class="font-monospace"><i class="bi bi-file-text me-1"></i>{{ $schedule->sk_number }}</span>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2 align-items-start flex-wrap">
                @if($schedule->hasSk())
                <a href="{{ route('asesor.schedule.sk.download', $schedule) }}"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-download me-1"></i>Unduh SK
                </a>
                @endif

                {{-- Tombol Mulai Asesmen — hanya muncul hari-H jika ada asesi yang siap --}}
                @if($schedule->assessment_date->isToday() && $canStartAsesmen)
                    @if ($schedule->assessment_start)
                        <button type="button" class="btn btn-sm btn-success fw-semibold" disabled>
                            <i class="bi bi-play-fill me-1"></i>Asesmen Telah Dimulai
                        </button>
                        @else
                        <button type="button" class="btn btn-sm btn-danger fw-semibold"
                                onclick="konfirmasiMulaiAsesmen()"
                                id="btn-mulai-asesmen">
                            <i class="bi bi-play-fill me-1"></i>Mulai Asesmen
                        </button>
                    @endif
                @elseif(!$schedule->assessment_date->isToday() || !$canStartAsesmen)
                <button type="button" class="btn btn-sm btn-secondary fw-semibold" disabled>
                    <i class="bi bi-play-fill me-1"></i>Mulai Asesmen
                </button>

                @endif

                {{-- Daftar Hadir — tombol header (hanya untuk download setelah dikunci) --}}
                @if($schedule->isDaftarHadirSigned())
                <a href="{{ route('asesor.schedule.daftar-hadir', $schedule) }}"
                   target="_blank" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-file-person me-1"></i>Daftar Hadir PDF
                </a>
                @else
                <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                        title="Verifikasi kehadiran di tab Daftar Peserta">
                    <i class="bi bi-file-person me-1"></i>Daftar Hadir
                </button>
                @endif

                <a href="{{ route('asesor.schedule') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>

        @if($schedule->notes)
        <div class="mt-3 p-3 rounded" style="background:#f8fafc; border-left:3px solid #627be9;">
            <small class="text-muted"><i class="bi bi-sticky me-1"></i>{{ $schedule->notes }}</small>
        </div>
        @endif
    </div>
</div>

{{-- ── STATS MINI ─────────────────────────────────────────── --}}
@php
    $asesmens    = $schedule->asesmens;
    $totalAsesi  = $asesmens->count();
    $apl01Done   = $asesmens->filter(fn($a) => $a->aplsatu?->status === 'verified')->count();
    $apl02Subm   = $asesmens->filter(fn($a) => $a->apldua?->status === 'submitted')->count();
    $apl02Ver    = $asesmens->filter(fn($a) => $a->apldua?->status === 'verified')->count();

    $distribusiTeori     = $schedule->distribusiSoalTeori;
    $distribusiObservasi = $schedule->distribusiSoalObservasi;

    $teoriSubmit = 0;
    foreach ($asesmens as $a) {
        $soal = $a->soalTeoriAsesi ?? collect();
        if ($soal->whereNotNull('submitted_at')->count() > 0) $teoriSubmit++;
    }
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-primary">{{ $totalAsesi }}</div>
            <div class="small text-muted">Total Asesi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-success">{{ $apl01Done }}</div>
            <div class="small text-muted">APL-01 Terverifikasi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold {{ $apl02Subm > 0 ? 'text-warning' : 'text-muted' }}">{{ $apl02Subm }}</div>
            <div class="small text-muted">APL-02 Perlu Verifikasi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-info">{{ $teoriSubmit }}</div>
            <div class="small text-muted">Selesai Ujian Teori</div>
        </div>
    </div>
</div>

{{-- ── MAIN TABS ───────────────────────────────────────────── --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
        <ul class="nav nav-tabs" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-peserta" type="button">
                    <i class="bi bi-people me-1"></i>Daftar Peserta
                    @if($apl02Subm > 0)
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">{{ $apl02Subm }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-progress" type="button">
                    <i class="bi bi-graph-up me-1"></i>Progress Asesi
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-penilaian">
                    <i class="bi bi-clipboard2-check me-1"></i>Penilaian
                    @php
                        $hasilObsCount   = $schedule->hasilObservasi->count();
                        $hasilPortoCount = $schedule->hasilPortofolio->count();
                        $hasBA           = $schedule->beritaAcara !== null;
                    @endphp
                    @if($hasBA)
                        <span class="badge bg-success ms-1" style="font-size:.6rem;">✓</span>
                    @elseif($hasilObsCount || $hasilPortoCount)
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Sebagian</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="mainTabsContent">

        {{-- ═══════════════════════════════════════════════════
             TAB 1: DAFTAR PESERTA + DAFTAR HADIR
        ═══════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="tab-peserta" role="tabpanel">

            @php
                $hadirCount      = $asesmens->filter(fn($a) => ($a->hadir ?? true) !== false)->count();
                $daftarHadirLocked = $schedule->isDaftarHadirSigned();
            @endphp
            <div class="px-4 pt-3 pb-2 border-bottom d-flex align-items-center gap-3 flex-wrap">
                <span class="fw-semibold small"><i class="bi bi-person-check text-success me-1"></i>Daftar Hadir</span>
                <span class="badge bg-success" id="badge-hadir">{{ $hadirCount }} Hadir</span>
                <span class="badge bg-secondary" id="badge-tidak-hadir">{{ $totalAsesi - $hadirCount }} Tidak Hadir</span>
                @if($daftarHadirLocked)
                <span class="badge bg-success ms-auto">
                    <i class="bi bi-lock-fill me-1"></i>Ditandatangani {{ $schedule->daftar_hadir_signed_at->translatedFormat('d M Y H:i') }}
                </span>
                <a href="{{ route('asesor.schedule.daftar-hadir', $schedule) }}"
                   target="_blank" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-pdf me-1"></i>Download PDF
                </a>
                @elseif(!$apl02Ak01Ready)
                <span class="btn btn-sm btn-outline-secondary ms-auto disabled">
                    <i class="bi bi-lock me-1"></i>Verifikasi APL-02 & FR.AK.01 dulu
                </span>
                @else
                <button class="btn btn-sm btn-outline-primary ms-auto" onclick="bukaModalVerifikasiHadir()">
                    <i class="bi bi-clipboard-check me-1"></i>Verifikasi & Tandatangani Daftar Hadir
                </button>
                @endif
            </div>

            <div class="p-0">
                @forelse($asesmens as $idx => $asesmen)
                @php
                    $aplsatu     = $asesmen->aplsatu;
                    $apldua      = $asesmen->apldua;
                    $needsVerify = $apldua?->status === 'submitted';
                    $isHadir     = ($asesmen->hadir ?? true) !== false;
                @endphp
                <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom
                    {{ $needsVerify ? 'bg-warning bg-opacity-5' : '' }}"
                    id="row-asesi-{{ $asesmen->id }}">

                    <div class="text-muted fw-bold" style="min-width:28px;">{{ $idx + 1 }}</div>

                    @if($asesmen->photo_path)
                    <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                         class="rounded-circle border" style="width:44px;height:44px;object-fit:cover;" alt="foto">
                    @else
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:44px;height:44px;font-size:1.1rem;">
                        {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
                    </div>
                    @endif

                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $asesmen->full_name }}</div>
                        <div class="text-muted small">NIK: {{ $asesmen->nik }}</div>
                    </div>

                    {{-- Toggle Hadir — dikunci jika daftar hadir sudah ditandatangani --}}
                    @if($daftarHadirLocked)
                    <button type="button"
                            class="btn btn-sm {{ $isHadir ? 'btn-success' : 'btn-outline-danger' }}"
                            disabled
                            title="Daftar hadir sudah ditandatangani, tidak dapat diubah">
                        <i class="bi {{ $isHadir ? 'bi-person-check-fill' : 'bi-person-x-fill' }} me-1"></i>
                        <span>{{ $isHadir ? 'Hadir' : 'Tidak Hadir' }}</span>
                        <i class="bi bi-lock-fill ms-1" style="font-size:.65rem;"></i>
                    </button>
                    @else
                    <button type="button"
                            class="btn btn-sm hadir-toggle-btn {{ $isHadir ? 'btn-success' : 'btn-outline-danger' }}"
                            id="btn-hadir-{{ $asesmen->id }}"
                            data-id="{{ $asesmen->id }}"
                            data-hadir="{{ $isHadir ? '1' : '0' }}"
                            title="Klik untuk mengubah status kehadiran"
                            onclick="toggleHadirBtn(this)">
                        <i class="bi {{ $isHadir ? 'bi-person-check-fill' : 'bi-person-x-fill' }} me-1"></i>
                        <span class="hadir-label">{{ $isHadir ? 'Hadir' : 'Tidak Hadir' }}</span>
                    </button>
                    @endif

                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        @if($aplsatu)
                        <span class="badge bg-{{ $aplsatu->status === 'verified' ? 'success' : ($aplsatu->status === 'submitted' ? 'info' : 'secondary') }}"
                              style="font-size:.7rem;">
                            APL-01: {{ ucfirst($aplsatu->status) }}
                        </span>
                        @else
                        <span class="badge bg-light text-muted border" style="font-size:.7rem;">APL-01: Belum Ada</span>
                        @endif

                        @if($apldua)
                        <span class="badge bg-{{ $apldua->status_badge }}" style="font-size:.7rem;">
                            APL-02: {{ $apldua->status_label }}
                        </span>
                        @if($needsVerify)
                        <span class="badge bg-warning text-dark" style="font-size:.68rem;">
                            <i class="bi bi-exclamation-circle me-1"></i>Perlu Verifikasi
                        </span>
                        @endif
                        @else
                        <span class="badge bg-light text-muted border" style="font-size:.7rem;">APL-02: Belum Ada</span>
                        @endif
                    </div>

                    <a href="{{ route('asesor.asesi.detail', [$schedule, $asesmen]) }}"
                       class="btn btn-sm {{ $needsVerify ? 'btn-warning' : 'btn-outline-primary' }} flex-shrink-0">
                        <i class="bi bi-{{ $needsVerify ? 'pen-fill' : 'eye' }} me-1"></i>
                        {{ $needsVerify ? 'Verifikasi' : 'Detail' }}
                    </a>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-person-x" style="font-size:2.5rem;"></i>
                    <p class="mt-2 mb-0">Belum ada peserta di jadwal ini.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════
             TAB 2: PROGRESS ASESI
        ═══════════════════════════════════════════════════ --}}
        <div class="tab-pane fade p-0" id="tab-progress" role="tabpanel">
            <div class="px-4 pt-3 pb-0">
                <ul class="nav nav-pills gap-1 mb-3">
                    <li class="nav-item">
                        <button class="nav-link active btn-sm" data-bs-toggle="pill" data-bs-target="#prog-teori" type="button">
                            <i class="bi bi-journal-text me-1"></i>Teori
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn-sm" data-bs-toggle="pill" data-bs-target="#prog-observasi" type="button">
                            <i class="bi bi-eye me-1"></i>Observasi
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content px-0 pb-0">
                <div class="tab-pane fade show active" id="prog-teori">
                    @if(!$distribusiTeori)
                    <div class="text-center py-5 text-muted px-4">
                        <i class="bi bi-exclamation-circle" style="font-size:2rem;opacity:.3;"></i>
                        <p class="mt-2 fw-semibold">Soal teori belum didistribusikan</p>
                    </div>
                    @else
                    <div class="px-4 py-2 border-bottom bg-light d-flex flex-wrap gap-4 small text-muted">
                        <span><i class="bi bi-collection me-1"></i>{{ $distribusiTeori->jumlah_soal }} soal/asesi</span>
                        <span><i class="bi bi-people me-1"></i>{{ $asesmens->count() }} peserta</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width:40px">#</th>
                                    <th>Nama Asesi</th>
                                    <th class="text-center" style="min-width:160px">Jawaban</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Mulai</th>
                                    <th class="text-center">Submit</th>
                                    <th class="text-center">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asesmens as $i => $asesmen)
                                @php
                                    $soalAsesi   = $asesmen->soalTeoriAsesi ?? collect();
                                    $total       = $soalAsesi->count();
                                    $answered    = $soalAsesi->whereNotNull('jawaban')->count();
                                    $submitted   = $soalAsesi->whereNotNull('submitted_at')->count() > 0;
                                    $started     = $soalAsesi->whereNotNull('started_at')->count() > 0;
                                    $startedAt   = $soalAsesi->whereNotNull('started_at')->min('started_at');
                                    $submittedAt = $soalAsesi->whereNotNull('submitted_at')->max('submitted_at');
                                    $pct         = $total > 0 ? round($answered / $total * 100) : 0;
                                    $benar = 0;
                                    if ($submitted) {
                                        foreach ($soalAsesi as $sa) {
                                            if ($sa->jawaban !== null && $sa->soalTeori && $sa->jawaban === $sa->soalTeori->jawaban_benar) {
                                                $benar++;
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:.875rem;">{{ $asesmen->full_name }}</div>
                                        <small class="text-muted">{{ $asesmen->user->email ?? '-' }}</small>
                                    </td>
                                    <td class="text-center" style="min-width:150px;">
                                        @if($total > 0)
                                        <div class="d-flex align-items-center gap-2 px-2">
                                            <div class="progress flex-grow-1" style="height:7px;">
                                                <div class="progress-bar {{ $submitted ? 'bg-success' : 'bg-primary' }}"
                                                     style="width:{{ $pct }}%;"></div>
                                            </div>
                                            <span class="text-muted" style="font-size:.75rem;min-width:38px;">{{ $answered }}/{{ $total }}</span>
                                        </div>
                                        @else
                                        <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($total === 0)
                                        <span class="badge bg-light text-muted border" style="font-size:.7rem;">Belum Ada Soal</span>
                                        @elseif($submitted)
                                        <span class="badge bg-success" style="font-size:.7rem;"><i class="bi bi-check-circle me-1"></i>Disubmit</span>
                                        @elseif($started)
                                        <span class="badge bg-warning text-dark" style="font-size:.7rem;"><i class="bi bi-pencil-fill me-1"></i>Sedang Mengerjakan</span>
                                        @else
                                        <span class="badge bg-secondary" style="font-size:.7rem;">Belum Mulai</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted" style="font-size:.78rem;">
                                        {{ $startedAt ? \Carbon\Carbon::parse($startedAt)->translatedFormat('H:i') : '—' }}
                                    </td>
                                    <td class="text-center text-muted" style="font-size:.78rem;">
                                        {{ $submittedAt ? \Carbon\Carbon::parse($submittedAt)->translatedFormat('H:i') : '—' }}
                                    </td>
                                    <td class="text-center">
                                        @if($submitted && $total > 0)
                                        @php $pctBenar = round($benar / $total * 100); @endphp
                                        <span class="fw-bold {{ $pctBenar >= 70 ? 'text-success' : 'text-danger' }}" style="font-size:.875rem;">
                                            {{ $benar }}/{{ $total }}
                                        </span>
                                        <div class="text-muted" style="font-size:.7rem;">{{ $pctBenar }}%</div>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="prog-observasi">
                    @if($distribusiObservasi->isEmpty())
                    <div class="text-center py-5 text-muted px-4">
                        <i class="bi bi-exclamation-circle" style="font-size:2rem;opacity:.3;"></i>
                        <p class="mt-2 fw-semibold">Soal observasi belum didistribusikan</p>
                    </div>
                    @else
                    @foreach($distribusiObservasi as $dist)
                    @php $obs = $dist->soalObservasi; @endphp
                    <div class="border-bottom px-4 py-3">
                        <div class="fw-semibold mb-2" style="font-size:.875rem;">
                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i>{{ $obs->judul }}
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0 table-bordered" style="font-size:.82rem;">
                            @php $paketDist = $dist->paketSoalObservasi; @endphp
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:160px;">Asesi</th>
                                    @if($paketDist)
                                    <th class="text-center">Paket {{ $paketDist->kode_paket }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asesmens as $asesmen)
                                <tr>
                                    <td><div class="fw-semibold">{{ $asesmen->full_name }}</div></td>
                                    @if($paketDist)
                                    @php
                                        $jawaban = $asesmen->jawabanObservasi
                                            ->where('paket_soal_observasi_id', $paketDist->id)
                                            ->first();
                                    @endphp
                                    <td class="text-center">
                                        @if($jawaban?->hasLink())
                                        <a href="{{ $jawaban->gdrive_link }}" target="_blank"
                                        class="badge bg-success text-decoration-none" style="font-size:.7rem;">
                                            <i class="bi bi-check-circle me-1"></i>Upload
                                        </a>
                                        @else
                                        <span class="badge bg-light text-muted border" style="font-size:.7rem;">—</span>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════
             TAB 3: PENILAIAN
        ═══════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="tab-penilaian">
            <div class="row g-4 p-3">
                @if(!$apl02Ak01Ready)
                <div class="col-12">
                    <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0">
                        <i class="bi bi-lock-fill flex-shrink-0"></i>
                        <div class="small">
                            <strong>Upload hasil & berita acara belum tersedia.</strong>
                            Minimal 1 asesi harus memiliki APL-02 dan FR.AK.01 yang sudah diverifikasi.
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-lg-7">

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-semibold small py-2">
                            <i class="bi bi-people me-2 text-primary"></i>Daftar Nama Asesi
                        </div>
                        <table class="table table-sm table-bordered mb-0" style="font-size:.875rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:40px;">No</th>
                                    <th>Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedule->asesmens as $i => $asesmen)
                                <tr>
                                    <td class="ps-3 text-muted text-center">{{ $i + 1 }}</td>
                                    <td>{{ $asesmen->full_name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Observasi --}}
                    @php $distribusiObs = $schedule->distribusiSoalObservasi ?? collect(); @endphp
                    @if($distribusiObs->isNotEmpty())
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-eye text-primary me-2"></i>Observasi
                        </h6>
                        <div class="d-flex flex-column gap-3">
                            @foreach($distribusiObs as $dist)
                            @php
                                $obs   = $dist->soalObservasi;
                                $hasil = $schedule->hasilObservasi->where('soal_observasi_id', $obs->id)->first();
                            @endphp
                            <div class="border rounded-3 overflow-hidden {{ $hasil ? 'border-success' : '' }}">
                                <div class="d-flex align-items-center gap-3 px-3 py-2
                                    {{ $hasil ? 'bg-success-subtle' : 'bg-light' }}">
                                    <i class="bi {{ $hasil ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} fs-5 flex-shrink-0"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">{{ $obs->judul }}</div>
                                        @if($hasil)
                                        <div class="text-muted" style="font-size:.75rem;">
                                            <i class="bi bi-file-earmark me-1"></i>{{ $hasil->file_name }}
                                            · {{ $hasil->uploaded_at->translatedFormat('d M Y H:i') }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2 flex-shrink-0">
                                        @if($obs->paket->isNotEmpty())
                                        <a href="{{ route('asesor.jadwal.template.observasi', [$schedule, $obs]) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Download soal observasi">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Soal
                                        </a>
                                        @endif
                                        @php $paketAktif = $dist->paketSoalObservasi; 
                                        @endphp
                                        @if($paketAktif?->lampiran_path)
                                        
                                        <a href="{{ route('asesor.observasi.download-lampiran', $paketAktif) }}"
                                        class="btn btn-sm btn-outline-info" title="Download panduan soal">
                                            <i class="bi bi-book me-1"></i> Lampiran Format Formulir
                                        </a>
                                        @endif
                                        @php
                                            $distObs = $schedule->distribusiSoalObservasi->firstWhere('soal_observasi_id', $obs->id);
                                        @endphp
                                        @if($distObs?->form_penilaian_path)
                                        <a href="{{ route('asesor.jadwal.observasi.form-penilaian', [$schedule, $obs]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Download form penilaian">
                                            <i class="bi bi-clipboard2-check me-1"></i>Form Penilaian
                                        </a>
                                        @else
                                        <span class="btn btn-sm btn-outline-secondary disabled">
                                            <i class="bi bi-clipboard2-x me-1"></i>Form Penilaian
                                        </span>
                                        @endif
                                        @if($hasil)
                                        <a href="{{ route('asesor.jadwal.observasi.download', [$schedule, $obs]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('asesor.jadwal.observasi.hapus', [$schedule, $obs]) }}"
                                              onsubmit="return confirm('Hapus file ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                        @endif
                                        <button class="btn btn-sm {{ $hasil ? 'btn-outline-primary' : 'btn-primary' }}"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#uploadObs{{ $obs->id }}">
                                            <i class="bi bi-upload me-1"></i>{{ $hasil ? 'Ganti' : 'Upload Hasil' }}
                                        </button>
                                    </div>
                                </div>
                                <div class="collapse" id="uploadObs{{ $obs->id }}">
                                    <div class="px-3 py-3 border-top bg-light">
                                        @if(!$apl02Ak01Ready)
                                        <div class="alert alert-warning py-2 small mb-0">
                                            <i class="bi bi-lock me-1"></i>Verifikasi APL-02 & FR.AK.01 minimal 1 asesi terlebih dahulu.
                                        </div>
                                        @else
                                        <form method="POST"
                                            action="{{ route('asesor.jadwal.observasi.upload', [$schedule, $obs]) }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="d-flex gap-2 align-items-end">
                                                <div class="flex-grow-1">
                                                    <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx" required>
                                                    <div class="form-text"><i class="bi bi-info-circle me-1"></i>Hanya file <strong>.xlsx</strong> · Maks. 20 MB</div>
                                                </div>
                                                <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan" style="width:130px;">
                                                <button type="submit" class="btn btn-primary btn-sm flex-shrink-0"><i class="bi bi-upload"></i></button>
                                            </div>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Portofolio --}}
                    @php $distribusiPorto = $schedule->distribusiPortofolio ?? collect(); @endphp
                    @if($distribusiPorto->isNotEmpty())
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-briefcase me-2" style="color:#7c3aed"></i>Portofolio
                        </h6>
                        <div class="d-flex flex-column gap-3">
                            @foreach($distribusiPorto as $dist)
                            @php
                                $porto = $dist->portofolio;
                                $hasil = $schedule->hasilPortofolio->where('portofolio_id', $porto->id)->first();
                            @endphp
                            <div class="border rounded-3 overflow-hidden {{ $hasil ? 'border-success' : '' }}">
                                <div class="d-flex align-items-center gap-3 px-3 py-2
                                    {{ $hasil ? 'bg-success-subtle' : 'bg-light' }}">
                                    <i class="bi {{ $hasil ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} fs-5 flex-shrink-0"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">{{ $porto->judul }}</div>
                                        @if($hasil)
                                        <div class="text-muted" style="font-size:.75rem;">
                                            {{ $hasil->file_name }} · {{ $hasil->uploaded_at->translatedFormat('d M Y H:i') }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2 flex-shrink-0">
                                        @if($porto->hasFile())
                                        <a href="{{ route('asesor.jadwal.template.portofolio', [$schedule, $porto]) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-file-earmark-arrow-down me-1"></i>Template
                                        </a>
                                        @endif
                                        @if($hasil)
                                        <a href="{{ route('asesor.jadwal.portofolio.download', [$schedule, $porto]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('asesor.jadwal.portofolio.hapus', [$schedule, $porto]) }}"
                                              onsubmit="return confirm('Hapus file ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                        @endif
                                        <button class="btn btn-sm {{ $hasil ? 'btn-outline-primary' : 'btn-primary' }}"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#uploadPorto{{ $porto->id }}">
                                            <i class="bi bi-upload me-1"></i>{{ $hasil ? 'Ganti' : 'Upload Hasil' }}
                                        </button>
                                    </div>
                                </div>
                                <div class="collapse" id="uploadPorto{{ $porto->id }}">
                                    <div class="px-3 py-3 border-top bg-light">
                                        @if(!$apl02Ak01Ready)
                                        <div class="alert alert-warning py-2 small mb-0">
                                            <i class="bi bi-lock me-1"></i>Verifikasi APL-02 & FR.AK.01 minimal 1 asesi terlebih dahulu.
                                        </div>
                                        @else
                                        <form method="POST"
                                            action="{{ route('asesor.jadwal.portofolio.upload', [$schedule, $porto]) }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="d-flex gap-2 align-items-end">
                                                <div class="flex-grow-1">
                                                    <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx" required>
                                                    <div class="form-text"><i class="bi bi-info-circle me-1"></i>Hanya file <strong>.xlsx</strong> · Maks. 20 MB</div>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm flex-shrink-0"><i class="bi bi-upload"></i></button>
                                            </div>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    {{-- Foto Dokumentasi --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-semibold small py-2 d-flex align-items-center justify-content-between">
                            <div><i class="bi bi-camera text-info me-2"></i>Foto Dokumentasi</div>
                            @if($schedule->hasFotoDokumentasi())
                            <span class="badge bg-success" style="font-size:.65rem;"><i class="bi bi-check-circle me-1"></i>Lengkap</span>
                            @elseif($schedule->foto_dokumentasi_1 || $schedule->foto_dokumentasi_2)
                            <span class="badge bg-warning text-dark" style="font-size:.65rem;">Sebagian</span>
                            @else
                            <span class="badge bg-secondary" style="font-size:.65rem;">Belum diupload</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if(!$apl02Ak01Ready)
                            <div class="alert alert-warning py-2 small mb-0">
                                <i class="bi bi-lock me-1"></i>Verifikasi APL-02 & FR.AK.01 minimal 1 asesi terlebih dahulu.
                            </div>
                            @else
                            <div class="row g-3 mb-3">
                                @foreach([1, 2] as $slot)
                                @php $col = "foto_dokumentasi_{$slot}"; $ada = $schedule->$col; @endphp
                                <div class="col-6">
                                    <div class="border rounded-3 overflow-hidden {{ $ada ? 'border-success' : '' }}" style="min-height:140px;">
                                        @if($ada)
                                        <img src="{{ route('asesor.schedule.foto-dokumentasi.preview', [$schedule, $slot]) }}"
                                            class="w-100" style="max-height:180px;object-fit:cover;" alt="Foto {{ $slot }}">
                                        <div class="d-flex justify-content-between align-items-center px-2 py-1 bg-success-subtle">
                                            <small class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Foto {{ $slot }}</small>
                                            <form method="POST" action="{{ route('asesor.schedule.foto-dokumentasi.hapus', [$schedule, $slot]) }}"
                                                onsubmit="return confirm('Hapus foto ini?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger py-0 px-1" style="font-size:.75rem;">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                        <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted flex-column"
                                            style="min-height:140px;">
                                            <i class="bi bi-image" style="font-size:2rem;opacity:.3;"></i>
                                            <small class="mt-1">Foto {{ $slot }} belum diupload</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <form method="POST"
                                action="{{ route('asesor.schedule.foto-dokumentasi.upload', $schedule) }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small">
                                            Foto 1 {{ $schedule->foto_dokumentasi_1 ? '(Ganti)' : '' }}
                                        </label>
                                        <input type="file" name="foto_1" class="form-control form-control-sm" accept="image/jpeg,image/png">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small">
                                            Foto 2 {{ $schedule->foto_dokumentasi_2 ? '(Ganti)' : '' }}
                                        </label>
                                        <input type="file" name="foto_2" class="form-control form-control-sm" accept="image/jpeg,image/png">
                                    </div>
                                    <div class="col-12">
                                        <div class="form-text mb-2"><i class="bi bi-info-circle me-1"></i>JPG/PNG · Maks. 5 MB per foto</div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-upload me-1"></i>Upload Foto
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @endif
                        </div>
                    </div>
                    @if($distribusiObs->isEmpty() && $distribusiPorto->isEmpty())
                    <div class="text-center py-5 text-muted border rounded-3">
                        <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3;"></i>
                        <p class="mt-2 mb-0 small">Tidak ada observasi atau portofolio untuk jadwal ini.</p>
                    </div>
                    @endif
                </div>

                {{-- Berita Acara --}}
                <div class="col-lg-5">
                    @php
                        $ba       = $schedule->beritaAcara;
                        $baLocked = $ba && $ba->isSigned();
                    @endphp
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
                            <div><i class="bi bi-file-text text-warning me-2"></i>Berita Acara</div>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                @if($ba && $ba->asesis->isNotEmpty())
                                <a href="{{ route('asesor.jadwal.berita-acara.pdf', $schedule) }}?preview=1"
                                   target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-file-pdf me-1"></i>Preview
                                </a>
                                <a href="{{ route('asesor.jadwal.berita-acara.pdf', $schedule) }}"
                                   class="btn btn-sm btn-danger">
                                    <i class="bi bi-download me-1"></i>PDF
                                </a>
                                @endif
                                @if($baLocked)
                                <span class="badge bg-success" style="font-size:.65rem;">
                                    <i class="bi bi-lock-fill me-1"></i>Ditandatangani
                                </span>
                                @elseif($ba)
                                <span class="badge bg-warning text-dark" style="font-size:.65rem;">Tersimpan</span>
                                @else
                                <span class="badge bg-secondary" style="font-size:.65rem;">Belum Diisi</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Info locked --}}
                            @if($baLocked)
                            <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.82rem;">
                                <i class="bi bi-lock-fill flex-shrink-0"></i>
                                <div>
                                    Berita acara telah ditandatangani oleh asesor pada
                                    <strong>{{ $ba->signed_at->translatedFormat('d M Y, H:i') }}</strong>.
                                    Data tidak dapat diubah.
                                </div>
                            </div>
                            @endif

                            @if($ba && $ba->asesis->isNotEmpty())
                            @php
                                $jumlahK  = $ba->asesis->where('rekomendasi', 'K')->count();
                                $jumlahBK = $ba->asesis->where('rekomendasi', 'BK')->count();
                            @endphp
                            <div class="mb-3 border rounded-3 overflow-hidden">
                                <div class="px-3 py-2 d-flex align-items-center justify-content-between fw-semibold small"
                                     style="background:#f0fdf4;border-bottom:1px solid #bbf7d0;">
                                    <span><i class="bi bi-check-circle-fill text-success me-2"></i>Hasil Terbaca</span>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-success">{{ $jumlahK }} K</span>
                                        <span class="badge bg-danger">{{ $jumlahBK }} BK</span>
                                    </div>
                                </div>
                                <table class="table table-sm mb-0" style="font-size:.8rem;">
                                    <tbody>
                                        @foreach($schedule->asesmens as $i => $asesmen)
                                        @php $rek = $rekomendasiMap[$asesmen->id] ?? null; @endphp
                                        <tr class="{{ $rek === 'K' ? 'table-success' : ($rek === 'BK' ? 'table-danger' : '') }}">
                                            <td class="ps-3 text-muted" style="width:32px;">{{ $i+1 }}</td>
                                            <td>{{ $asesmen->full_name }}</td>
                                            <td class="text-end pe-3" style="width:55px;">
                                                @if($rek)
                                                <span class="badge {{ $rek === 'K' ? 'bg-success' : 'bg-danger' }}">{{ $rek }}</span>
                                                @else
                                                <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="mb-3 p-3 border rounded-3 text-center text-muted bg-light" style="font-size:.8rem;">
                                <i class="bi bi-file-earmark-x d-block mb-1" style="font-size:1.5rem;opacity:.3;"></i>
                                Berita Acara belum terbaca. Upload file .xlsx hasil penilaian untuk auto-extract K/BK.
                            </div>
                            @endif

                            @if(!$baLocked)
                            <form method="POST" action="{{ route('asesor.jadwal.berita-acara.simpan', $schedule) }}">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label fw-semibold small">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_pelaksanaan"
                                           class="form-control form-control-sm"
                                           value="{{ $ba?->tanggal_pelaksanaan?->translatedFormat('Y-m-d') ?? $schedule->assessment_date->translatedFormat('Y-m-d') }}"
                                           required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-semibold small">
                                        Rekomendasi per Asesi
                                        <span class="text-muted fw-normal">(edit jika perlu)</span>
                                    </label>
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($schedule->asesmens as $asesmen)
                                        @php $rek = $rekomendasiMap[$asesmen->id] ?? null; @endphp
                                        <div class="d-flex align-items-center gap-2 px-2 py-1 border rounded-2
                                            {{ $rek === 'K' ? 'border-success bg-success-subtle' : ($rek === 'BK' ? 'border-danger bg-danger-subtle' : 'bg-light') }}"
                                             style="font-size:.8rem;">
                                            <div class="flex-grow-1 fw-semibold">{{ $asesmen->full_name }}</div>
                                            <div class="d-flex gap-2 flex-shrink-0">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="radio"
                                                           name="rekomendasi[{{ $asesmen->id }}]"
                                                           id="k_{{ $asesmen->id }}" value="K"
                                                           {{ $rek === 'K' ? 'checked' : '' }} required>
                                                    <label class="form-check-label fw-bold text-success" for="k_{{ $asesmen->id }}">K</label>
                                                </div>
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="radio"
                                                           name="rekomendasi[{{ $asesmen->id }}]"
                                                           id="bk_{{ $asesmen->id }}" value="BK"
                                                           {{ $rek === 'BK' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold text-danger" for="bk_{{ $asesmen->id }}">BK</label>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Catatan</label>
                                    <textarea name="catatan" class="form-control form-control-sm" rows="2"
                                              placeholder="Opsional...">{{ $ba?->catatan }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100" {{ !$apl02Ak01Ready ? 'disabled' : '' }}>
                                    <i class="bi bi-save me-1"></i>Simpan Berita Acara
                                    @if(!$apl02Ak01Ready)<i class="bi bi-lock ms-1"></i>@endif
                                </button>
                                @if(!$apl02Ak01Ready)
                                <div class="text-muted small mt-1 text-center">
                                    <i class="bi bi-info-circle me-1"></i>Verifikasi APL-02 & FR.AK.01 minimal 1 asesi terlebih dahulu.
                                </div>
                                @endif
                            </form>

                            {{-- Tombol TTD Berita Acara (hanya tampil jika data sudah ada) --}}
                            @if($ba && $ba->asesis->isNotEmpty())
                            <hr class="my-3">
                            <button type="button"
                                    class="btn btn-success btn-sm w-100"
                                    onclick="bukaModalTtdBeritaAcara()">
                                <i class="bi bi-pen-fill me-1"></i>Tanda Tangan & Kunci Berita Acara
                            </button>
                            @endif
                            @else
                            {{-- Jika sudah locked: tampilkan read-only --}}
                            <div class="mb-2">
                                <label class="form-label fw-semibold small text-muted">Tanggal Pelaksanaan</label>
                                <div class="form-control form-control-sm bg-light" style="pointer-events:none;">
                                    {{ $ba->tanggal_pelaksanaan->translatedFormat('d M Y') }}
                                </div>
                            </div>
                            @if($ba->catatan)
                            <div class="mb-2">
                                <label class="form-label fw-semibold small text-muted">Catatan</label>
                                <div class="form-control form-control-sm bg-light" style="pointer-events:none;white-space:pre-wrap;">{{ $ba->catatan }}</div>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Modal Verifikasi Daftar Hadir --}}
        <div class="modal fade" id="modalVerifikasiHadir" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-clipboard-check me-2"></i>Verifikasi & Tandatangani Daftar Hadir
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Rekap kehadiran --}}
                        <div class="border rounded-3 overflow-hidden mb-4">
                            <div class="px-3 py-2 bg-light fw-semibold small border-bottom d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-people me-2 text-primary"></i>Rekap Kehadiran</span>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-success">{{ $hadirCount }} Hadir</span>
                                    <span class="badge bg-secondary">{{ $totalAsesi - $hadirCount }} Tidak Hadir</span>
                                </div>
                            </div>
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr style="font-size:.8rem;">
                                        <th class="ps-3" style="width:40px;">#</th>
                                        <th>Nama</th>
                                        <th class="text-center" style="width:110px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asesmens as $i => $asesmen)
                                    @php $isHadir = ($asesmen->hadir ?? true) !== false; @endphp
                                    <tr style="font-size:.82rem;" class="{{ $isHadir ? '' : 'table-danger' }}">
                                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $asesmen->full_name }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $isHadir ? 'bg-success' : 'bg-danger' }}" style="font-size:.7rem;">
                                                {{ $isHadir ? 'Hadir' : 'Tidak Hadir' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning d-flex gap-2 py-2 px-3 mb-4" style="font-size:.82rem;">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                            <div>Setelah ditandatangani, <strong>daftar hadir tidak dapat diubah lagi</strong>. Pastikan data kehadiran sudah benar sebelum melanjutkan.</div>
                        </div>

                        @include('partials._signature_pad', [
                            'padId'    => 'daftar-hadir-ttd',
                            'padLabel' => 'Tanda Tangan Asesor',
                            'padHeight' => 160,
                            'savedSig'  => $asesor?->user?->signature_image,
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                            Periksa Ulang
                        </button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" id="btn-confirm-ttd-hadir"
                                onclick="confirmTtdDaftarHadir()">
                            <i class="bi bi-lock-fill me-1"></i>Tandatangani & Kunci
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal TTD Berita Acara --}}
        <div class="modal fade" id="modalTtdBeritaAcara" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success text-white">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-pen-fill me-2"></i>Tanda Tangan Berita Acara
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex gap-2 py-2 mb-3" style="font-size:.82rem;">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                            <div>Setelah ditandatangani, berita acara <strong>tidak dapat diubah lagi</strong>. Pastikan data sudah benar.</div>
                        </div>
                        @include('partials._signature_pad', [
                            'padId'    => 'ba-asesor-ttd',
                            'padLabel' => 'Tanda Tangan Asesor',
                            'padHeight' => 160,
                            'savedSig'  => $asesor?->user?->signature_image,
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success btn-sm fw-semibold" id="btn-confirm-ttd-ba"
                                onclick="confirmTtdBeritaAcara()">
                            <i class="bi bi-lock-fill me-1"></i>Tanda Tangan & Kunci
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal TTD universal --}}
        <div class="modal fade" id="modalTtdAsesor" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-pen me-2 text-primary"></i>
                            <span id="modalTtdTitle">Tanda Tangan Asesor</span>
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('partials._signature_pad', [
                            'padId'    => 'asesor-ttd',
                            'padLabel' => 'Tanda Tangan',
                            'padHeight' => 160,
                            'savedSig'  => $asesor?->user?->signature_image,
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="simpanTtdAsesor()">
                            <i class="bi bi-save me-1"></i>Simpan Tanda Tangan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content;
const TOTAL_ASESI  = {{ $totalAsesi }};

// ── Restore active tab from URL hash ──
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tab) new bootstrap.Tab(tab).show();
    }
    document.querySelectorAll('#mainTabs [data-bs-toggle="tab"]').forEach(function (tabEl) {
        tabEl.addEventListener('shown.bs.tab', function (e) {
            history.replaceState(null, null, e.target.dataset.bsTarget);
        });
    });
});

// ── Toggle Hadir (tombol dinamis) ──
async function toggleHadirBtn(btn) {
    const asesmenId  = btn.dataset.id;
    const isHadir    = btn.dataset.hadir === '1';   // status saat ini
    const newHadir   = !isHadir;                    // status baru

    btn.disabled = true;

    try {
        const res  = await fetch(`/asesor/asesmen/${asesmenId}/hadir`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ hadir: newHadir }),
        });
        const data = await res.json();

        if (data.success) {
            btn.dataset.hadir = data.hadir ? '1' : '0';

            if (data.hadir) {
                btn.className = 'btn btn-sm hadir-toggle-btn btn-success';
                btn.querySelector('.hadir-label').textContent = 'Hadir';
                btn.querySelector('i').className = 'bi bi-person-check-fill me-1';
            } else {
                btn.className = 'btn btn-sm hadir-toggle-btn btn-outline-danger';
                btn.querySelector('.hadir-label').textContent = 'Tidak Hadir';
                btn.querySelector('i').className = 'bi bi-person-x-fill me-1';
            }

            // Update badge counts
            const hadirCount = document.querySelectorAll('.hadir-toggle-btn[data-hadir="1"]').length;
            document.getElementById('badge-hadir').textContent        = hadirCount + ' Hadir';
            document.getElementById('badge-tidak-hadir').textContent  = (TOTAL_ASESI - hadirCount) + ' Tidak Hadir';
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message ?? 'Gagal mengubah kehadiran.', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan.', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
    } finally {
        btn.disabled = false;
    }
}

// ── Init sig pad saat modal dibuka ──
document.getElementById('modalTtdAsesor').addEventListener('shown.bs.modal', () => {
    SigPadManager.init('asesor-ttd', @json($asesor?->user?->signature_image));
});

function bukaModalTtd(konteks) {
    const titles = {
        'daftar-hadir': 'Tanda Tangan untuk Daftar Hadir',
        'berita-acara': 'Tanda Tangan untuk Berita Acara',
    };
    document.getElementById('modalTtdTitle').textContent = titles[konteks] ?? 'Tanda Tangan Asesor';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTtdAsesor')).show();
}

async function simpanTtdAsesor() {
    if (SigPadManager.isEmpty('asesor-ttd')) {
        Swal.fire({ icon: 'warning', title: 'Tanda Tangan Kosong', text: 'Gambar atau upload tanda tangan dulu.' });
        return;
    }

    const dataURL = await SigPadManager.prepareAndGet('asesor-ttd');

    try {
        const res  = await fetch('/user/signature', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ signature: dataURL }),
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTtdAsesor')).hide();
            Swal.fire({
                icon: 'success', title: 'Tanda tangan disimpan!',
                text: 'Tanda tangan akan muncul di Daftar Hadir dan Berita Acara PDF.',
                timer: 2000, showConfirmButton: false,
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}

// ── Modal Verifikasi Daftar Hadir ──
const _ttdHadirUrl = '{{ route("asesor.schedule.daftar-hadir.sign", $schedule) }}';

document.getElementById('modalVerifikasiHadir')?.addEventListener('shown.bs.modal', () => {
    SigPadManager.init('daftar-hadir-ttd', @json($asesor?->user?->signature_image));
});

function bukaModalVerifikasiHadir() {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerifikasiHadir')).show();
}

async function confirmTtdDaftarHadir() {
    if (SigPadManager.isEmpty('daftar-hadir-ttd')) {
        Swal.fire({
            icon: 'warning', title: 'Tanda Tangan Diperlukan',
            text: 'Silakan tanda tangan terlebih dahulu.',
            toast: true, position: 'top-end', showConfirmButton: false, timer: 2500,
        });
        return;
    }

    const btn = document.getElementById('btn-confirm-ttd-hadir');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

    try {
        const dataURL = await SigPadManager.prepareAndGet('daftar-hadir-ttd');

        const res = await fetch(_ttdHadirUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ signature: dataURL }),
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message ?? 'Gagal menyimpan tanda tangan.');
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerifikasiHadir')).hide();
        Swal.fire({
            icon: 'success', title: 'Daftar hadir ditandatangani!',
            text: 'Kehadiran telah dikunci dan PDF siap didownload.',
            timer: 2000, showConfirmButton: false,
        }).then(() => location.reload());

    } catch (err) {
        Swal.fire('Gagal', err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i>Tandatangani & Kunci';
    }
}

// ── Modal TTD Berita Acara ──
const _ttdBaUrl = '{{ route("asesor.jadwal.berita-acara.tanda-tangan", $schedule) }}';

document.getElementById('modalTtdBeritaAcara')?.addEventListener('shown.bs.modal', () => {
    SigPadManager.init('ba-asesor-ttd', @json($asesor?->user?->signature_image));
});

function bukaModalTtdBeritaAcara() {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTtdBeritaAcara')).show();
}

async function confirmTtdBeritaAcara() {
    if (SigPadManager.isEmpty('ba-asesor-ttd')) {
        Swal.fire({
            icon: 'warning', title: 'Tanda Tangan Diperlukan',
            text: 'Silakan tanda tangan terlebih dahulu.',
            toast: true, position: 'top-end', showConfirmButton: false, timer: 2500,
        });
        return;
    }

    const btn = document.getElementById('btn-confirm-ttd-ba');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

    try {
        const dataURL = await SigPadManager.prepareAndGet('ba-asesor-ttd');

        const res  = await fetch(_ttdBaUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ signature: dataURL }),
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message ?? 'Gagal menyimpan tanda tangan.');
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTtdBeritaAcara')).hide();
        Swal.fire({
            icon: 'success', title: 'Berita acara ditandatangani!',
            text: 'Data berita acara telah dikunci.',
            timer: 2000, showConfirmButton: false,
        }).then(() => location.reload());

    } catch (err) {
        Swal.fire('Gagal', err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i>Tanda Tangan & Kunci';
    }
}

// ── Mulai Asesmen ──
async function konfirmasiMulaiAsesmen() {
    const result = await Swal.fire({
        title: 'Mulai Asesmen?',
        html: 'Semua asesi yang FR.AK.01 dan APL-02-nya sudah diverifikasi akan diubah statusnya menjadi <strong>Sedang Diases</strong>.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-play-fill me-1"></i>Ya, Mulai',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    const btn = document.getElementById('btn-mulai-asesmen');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memulai...';

    try {
        const res  = await fetch('{{ route("asesor.schedule.mulai", $schedule) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success', title: 'Asesmen Dimulai!',
                text: data.message,
                showConfirmButton: false, timer: 2000,
            }).then(() => location.reload());
        } else {
            Swal.fire('Tidak Bisa Dimulai', data.message, 'warning');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-fill me-1"></i>Mulai Asesmen';
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-fill me-1"></i>Mulai Asesmen';
    }
}
</script>
@endpush

@endsection