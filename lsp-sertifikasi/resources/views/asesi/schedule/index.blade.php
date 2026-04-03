@extends('layouts.app')
@section('title', 'Jadwal Asesmen')
@section('page-title', 'Jadwal Asesmen')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')

@php
    $frak01  = $asesmen->frak01  ?? null;
    $aplsatu = $asesmen->aplsatu ?? null;
    $apldua  = $asesmen->apldua  ?? null;
    $frak04  = $asesmen->frak04  ?? null;

    $daysLeft = now()->startOfDay()->diffInDays($schedule->assessment_date->startOfDay(), false);

    // Hitung status soal teori asesi ini
    $soalTeori   = $asesmen->soalTeoriAsesi ?? collect();
    $totalTeori  = $soalTeori->count();
    $teoriSubmit = $totalTeori > 0 && $soalTeori->whereNotNull('submitted_at')->count() > 0;
    $teoriMulai  = $totalTeori > 0 && $soalTeori->whereNotNull('started_at')->count() > 0;
@endphp

{{-- Alert APL-01 dikembalikan --}}
@if($aplsatu && $aplsatu->status === 'returned')
<div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        <div class="fw-bold mb-1">APL-01 Dikembalikan oleh Admin</div>
        @if($aplsatu->rejection_notes)
        <div class="small bg-white border border-danger rounded p-2 mb-2">{{ $aplsatu->rejection_notes }}</div>
        @endif
        <a href="{{ route('asesi.apl01') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-pencil me-1"></i>Perbaiki Sekarang
        </a>
    </div>
</div>
@endif

{{-- ── HERO JADWAL ─────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="d-flex flex-wrap">

            {{-- Tanggal besar --}}
            <div class="d-flex flex-column align-items-center justify-content-center px-4 py-4 text-white flex-shrink-0"
                 style="min-width:110px;
                 background:{{ $daysLeft === 0 ? '#f59e0b' : ($daysLeft < 0 ? '#64748b' : '#3b82f6') }};">
                <div style="font-size:2.8rem;font-weight:900;line-height:1;">
                    {{ $schedule->assessment_date->translatedFormat('d') }}
                </div>
                <div style="font-size:.85rem;font-weight:600;text-transform:uppercase;opacity:.85;">
                    {{ $schedule->assessment_date->translatedFormat('M') }}
                </div>
                <div style="font-size:.8rem;opacity:.75;">
                    {{ $schedule->assessment_date->translatedFormat('Y') }}
                </div>
            </div>

            {{-- Info jadwal --}}
            <div class="flex-grow-1 p-4">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $schedule->skema->name ?? '-' }}</h5>
                        <div class="d-flex flex-wrap gap-3 text-muted small">
                            <span><i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}</span>
                            <span><i class="bi bi-building me-1"></i>{{ $schedule->tuk->name ?? '-' }}</span>
                            @if($schedule->location)
                            <span><i class="bi bi-geo-alt me-1"></i>{{ $schedule->location }}</span>
                            @endif
                            @if($schedule->asesor)
                            <span><i class="bi bi-person-badge me-1"></i>{{ $schedule->asesor->nama }}</span>
                            @endif
                        </div>
                        @if($schedule->notes)
                        <div class="mt-2 small text-muted fst-italic">
                            <i class="bi bi-sticky me-1"></i>{{ $schedule->notes }}
                        </div>
                        @endif
                    </div>

                    {{-- Countdown badge --}}
                    <div class="flex-shrink-0">
                        @if($daysLeft > 0)
                        <span class="badge bg-primary px-3 py-2" style="font-size:.85rem;">
                            <i class="bi bi-hourglass-split me-1"></i>{{ $daysLeft }} hari lagi
                        </span>
                        @elseif($daysLeft === 0)
                        <span class="badge bg-warning text-dark px-3 py-2" style="font-size:.85rem;">
                            <i class="bi bi-alarm me-1"></i>Hari ini!
                        </span>
                        @else
                        <span class="badge bg-secondary px-3 py-2" style="font-size:.85rem;">
                            <i class="bi bi-check-circle me-1"></i>Selesai
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Online meeting link --}}
                @if($schedule->isOnline() && $schedule->meeting_link)
                <div class="mt-3">
                    <a href="{{ $schedule->meeting_link }}" target="_blank"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-camera-video me-1"></i>Buka Link Meeting
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── KOLOM KIRI: Status Dokumen ──────────────────────── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-folder2 text-primary"></i>Dokumen Pra-Asesmen
            </div>
            <div class="list-group list-group-flush">

                {{-- FR.AK.01 --}}
                <a href="{{ route('asesi.frak01') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-3 px-4 py-3">
                    @php
                        $frak01Icon = match($frak01?->status) {
                            'verified','approved' => ['bi-patch-check-fill','text-success'],
                            'submitted'           => ['bi-check-circle-fill','text-primary'],
                            'returned'            => ['bi-arrow-return-left','text-danger'],
                            'draft'               => ['bi-pen-fill','text-warning'],
                            default               => ['bi-circle','text-muted'],
                        };
                    @endphp
                    <i class="bi {{ $frak01Icon[0] }} {{ $frak01Icon[1] }} fs-5 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">FR.AK.01</div>
                        <div class="text-muted" style="font-size:.78rem;">Persetujuan Asesmen & Kerahasiaan</div>
                    </div>
                    @if($frak01)
                    <span class="badge bg-{{ $frak01->status_badge }}" style="font-size:.68rem;">{{ $frak01->status_label }}</span>
                    @else
                    <span class="badge bg-secondary" style="font-size:.68rem;">Belum Ada</span>
                    @endif
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>

                {{-- APL-01 --}}
                <a href="{{ route('asesi.apl01') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-3 px-4 py-3
                   {{ $aplsatu?->status === 'returned' ? 'list-group-item-danger' : '' }}">
                    @php
                        $apl01Icon = match($aplsatu?->status) {
                            'verified','approved' => ['bi-patch-check-fill','text-success'],
                            'submitted'           => ['bi-check-circle-fill','text-primary'],
                            'returned'            => ['bi-arrow-return-left','text-danger'],
                            'draft'               => ['bi-pencil-square','text-warning'],
                            default               => ['bi-circle','text-muted'],
                        };
                    @endphp
                    <i class="bi {{ $apl01Icon[0] }} {{ $apl01Icon[1] }} fs-5 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">APL-01</div>
                        <div class="text-muted" style="font-size:.78rem;">Permohonan Sertifikasi</div>
                        @if($aplsatu && $aplsatu->buktiKelengkapan->isNotEmpty())
                        @php
                            $totalBukti = $aplsatu->buktiKelengkapan->count();
                            $uploadBukti = $aplsatu->buktiKelengkapan->whereNotNull('gdrive_file_url')->count();
                        @endphp
                        <div class="progress mt-1" style="height:3px;width:80px;">
                            <div class="progress-bar bg-success"
                                 style="width:{{ $totalBukti > 0 ? round($uploadBukti/$totalBukti*100) : 0 }}%"></div>
                        </div>
                        @endif
                    </div>
                    @if($aplsatu)
                    <span class="badge bg-{{ $aplsatu->status_badge }}" style="font-size:.68rem;">{{ $aplsatu->status_label }}</span>
                    @else
                    <span class="badge bg-secondary" style="font-size:.68rem;">Belum Ada</span>
                    @endif
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>

                {{-- APL-02 --}}
                <a href="{{ route('asesi.apldua') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-3 px-4 py-3">
                    @php
                        $apl02Icon = match($apldua?->status) {
                            'verified','approved' => ['bi-patch-check-fill','text-success'],
                            'submitted'           => ['bi-check-circle-fill','text-primary'],
                            'draft'               => ['bi-pencil-square','text-warning'],
                            default               => ['bi-circle','text-muted'],
                        };
                    @endphp
                    <i class="bi {{ $apl02Icon[0] }} {{ $apl02Icon[1] }} fs-5 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">APL-02</div>
                        <div class="text-muted" style="font-size:.78rem;">Asesmen Mandiri</div>
                        @if($apldua && $apldua->jawabans->isNotEmpty())
                        @php $prog = $apldua->progress; @endphp
                        <div class="progress mt-1" style="height:3px;width:80px;">
                            <div class="progress-bar bg-success"
                                 style="width:{{ $prog['total'] > 0 ? round($prog['answered']/$prog['total']*100) : 0 }}%"></div>
                        </div>
                        @endif
                    </div>
                    @if($apldua)
                    <span class="badge bg-{{ $apldua->status_badge }}" style="font-size:.68rem;">{{ $apldua->status_label }}</span>
                    @else
                    <span class="badge bg-secondary" style="font-size:.68rem;">Belum Ada</span>
                    @endif
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>

                {{-- FR.AK.04 --}}
                <a href="{{ route('asesi.frak04') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-3 px-4 py-3">
                    <i class="bi {{ $frak04?->status === 'submitted' ? 'bi-megaphone-fill text-warning' : 'bi-megaphone text-muted' }} fs-5 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">FR.AK.04
                            <span class="badge bg-light text-muted border ms-1" style="font-size:.6rem;">Opsional</span>
                        </div>
                        <div class="text-muted" style="font-size:.78rem;">Banding Asesmen</div>
                    </div>
                    @if($frak04?->status === 'submitted')
                    <span class="badge bg-warning text-dark" style="font-size:.68rem;">Diajukan</span>
                    @else
                    <span class="badge bg-light text-muted border" style="font-size:.68rem;">Belum</span>
                    @endif
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>

            </div>
        </div>
    </div>

    {{-- ── KOLOM KANAN: Soal Asesmen ───────────────────────── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-journal-check text-primary"></i>Soal Asesmen
            </div>
            <div class="card-body d-flex flex-column gap-3 p-4">

                {{-- Soal Teori --}}
                @php
                    $distribusiTeori = $asesmen->schedule?->distribusiSoalTeori ?? null;
                @endphp
                <div class="border rounded-3 p-3 d-flex align-items-center gap-3
                    {{ $teoriSubmit ? 'border-success bg-success bg-opacity-5' : ($totalTeori > 0 ? 'border-primary bg-primary bg-opacity-5' : '') }}">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                        {{ $teoriSubmit ? 'bg-success' : ($totalTeori > 0 ? 'bg-primary' : 'bg-secondary') }} text-white"
                         style="width:44px;height:44px;">
                        <i class="bi {{ $teoriSubmit ? 'bi-check-lg' : 'bi-journal-text' }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">Soal Teori (Pilihan Ganda)</div>
                        @if($totalTeori > 0)
                        <div class="small text-muted">{{ $distribusiTeori->jumlah_soal ?? $totalTeori }} soal
                            @if($teoriSubmit) · <span class="text-success fw-semibold">Selesai</span>
                            @elseif($teoriMulai) · <span class="text-warning fw-semibold">Sedang dikerjakan</span>
                            @else · Belum dimulai
                            @endif
                        </div>
                        @if(!$teoriSubmit && $totalTeori > 0)
                        @php
                            $answered = $soalTeori->whereNotNull('jawaban')->count();
                        @endphp
                        <div class="progress mt-2" style="height:5px;">
                            <div class="progress-bar bg-primary"
                                 style="width:{{ round($answered/$totalTeori*100) }}%"></div>
                        </div>
                        @endif
                        @else
                        <div class="small text-muted">Belum ada soal terdistribusi</div>
                        @endif
                    </div>
                    @if($totalTeori > 0 && !$teoriSubmit)
                    <a href="{{ route('asesi.soal.teori.intro') }}"
                       class="btn btn-sm {{ $teoriMulai ? 'btn-warning' : 'btn-primary' }} flex-shrink-0">
                        {{ $teoriMulai ? 'Lanjutkan' : 'Mulai' }}
                    </a>
                    @elseif($teoriSubmit)
                    <span class="badge bg-success flex-shrink-0">
                        <i class="bi bi-check-circle me-1"></i>Submit
                    </span>
                    @else
                    <span class="badge bg-secondary flex-shrink-0">Menunggu</span>
                    @endif
                </div>

                {{-- Soal Observasi --}}
                @php
                    $distribusiObservasi = $asesmen->schedule?->distribusiSoalObservasi ?? collect();
                    $obsTotal   = 0;
                    $obsUpload  = 0;
                    foreach($distribusiObservasi as $dist) {
                        foreach($dist->soalObservasi->paket ?? [] as $p) {
                            $obsTotal++;
                            $jwb = ($asesmen->jawabanObservasi ?? collect())->where('paket_soal_observasi_id', $p->id)->first();
                            if($jwb?->hasLink()) $obsUpload++;
                        }
                    }
                @endphp
                <div class="border rounded-3 p-3 d-flex align-items-center gap-3
                    {{ $obsUpload > 0 && $obsUpload === $obsTotal ? 'border-success bg-success bg-opacity-5' : ($obsTotal > 0 ? '' : '') }}">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                        {{ $obsUpload === $obsTotal && $obsTotal > 0 ? 'bg-success' : ($obsTotal > 0 ? 'bg-info' : 'bg-secondary') }} text-white"
                         style="width:44px;height:44px;">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">Soal Observasi</div>
                        @if($obsTotal > 0)
                        <div class="small text-muted">
                            {{ $obsUpload }}/{{ $obsTotal }} paket diupload
                        </div>
                        <div class="progress mt-2" style="height:5px;">
                            <div class="progress-bar bg-info"
                                 style="width:{{ round($obsUpload/$obsTotal*100) }}%"></div>
                        </div>
                        @else
                        <div class="small text-muted">Belum ada soal terdistribusi</div>
                        @endif
                    </div>
                    @if($obsTotal > 0)
                    <a href="{{ route('asesi.soal.observasi.index') }}"
                       class="btn btn-sm btn-outline-info flex-shrink-0">
                        <i class="bi bi-upload me-1"></i>Upload
                    </a>
                    @else
                    <span class="badge bg-secondary flex-shrink-0">Menunggu</span>
                    @endif
                </div>

                {{-- Portofolio --}}
                @php
                    $distribusiPortofolio = $asesmen->schedule?->distribusiPortofolio ?? collect();
                @endphp
                <div class="border rounded-3 p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                        {{ $distribusiPortofolio->isNotEmpty() ? 'bg-warning' : 'bg-secondary' }} text-white"
                         style="width:44px;height:44px;">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">Portofolio</div>
                        <div class="small text-muted">
                            @if($distribusiPortofolio->isNotEmpty())
                            {{ $distribusiPortofolio->count() }} form tersedia
                            @else
                            Belum ada portofolio terdistribusi
                            @endif
                        </div>
                    </div>
                    @if($distribusiPortofolio->isNotEmpty())
                    <a href="{{ route('asesi.documents') }}"
                       class="btn btn-sm btn-outline-warning flex-shrink-0">
                        <i class="bi bi-eye me-1"></i>Lihat
                    </a>
                    @else
                    <span class="badge bg-secondary flex-shrink-0">Menunggu</span>
                    @endif
                </div>

            </div>
        </div>
    </div>

</div>

@endsection