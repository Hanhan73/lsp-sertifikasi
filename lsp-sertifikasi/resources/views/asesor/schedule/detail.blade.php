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

            {{-- Action buttons --}}
            <div class="d-flex gap-2 align-items-start flex-wrap">
                @if($schedule->hasSk())
                <a href="{{ route('asesor.schedule.sk.download', $schedule) }}"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-download me-1"></i>Unduh SK
                </a>
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

    // Hitung sudah submit soal teori
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
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-soal" type="button">
                    <i class="bi bi-collection me-1"></i>Soal Asesmen
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
                        $hasilObsCount  = $schedule->hasilObservasi->count();
                        $hasilPortoCount = $schedule->hasilPortofolio->count();
                        $hasBA = $schedule->beritaAcara !== null;
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

            {{-- Daftar Hadir mini strip --}}
            @php
                $hadirCount = $asesmens->where('hadir', true)->count(); // kolom hadir jika ada
            @endphp
            <div class="px-4 pt-3 pb-2 border-bottom d-flex align-items-center gap-3 flex-wrap">
                <span class="fw-semibold small"><i class="bi bi-person-check text-success me-1"></i>Daftar Hadir</span>
                <span class="badge bg-success">{{ $hadirCount }} Hadir</span>
                <span class="badge bg-secondary">{{ $totalAsesi - $hadirCount }} Belum</span>
                @include('asesor.schedule._daftar-hadir-btn', ['schedule' => $schedule, 'asesor' => $asesor])
            </div>

            <div class="p-0">
                @forelse($asesmens as $idx => $asesmen)
                @php
                    $aplsatu     = $asesmen->aplsatu;
                    $apldua      = $asesmen->apldua;
                    $needsVerify = $apldua?->status === 'submitted';
                @endphp
                <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom
                    {{ $needsVerify ? 'bg-warning bg-opacity-5' : '' }}">

                    {{-- No --}}
                    <div class="text-muted fw-bold" style="min-width:28px;">{{ $idx + 1 }}</div>

                    {{-- Foto --}}
                    @if($asesmen->photo_path)
                    <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                         class="rounded-circle border" style="width:44px;height:44px;object-fit:cover;" alt="foto">
                    @else
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:44px;height:44px;font-size:1.1rem;">
                        {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
                    </div>
                    @endif

                    {{-- Info --}}
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $asesmen->full_name }}</div>
                        <div class="text-muted small">NIK: {{ $asesmen->nik }}</div>
                    </div>

                    {{-- Kehadiran --}}
                    <div class="form-check form-switch d-flex align-items-center gap-1 flex-shrink-0">
                        <input class="form-check-input hadir-toggle" type="checkbox"
                               id="hadir-{{ $asesmen->id }}"
                               data-id="{{ $asesmen->id }}"
                               {{ $asesmen->hadir ?? false ? 'checked' : '' }}
                               title="Tandai hadir">
                        <label class="form-check-label small text-muted" for="hadir-{{ $asesmen->id }}">Hadir</label>
                    </div>

                    {{-- Status badges --}}
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

                    {{-- Action --}}
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
             TAB 2: SOAL ASESMEN (detail bank soal yang terdistribusi)
        ═══════════════════════════════════════════════════ --}}
        <div class="tab-pane fade p-0" id="tab-soal" role="tabpanel">

            {{-- Sub-tabs: Teori | Observasi | Portofolio --}}
            <div class="px-4 pt-3 pb-0">
                <ul class="nav nav-pills gap-1 mb-3" id="soalTabs">
                    <li class="nav-item">
                        <button class="nav-link active btn-sm" data-bs-toggle="pill" data-bs-target="#soal-teori" type="button">
                            <i class="bi bi-journal-text me-1"></i>Soal Teori PG
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn-sm" data-bs-toggle="pill" data-bs-target="#soal-observasi" type="button">
                            <i class="bi bi-eye me-1"></i>Observasi
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn-sm" data-bs-toggle="pill" data-bs-target="#soal-portofolio" type="button">
                            <i class="bi bi-folder2-open me-1"></i>Portofolio
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content px-4 pb-4">

                {{-- Soal Teori --}}
                <div class="tab-pane fade show active" id="soal-teori">
                    @if(!$distribusiTeori)
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-journal-x" style="font-size:2.5rem;opacity:.3;"></i>
                        <p class="mt-2 fw-semibold">Soal teori belum didistribusikan ke jadwal ini</p>
                        <small>Hubungi Manajer Sertifikasi untuk mendistribusikan soal.</small>
                    </div>
                    @else
                    {{-- Konfigurasi distribusi --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-light text-center">
                                <div class="fs-4 fw-bold text-primary">{{ $distribusiTeori->jumlah_soal }}</div>
                                <div class="small text-muted">Soal / Asesi</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-light text-center">
                                @php $totalBankTeori = \App\Models\SoalTeori::where('skema_id', $schedule->skema_id)->count(); @endphp
                                <div class="fs-4 fw-bold text-secondary">{{ $totalBankTeori }}</div>
                                <div class="small text-muted">Total di Bank Soal</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-light text-center">
                                <div class="fs-4 fw-bold text-success">{{ $asesmens->count() }}</div>
                                <div class="small text-muted">Peserta Menerima</div>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar soal teori (read-only) --}}
                    @php
                        $bankTeori = \App\Models\SoalTeori::where('skema_id', $schedule->skema_id)
                            ->orderBy('id')->get();
                    @endphp
                    @if($bankTeori->isEmpty())
                    <p class="text-muted">Bank soal teori kosong.</p>
                    @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($bankTeori as $i => $soal)
                        <div class="border rounded-3 p-3">
                            <div class="d-flex gap-2 align-items-start">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold"
                                      style="min-width:28px;font-size:.8rem;">{{ $i + 1 }}</span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-2" style="font-size:.875rem;">{{ $soal->pertanyaan }}</div>
                                    <div class="row g-1">
                                        @foreach(['a','b','c','d','e'] as $opt)
                                            @php $val = $soal->{'pilihan_' . $opt} ?? null; @endphp
                                            @if($val)
                                            <div class="col-md-6">
                                                <span class="d-inline-flex align-items-center gap-2 px-2 py-1 rounded-2 w-100
                                                    {{ $soal->jawaban_benar === $opt ? 'bg-success bg-opacity-10 border border-success' : 'bg-light' }}"
                                                    style="font-size:.8rem;">
                                                    <span class="badge {{ $soal->jawaban_benar === $opt ? 'bg-success' : 'bg-secondary' }}"
                                                          style="font-size:.7rem;min-width:20px;">
                                                        {{ strtoupper($opt) }}
                                                    </span>
                                                    {{ $val }}
                                                    @if($soal->jawaban_benar === $opt)
                                                    <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                                    @endif
                                                </span>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endif
                </div>

                {{-- Soal Observasi --}}
                <div class="tab-pane fade" id="soal-observasi">
                    @if($distribusiObservasi->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-eye-slash" style="font-size:2.5rem;opacity:.3;"></i>
                        <p class="mt-2 fw-semibold">Soal observasi belum didistribusikan</p>
                    </div>
                    @else
                    @php
                        $observasiList = \App\Models\SoalObservasi::with('paket')
                            ->where('skema_id', $schedule->skema_id)->get();
                    @endphp
                    @foreach($distribusiObservasi as $dist)
                    @php $obs = $dist->soalObservasi; @endphp
                    <div class="border rounded-3 p-4 mb-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                            <h6 class="fw-bold mb-0">{{ $obs->judul }}</h6>
                            <span class="badge bg-primary ms-auto">{{ $obs->paket->count() }} Paket</span>
                        </div>
                        <div class="row g-2">
                            @foreach($obs->paket as $paket)
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded-3 p-3 bg-light d-flex align-items-center gap-2">
                                    <span class="badge bg-primary" style="font-size:.75rem;">Paket {{ $paket->kode_paket }}</span>
                                    <div class="flex-grow-1 small">
                                        <div class="fw-semibold text-truncate">{{ $paket->file_name ?? 'File tersedia' }}</div>
                                    </div>
                                    @if($paket->file_path)
                                    <a href="{{ route('asesor.observasi.download-paket', $paket) }}"
                                       class="btn btn-xs btn-outline-primary btn-sm flex-shrink-0" style="font-size:.7rem;"
                                       title="Unduh paket">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

                {{-- Portofolio --}}
                <div class="tab-pane fade" id="soal-portofolio">
                    @php
                        $distribusiPortofolio = $schedule->distribusiPortofolio ?? collect();
                    @endphp
                    @if($distribusiPortofolio->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-folder-x" style="font-size:2.5rem;opacity:.3;"></i>
                        <p class="mt-2 fw-semibold">Portofolio belum didistribusikan</p>
                    </div>
                    @else
                    @foreach($distribusiPortofolio as $dp)
                    @php $porto = $dp->portofolio; @endphp
                    <div class="border rounded-3 p-4 mb-3 d-flex align-items-center gap-3">
                        <i class="bi bi-folder2-open text-warning fs-4"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $porto->nama ?? '-' }}</div>
                            <div class="text-muted small">{{ $porto->deskripsi ?? '-' }}</div>
                        </div>
                        @if($porto->file_path)
                        <a href="{{ route('asesor.portofolio.download', $porto) }}"
                           class="btn btn-sm btn-outline-warning flex-shrink-0">
                            <i class="bi bi-download me-1"></i>Unduh Form
                        </a>
                        @endif
                    </div>
                    @endforeach
                    @endif
                </div>

            </div>{{-- /soal sub-tab content --}}
        </div>

        {{-- ═══════════════════════════════════════════════════
             TAB 3: PROGRESS ASESI
        ═══════════════════════════════════════════════════ --}}
        <div class="tab-pane fade p-0" id="tab-progress" role="tabpanel">

            {{-- Sub-tabs: Teori | Observasi --}}
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

                {{-- Progress Teori --}}
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
                        <span class="ms-auto"><i class="bi bi-arrow-clockwise me-1"></i>
                            <a href="#" onclick="location.reload()" class="text-muted text-decoration-none">Refresh</a>
                        </span>
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

                                    // Skor: benar / total
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
                                            <span class="text-muted" style="font-size:.75rem;min-width:38px;">
                                                {{ $answered }}/{{ $total }}
                                            </span>
                                        </div>
                                        @else
                                        <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($total === 0)
                                        <span class="badge bg-light text-muted border" style="font-size:.7rem;">Belum Ada Soal</span>
                                        @elseif($submitted)
                                        <span class="badge bg-success" style="font-size:.7rem;">
                                            <i class="bi bi-check-circle me-1"></i>Disubmit
                                        </span>
                                        @elseif($started)
                                        <span class="badge bg-warning text-dark" style="font-size:.7rem;">
                                            <i class="bi bi-pencil-fill me-1"></i>Sedang Mengerjakan
                                        </span>
                                        @else
                                        <span class="badge bg-secondary" style="font-size:.7rem;">Belum Mulai</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted" style="font-size:.78rem;">
                                        {{ $startedAt ? \Carbon\Carbon::parse($startedAt)->format('H:i') : '—' }}
                                    </td>
                                    <td class="text-center text-muted" style="font-size:.78rem;">
                                        {{ $submittedAt ? \Carbon\Carbon::parse($submittedAt)->format('H:i') : '—' }}
                                    </td>
                                    <td class="text-center">
                                        @if($submitted && $total > 0)
                                        @php $pctBenar = round($benar / $total * 100); @endphp
                                        <span class="fw-bold {{ $pctBenar >= 70 ? 'text-success' : 'text-danger' }}"
                                              style="font-size:.875rem;">
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

                {{-- Progress Observasi --}}
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
                            <span class="text-muted fw-normal">({{ $obs->paket->count() }} paket)</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0 table-bordered" style="font-size:.82rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:160px;">Asesi</th>
                                        @foreach($obs->paket as $paket)
                                        <th class="text-center">Paket {{ $paket->kode_paket }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asesmens as $asesmen)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $asesmen->full_name }}</div>
                                        </td>
                                        @foreach($obs->paket as $paket)
                                        @php
                                            $jawaban = $asesmen->jawabanObservasi
                                                ->where('paket_soal_observasi_id', $paket->id)
                                                ->first();
                                        @endphp
                                        <td class="text-center">
                                            @if($jawaban?->hasLink())
                                            <a href="{{ $jawaban->gdrive_link }}" target="_blank"
                                               class="badge bg-success text-decoration-none" style="font-size:.7rem;">
                                                <i class="bi bi-check-circle me-1"></i>Upload
                                            </a>
                                            @elseif($jawaban)
                                            <span class="badge bg-warning text-dark" style="font-size:.7rem;">Belum Link</span>
                                            @else
                                            <span class="badge bg-light text-muted border" style="font-size:.7rem;">—</span>
                                            @endif
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

            </div>{{-- /progress sub-tab --}}
        </div>

        {{-- ═══════════════════════════════════════════════════
             TAB 4: PENILAIAN ASESMEN
        ═══════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="tab-penilaian">
    <div class="row g-4 p-3">
 
        {{-- ── KOLOM KIRI: Upload Hasil ── --}}
        <div class="col-lg-7">
 
            {{-- Hasil Observasi --}}
            @php
                $distribusiObs = $schedule->distribusiSoalObservasi ?? collect();
            @endphp
            @if($distribusiObs->isNotEmpty())
            <div class="mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-eye text-primary me-2"></i>Hasil Observasi
                </h6>
                <div class="d-flex flex-column gap-2">
                    @foreach($distribusiObs as $dist)
                    @php
                        $obs   = $dist->soalObservasi;
                        $hasil = $schedule->hasilObservasi
                            ->where('soal_observasi_id', $obs->id)->first();
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
                                    · {{ $hasil->uploaded_at->format('d M Y H:i') }}
                                </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                @if($hasil)
                                <a href="{{ route('asesor.jadwal.observasi.download', [$schedule, $obs]) }}"
                                   class="btn btn-sm btn-outline-success" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('asesor.jadwal.observasi.hapus', [$schedule, $obs]) }}"
                                      onsubmit="return confirm('Hapus file ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                                @endif
                                <button class="btn btn-sm {{ $hasil ? 'btn-outline-primary' : 'btn-primary' }}"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#uploadObs{{ $obs->id }}">
                                    <i class="bi bi-upload me-1"></i>{{ $hasil ? 'Ganti' : 'Upload' }}
                                </button>
                            </div>
                        </div>
                        <div class="collapse" id="uploadObs{{ $obs->id }}">
                            <div class="px-3 py-3 border-top bg-light">
                                <form method="POST"
                                      action="{{ route('asesor.jadwal.observasi.upload', [$schedule, $obs]) }}"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">
                                            File Hasil Penilaian
                                            <span class="text-muted fw-normal">(Excel/PDF, maks. 20 MB)</span>
                                        </label>
                                        <input type="file" name="file" class="form-control form-control-sm"
                                               accept=".xlsx,.xlsm,.xls,.pdf" required>
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" name="catatan" class="form-control form-control-sm"
                                               placeholder="Catatan (opsional)">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-upload me-1"></i>Upload
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
 
            {{-- Hasil Portofolio --}}
            @php
                $distribusiPorto = $schedule->distribusiPortofolio ?? collect();
            @endphp
            @if($distribusiPorto->isNotEmpty())
            <div class="mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-briefcase text-purple me-2" style="color:#7c3aed"></i>Hasil Portofolio
                </h6>
                <div class="d-flex flex-column gap-2">
                    @foreach($distribusiPorto as $dist)
                    @php
                        $porto = $dist->portofolio;
                        $hasil = $schedule->hasilPortofolio
                            ->where('portofolio_id', $porto->id)->first();
                    @endphp
                    <div class="border rounded-3 overflow-hidden {{ $hasil ? 'border-success' : '' }}">
                        <div class="d-flex align-items-center gap-3 px-3 py-2
                            {{ $hasil ? 'bg-success-subtle' : 'bg-light' }}">
                            <i class="bi {{ $hasil ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} fs-5 flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">{{ $porto->judul }}</div>
                                @if($hasil)
                                <div class="text-muted" style="font-size:.75rem;">
                                    <i class="bi bi-file-earmark me-1"></i>{{ $hasil->file_name }}
                                    · {{ $hasil->uploaded_at->format('d M Y H:i') }}
                                </div>
                                @endif
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                @if($hasil)
                                <a href="{{ route('asesor.jadwal.portofolio.download', [$schedule, $porto]) }}"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-download"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('asesor.jadwal.portofolio.hapus', [$schedule, $porto]) }}"
                                      onsubmit="return confirm('Hapus file ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                                @endif
                                <button class="btn btn-sm {{ $hasil ? 'btn-outline-primary' : 'btn-primary' }}"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#uploadPorto{{ $porto->id }}">
                                    <i class="bi bi-upload me-1"></i>{{ $hasil ? 'Ganti' : 'Upload' }}
                                </button>
                            </div>
                        </div>
                        <div class="collapse" id="uploadPorto{{ $porto->id }}">
                            <div class="px-3 py-3 border-top bg-light">
                                <form method="POST"
                                      action="{{ route('asesor.jadwal.portofolio.upload', [$schedule, $porto]) }}"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">
                                            File Hasil Penilaian
                                            <span class="text-muted fw-normal">(Excel/PDF, maks. 20 MB)</span>
                                        </label>
                                        <input type="file" name="file" class="form-control form-control-sm"
                                               accept=".xlsx,.xlsm,.xls,.pdf" required>
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" name="catatan" class="form-control form-control-sm"
                                               placeholder="Catatan (opsional)">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-upload me-1"></i>Upload
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
 
            @if($distribusiObs->isEmpty() && $distribusiPorto->isEmpty())
            <div class="text-center py-5 text-muted border rounded-3">
                <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3;"></i>
                <p class="mt-2 mb-0 small">Tidak ada soal observasi atau portofolio yang perlu diupload untuk jadwal ini.</p>
            </div>
            @endif
 
        </div>
 
        {{-- ── KOLOM KANAN: Berita Acara ── --}}
        <div class="col-lg-5">
            @php $ba = $schedule->beritaAcara; @endphp
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-file-text text-warning"></i>Berita Acara
                    @if($ba)
                    <span class="badge bg-success ms-auto" style="font-size:.65rem;">Tersimpan</span>
                    @else
                    <span class="badge bg-secondary ms-auto" style="font-size:.65rem;">Belum Diisi</span>
                    @endif
                </div>
                <div class="card-body">
 
                    {{-- Upload file BA (opsional) --}}
                    <div class="mb-3 p-3 border rounded-3 bg-light">
                        <div class="fw-semibold small mb-2">
                            <i class="bi bi-file-earmark-arrow-up me-1"></i>Upload File Berita Acara
                            <span class="text-muted fw-normal">(opsional)</span>
                        </div>
                        @if($ba && $ba->file_path)
                        <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-white border rounded-2">
                            <i class="bi bi-file-earmark-check text-success"></i>
                            <span class="small text-truncate flex-grow-1">{{ $ba->file_name }}</span>
                            <a href="{{ route('asesor.jadwal.berita-acara.download-file', $schedule) }}"
                               class="btn btn-link btn-sm p-0 text-primary">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                        @endif
                        <form method="POST"
                              action="{{ route('asesor.jadwal.berita-acara.upload-file', $schedule) }}"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex gap-2">
                                <input type="file" name="file" class="form-control form-control-sm"
                                       accept=".pdf,.xlsx,.xlsm,.xls,.doc,.docx" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="bi bi-upload"></i>
                                </button>
                            </div>
                        </form>
                    </div>
 
                    {{-- Form isi K/BK per asesi --}}
                    <form method="POST" action="{{ route('asesor.jadwal.berita-acara.simpan', $schedule) }}">
                        @csrf
 
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                Tanggal Pelaksanaan <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_pelaksanaan"
                                   class="form-control form-control-sm"
                                   value="{{ $ba?->tanggal_pelaksanaan?->format('Y-m-d') ?? $schedule->assessment_date->format('Y-m-d') }}"
                                   required>
                        </div>
 
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Rekomendasi per Asesi</label>
                            <div class="d-flex flex-column gap-2">
                                @foreach($schedule->asesmens as $asesmen)
                                @php $rek = $rekomendasiMap[$asesmen->id] ?? null; @endphp
                                <div class="d-flex align-items-center gap-3 p-2 border rounded-2
                                    {{ $rek === 'K' ? 'border-success bg-success-subtle' : ($rek === 'BK' ? 'border-danger bg-danger-subtle' : 'bg-light') }}">
                                    <div class="flex-grow-1 small fw-semibold">
                                        {{ $asesmen->full_name }}
                                    </div>
                                    <div class="d-flex gap-2 flex-shrink-0">
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input" type="radio"
                                                   name="rekomendasi[{{ $asesmen->id }}]"
                                                   id="k_{{ $asesmen->id }}" value="K"
                                                   {{ $rek === 'K' ? 'checked' : '' }} required>
                                            <label class="form-check-label fw-bold text-success small"
                                                   for="k_{{ $asesmen->id }}">K</label>
                                        </div>
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input" type="radio"
                                                   name="rekomendasi[{{ $asesmen->id }}]"
                                                   id="bk_{{ $asesmen->id }}" value="BK"
                                                   {{ $rek === 'BK' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-danger small"
                                                   for="bk_{{ $asesmen->id }}">BK</label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
 
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Catatan</label>
                            <textarea name="catatan" class="form-control form-control-sm" rows="2"
                                      placeholder="Catatan tambahan (opsional)...">{{ $ba?->catatan }}</textarea>
                        </div>
 
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-save me-1"></i>Simpan Berita Acara
                        </button>
                    </form>
 
                </div>
            </div>
        </div>
 
    </div>
</div>
    </div>{{-- /tab-content --}}
</div>

@push('scripts')
<script>
// Restore active tab from URL hash
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tab) {
            new bootstrap.Tab(tab).show();
        }
    }

    // Update hash on tab change
    document.querySelectorAll('#mainTabs [data-bs-toggle="tab"]').forEach(function (tabEl) {
        tabEl.addEventListener('shown.bs.tab', function (e) {
            history.replaceState(null, null, e.target.dataset.bsTarget);
        });
    });

    // Hadir toggle — kirim AJAX
    document.querySelectorAll('.hadir-toggle').forEach(function (el) {
        el.addEventListener('change', function () {
            const asesmenId = this.dataset.id;
            const hadir     = this.checked;
            fetch(`/asesor/asesmen/${asesmenId}/hadir`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ hadir }),
            }).then(r => r.json()).then(data => {
                if (!data.success) {
                    this.checked = !hadir; // revert
                    alert('Gagal mengubah status kehadiran.');
                }
            }).catch(() => {
                this.checked = !hadir;
            });
        });
    });
});
</script>
@endpush

@endsection