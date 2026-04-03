@extends('layouts.app')
@section('title', 'Dashboard Direktur')
@section('page-title', 'Dashboard Direktur')
@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@push('styles')
<style>
/* ─── Tokens ───────────────────────────────────────────────── */
:root {
    --c-blue:    #2563eb;
    --c-indigo:  #6366f1;
    --c-green:   #10b981;
    --c-amber:   #f59e0b;
    --c-slate:   #64748b;
    --radius-lg: 14px;
    --radius-md: 10px;
    --shadow-sm: 0 1px 4px rgba(0,0,0,.06);
    --shadow-md: 0 4px 18px rgba(0,0,0,.09);
}

/* ─── KPI Cards ────────────────────────────────────────────── */
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; margin-bottom: 28px; }
.kpi-card {
    background: #fff; border-radius: var(--radius-lg);
    border: 1.5px solid #e2e8f0; padding: 18px 16px;
    display: flex; flex-direction: column; gap: 6px;
    transition: box-shadow .18s, border-color .18s, transform .18s;
}
.kpi-card:hover { box-shadow: var(--shadow-md); border-color: #bfdbfe; transform: translateY(-2px); }
.kpi-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.kpi-value { font-size: 2rem; font-weight: 800; line-height: 1; color: #0f172a; }
.kpi-label { font-size: .75rem; color: var(--c-slate); font-weight: 500; }

/* ─── Section Heading ──────────────────────────────────────── */
.sec-head {
    font-size: .72rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: var(--c-slate);
    margin-bottom: 12px; display: flex; align-items: center; gap: 6px;
}
.sec-head::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

/* ─── Progress Funnel ──────────────────────────────────────── */
.funnel-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; }
.funnel-item {
    background: #fff; border: 1.5px solid #e2e8f0; border-radius: var(--radius-md);
    padding: 14px 12px; text-align: center;
    transition: box-shadow .18s;
}
.funnel-item:hover { box-shadow: var(--shadow-md); }
.funnel-dot {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; margin: 0 auto 8px;
}
.funnel-count { font-size: 1.6rem; font-weight: 800; line-height: 1; }
.funnel-label { font-size: .7rem; color: var(--c-slate); margin-top: 4px; line-height: 1.3; }

/* ─── Panel Card ───────────────────────────────────────────── */
.panel {
    background: #fff; border: 1.5px solid #e2e8f0;
    border-radius: var(--radius-lg); overflow: hidden;
    margin-bottom: 24px;
}
.panel-head {
    padding: 14px 18px; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between;
}
.panel-head .ttl { font-weight: 700; font-size: .88rem; color: #0f172a; }
.panel-body { padding: 6px 0; }
.panel-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 18px; border-bottom: 1px solid #f8fafc;
    font-size: .83rem;
}
.panel-row:last-child { border-bottom: none; }
.panel-row:hover { background: #f8fafc; }

/* ─── Asesor Avatar ────────────────────────────────────────── */
.asesor-av {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
}
.asesor-av-fallback {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--c-indigo), var(--c-blue));
    color: #fff; font-size: .85rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

/* ─── Skema bar ────────────────────────────────────────────── */
.skema-bar-wrap { flex: 1; margin: 0 10px; }
.skema-bar-track { height: 6px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
.skema-bar-fill  { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--c-blue), var(--c-indigo)); }

/* ─── Pending approval chip ─────────────────────────────────── */
.approval-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: #fef9c3; border: 1px solid #fde047;
    border-radius: 99px; padding: 3px 10px;
    font-size: .72rem; font-weight: 600; color: #92400e;
}

/* ─── Responsive ────────────────────────────────────────────── */
@media(max-width:576px){
    .kpi-grid { grid-template-columns: 1fr 1fr; }
    .funnel-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@section('content')

{{-- ── Sambutan ─────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0">Selamat datang, {{ auth()->user()->name }} 👋</h5>
        <small class="text-muted">Ringkasan aktivitas sistem LSP hari ini</small>
    </div>
    <span class="text-muted small"><i class="bi bi-clock me-1"></i>{{ now()->translatedFormat('l, d F Y') }}</span>
</div>

{{-- ── KPI Cards ────────────────────────────────────────────── --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#eff6ff;">
            <i class="bi bi-people" style="color:var(--c-blue);"></i>
        </div>
        <div class="kpi-value">{{ number_format($stats['total_asesi']) }}</div>
        <div class="kpi-label">Total Asesi</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f0fdf4;">
            <i class="bi bi-award" style="color:var(--c-green);"></i>
        </div>
        <div class="kpi-value">{{ number_format($stats['certified']) }}</div>
        <div class="kpi-label">Tersertifikasi</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fefce8;">
            <i class="bi bi-calendar-check" style="color:var(--c-amber);"></i>
        </div>
        <div class="kpi-value">{{ number_format($stats['total_jadwal']) }}</div>
        <div class="kpi-label">Total Jadwal</div>
    </div>
    @if($stats['pending_approval'] > 0)
    <div class="kpi-card" style="border-color:#fde047;">
        <div class="kpi-icon" style="background:#fefce8;">
            <i class="bi bi-hourglass-split" style="color:var(--c-amber);"></i>
        </div>
        <div class="kpi-value" style="color:var(--c-amber);">{{ $stats['pending_approval'] }}</div>
        <div class="kpi-label">Perlu Approval</div>
    </div>
    @endif
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#eef2ff;">
            <i class="bi bi-person-badge" style="color:var(--c-indigo);"></i>
        </div>
        <div class="kpi-value">{{ $stats['total_asesor'] }}</div>
        <div class="kpi-label">Asesor</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f0fdf4;">
            <i class="bi bi-building" style="color:var(--c-green);"></i>
        </div>
        <div class="kpi-value">{{ $stats['total_tuk'] }}</div>
        <div class="kpi-label">TUK Aktif</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fdf4ff;">
            <i class="bi bi-patch-check" style="color:#a855f7;"></i>
        </div>
        <div class="kpi-value">{{ $stats['total_skema'] }}</div>
        <div class="kpi-label">Skema Aktif</div>
    </div>
</div>

{{-- ── Progres Asesi ────────────────────────────────────────── --}}
<div class="sec-head"><i class="bi bi-bar-chart-steps text-primary"></i> Progres Asesi per Tahap</div>
<div class="funnel-grid mb-4">
    @foreach($progressStatus as $key => $item)
    <div class="funnel-item">
        <div class="funnel-dot" style="background: {{ $item['color'] }}22;">
            <i class="{{ $item['icon'] }}" style="color: {{ $item['color'] }};"></i>
        </div>
        <div class="funnel-count" style="color: {{ $item['color'] }};">{{ $item['count'] }}</div>
        <div class="funnel-label">{{ $item['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- ── Grid 2 kolom ─────────────────────────────────────────── --}}
<div class="row g-4">

    {{-- ── TUK & Batch ──────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="sec-head"><i class="bi bi-building text-success"></i> TUK & Pendaftaran Asesi</div>
        <div class="panel">
            <div class="panel-head">
                <span class="ttl">Rekap per TUK</span>
                <span class="text-muted" style="font-size:.75rem;">{{ $tukBatchStats->count() }} TUK</span>
            </div>
            <div class="panel-body">
                @forelse($tukBatchStats as $tuk)
                <div class="panel-row">
                    <div style="flex:1; min-width:0;">
                        <div class="fw-semibold text-truncate">{{ $tuk->name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">
                            Kode: {{ $tuk->code }}
                        </div>
                    </div>
                    <div class="text-center" style="min-width:56px;">
                        <div class="fw-bold" style="color:var(--c-blue);">{{ $tuk->asesmens_count }}</div>
                        <div style="font-size:.65rem;color:var(--c-slate);">Total Asesi</div>
                    </div>
                    <div class="text-center" style="min-width:56px;">
                        <div class="fw-bold" style="color:var(--c-indigo);">{{ $tuk->collective_count }}</div>
                        <div style="font-size:.65rem;color:var(--c-slate);">Kolektif</div>
                    </div>
                    <div class="text-center" style="min-width:56px;">
                        <div class="fw-bold" style="color:var(--c-amber);">{{ $tuk->schedules_count }}</div>
                        <div style="font-size:.65rem;color:var(--c-slate);">Jadwal</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">Belum ada data TUK</div>
                @endforelse
            </div>
        </div>

        {{-- Batch Kolektif Terbaru --}}
        @if($latestBatches->count())
        <div class="sec-head mt-2"><i class="bi bi-people-fill text-primary"></i> Batch Kolektif Terbaru</div>
        <div class="panel">
            <div class="panel-body">
                @foreach($latestBatches as $batch)
                <div class="panel-row">
                    <div style="flex:1;min-width:0;">
                        <div class="fw-semibold" style="font-size:.82rem;">{{ $batch->tuk->name ?? '-' }}</div>
                        <div class="text-muted" style="font-size:.7rem;">
                            Batch: <code>{{ $batch->collective_batch_id }}</code>
                        </div>
                    </div>
                    <div class="text-end" style="min-width:80px;">
                        <span class="badge" style="background:#eff6ff;color:var(--c-blue);font-size:.72rem;">
                            {{ $batch->skema->name ?? '-' }}
                        </span>
                        <div class="text-muted mt-1" style="font-size:.68rem;">
                            {{ $batch->total }} peserta
                        </div>
                    </div>
                    <div class="text-muted" style="font-size:.7rem;min-width:70px;text-align:right;">
                        {{ \Carbon\Carbon::parse($batch->created_date)->translatedFormat('d M Y') }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── Asesor & Jadwal ──────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="sec-head"><i class="bi bi-person-badge text-indigo" style="color:var(--c-indigo)"></i> Data Asesor</div>
        <div class="panel">
            <div class="panel-head">
                <span class="ttl">Asesor & Jadwal</span>
                <a href="{{ route('admin.asesors.index') }}" class="text-primary" style="font-size:.75rem;">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="panel-body">
                @forelse($asesorStats as $asesor)
                <div class="panel-row">
                    @if($asesor->foto_path)
                        <img src="{{ $asesor->foto_url }}" class="asesor-av" alt="{{ $asesor->nama }}">
                    @else
                        <div class="asesor-av-fallback">{{ strtoupper(substr($asesor->nama, 0, 1)) }}</div>
                    @endif
                    <div style="flex:1;min-width:0;">
                        <div class="fw-semibold text-truncate" style="font-size:.84rem;">{{ $asesor->nama }}</div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($asesor->skemas->take(3) as $skema)
                            <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.65rem;font-weight:500;">
                                {{ Str::limit($skema->name, 20) }}
                            </span>
                            @endforeach
                            @if($asesor->skemas->count() > 3)
                            <span class="badge bg-secondary" style="font-size:.65rem;">+{{ $asesor->skemas->count() - 3 }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-center" style="min-width:50px;">
                        <div class="fw-bold" style="color:var(--c-blue);font-size:1rem;">{{ $asesor->upcoming_schedules_count }}</div>
                        <div style="font-size:.62rem;color:var(--c-slate);">Jadwal<br>Mendatang</div>
                    </div>
                    <div class="text-center" style="min-width:50px;">
                        <div class="fw-bold" style="color:var(--c-slate);font-size:1rem;">{{ $asesor->schedules_count }}</div>
                        <div style="font-size:.62rem;color:var(--c-slate);">Total<br>Jadwal</div>
                    </div>
                    <div>
                        <span class="badge bg-{{ $asesor->status_badge }}" style="font-size:.67rem;">{{ $asesor->status_label }}</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">Belum ada data asesor</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ── Skema Sertifikasi ────────────────────────────────────── --}}
<div class="sec-head"><i class="bi bi-patch-check text-purple" style="color:#a855f7"></i> Skema Sertifikasi</div>
@php $maxAsesiSkema = $skemaStats->max('asesmens_count') ?: 1; @endphp
<div class="panel mb-4">
    <div class="panel-head">
        <span class="ttl">Pendaftar per Skema</span>
        <span class="text-muted" style="font-size:.75rem;">{{ $skemaStats->count() }} skema aktif</span>
    </div>
    <div class="panel-body">
        @forelse($skemaStats as $skema)
        <div class="panel-row">
            <div style="min-width:160px;max-width:200px;">
                <div class="fw-semibold" style="font-size:.82rem;">{{ $skema->name }}</div>
                <div class="d-flex gap-1 mt-1 flex-wrap">
                    <span class="badge bg-{{ $skema->jenis_badge }}" style="font-size:.65rem;">{{ $skema->jenis_label }}</span>
                    @if($skema->fee)
                    <span class="badge" style="background:#f0fdf4;color:#16a34a;font-size:.65rem;">
                        Rp {{ number_format($skema->fee, 0, ',', '.') }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="skema-bar-wrap">
                <div class="skema-bar-track">
                    <div class="skema-bar-fill" style="width: {{ $maxAsesiSkema > 0 ? round(($skema->asesmens_count / $maxAsesiSkema) * 100) : 0 }}%;"></div>
                </div>
            </div>
            <div class="text-center" style="min-width:54px;">
                <div class="fw-bold" style="color:var(--c-blue);">{{ $skema->asesmens_count }}</div>
                <div style="font-size:.62rem;color:var(--c-slate);">Pendaftar</div>
            </div>
            <div class="text-center" style="min-width:54px;">
                <div class="fw-bold" style="color:var(--c-green);">{{ $skema->certified_count }}</div>
                <div style="font-size:.62rem;color:var(--c-slate);">Sertifikat</div>
            </div>
            <div class="text-center" style="min-width:54px;">
                <div class="fw-bold" style="color:var(--c-indigo);">{{ $skema->asesors_count }}</div>
                <div style="font-size:.62rem;color:var(--c-slate);">Asesor</div>
            </div>
        </div>
        @empty
        <div class="text-center py-4 text-muted small">Belum ada skema aktif</div>
        @endforelse
    </div>
</div>

{{-- ── Jadwal Pending Approval ──────────────────────────────── --}}
@if($pendingSchedules->count())
<div class="sec-head"><i class="bi bi-exclamation-circle text-warning"></i> Menunggu Persetujuan Anda</div>
<div class="panel">
    <div class="panel-head">
        <span class="ttl">Jadwal Perlu Disetujui</span>
        <a href="{{ route('direktur.schedules.index') }}" class="text-primary" style="font-size:.75rem;">
            Lihat Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="panel-body">
        @foreach($pendingSchedules as $schedule)
        <div class="panel-row">
            <div class="text-center" style="min-width:44px;">
                <div style="font-size:1.2rem;font-weight:800;color:var(--c-blue);line-height:1;">
                    {{ $schedule->assessment_date->translatedFormat('d') }}
                </div>
                <div style="font-size:.62rem;color:var(--c-slate);text-transform:uppercase;">
                    {{ $schedule->assessment_date->translatedFormat('M Y') }}
                </div>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="fw-semibold" style="font-size:.84rem;">{{ $schedule->tuk->name ?? '-' }}</div>
                <div class="text-muted" style="font-size:.72rem;">
                    <i class="bi bi-patch-check me-1"></i>{{ $schedule->skema->name ?? '-' }}
                    <span class="mx-1">·</span>
                    <i class="bi bi-people me-1"></i>{{ $schedule->asesmens->count() }} peserta
                    @if($schedule->asesor)
                    <span class="mx-1">·</span>
                    <i class="bi bi-person-check me-1"></i>{{ $schedule->asesor->nama }}
                    @else
                    <span class="badge bg-danger ms-1" style="font-size:.62rem;">Belum ada asesor</span>
                    @endif
                </div>
            </div>
            <div>
                <a href="{{ route('direktur.schedules.show', $schedule) }}"
                   class="btn btn-warning btn-sm" style="font-size:.75rem;">
                    <i class="bi bi-eye me-1"></i>Review
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection