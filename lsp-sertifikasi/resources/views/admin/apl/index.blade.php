@extends('layouts.app')
@section('title', 'Dokumen Proses Asesmen')
@section('page-title', 'Dokumen Proses Asesmen')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
/* ── Schedule Cards ── */
.schedule-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    transition: box-shadow .2s;
    background: #fff;
    overflow: hidden;
    margin-bottom: 12px;
}
.schedule-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.09); }
.schedule-header {
    padding: 14px 18px 12px;
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);
    cursor: pointer;
    user-select: none;
}
.schedule-body { padding: 0; }

/* ── Status pills ── */
.status-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: .73rem; font-weight: 600;
}
.pill-upcoming { background:#dbeafe; color:#1d4ed8; }
.pill-today    { background:#d1fae5; color:#065f46; }
.pill-past     { background:#f1f5f9; color:#64748b; }

/* ── Toggle arrow ── */
.toggle-arrow { transition: transform .2s; color: #94a3b8; }
.toggle-arrow.open { transform: rotate(90deg); }

/* ── Asesi rows ── */
.asesi-row td { vertical-align: middle; padding: 10px 14px; }
.asesi-row:hover { background: #f8faff; }
.asesi-row { cursor: pointer; }

/* ── Doc badge ── */
.doc-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 20px; font-size: .7rem; font-weight: 600;
    text-decoration: none;
}
.doc-badge:hover { opacity: .85; }

/* ── Needs action pulse ── */
.needs-action { animation: pulse-warn 2s ease-in-out infinite; }
@keyframes pulse-warn {
    0%,100% { box-shadow: 0 0 0 0 rgba(245,158,11,.4); }
    50%      { box-shadow: 0 0 0 6px rgba(245,158,11,0); }
}

/* ── Filter bar ── */
.filter-bar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 18px;
    display: flex; gap: 10px; align-items: center; flex-wrap: wrap;
}

/* ── Stat cards ── */
.mini-stat { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:14px 18px; }
.mini-stat .value { font-size: 1.8rem; font-weight: 700; line-height: 1; }

/* ── Empty state ── */
.empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
.empty-state i { font-size:4rem; opacity:.4; }
</style>
@endpush

@section('content')

{{-- ── Stat row ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="value text-primary">{{ $schedules->where('assessment_date', '>=', today())->count() }}</div>
            <div class="small text-muted mt-1">Jadwal Aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="value text-warning">{{ $pendingApl01Count }}</div>
            <div class="small text-muted mt-1">APL-01 Perlu Verifikasi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="value text-success">{{ $verifiedApl01Count }}</div>
            <div class="small text-muted mt-1">APL-01 Terverifikasi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="value text-secondary">{{ $totalAsesiScheduled }}</div>
            <div class="small text-muted mt-1">Total Asesi Terjadwal</div>
        </div>
    </div>
</div>

{{-- ── Filter bar ── --}}
<div class="filter-bar">
    <i class="bi bi-funnel text-muted"></i>
    <strong class="small me-auto">Filter Jadwal</strong>

    <select id="filter-tuk" class="form-select form-select-sm" style="width:auto;">
        <option value="">Semua TUK</option>
        @foreach($tuks as $tuk)
        <option value="{{ $tuk->id }}">{{ $tuk->name }}</option>
        @endforeach
    </select>

    <select id="filter-status" class="form-select form-select-sm" style="width:auto;">
        <option value="">Semua Status</option>
        <option value="today">Hari Ini</option>
        <option value="upcoming">Akan Datang</option>
        <option value="past">Sudah Lewat</option>
    </select>

    <input type="text" id="filter-search" class="form-control form-control-sm"
           placeholder="Cari nama asesi…" style="width:180px;">
</div>

{{-- ── Schedule list ── --}}
@forelse($schedules as $schedule)
@php
    $isToday    = $schedule->assessment_date->isToday();
    $isPast     = $schedule->assessment_date->isPast() && !$isToday;
    $statusKey  = $isPast ? 'past' : ($isToday ? 'today' : 'upcoming');
    $pillClass  = $isPast ? 'pill-past' : ($isToday ? 'pill-today' : 'pill-upcoming');
    $pillLabel  = $isPast ? 'Sudah Lewat' : ($isToday ? 'Hari Ini' : 'Akan Datang');
    $pillIcon   = $isPast ? 'bi-clock-history' : ($isToday ? 'bi-circle-fill' : 'bi-calendar-event');

    $pendingApl01InSched = $schedule->asesmens->filter(fn($a) => $a->aplsatu?->status === 'submitted')->count();
    $pendingApl02InSched = $schedule->asesmens->filter(fn($a) => $a->apldua?->status === 'submitted')->count();
    $hasUrgent = ($pendingApl01InSched + $pendingApl02InSched) > 0;
@endphp

<div class="schedule-card schedule-item"
     data-tuk="{{ $schedule->tuk_id }}"
     data-status="{{ $statusKey }}">

    {{-- Header ── --}}
    <div class="schedule-header d-flex align-items-center gap-3" onclick="toggleSchedule(this)">
        <i class="bi bi-chevron-right toggle-arrow fs-6"></i>

        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <strong>{{ $schedule->assessment_date->translatedFormat('d F Y') }}</strong>
                <span class="status-pill {{ $pillClass }}">
                    <i class="bi {{ $pillIcon }}" style="font-size:.55rem;"></i>
                    {{ $pillLabel }}
                </span>
                @if($pendingApl01InSched > 0)
                <span class="badge bg-warning text-dark needs-action" style="font-size:.68rem;">
                    <i class="bi bi-file-earmark-check me-1"></i>{{ $pendingApl01InSched }} APL-01 Perlu Verif
                </span>
                @endif
                @if($pendingApl02InSched > 0)
                <span class="badge bg-info needs-action" style="font-size:.68rem;">
                    <i class="bi bi-clipboard-check me-1"></i>{{ $pendingApl02InSched }} APL-02 Perlu Verif
                </span>
                @endif
            </div>
            <div class="d-flex gap-3 mt-1 flex-wrap small text-muted">
                <span><i class="bi bi-building me-1"></i>{{ $schedule->tuk?->name ?? '-' }}</span>
                <span><i class="bi bi-award me-1"></i>{{ $schedule->skema?->name ?? '-' }}</span>
                @if($schedule->asesor)
                <span><i class="bi bi-person-badge me-1"></i>{{ $schedule->asesor->nama }}</span>
                @else
                <span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Asesor belum ditugaskan</span>
                @endif
                @if($schedule->start_time)
                <span><i class="bi bi-clock me-1"></i>{{ $schedule->start_time }}{{ $schedule->end_time ? ' – ' . $schedule->end_time : '' }}</span>
                @endif
                <span><i class="bi bi-people me-1"></i>{{ $schedule->asesmens->count() }} Asesi</span>
            </div>
        </div>
    </div>

    {{-- Body — asesi list ── --}}
    <div class="schedule-body schedule-content" style="display:none;">
        @if($schedule->asesmens->isEmpty())
        <div class="text-center py-4 text-muted small">
            <i class="bi bi-inbox me-1"></i>Belum ada asesi di jadwal ini.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr style="background:#f8fafc; font-size:.78rem; color:#64748b; text-transform:uppercase; letter-spacing:.4px;">
                        <th style="padding:10px 14px; width:30px;">#</th>
                        <th style="padding:10px 14px;">Nama Asesi</th>
                        <th style="padding:10px 14px;">NIK</th>
                        <th style="padding:10px 14px;">Status</th>
                        <th style="padding:10px 14px;" colspan="2">Dokumen APL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule->asesmens as $idx => $asesi)
                    @php
                        $apl01 = $asesi->aplsatu;
                        $apl02 = $asesi->apldua;
                    @endphp
                    <tr class="asesi-row border-bottom"
                        onclick="window.location='{{ route('admin.asesi.show', $asesi) }}'">
                        <td style="padding:10px 14px;" class="text-muted small">{{ $idx + 1 }}</td>
                        <td style="padding:10px 14px;">
                            <div class="fw-semibold">{{ $asesi->full_name }}</div>
                            @if($apl01?->status === 'submitted' || $apl02?->status === 'submitted')
                            <span class="badge bg-warning text-dark mt-1" style="font-size:.62rem;">
                                <i class="bi bi-exclamation-circle me-1"></i>Perlu Tindakan
                            </span>
                            @endif
                        </td>
                        <td style="padding:10px 14px;" class="font-monospace small text-muted">{{ $asesi->nik ?? '-' }}</td>
                        <td style="padding:10px 14px;">
                            <span class="badge bg-{{ $asesi->status_badge }}">{{ $asesi->status_label }}</span>
                        </td>

                        {{-- APL-01 badge ── --}}
                        <td style="padding:10px 8px;" onclick="event.stopPropagation()">
                            @if($apl01)
                            <a href="{{ route('admin.apl01.show', $apl01) }}"
                               class="doc-badge {{ $apl01->status === 'submitted' ? 'bg-warning text-dark' : ($apl01->status === 'verified' ? 'bg-success text-white' : 'bg-secondary text-white') }}">
                                <i class="bi bi-file-earmark-person"></i>
                                APL-01: {{ ucfirst($apl01->status) }}
                            </a>
                            @else
                            <span class="doc-badge bg-light text-muted border">
                                <i class="bi bi-file-earmark-person"></i>APL-01: -
                            </span>
                            @endif
                        </td>

                        {{-- APL-02 badge ── --}}
                        <td style="padding:10px 8px;" onclick="event.stopPropagation()">
                            @if($apl02)
                                @if(in_array($apl02->status, ['verified', 'approved']))
                                <a href="{{ route('admin.apl02.pdf', $apl02) }}?preview=1"
                                   target="_blank"
                                   class="doc-badge bg-success text-white"
                                   title="Lihat PDF APL-02">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                    APL-02: Verified
                                </a>
                                @elseif($apl02->status === 'submitted')
                                <span class="doc-badge bg-info text-white">
                                    <i class="bi bi-clipboard-check"></i>
                                    APL-02: Submitted
                                </span>
                                @else
                                <span class="doc-badge bg-secondary text-white">
                                    <i class="bi bi-clipboard"></i>
                                    APL-02: {{ ucfirst($apl02->status) }}
                                </span>
                                @endif
                            @else
                            <span class="doc-badge bg-light text-muted border">
                                <i class="bi bi-clipboard"></i>APL-02: -
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@empty
<div class="empty-state">
    <i class="bi bi-calendar-x d-block mb-3"></i>
    <h5>Belum Ada Jadwal Asesmen</h5>
    <p class="small">Jadwal yang dibuat oleh TUK akan muncul di sini.</p>
</div>
@endforelse

@endsection

@push('scripts')
<script>
// ── Toggle expand/collapse ──
function toggleSchedule(headerEl) {
    const content = headerEl.closest('.schedule-card').querySelector('.schedule-content');
    const arrow   = headerEl.querySelector('.toggle-arrow');
    const open    = content.style.display !== 'none';
    content.style.display = open ? 'none' : 'block';
    arrow.classList.toggle('open', !open);
}

// ── Filter ──
function applyFilters() {
    const tuk    = document.getElementById('filter-tuk').value;
    const status = document.getElementById('filter-status').value;
    const search = document.getElementById('filter-search').value.toLowerCase();

    document.querySelectorAll('.schedule-item').forEach(card => {
        const okTuk    = !tuk    || card.dataset.tuk === tuk;
        const okStatus = !status || card.dataset.status === status;
        let   okSearch = !search;
        if (search) {
            card.querySelectorAll('tbody tr').forEach(r => {
                if (r.textContent.toLowerCase().includes(search)) okSearch = true;
            });
        }
        card.style.display = (okTuk && okStatus && okSearch) ? '' : 'none';
    });
}
['filter-tuk','filter-status','filter-search'].forEach(id =>
    document.getElementById(id)?.addEventListener(id === 'filter-search' ? 'input' : 'change', applyFilters)
);

// ── Auto-expand cards with urgent items ──
document.querySelectorAll('.schedule-item').forEach(card => {
    if (card.querySelector('.needs-action') || card.dataset.status === 'today') {
        const h = card.querySelector('.schedule-header');
        if (h) toggleSchedule(h);
    }
});
</script>
@endpush