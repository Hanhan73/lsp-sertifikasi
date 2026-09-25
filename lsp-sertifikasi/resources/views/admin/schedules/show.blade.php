@extends('layouts.app')
@section('title', 'Detail Jadwal — ' . $schedule->assessment_date->translatedFormat('d M Y'))
@section('page-title', 'Detail Jadwal Asesmen')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.section-heading { font-size:.72rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#64748b; margin-bottom:10px; }
.info-row { display:flex; gap:8px; padding:6px 0; border-bottom:1px solid #f1f5f9; }
.info-row:last-child { border-bottom:none; }
.info-label { color:#94a3b8; font-size:.82rem; min-width:130px; flex-shrink:0; }
.info-value  { font-weight:600; font-size:.88rem; }

.date-badge { display:flex; flex-direction:column; align-items:center; justify-content:center; width:64px; height:72px; border-radius:10px; flex-shrink:0; border:2px solid #bfdbfe; background:#eff6ff; text-align:center; }
.date-badge.past   { border-color:#e2e8f0; background:#f8fafc; }
.date-badge.today  { border-color:#38bdf8; background:#f0f9ff; }
.date-badge.future { border-color:#bbf7d0; background:#f0fdf4; }
.date-badge .day   { font-size:1.7rem; font-weight:900; line-height:1; }
.date-badge .month { font-size:.65rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
.date-badge .year  { font-size:.62rem; color:#94a3b8; }

.asesor-card { border:1.5px solid #e2e8f0; border-radius:10px; padding:14px 16px; background:#fff; transition:border-color .2s; }
.asesor-card.has-asesor { border-color:#bfdbfe; background:#f8fbff; }
.asesor-avatar-lg { width:52px; height:52px; border-radius:50%; object-fit:cover; border:2px solid #e0e7ff; flex-shrink:0; }
.asesor-avatar-placeholder-lg { width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#4f46e5,#2563eb); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.1rem; font-weight:700; flex-shrink:0; }

/* Online badge */
.loc-badge-online  { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; border-radius:99px; padding:2px 10px; font-size:.72rem; font-weight:600; display:inline-flex; align-items:center; gap:4px; }
.loc-badge-offline { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:99px; padding:2px 10px; font-size:.72rem; font-weight:600; display:inline-flex; align-items:center; gap:4px; }

/* Checklist progress */
.checklist-item { display:flex; gap:8px; padding:8px 0; border-bottom:1px solid #f1f5f9; }
.checklist-item:last-child { border-bottom:none; }
.min-width-0 { min-width:0; }
.checklist-toggle { background:none; border:0; padding:0; width:100%; text-align:left; cursor:pointer; }
.checklist-toggle .bi-chevron-down { transition:transform .2s; font-size:.7rem; }
.checklist-toggle[aria-expanded="true"] .bi-chevron-down { transform:rotate(180deg); }
.checklist-toggle[aria-expanded="true"] .checklist-preview { display:none; }
.checklist-full { background:#f8fafc; border-radius:6px; font-size:.78rem; white-space:pre-line; }

/* Dokumen asesmen */
.doc-box { border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; height:100%; }
.doc-box .doc-title { font-size:.82rem; font-weight:600; margin-bottom:2px; }
.doc-box .doc-sub { font-size:.72rem; color:#94a3b8; margin-bottom:10px; }
.foto-thumb { width:100%; aspect-ratio:4/3; object-fit:cover; border-radius:8px; border:1px solid #e2e8f0; background:#f8fafc; display:block; }
.foto-empty { width:100%; aspect-ratio:4/3; border-radius:8px; border:1px dashed #cbd5e1; display:flex; align-items:center; justify-content:center; color:#cbd5e1; }

/* Umpan balik */
.ub-row td { font-size:.82rem; vertical-align:top; }
.ub-catatan { font-size:.75rem; color:#64748b; white-space:pre-line; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible shadow-sm mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible shadow-sm mb-4">
    <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Approval Banner --}}
@if($schedule->isPendingApproval())
<div class="alert alert-warning d-flex align-items-center gap-3 shadow-sm mb-4">
    <i class="bi bi-hourglass-split fs-4 flex-shrink-0"></i>
    <div>
        <div class="fw-semibold">Jadwal Menunggu Persetujuan Direktur</div>
        <div class="small">Jadwal sudah dibuat dan sedang dalam antrian review. Status asesi belum berubah ke "Terjadwal" sampai Direktur menyetujui.</div>
    </div>
</div>
@elseif($schedule->isRejected())
<div class="alert alert-danger d-flex align-items-start gap-3 shadow-sm mb-4">
    <i class="bi bi-x-circle-fill fs-4 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        <div class="fw-semibold">Jadwal Ditolak oleh Direktur</div>
        <div class="mt-1"><strong>Alasan:</strong> {{ $schedule->approval_notes }}</div>
        <div class="small text-muted mt-1">Ditolak pada {{ $schedule->rejected_at?->translatedFormat('d M Y H:i') }}</div>
        <div class="mt-2">
            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-warning">
                <i class="bi bi-pencil me-1"></i>Perbaiki &amp; Ajukan Ulang
            </a>
        </div>
    </div>
</div>
@elseif($schedule->isApproved())
<div class="alert alert-success d-flex align-items-center gap-3 shadow-sm mb-4">
    <i class="bi bi-check-circle-fill fs-4 flex-shrink-0"></i>
    <div class="flex-grow-1">
        <div class="fw-semibold">Jadwal Telah Disetujui Direktur</div>
        <div class="small">
            Nomor SK: <span class="font-monospace fw-bold">{{ $schedule->sk_number }}</span>
            &nbsp;&bull;&nbsp; Disetujui pada {{ $schedule->approved_at?->translatedFormat('d M Y H:i') }}
        </div>
    </div>
    @if($schedule->hasSk())
    <a href="{{ route('admin.schedules.sk.download', $schedule) }}" class="btn btn-sm btn-success ms-auto">
        <i class="bi bi-download me-1"></i>Unduh SK
    </a>
    @endif
</div>
@endif

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Jadwal Asesmen</a></li>
        <li class="breadcrumb-item active">{{ $schedule->assessment_date->translatedFormat('d M Y') }}</li>
    </ol>
</nav>

@php
    $isPast     = $schedule->assessment_date->isPast() && !$schedule->assessment_date->isToday();
    $isToday    = $schedule->assessment_date->isToday();
    $dateClass  = $isToday ? 'today' : ($isPast ? 'past' : 'future');
    $isOnline   = $schedule->location_type === 'online';

    $pakaiTeori = (bool) $schedule->distribusiSoalTeori
        || collect($progress)->contains(fn($x) => $x['teori']['total'] > 0);
    $totalObs   = $schedule->distribusiSoalObservasi->count();
    $pakaiPorto = $schedule->distribusiPortofolio->isNotEmpty();

    $allItems  = collect($checklist)->flatten(1)->reject(fn($i) => $i['optional'] ?? false);
    $doneItems = $allItems->where('done', true)->count();
    $pct       = $allItems->count() ? round($doneItems / $allItems->count() * 100) : 0;

    $ba        = $schedule->beritaAcara;
    $jmlUB     = count($umpanBalik);
    $jmlPertanyaanUB = max(count($pertanyaanUmpanBalik), count($rekapUmpanBalik));
@endphp

{{-- ══════════════════════════════════════════════════════════
     ROW 1: Header
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Jadwal utama --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex gap-3 align-items-start">
                <div class="date-badge {{ $dateClass }}">
                    <div class="day" style="color:{{ $isToday ? '#0284c7' : ($isPast ? '#94a3b8' : '#16a34a') }}">{{ $schedule->assessment_date->translatedFormat('d') }}</div>
                    <div class="month" style="color:{{ $isToday ? '#0284c7' : ($isPast ? '#94a3b8' : '#16a34a') }}">{{ $schedule->assessment_date->translatedFormat('M') }}</div>
                    <div class="year">{{ $schedule->assessment_date->translatedFormat('Y') }}</div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1">
                        {{ $schedule->assessment_date->translatedFormat('l, d F Y') }}
                        @if($isToday)<span class="badge bg-info ms-1 fs-6">Hari Ini</span>
                        @elseif($isPast)<span class="badge bg-secondary ms-1 fs-6">Selesai</span>
                        @else<span class="badge bg-success ms-1 fs-6">Akan Datang</span>@endif
                    </h5>
                    <div class="text-muted small mb-1">
                        <i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}
                    </div>
                    <div class="text-muted small mb-2">
                        <i class="bi bi-{{ $isOnline ? 'camera-video' : 'geo-alt' }} me-1"></i>
                        {{ $schedule->location }}
                        @if($isOnline && $schedule->meeting_link)
                        <a href="{{ $schedule->meeting_link }}" target="_blank" class="ms-1 text-primary" title="Buka link meeting">
                            <i class="bi bi-box-arrow-up-right" style="font-size:.75rem;"></i>
                        </a>
                        @endif
                    </div>
                    <div class="mb-2">
                        <span class="{{ $isOnline ? 'loc-badge-online' : 'loc-badge-offline' }}">
                            <i class="bi bi-{{ $isOnline ? 'camera-video' : 'building' }}"></i>
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule({{ $schedule->id }}, {{ $pesertaCount }})">
                            <i class="bi bi-trash3 me-1"></i>Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info asesmen --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="section-heading"><i class="bi bi-award me-1"></i>Info Asesmen</div>
                <div class="info-row">
                    <span class="info-label">Skema</span>
                    <span class="info-value">{{ $schedule->skema?->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kode Skema</span>
                    <span class="info-value font-monospace small">{{ $schedule->skema?->code ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">TUK</span>
                    <span class="info-value">{{ $schedule->tuk?->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jenis</span>
                    <span class="info-value">
                        <span class="{{ $isOnline ? 'loc-badge-online' : 'loc-badge-offline' }}">
                            <i class="bi bi-{{ $isOnline ? 'camera-video' : 'building' }}"></i>
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lokasi</span>
                    <span class="info-value">{{ $schedule->location }}</span>
                </div>
                @if($isOnline && $schedule->meeting_link)
                <div class="info-row">
                    <span class="info-label">Link Meeting</span>
                    <span class="info-value">
                        <a href="{{ $schedule->meeting_link }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.75rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Buka Link
                        </a>
                        <div class="text-muted mt-1" style="font-size:.68rem;word-break:break-all;">{{ $schedule->meeting_link }}</div>
                    </span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Total Peserta</span>
                    <span class="info-value">{{ $pesertaCount }} orang</span>
                </div>
                @if($schedule->notes)
                <div class="info-row">
                    <span class="info-label">Catatan</span>
                    <span class="info-value small">{{ $schedule->notes }}</span>
                </div>
                @endif
                @if($schedule->created_at)
                <div class="info-row">
                    <span class="info-label">Dibuat</span>
                    <span class="info-value small">{{ $schedule->created_at->translatedFormat('d M Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Asesor --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="section-heading d-flex justify-content-between">
                    <span><i class="bi bi-person-badge me-1"></i>Asesor</span>
                    <button class="btn btn-link btn-sm p-0 text-primary" style="font-size:.72rem;text-transform:none;letter-spacing:0;" onclick="openAsesorModal()">
                        <i class="bi bi-pencil me-1"></i>{{ $schedule->asesor ? 'Ganti' : 'Tugaskan' }}
                    </button>
                </div>
                @if($schedule->asesor)
                <div class="asesor-card has-asesor">
                    <div class="d-flex gap-3 align-items-center">
                        @if($schedule->asesor->foto_url)
                        <img src="{{ $schedule->asesor->foto_url }}" class="asesor-avatar-lg" alt="">
                        @else
                        <div class="asesor-avatar-placeholder-lg">{{ strtoupper(substr($schedule->asesor->nama, 0, 1)) }}</div>
                        @endif
                        <div>
                            <div class="fw-bold">{{ $schedule->asesor->nama }}</div>
                            <div class="text-muted small">{{ $schedule->asesor->no_reg_met ?? 'Tanpa no. reg' }}</div>
                            <div class="text-muted small">{{ $schedule->asesor->email ?? '-' }}</div>
                            <button type="button" class="btn btn-danger btn-sm mt-2 py-0 px-2" style="font-size:.72rem;" onclick="unassignAsesor({{ $schedule->id }})">
                                <i class="bi bi-x me-1"></i>Lepas Asesor
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="asesor-card text-center py-3">
                    <i class="bi bi-person-dash fs-2 d-block mb-2 opacity-25"></i>
                    <p class="small text-muted mb-2">Asesor belum ditugaskan.</p>
                    <button class="btn btn-warning btn-sm" onclick="openAsesorModal()">
                        <i class="bi bi-person-plus me-1"></i>Tugaskan Asesor
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 2: Progress Asesmen (checklist)
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 flex-wrap">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:26px;height:26px;background:#eff6ff;">
            <i class="bi bi-list-check text-primary" style="font-size:.8rem;"></i>
        </div>
        <span class="fw-semibold">Progress Asesmen</span>
        <div class="ms-auto d-flex align-items-center gap-2" style="min-width:220px;">
            <div class="progress flex-grow-1" style="height:8px;">
                <div class="progress-bar {{ $pct === 100 ? 'bg-success' : '' }}" style="width:{{ $pct }}%"></div>
            </div>
            <span class="small fw-semibold">{{ $doneItems }}/{{ $allItems->count() }}</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            @foreach($checklist as $group => $items)
            <div class="col-lg-{{ intdiv(12, max(count($checklist), 1)) }}">
                <div class="section-heading">{{ $group }}</div>
                @foreach($items as $item)
                @php
                    $opt        = $item['optional'] ?? false;
                    $hasFull    = !empty($item['full']) && mb_strlen($item['full']) > 60;
                    $collapseId = 'checklist-full-' . $loop->parent->index . '-' . $loop->index;
                @endphp
                <div class="checklist-item">
                    @if($item['done'])
                    <i class="bi bi-check-circle-fill text-success" style="margin-top:2px;"></i>
                    @else
                    <i class="bi bi-circle text-muted {{ $opt ? 'opacity-50' : '' }}" style="margin-top:2px;"></i>
                    @endif
                    <div class="flex-grow-1 min-width-0">
                        @if($hasFull)
                        <button type="button" class="checklist-toggle" data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                            <div class="d-flex align-items-center gap-1">
                                <span class="small fw-semibold {{ $item['done'] ? '' : 'text-muted' }}">
                                    {{ $item['label'] }}
                                    @if($opt)<span class="fw-normal text-muted">(opsional)</span>@endif
                                </span>
                                <i class="bi bi-chevron-down text-muted ms-auto"></i>
                            </div>
                            <div class="checklist-preview text-muted text-truncate" style="font-size:.75rem;">{{ $item['detail'] }}</div>
                        </button>
                        <div class="collapse" id="{{ $collapseId }}">
                            <div class="checklist-full text-muted mt-1 p-2">{{ $item['full'] }}</div>
                        </div>
                        @else
                        <div class="small fw-semibold {{ $item['done'] ? '' : 'text-muted' }}">
                            {{ $item['label'] }}
                            @if($opt)<span class="fw-normal text-muted">(opsional)</span>@endif
                        </div>
                        @if(!empty($item['detail']))
                        <div class="text-muted text-truncate" style="font-size:.75rem;" title="{{ $item['detail'] }}">{{ $item['detail'] }}</div>
                        @endif
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 3: Dokumen Asesmen
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:26px;height:26px;background:#fef3c7;">
            <i class="bi bi-folder2-open text-warning" style="font-size:.8rem;"></i>
        </div>
        <span class="fw-semibold">Dokumen Asesmen</span>
    </div>
    <div class="card-body">
        <div class="row g-3">

            {{-- Daftar Hadir --}}
            <div class="col-md-6 col-xl-3">
                <div class="doc-box">
                    <div class="doc-title"><i class="bi bi-person-check me-1 text-success"></i>Daftar Hadir</div>
                    @if($daftarHadirSigned)
                    <div class="doc-sub">Sudah ditandatangani asesor</div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.schedules.daftar-hadir', $schedule) }}?preview=1" target="_blank" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="bi bi-eye me-1"></i>Lihat
                        </a>
                        <a href="{{ route('admin.schedules.daftar-hadir', $schedule) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-download me-1"></i>Unduh
                        </a>
                    </div>
                    @else
                    <div class="doc-sub">Asesor belum menandatangani</div>
                    <button class="btn btn-sm btn-outline-secondary w-100" disabled>
                        <i class="bi bi-hourglass-split me-1"></i>Belum tersedia
                    </button>
                    @endif
                </div>
            </div>

            {{-- Berita Acara --}}
            <div class="col-md-6 col-xl-3">
                <div class="doc-box">
                    <div class="doc-title"><i class="bi bi-file-earmark-text me-1 text-warning"></i>Berita Acara</div>
                    @if($ba)
                    <div class="doc-sub">
                        {{ $ba->asesis->where('rekomendasi', 'K')->count() }} K ·
                        {{ $ba->asesis->where('rekomendasi', 'BK')->count() }} BK
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <a href="{{ route('admin.schedules.berita-acara.pdf', $schedule) }}?preview=1" target="_blank" class="btn btn-sm btn-outline-secondary flex-fill">
                            <i class="bi bi-eye me-1"></i>Lihat
                        </a>
                        <a href="{{ route('admin.schedules.berita-acara.pdf', $schedule) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-download me-1"></i>PDF
                        </a>
                    </div>
                    @if($ba->file_path)
                    <a href="{{ route('admin.schedules.berita-acara.file', $schedule) }}" class="btn btn-sm btn-light border w-100 text-truncate" title="{{ $ba->file_name }}">
                        <i class="bi bi-file-earmark-excel me-1 text-success"></i>File asli asesor
                    </a>
                    @endif
                    @else
                    <div class="doc-sub">Belum dibuat asesor</div>
                    <button class="btn btn-sm btn-outline-secondary w-100" disabled>
                        <i class="bi bi-hourglass-split me-1"></i>Belum tersedia
                    </button>
                    @endif
                </div>
            </div>

            {{-- Foto Dokumentasi --}}
            <div class="col-md-6 col-xl-3">
                <div class="doc-box">
                    <div class="doc-title"><i class="bi bi-camera me-1 text-primary"></i>Foto Dokumentasi</div>
                    <div class="doc-sub">Klik foto untuk memperbesar</div>
                    <div class="row g-2">
                        @foreach([1, 2] as $slot)
                        @php $adaFoto = filled($schedule->{"foto_dokumentasi_{$slot}"}); @endphp
                        <div class="col-6">
                            @if($adaFoto)
                            @php $fotoUrl = route('admin.schedules.foto', [$schedule, $slot]); @endphp
                            <a href="#" onclick="lihatFoto('{{ $fotoUrl }}', {{ $slot }}); return false;">
                                <img src="{{ $fotoUrl }}" class="foto-thumb" alt="Foto {{ $slot }}" loading="lazy">
                            </a>
                            <a href="{{ $fotoUrl }}?download=1" class="btn btn-sm btn-link p-0 mt-1" style="font-size:.72rem;">
                                <i class="bi bi-download me-1"></i>Unduh
                            </a>
                            @else
                            <div class="foto-empty"><i class="bi bi-image fs-4"></i></div>
                            <div class="text-muted mt-1" style="font-size:.72rem;">Foto {{ $slot }} belum ada</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Umpan Balik --}}
            <div class="col-md-6 col-xl-3">
                <div class="doc-box">
                    <div class="doc-title"><i class="bi bi-chat-square-text me-1 text-info"></i>Umpan Balik (FR.AK.03)</div>
                    <div class="doc-sub">{{ $jmlUB }}/{{ $pesertaCount }} peserta sudah mengisi</div>
                    @if($jmlUB > 0)
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#modalRekapUB">
                        <i class="bi bi-bar-chart me-1"></i>Lihat Rekap
                    </button>
                    <div class="text-muted mt-2" style="font-size:.72rem;">
                        Jawaban per peserta ada di tab <strong>Progress Asesmen</strong> di bawah.
                    </div>
                    @else
                    <button class="btn btn-sm btn-outline-secondary w-100" disabled>
                        <i class="bi bi-hourglass-split me-1"></i>Belum ada
                    </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 4: Daftar Peserta (2 tab)
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 flex-wrap">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:26px;height:26px;background:#f0fdf4;">
            <i class="bi bi-people-fill text-success" style="font-size:.75rem;"></i>
        </div>
        <span class="fw-semibold">Daftar Peserta</span>
        <span class="badge bg-success ms-1">{{ $pesertaCount }}</span>

        <ul class="nav nav-pills ms-3" role="tablist">
            <li class="nav-item">
                <button class="nav-link active py-1 px-3 small" data-bs-toggle="tab" data-bs-target="#tab-pra" type="button">
                    Pra-Asesmen
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-1 px-3 small" data-bs-toggle="tab" data-bs-target="#tab-progress" type="button">
                    Progress Asesmen
                </button>
            </li>
        </ul>

        <div class="ms-auto">
            <input type="text" class="form-control form-control-sm" id="search-peserta" placeholder="Cari peserta..." style="max-width:200px;">
        </div>
    </div>
    <div class="card-body p-0">
        @if($peserta->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
            <p class="small">Belum ada peserta dalam jadwal ini.</p>
        </div>
        @else
        <div class="tab-content">

            {{-- ── TAB 1: Pra-Asesmen ── --}}
            <div class="tab-pane fade show active" id="tab-pra">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 peserta-table">
                        <thead class="table-light" style="font-size:.78rem;">
                            <tr>
                                <th class="ps-3" width="40">#</th>
                                <th>Asesi</th>
                                <th>Status</th>
                                <th class="text-center">APL-01</th>
                                <th class="text-center">APL-02</th>
                                <th class="text-center">FR.AK.01</th>
                                <th class="text-center">Hasil</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peserta as $i => $asesmen)
                            <tr data-search="{{ strtolower($asesmen->full_name . ' ' . ($asesmen->user?->email ?? '') . ' ' . ($asesmen->institution ?? '')) }}">
                                <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width:32px;height:32px;background:#e0e7ff;color:#4f46e5;font-size:.72rem;font-weight:700;">
                                            {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ $asesmen->full_name }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">{{ $asesmen->user?->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-{{ $asesmen->status_badge }}">{{ $asesmen->status_label }}</span></td>
                                <td class="text-center">
                                    @if($asesmen->aplsatu)<span class="badge bg-{{ $asesmen->aplsatu->status_badge }}">{{ $asesmen->aplsatu->status_label }}</span>
                                    @else<span class="text-muted" style="font-size:.75rem;">—</span>@endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->apldua)<span class="badge bg-{{ $asesmen->apldua->status_badge }}">{{ $asesmen->apldua->status_label }}</span>
                                    @else<span class="text-muted" style="font-size:.75rem;">—</span>@endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->frak01)<span class="badge bg-{{ $asesmen->frak01->status_badge }}">{{ $asesmen->frak01->status_label }}</span>
                                    @else<span class="text-muted" style="font-size:.75rem;">—</span>@endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->result)
                                    <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">{{ ucfirst($asesmen->result) }}</span>
                                    @else<span class="text-muted" style="font-size:.75rem;">—</span>@endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.asesi.show', $asesmen) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail Asesi">
                                        <i class="bi bi-person-lines-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 2: Progress Asesmen ── --}}
            <div class="tab-pane fade" id="tab-progress">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 peserta-table">
                        <thead class="table-light" style="font-size:.78rem;">
                            <tr>
                                <th class="ps-3" width="40">#</th>
                                <th>Asesi</th>
                                <th class="text-center">Kehadiran</th>
                                @if($pakaiTeori)<th class="text-center">Teori</th>@endif
                                @if($totalObs)<th class="text-center">Observasi</th>@endif
                                @if($pakaiPorto)<th class="text-center">Dok. Ujikom</th>@endif
                                <th class="text-center">Umpan Balik</th>
                                <th class="text-center pe-3">Rekomendasi BA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peserta as $i => $asesmen)
                            @php $pg = $progress[$asesmen->id]; @endphp
                            <tr data-search="{{ strtolower($asesmen->full_name . ' ' . ($asesmen->user?->email ?? '') . ' ' . ($asesmen->institution ?? '')) }}">
                                <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-semibold small">{{ $asesmen->full_name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $asesmen->institution ?? ($asesmen->user?->email ?? '-') }}</div>
                                </td>

                                {{-- Kehadiran --}}
                                <td class="text-center">
                                    @if(!$asesmenDimulai)
                                    <span class="text-muted" style="font-size:.75rem;">—</span>
                                    @elseif($pg['hadir'])
                                    <span class="badge bg-success">Hadir</span>
                                    @else
                                    <span class="badge bg-danger">Tidak hadir</span>
                                    @endif
                                </td>

                                {{-- Teori --}}
                                @if($pakaiTeori)
                                <td class="text-center">
                                    @switch($pg['teori']['status'])
                                        @case('selesai')
                                            <span class="badge bg-success">Selesai</span>
                                            <div class="small fw-bold mt-1">Nilai {{ $pg['teori']['nilai'] }}</div>
                                            <div class="text-muted" style="font-size:.7rem;">{{ $pg['teori']['benar'] }}/{{ $pg['teori']['total'] }} benar</div>
                                            @break
                                        @case('selesai_arsip')
                                            <span class="badge bg-success">Selesai</span>
                                            <div class="text-muted mt-1" style="font-size:.7rem;"
                                                 title="Jadwal lama sebelum sistem paket soal. Status diambil dari berita acara, data jawaban tidak tersimpan.">
                                                Nilai tidak tersimpan
                                            </div>
                                            @break
                                        @case('mengerjakan')
                                            <span class="badge bg-warning text-dark">Mengerjakan</span>
                                            <div class="text-muted" style="font-size:.7rem;">{{ $pg['teori']['dijawab'] }}/{{ $pg['teori']['total'] }} dijawab</div>
                                            @break
                                        @case('belum')
                                            <span class="badge bg-light text-muted border">Belum mulai</span>
                                            @break
                                        @case('hilang')
                                            <span class="badge bg-light text-muted border"
                                                  title="Distribusi ada, tapi data jawaban asesi tidak tersimpan di sistem">
                                                Data tidak tersedia
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">Belum dapat soal</span>
                                    @endswitch
                                </td>
                                @endif

                                {{-- Observasi --}}
                                @if($totalObs)
                                @php $od = $pg['observasi']['done']; @endphp
                                <td class="text-center">
                                    <span class="badge {{ $od >= $totalObs ? 'bg-success' : ($od > 0 ? 'bg-warning text-dark' : 'bg-light text-muted border') }}">
                                        {{ $od }}/{{ $totalObs }} link
                                    </span>
                                </td>
                                @endif

                                {{-- Dok. Ujikom (portofolio) --}}
                                @if($pakaiPorto)
                                <td class="text-center">
                                    @if($pg['ujikom'])
                                    <a href="{{ $asesmen->apldua->gdrive_ujikom }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.72rem;">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Buka
                                    </a>
                                    @else
                                    <span class="badge bg-light text-muted border">Belum</span>
                                    @endif
                                </td>
                                @endif

                                {{-- Umpan Balik --}}
                                <td class="text-center">
                                    @if($pg['umpan_balik'] && isset($umpanBalik[$asesmen->id]))
                                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size:.72rem;"
                                            onclick="lihatUmpanBalik({{ $asesmen->id }})">
                                        <i class="bi bi-chat-square-text me-1"></i>Lihat
                                    </button>
                                    @else
                                    <i class="bi bi-dash-circle text-muted" title="Belum mengisi FR.AK.03"></i>
                                    @endif
                                </td>

                                {{-- Rekomendasi BA --}}
                                <td class="text-center pe-3">
                                    @if($pg['rekomendasi'] === 'K')
                                    <span class="badge bg-success">K</span>
                                    @elseif($pg['rekomendasi'] === 'BK')
                                    <span class="badge bg-danger">BK</span>
                                    @else
                                    <span class="text-muted" style="font-size:.75rem;">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════ --}}

{{-- Modal Foto --}}
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalFotoTitle">Foto Dokumentasi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2 text-center bg-light">
                <img id="modalFotoImg" src="" alt="Foto dokumentasi" style="max-width:100%;max-height:75vh;border-radius:6px;">
            </div>
            <div class="modal-footer py-2">
                <a id="modalFotoDownload" href="#" class="btn btn-sm btn-primary">
                    <i class="bi bi-download me-1"></i>Unduh
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Modal Umpan Balik per asesi --}}
<div class="modal fade" id="modalUmpanBalik" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0"><i class="bi bi-chat-square-text me-2"></i>Umpan Balik — <span id="ubNama"></span></h5>
                    <div class="text-muted small" id="ubTanggal"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light" style="font-size:.75rem;">
                        <tr>
                            <th class="ps-3" width="36">No</th>
                            <th>Komponen</th>
                            <th class="text-center" width="80">Jawaban</th>
                            <th width="30%">Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="ubBody"></tbody>
                </table>
                <div class="p-3 border-top" id="ubCatatanLainBox" style="display:none;">
                    <div class="small fw-semibold mb-1">Catatan / komentar lainnya</div>
                    <div class="ub-catatan" id="ubCatatanLain"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Rekap Umpan Balik --}}
@if($jmlUB > 0)
<div class="modal fade" id="modalRekapUB" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0"><i class="bi bi-bar-chart me-2"></i>Rekap Umpan Balik (FR.AK.03)</h5>
                    <div class="text-muted small">{{ $jmlUB }} dari {{ $pesertaCount }} peserta sudah mengisi</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light" style="font-size:.75rem;">
                        <tr>
                            <th class="ps-3" width="36">No</th>
                            <th>Komponen</th>
                            <th width="200">Ya / Tidak</th>
                            <th width="30%">Catatan peserta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < $jmlPertanyaanUB; $i++)
                        @php
                            $r      = $rekapUmpanBalik[$i] ?? ['ya' => 0, 'tidak' => 0, 'catatan' => []];
                            $totR   = $r['ya'] + $r['tidak'];
                            $pctYa  = $totR ? round($r['ya'] / $totR * 100) : 0;
                        @endphp
                        <tr class="ub-row">
                            <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                            <td>{{ $pertanyaanUmpanBalik[$i] ?? 'Pertanyaan ' . ($i + 1) }}</td>
                            <td>
                                <div class="d-flex justify-content-between" style="font-size:.72rem;">
                                    <span class="text-success fw-semibold">Ya {{ $r['ya'] }}</span>
                                    <span class="text-danger fw-semibold">Tidak {{ $r['tidak'] }}</span>
                                </div>
                                <div class="progress mt-1" style="height:6px;">
                                    <div class="progress-bar bg-success" style="width:{{ $pctYa }}%"></div>
                                    <div class="progress-bar bg-danger" style="width:{{ $totR ? 100 - $pctYa : 0 }}%"></div>
                                </div>
                            </td>
                            <td>
                                @forelse($r['catatan'] as $c)
                                <div class="ub-catatan mb-1"><strong>{{ $c['nama'] }}:</strong> {{ $c['catatan'] }}</div>
                                @empty
                                <span class="text-muted" style="font-size:.75rem;">—</span>
                                @endforelse
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>

                @php $catatanLain = collect($umpanBalik)->filter(fn($u) => filled($u['catatan_lain'])); @endphp
                @if($catatanLain->isNotEmpty())
                <div class="p-3 border-top">
                    <div class="small fw-semibold mb-2">Catatan / komentar lainnya</div>
                    @foreach($catatanLain as $u)
                    <div class="ub-catatan mb-2"><strong>{{ $u['nama'] }}:</strong> {{ $u['catatan_lain'] }}</div>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal Assign Asesor --}}
<div class="modal fade" id="modalAsesor" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Tugaskan Asesor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="modal-search-asesor" placeholder="Cari asesor...">
                </div>
                <div id="asesor-list-modal">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <div class="small text-muted mt-2">Memuat daftar asesor...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-assign-asesor" disabled onclick="submitAssignAsesor()">
                    <i class="bi bi-person-check me-1"></i>Tugaskan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content;
const SCHEDULE_ID = {{ $schedule->id }};
const UMPAN_BALIK = @json($umpanBalik);
const PERTANYAAN_UB = @json($pertanyaanUmpanBalik);
let selectedAsesorId = null;

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

// Search peserta — berlaku untuk kedua tab
document.getElementById('search-peserta')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.peserta-table tbody tr').forEach(tr => {
        tr.style.display = !q || tr.dataset.search?.includes(q) ? '' : 'none';
    });
});

// ── Foto dokumentasi ─────────────────────────────────────────
function lihatFoto(url, slot) {
    document.getElementById('modalFotoTitle').textContent = 'Foto Dokumentasi ' + slot;
    document.getElementById('modalFotoImg').src = url;
    document.getElementById('modalFotoDownload').href = url + '?download=1';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFoto')).show();
}

// ── Umpan balik per asesi ────────────────────────────────────
function lihatUmpanBalik(asesmenId) {
    const data = UMPAN_BALIK[asesmenId];
    if (!data) return;

    document.getElementById('ubNama').textContent    = data.nama;
    document.getElementById('ubTanggal').textContent = data.submitted_at ? 'Diisi ' + data.submitted_at : '';

    const jumlah = Math.max(PERTANYAAN_UB.length, data.jawaban.length);
    let rows = '';
    for (let i = 0; i < jumlah; i++) {
        const item = data.jawaban[i] ?? { jawaban: null, catatan: null };
        const badge = item.jawaban === 'ya'
            ? '<span class="badge bg-success">Ya</span>'
            : item.jawaban === 'tidak'
                ? '<span class="badge bg-danger">Tidak</span>'
                : '<span class="text-muted">—</span>';

        rows += `
            <tr class="ub-row">
                <td class="ps-3 text-muted">${i + 1}</td>
                <td>${escHtml(PERTANYAAN_UB[i] ?? 'Pertanyaan ' + (i + 1))}</td>
                <td class="text-center">${badge}</td>
                <td><div class="ub-catatan">${item.catatan ? escHtml(item.catatan) : '<span class="text-muted">—</span>'}</div></td>
            </tr>`;
    }
    document.getElementById('ubBody').innerHTML = rows;

    const box = document.getElementById('ubCatatanLainBox');
    if (data.catatan_lain) {
        document.getElementById('ubCatatanLain').textContent = data.catatan_lain;
        box.style.display = '';
    } else {
        box.style.display = 'none';
    }

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUmpanBalik')).show();
}

// ── Asesor ───────────────────────────────────────────────────
async function openAsesorModal() {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAsesor'));
    modal.show();
    try {
        const res  = await fetch(`/admin/schedules/${SCHEDULE_ID}/available-asesors`, { headers: { Accept:'application/json', 'X-CSRF-TOKEN':CSRF } });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        renderAsesorList(data.asesors);
    } catch(e) {
        document.getElementById('asesor-list-modal').innerHTML = `<div class="alert alert-danger">Gagal memuat daftar asesor: ${e.message}</div>`;
    }
}

function renderAsesorList(asesors) {
    const container = document.getElementById('asesor-list-modal');
    if (!asesors.length) {
        container.innerHTML = `<div class="text-center py-4 text-muted"><i class="bi bi-person-x fs-2 d-block mb-2 opacity-25"></i><p class="small">Tidak ada asesor tersedia.</p></div>`;
        return;
    }
    container.innerHTML = asesors.map(a => `
        <label class="asesor-opt d-flex align-items-center gap-3 p-3 rounded border mb-2"
               style="cursor:pointer;transition:all .15s;"
               data-id="${a.id}" data-search="${(a.nama + ' ' + (a.no_reg_met ?? '')).toLowerCase()}"
               onmouseover="this.style.background='#f0f7ff';this.style.borderColor='#93c5fd';"
               onmouseout="if(!this.classList.contains('selected')){this.style.background='';this.style.borderColor='';}"
               onclick="selectAsesor(${a.id}, this)">
            <div style="width:40px;height:40px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,#4f46e5,#2563eb);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;">
                ${a.nama.charAt(0).toUpperCase()}
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold small">${a.nama}</div>
                <div class="text-muted" style="font-size:.75rem;">${a.no_reg_met ?? 'Tanpa no. registrasi'}${a.email ? ' &bull; ' + a.email : ''}</div>
            </div>
            <i class="bi bi-circle text-muted" id="asesor-icon-${a.id}"></i>
        </label>`).join('');
}

function selectAsesor(id, el) {
    document.querySelectorAll('.asesor-opt').forEach(o => {
        o.classList.remove('selected'); o.style.background = ''; o.style.borderColor = '';
        const ico = o.querySelector('[id^="asesor-icon-"]');
        if (ico) ico.className = 'bi bi-circle text-muted';
    });
    el.classList.add('selected'); el.style.background = '#eff6ff'; el.style.borderColor = '#2563eb';
    const ico = document.getElementById(`asesor-icon-${id}`);
    if (ico) ico.className = 'bi bi-check-circle-fill text-primary';
    selectedAsesorId = id;
    document.getElementById('btn-assign-asesor').disabled = false;
}

document.getElementById('modal-search-asesor')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.asesor-opt').forEach(opt => {
        opt.style.display = !q || opt.dataset.search?.includes(q) ? '' : 'none';
    });
});

async function submitAssignAsesor() {
    if (!selectedAsesorId) return;
    const btn = document.getElementById('btn-assign-asesor');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menugaskan...';
    try {
        const res  = await fetch(`/admin/schedules/${SCHEDULE_ID}/assign-asesor`, {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,Accept:'application/json'},
            body: JSON.stringify({ asesor_id: selectedAsesorId }),
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalAsesor'))?.hide();
            await Swal.fire({ icon:'success', title:'Berhasil!', text:data.message, timer:1600, showConfirmButton:false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.disabled = false; btn.innerHTML = '<i class="bi bi-person-check me-1"></i>Tugaskan';
        }
    } catch { Swal.fire('Error','Terjadi kesalahan.','error'); btn.disabled=false; btn.innerHTML='<i class="bi bi-person-check me-1"></i>Tugaskan'; }
}

async function unassignAsesor(scheduleId) {
    const result = await Swal.fire({ title:'Lepas Asesor?', text:'Asesor akan dilepas dari jadwal ini.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, Lepas', cancelButtonText:'Batal', confirmButtonColor:'#dc2626', reverseButtons:true });
    if (!result.isConfirmed) return;
    try {
        const res  = await fetch(`/admin/schedules/${scheduleId}/unassign-asesor`, { method:'POST', headers:{'X-CSRF-TOKEN':CSRF,Accept:'application/json'} });
        const data = await res.json();
        if (data.success) { await Swal.fire({icon:'success',title:'Berhasil!',timer:1400,showConfirmButton:false}); location.reload(); }
        else Swal.fire('Gagal', data.message, 'error');
    } catch { Swal.fire('Error','Terjadi kesalahan.','error'); }
}

async function deleteSchedule(id, peserta) {
    const result = await Swal.fire({
        title:'Hapus Jadwal?',
        html:`<p class="text-muted small mb-2">${peserta} asesi akan dikembalikan ke status sebelumnya.</p><div class="alert alert-warning py-2 small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan tidak dapat dibatalkan.</div>`,
        icon:'warning', showCancelButton:true, confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', confirmButtonColor:'#dc2626', reverseButtons:true,
    });
    if (!result.isConfirmed) return;
    try {
        const res  = await fetch(`/admin/schedules/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,Accept:'application/json'} });
        const data = await res.json();
        if (data.success) { await Swal.fire({icon:'success',title:'Berhasil!',text:data.message,timer:1600,showConfirmButton:false}); window.location.href='{{ route("admin.schedules.index") }}'; }
        else Swal.fire('Gagal', data.message, 'error');
    } catch { Swal.fire('Error','Terjadi kesalahan.','error'); }
}
</script>
@endpush