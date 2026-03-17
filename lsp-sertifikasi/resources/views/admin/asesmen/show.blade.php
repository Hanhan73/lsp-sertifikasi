@extends('layouts.app')
@section('title', 'Detail Asesi — ' . ($asesmen->full_name ?? $asesmen->user?->name))
@section('page-title', 'Proses Asesmen')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
/* ── Breadcrumb ── */
.bc-link { color: #64748b; text-decoration: none; font-size: .82rem; }
.bc-link:hover { color: #1e40af; }

/* ── Hero strip ── */
.asesi-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 14px;
    padding: 22px 28px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
}
.asesi-hero .avatar {
    width: 60px; height: 60px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; flex-shrink: 0;
}
.asesi-hero .name { font-size: 1.25rem; font-weight: 700; line-height: 1.2; }
.asesi-hero .meta { font-size: .8rem; opacity: .8; margin-top: 4px; }

/* ── Section tabs ── */
.doc-tabs { display: flex; gap: 6px; margin-bottom: 18px; flex-wrap: wrap; }
.doc-tab {
    padding: 7px 18px; border-radius: 8px; font-size: .82rem; font-weight: 600;
    border: none; cursor: pointer; transition: all .2s;
    background: #f1f5f9; color: #475569;
}
.doc-tab.active { background: #1d4ed8; color: #fff; }
.doc-tab:hover:not(.active) { background: #e2e8f0; }
.doc-section { display: none; }
.doc-section.active { display: block; }

/* ── Doc cards ── */
.doc-card {
    border: 1.5px solid #e2e8f0; border-radius: 12px;
    background: #fff; overflow: hidden;
    transition: box-shadow .2s;
}
.doc-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); }
.doc-card-header {
    padding: 14px 18px;
    display: flex; align-items: center; gap: 12px;
    border-bottom: 1px solid #f1f5f9;
}
.doc-card-header .doc-icon {
    width: 38px; height: 38px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.doc-card-body { padding: 16px 18px; }

/* ── Status chips ── */
.chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 11px; border-radius: 20px;
    font-size: .72rem; font-weight: 600; white-space: nowrap;
}
.chip-draft     { background: #f1f5f9; color: #64748b; }
.chip-submitted { background: #fef9c3; color: #a16207; }
.chip-verified  { background: #d1fae5; color: #065f46; }
.chip-returned  { background: #fee2e2; color: #991b1b; }

/* ── Info grid ── */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px; }
@media(max-width:576px){ .info-grid { grid-template-columns: 1fr; } }
.info-row .lbl { font-size: .76rem; color: #94a3b8; margin-bottom: 1px; }
.info-row .val { font-size: .88rem; font-weight: 600; color: #1e293b; }

/* ── Progress bar ── */
.prog-bar-wrap { background: #f1f5f9; border-radius: 99px; height: 7px; overflow: hidden; }
.prog-bar-fill { height: 100%; border-radius: 99px; transition: width .5s; }

/* ── Timeline (sidebar) ── */
.tl { padding: 0; list-style: none; position: relative; }
.tl::before {
    content: ''; position: absolute;
    left: 9px; top: 6px; bottom: 6px; width: 2px; background: #e2e8f0;
}
.tl-item { position: relative; padding-left: 28px; padding-bottom: 18px; }
.tl-dot {
    position: absolute; left: 0; top: 4px;
    width: 18px; height: 18px; border-radius: 50%;
    border: 3px solid #fff; background: #cbd5e1;
    box-shadow: 0 0 0 2px #e2e8f0;
}
.tl-dot.done { background: #10b981; box-shadow: 0 0 0 2px #d1fae5; }
.tl-dot.current { background: #f59e0b; box-shadow: 0 0 0 2px #fef9c3; }
.tl-dot.pending { background: #e2e8f0; box-shadow: 0 0 0 2px #f1f5f9; }
.tl-title { font-size: .82rem; font-weight: 600; color: #1e293b; }
.tl-sub { font-size: .75rem; color: #94a3b8; margin-top: 1px; }

/* ── Signature box ── */
.ttd-box {
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    background: #f8fafc; min-height: 80px;
    display: flex; align-items: center; justify-content: center; padding: 8px;
}
.ttd-box img { max-height: 80px; max-width: 200px; display: block; }

/* ── Bukti item ── */
.bukti-row {
    border-left: 3px solid #e2e8f0; padding: 8px 12px;
    border-radius: 0 6px 6px 0; margin-bottom: 6px;
    background: #fafafa;
}
.bukti-row.ok  { border-left-color: #10b981; background: #f0fdf4; }
.bukti-row.warn{ border-left-color: #f59e0b; background: #fffbeb; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav class="mb-3">
    <a href="{{ route('admin.apl01.index') }}" class="bc-link">
        <i class="bi bi-journal-check me-1"></i>Proses Asesmen
    </a>
    <span class="text-muted small mx-2">/</span>
    <span class="text-muted small">{{ $asesmen->full_name ?? $asesmen->user?->name }}</span>
</nav>

{{-- Hero --}}
<div class="asesi-hero">
    <div class="avatar"><i class="bi bi-person-fill"></i></div>
    <div class="flex-grow-1">
        <div class="name">{{ $asesmen->full_name ?? $asesmen->user?->name ?? '-' }}</div>
        <div class="meta">
            <span class="me-3"><i class="bi bi-building me-1"></i>{{ $asesmen->schedule?->tuk?->name ?? $asesmen->tuk?->name ?? '-' }}</span>
            <span class="me-3"><i class="bi bi-award me-1"></i>{{ $asesmen->skema?->name ?? '-' }}</span>
            @if($asesmen->schedule)
            <span><i class="bi bi-calendar me-1"></i>{{ $asesmen->schedule->assessment_date->translatedFormat('d F Y') }}</span>
            @endif
        </div>
    </div>
    <span class="badge bg-white bg-opacity-20 text-white px-3 py-2 fs-6">
        {{ $asesmen->status_label }}
    </span>
</div>

<div class="row g-4">

    {{-- ── KIRI: Dokumen --}}
    <div class="col-lg-8">

        {{-- Tabs --}}
        <div class="doc-tabs">
            <button class="doc-tab active" onclick="switchTab(this,'tab-apl01')">
                <i class="bi bi-file-earmark-person me-1"></i>APL-01
                @if($aplsatu?->status === 'submitted')
                <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Perlu Verif</span>
                @endif
            </button>
            <button class="doc-tab" onclick="switchTab(this,'tab-apl02')">
                <i class="bi bi-clipboard-check me-1"></i>APL-02
                @if($apldua?->status === 'submitted')
                <span class="badge bg-info ms-1" style="font-size:.6rem;">Perlu Verif</span>
                @endif
            </button>
            <button class="doc-tab" onclick="switchTab(this,'tab-profil')">
                <i class="bi bi-person-lines-fill me-1"></i>Profil
            </button>
        </div>

        {{-- ══ TAB APL-01 ══ --}}
        <div id="tab-apl01" class="doc-section active">
            @if($aplsatu)
            <div class="doc-card mb-3">
                <div class="doc-card-header">
                    <div class="doc-icon" style="background:#eff6ff; color:#1d4ed8;">
                        <i class="bi bi-file-earmark-person"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">FR.APL.01 — Permohonan Sertifikasi</div>
                        <div class="small text-muted mt-1">
                            @php
                                $chip = match($aplsatu->status) {
                                    'draft'     => ['chip-draft',     'Draft'],
                                    'submitted' => ['chip-submitted', 'Menunggu Verifikasi'],
                                    'verified'  => ['chip-verified',  'Terverifikasi'],
                                    'returned'  => ['chip-returned',  'Dikembalikan'],
                                    default     => ['chip-draft',     ucfirst($aplsatu->status)],
                                };
                            @endphp
                            <span class="chip {{ $chip[0] }}">{{ $chip[1] }}</span>
                            @if($aplsatu->submitted_at)
                            <span class="ms-2 text-muted" style="font-size:.75rem;">
                                Submit: {{ $aplsatu->submitted_at->format('d M Y H:i') }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.apl01.show', $aplsatu) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Detail & Verifikasi
                        </a>
                        @if(in_array($aplsatu->status, ['verified','approved']))
                        <a href="{{ route('admin.apl01.pdf', [$aplsatu, 'preview' => 1]) }}"
                           target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </a>
                        @endif
                    </div>
                </div>
                <div class="doc-card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="lbl">Nama Lengkap</div>
                            <div class="val">{{ $aplsatu->nama_lengkap }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">NIK</div>
                            <div class="val font-monospace">{{ $aplsatu->nik ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">Tujuan Asesmen</div>
                            <div class="val">{{ $aplsatu->tujuan_asesmen ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">Institusi</div>
                            <div class="val">{{ $aplsatu->nama_institusi ?? '-' }}</div>
                        </div>
                    </div>

                    {{-- Progress bukti --}}
                    @php
                        $totalBukti = $aplsatu->buktiKelengkapan?->count() ?? 0;
                        $okBukti    = $aplsatu->buktiKelengkapan?->where('status', 'Ada Memenuhi Syarat')->count() ?? 0;
                        $pct        = $totalBukti > 0 ? round($okBukti / $totalBukti * 100) : 0;
                    @endphp
                    @if($totalBukti > 0)
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Bukti Kelengkapan</span>
                            <span class="fw-semibold">{{ $okBukti }}/{{ $totalBukti }} Memenuhi</span>
                        </div>
                        <div class="prog-bar-wrap">
                            <div class="prog-bar-fill bg-{{ $pct === 100 ? 'success' : 'warning' }}"
                                 style="width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                    @endif

                    {{-- TTD asesi --}}
                    @if($aplsatu->ttd_pemohon)
                    <div class="mt-3 pt-3 border-top d-flex gap-4 align-items-center flex-wrap">
                        <div>
                            <div class="lbl mb-1">Tanda Tangan Pemohon</div>
                            <div class="ttd-box" style="width:160px;">
                                <img src="{{ $aplsatu->ttd_pemohon_image }}" alt="TTD">
                            </div>
                        </div>
                        @if($aplsatu->ttd_admin)
                        <div>
                            <div class="lbl mb-1">Tanda Tangan Admin LSP</div>
                            <div class="ttd-box" style="width:160px;">
                                <img src="{{ $aplsatu->ttd_admin_image }}" alt="TTD Admin">
                            </div>
                            <div class="small text-muted mt-1">{{ $aplsatu->nama_ttd_admin }}</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="doc-card">
                <div class="doc-card-body text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-x d-block mb-2" style="font-size:2.5rem; opacity:.3;"></i>
                    <div class="fw-semibold">APL-01 belum diisi</div>
                    <div class="small mt-1">Asesi belum mengisi formulir permohonan sertifikasi.</div>
                </div>
            </div>
            @endif
        </div>

        {{-- ══ TAB APL-02 ══ --}}
        <div id="tab-apl02" class="doc-section">
            @if($apldua)
            <div class="doc-card mb-3">
                <div class="doc-card-header">
                    <div class="doc-icon" style="background:#f0fdf4; color:#16a34a;">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">FR.APL.02 — Asesmen Mandiri</div>
                        <div class="small mt-1">
                            @php
                                $chip2 = match($apldua->status) {
                                    'draft'     => ['chip-draft',     'Draft'],
                                    'submitted' => ['chip-submitted', 'Menunggu Verifikasi'],
                                    'verified'  => ['chip-verified',  'Terverifikasi'],
                                    default     => ['chip-draft',     ucfirst($apldua->status)],
                                };
                            @endphp
                            <span class="chip {{ $chip2[0] }}">{{ $chip2[1] }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if(in_array($apldua->status, ['verified','approved']))
                        <a href="{{ route('admin.apl02.pdf', [$apldua, 'preview' => 1]) }}"
                           target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF APL-02
                        </a>
                        @endif
                    </div>
                </div>
                <div class="doc-card-body">
                    {{-- Progress K/BK --}}
                    @php
                        $prog = $apldua->progress;
                    @endphp
                    @if($prog['total'] > 0)
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <div class="text-center p-2 rounded" style="background:#f8fafc;">
                                <div style="font-size:1.4rem; font-weight:700; color:#1d4ed8;">{{ $prog['answered'] }}</div>
                                <div class="small text-muted">Dijawab</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 rounded" style="background:#f0fdf4;">
                                <div style="font-size:1.4rem; font-weight:700; color:#16a34a;">{{ $prog['k'] }}</div>
                                <div class="small text-muted">Kompeten</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-2 rounded" style="background:#fff7ed;">
                                <div style="font-size:1.4rem; font-weight:700; color:#d97706;">{{ $prog['bk'] }}</div>
                                <div class="small text-muted">Belum Kompeten</div>
                            </div>
                        </div>
                    </div>
                    <div class="prog-bar-wrap mb-3">
                        <div class="prog-bar-fill bg-primary"
                             style="width:{{ round($prog['answered']/$prog['total']*100) }}%;"></div>
                    </div>
                    @endif

                    {{-- Rekomendasi asesor --}}
                    @if($apldua->rekomendasi_asesor)
                    <div class="p-3 rounded mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                        <div class="small text-muted mb-1">Rekomendasi Asesor</div>
                        <strong class="{{ $apldua->rekomendasi_asesor === 'lanjut' ? 'text-success' : 'text-danger' }}">
                            {{ $apldua->rekomendasi_asesor === 'lanjut' ? '✓ Dapat dilanjutkan' : '✗ Tidak dapat dilanjutkan' }}
                        </strong>
                        @if($apldua->catatan_asesor)
                        <div class="small text-muted mt-1">{{ $apldua->catatan_asesor }}</div>
                        @endif
                    </div>
                    @endif

                    {{-- TTD APL-02 --}}
                    @if($apldua->ttd_asesi || $apldua->ttd_asesor)
                    <div class="d-flex gap-4 flex-wrap pt-2 border-top">
                        @if($apldua->ttd_asesi)
                        <div>
                            <div class="lbl mb-1">TTD Asesi</div>
                            <div class="ttd-box" style="width:150px;">
                                <img src="{{ $apldua->ttd_asesi_image }}" alt="TTD Asesi">
                            </div>
                            <div class="small text-muted mt-1">{{ $apldua->tanggal_ttd_asesi?->format('d M Y') }}</div>
                        </div>
                        @endif
                        @if($apldua->ttd_asesor)
                        <div>
                            <div class="lbl mb-1">TTD Asesor</div>
                            <div class="ttd-box" style="width:150px;">
                                <img src="{{ $apldua->ttd_asesor_image }}" alt="TTD Asesor">
                            </div>
                            <div class="small text-muted mt-1">{{ $apldua->nama_ttd_asesor }}</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="doc-card">
                <div class="doc-card-body text-center py-5 text-muted">
                    <i class="bi bi-clipboard-x d-block mb-2" style="font-size:2.5rem; opacity:.3;"></i>
                    <div class="fw-semibold">APL-02 belum diisi</div>
                    <div class="small mt-1">Asesi belum mengisi asesmen mandiri.</div>
                </div>
            </div>
            @endif
        </div>

        {{-- ══ TAB PROFIL ══ --}}
        <div id="tab-profil" class="doc-section">
            <div class="doc-card">
                <div class="doc-card-body">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="lbl">Nama Lengkap</div>
                            <div class="val">{{ $asesmen->full_name ?? $asesmen->user?->name ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">NIK</div>
                            <div class="val font-monospace">{{ $aplsatu?->nik ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">Email</div>
                            <div class="val">{{ $asesmen->user?->email ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">No. HP</div>
                            <div class="val">{{ $aplsatu?->hp ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">Tempat / Tanggal Lahir</div>
                            <div class="val">
                                {{ $aplsatu?->tempat_lahir ?? '-' }}
                                @if($aplsatu?->tanggal_lahir)
                                , {{ \Carbon\Carbon::parse($aplsatu->tanggal_lahir)->format('d M Y') }}
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">Jenis Kelamin</div>
                            <div class="val">{{ $aplsatu?->jenis_kelamin ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">Pendidikan</div>
                            <div class="val">{{ $aplsatu?->kualifikasi_pendidikan ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="lbl">Institusi / Jabatan</div>
                            <div class="val">{{ $aplsatu?->nama_institusi ?? '-' }}{{ $aplsatu?->jabatan ? ' — ' . $aplsatu->jabatan : '' }}</div>
                        </div>
                        <div class="info-row" style="grid-column:1/-1;">
                            <div class="lbl">Alamat</div>
                            <div class="val">{{ $aplsatu?->alamat_rumah ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── KANAN: Status & Timeline --}}
    <div class="col-lg-4">

        {{-- Status Card --}}
        <div class="doc-card mb-3">
            <div class="doc-card-body">
                <div class="fw-semibold mb-3 small text-muted text-uppercase" style="letter-spacing:.5px;">
                    <i class="bi bi-activity me-1"></i>Status Proses
                </div>
                <ul class="tl">
                    @php
                        $steps = [
                            ['label' => 'Pendaftaran',      'sub' => $asesmen->registration_date?->format('d M Y'), 'done' => true],
                            ['label' => 'APL-01 Submit',    'sub' => $aplsatu?->submitted_at?->format('d M Y'),    'done' => (bool)$aplsatu?->submitted_at],
                            ['label' => 'APL-01 Verified',  'sub' => $aplsatu?->verified_at?->format('d M Y'),     'done' => in_array($aplsatu?->status, ['verified','approved'])],
                            ['label' => 'APL-02 Submit',    'sub' => $apldua?->submitted_at?->format('d M Y'),     'done' => (bool)$apldua?->submitted_at],
                            ['label' => 'APL-02 Verified',  'sub' => $apldua?->verified_at?->format('d M Y'),      'done' => in_array($apldua?->status, ['verified','approved'])],
                        ];
                        $firstPending = null;
                        foreach ($steps as $i => $s) {
                            if (!$s['done']) { $firstPending = $i; break; }
                        }
                    @endphp
                    @foreach($steps as $i => $step)
                    <li class="tl-item">
                        <div class="tl-dot {{ $step['done'] ? 'done' : ($i === $firstPending ? 'current' : 'pending') }}"></div>
                        <div class="tl-title">{{ $step['label'] }}</div>
                        @if($step['sub'])
                        <div class="tl-sub">{{ $step['sub'] }}</div>
                        @elseif(!$step['done'])
                        <div class="tl-sub">Belum selesai</div>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Info Jadwal --}}
        @if($asesmen->schedule)
        <div class="doc-card mb-3">
            <div class="doc-card-header">
                <div class="doc-icon" style="background:#f5f3ff; color:#7c3aed;">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="fw-semibold small">Jadwal Asesmen</div>
            </div>
            <div class="doc-card-body">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="lbl">Tanggal</div>
                        <div class="val">{{ $asesmen->schedule->assessment_date->translatedFormat('d F Y') }}</div>
                    </div>
                    @if($asesmen->schedule->start_time)
                    <div class="info-row">
                        <div class="lbl">Waktu</div>
                        <div class="val">{{ $asesmen->schedule->start_time }}{{ $asesmen->schedule->end_time ? ' – ' . $asesmen->schedule->end_time : '' }}</div>
                    </div>
                    @endif
                    @if($asesmen->schedule->asesor)
                    <div class="info-row" style="grid-column:1/-1;">
                        <div class="lbl">Asesor</div>
                        <div class="val">{{ $asesmen->schedule->asesor->nama }}</div>
                    </div>
                    @endif
                    @if($asesmen->schedule->location)
                    <div class="info-row" style="grid-column:1/-1;">
                        <div class="lbl">Lokasi</div>
                        <div class="val">{{ $asesmen->schedule->location }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Quick actions --}}
        <div class="doc-card">
            <div class="doc-card-body d-grid gap-2">
                @if($aplsatu && $aplsatu->status === 'submitted')
                <a href="{{ route('admin.apl01.show', $aplsatu) }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-check-circle me-1"></i>Verifikasi APL-01
                </a>
                @elseif($aplsatu)
                <a href="{{ route('admin.apl01.show', $aplsatu) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye me-1"></i>Lihat APL-01
                </a>
                @if(in_array($aplsatu->status, ['verified','approved']))
                <a href="{{ route('admin.apl01.pdf', [$aplsatu, 'preview' => 1]) }}" target="_blank"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF APL-01
                </a>
                @endif
                @endif

                @if($apldua && in_array($apldua->status, ['verified','approved']))
                <a href="{{ route('admin.apl02.pdf', [$apldua, 'preview' => 1]) }}" target="_blank"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF APL-02
                </a>
                @endif

                <a href="{{ route('admin.apl01.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(btn, sectionId) {
    document.querySelectorAll('.doc-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.doc-section').forEach(s => s.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(sectionId).classList.add('active');
}
</script>
@endpush