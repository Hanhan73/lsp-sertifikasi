@extends('layouts.app')
@section('title', 'Manajemen Pra-Asesmen')
@section('page-title', 'Manajemen Pra-Asesmen')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
/* ── Stats ── */
.stat-chip {
    display: flex; align-items: center; gap: 12px;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 14px 18px;
}
.stat-chip .icon-wrap {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
.stat-chip .val { font-size: 1.6rem; font-weight: 700; line-height: 1; }
.stat-chip .lbl { font-size: .78rem; color: #64748b; }

/* ── Batch accordion ── */
.batch-card {
    border: 1px solid #e2e8f0; border-radius: 12px;
    overflow: hidden; margin-bottom: 10px;
    transition: box-shadow .2s;
}
.batch-card:hover { box-shadow: 0 3px 14px rgba(0,0,0,.07); }
.batch-header {
    padding: 13px 18px; cursor: pointer; user-select: none;
    background: #f8fafc; display: flex; align-items: center; gap: 12px;
}
.batch-header:hover { background: #f1f5f9; }
.chevron { transition: transform .2s; color: #94a3b8; }
.chevron.open { transform: rotate(90deg); }

/* ── Asesi row dalam batch ── */
.asesi-row { cursor: pointer; transition: background .15s; }
.asesi-row:hover { background: #f0f7ff !important; }

/* ── Doc badge ── */
.dbadge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 20px; font-size: .68rem;
    font-weight: 600; white-space: nowrap;
}

/* ── Status pill ── */
.spill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: .72rem; font-weight: 600;
}
.spill-started  { background: #dbeafe; color: #1d4ed8; }
.spill-waiting  { background: #fef9c3; color: #854d0e; }
.spill-done     { background: #dcfce7; color: #166534; }
.spill-default  { background: #f1f5f9; color: #64748b; }

/* ── Progress mini ── */
.prog-wrap { display:flex; gap:3px; align-items:center; }
.prog-wrap .dot {
    width:8px; height:8px; border-radius:50%;
    background:#e2e8f0;
}
.prog-wrap .dot.done { background:#22c55e; }
.prog-wrap .dot.partial { background:#f59e0b; }
.prog-wrap .dot.pending { background:#38bdf8; }

/* ── Empty ── */
.empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
</style>
@endpush

@section('content')

{{-- ── Stats ── --}}
<div class="row g-3 mb-4">
    @php
        $allAsesmens = $batches->flatten()->merge($mandiri);
        $statWaiting  = $allAsesmens->where('status','data_completed')->count();
        $statStarted  = $allAsesmens->whereIn('status',['asesmen_started','scheduled','pre_assessment_completed'])->count();
        $statAssessed = $allAsesmens->where('status','assessed')->count();
        $totalBatches = $batches->count();
    @endphp
    <div class="col-6 col-md-3">
        <div class="stat-chip">
            <div class="icon-wrap bg-warning bg-opacity-10">
                <i class="bi bi-hourglass-split text-warning"></i>
            </div>
            <div>
                <div class="val">{{ $statWaiting }}</div>
                <div class="lbl">Menunggu Dimulai</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-chip">
            <div class="icon-wrap bg-primary bg-opacity-10">
                <i class="bi bi-play-circle text-primary"></i>
            </div>
            <div>
                <div class="val">{{ $statStarted }}</div>
                <div class="lbl">Sedang Berjalan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-chip">
            <div class="icon-wrap bg-success bg-opacity-10">
                <i class="bi bi-check2-circle text-success"></i>
            </div>
            <div>
                <div class="val">{{ $statAssessed }}</div>
                <div class="lbl">Sudah Diases</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-chip">
            <div class="icon-wrap bg-info bg-opacity-10">
                <i class="bi bi-people text-info"></i>
            </div>
            <div>
                <div class="val">{{ $totalBatches }}</div>
                <div class="lbl">Total Batch</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Tabs ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-3">
        <ul class="nav nav-tabs card-header-tabs" id="mainTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-kolektif">
                    <i class="bi bi-people me-1"></i> Kolektif
                    <span class="badge bg-primary ms-1">{{ $batches->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-mandiri">
                    <i class="bi bi-person me-1"></i> Mandiri
                    <span class="badge bg-secondary ms-1">{{ $mandiri->count() }}</span>
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-3">
        <div class="tab-content">

            {{-- ════════════════════════════════
                 TAB KOLEKTIF
            ════════════════════════════════ --}}
            <div class="tab-pane fade show active" id="tab-kolektif">
                @if($batches->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-people d-block fs-1 mb-2 opacity-25"></i>
                    <h6>Tidak ada batch kolektif</h6>
                </div>
                @else

                {{-- Filter bar --}}
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <input type="text" id="batch-search" class="form-control form-control-sm"
                        placeholder="Cari nama asesi atau batch ID…" style="max-width:260px;">
                    <select id="batch-status-filter" class="form-select form-select-sm" style="width:auto;">
                        <option value="">Semua Status</option>
                        <option value="data_completed">Menunggu Dimulai</option>
                        <option value="asesmen_started">Asesmen Dimulai</option>
                        <option value="scheduled">Terjadwal</option>
                        <option value="assessed">Sudah Diases</option>
                    </select>
                </div>

                <div id="batch-list">
                @foreach($batches as $batchId => $members)
                @php
                    $first      = $members->first();
                    $total      = $members->count();

                    // Hitung status breakdown per batch
                    $countWaiting  = $members->where('status','data_completed')->count();
                    $countStarted  = $members->whereIn('status',['asesmen_started','scheduled'])->count();
                    $countAssessed = $members->where('status','assessed')->count();

                    // Overall batch status untuk filter
                    $batchStatusKey = $countAssessed === $total ? 'assessed'
                        : ($countWaiting === $total ? 'data_completed'
                        : ($countStarted > 0 ? 'asesmen_started' : 'scheduled'));

                    // Doc progress: berapa asesi sudah submit APL-01
                    $apl01Done = $members->filter(fn($m) => in_array($m->aplsatu?->status, ['submitted','verified','approved']))->count();
                    $apl02Done = $members->filter(fn($m) => in_array($m->apldua?->status, ['submitted','verified','approved']))->count();
                    $ak01Done  = $members->filter(fn($m) => in_array($m->frak01?->status, ['submitted','verified','approved']))->count();

                    // Alert: ada dokumen perlu diverifikasi?
                    $needsAction = $members->filter(fn($m) =>
                        $m->aplsatu?->status === 'submitted' ||
                        $m->apldua?->status  === 'submitted' ||
                        $m->frak04?->status  === 'submitted'
                    )->count();
                @endphp

                <div class="batch-card batch-item"
                    data-batch-id="{{ $batchId }}"
                    data-batch-status="{{ $batchStatusKey }}"
                    data-batch-text="{{ strtolower($batchId . ' ' . $members->pluck('full_name')->join(' ')) }}">

                    {{-- Batch Header --}}
                    <div class="batch-header" onclick="toggleBatch(this)">
                        <i class="bi bi-chevron-right chevron fs-6"></i>

                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold">{{ $batchId }}</span>

                                {{-- Status pill --}}
                                @if($countWaiting === $total)
                                <span class="spill spill-waiting"><i class="bi bi-hourglass-split"></i> Menunggu Dimulai</span>
                                @elseif($countAssessed === $total)
                                <span class="spill spill-done"><i class="bi bi-check2-circle"></i> Semua Sudah Diases</span>
                                @else
                                <span class="spill spill-started"><i class="bi bi-play-circle"></i> Berjalan</span>
                                @endif

                                {{-- Needs action badge --}}
                                @if($needsAction > 0)
                                <span class="badge bg-warning text-dark" style="animation:pulse 2s infinite;">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $needsAction }} perlu verifikasi
                                </span>
                                @endif

                                {{-- Tombol Mulai (hanya jika semua masih data_completed) --}}
                                @if($countWaiting === $total)
                                <span onclick="event.stopPropagation()">
                                    <button class="btn btn-sm btn-primary ms-1"
                                        onclick="confirmStartBatch('{{ $batchId }}', {{ $total }})">
                                        <i class="bi bi-play-circle me-1"></i>Mulai Pra-Asesmen
                                    </button>
                                </span>
                                <span onclick="event.stopPropagation()">
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick="window.location='{{ route('admin.praasesmen.batch.show', $batchId) }}'">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </button>
                                </span>
                                @endif

                            </div>

                            <div class="d-flex gap-3 mt-1 flex-wrap" style="font-size:.78rem; color:#64748b;">
                                <span><i class="bi bi-building me-1"></i>{{ $first->tuk?->name ?? '-' }}</span>
                                <span><i class="bi bi-award me-1"></i>{{ $first->skema?->name ?? '-' }}</span>
                                <span><i class="bi bi-people me-1"></i>{{ $total }} peserta</span>
                                <span><i class="bi bi-calendar me-1"></i>{{ $first->registration_date->format('d M Y') }}</span>

                                {{-- Progress dokumen mini --}}
                                <span class="ms-2">
                                    APL-01: <strong>{{ $apl01Done }}/{{ $total }}</strong>
                                    &bull; APL-02: <strong>{{ $apl02Done }}/{{ $total }}</strong>
                                    &bull; AK.01: <strong>{{ $ak01Done }}/{{ $total }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Batch Body — Tabel Asesi --}}
                    <div class="batch-body" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="font-size:.83rem;">
                                <thead style="background:#f8fafc; color:#64748b; font-size:.72rem; text-transform:uppercase;">
                                    <tr>
                                        <th class="ps-3" width="30">#</th>
                                        <th>Nama Asesi</th>
                                        <th>Status</th>
                                        <th class="text-center">APL-01</th>
                                        <th class="text-center">APL-02</th>
                                        <th class="text-center">AK.01</th>
                                        <th class="text-center">AK.04</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($members as $i => $asesi)
                                    @php
                                        $hasUrgent = $asesi->aplsatu?->status === 'submitted'
                                            || $asesi->apldua?->status === 'submitted'
                                            || $asesi->frak04?->status === 'submitted';
                                    @endphp
                                    <tr class="asesi-row border-bottom {{ $hasUrgent ? 'table-warning' : '' }}"
                                        onclick="window.location='{{ route('admin.asesi.show', $asesi) }}'">
                                        <td class="ps-3 text-muted">{{ $i+1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $asesi->full_name }}</div>
                                            @if($hasUrgent)
                                            <span style="font-size:.65rem;" class="text-warning fw-bold">
                                                <i class="bi bi-exclamation-circle"></i> Perlu tindakan
                                            </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $asesi->status_badge }}">{{ $asesi->status_label }}</span>
                                        </td>

                                        {{-- APL-01 --}}
                                        <td class="text-center" onclick="event.stopPropagation()">
                                            @php $s = $asesi->aplsatu?->status; @endphp
                                            @if($s)
                                            <a href="{{ route('admin.asesi.show', $asesi) }}?tab=apl01"
                                                class="dbadge bg-{{ $asesi->aplsatu->status_badge }} text-white">
                                                {{ $asesi->aplsatu->status_label }}
                                            </a>
                                            @else
                                            <span class="dbadge bg-light text-muted border">-</span>
                                            @endif
                                        </td>

                                        {{-- APL-02 --}}
                                        <td class="text-center" onclick="event.stopPropagation()">
                                            @php $s = $asesi->apldua?->status; @endphp
                                            @if($s)
                                            <a href="{{ route('admin.asesi.show', $asesi) }}?tab=apl02"
                                                class="dbadge bg-{{ $asesi->apldua->status_badge }} text-white">
                                                {{ $asesi->apldua->status_label }}
                                            </a>
                                            @else
                                            <span class="dbadge bg-light text-muted border">-</span>
                                            @endif
                                        </td>

                                        {{-- FR.AK.01 --}}
                                        <td class="text-center" onclick="event.stopPropagation()">
                                            @php $s = $asesi->frak01?->status; @endphp
                                            @if($s)
                                            <a href="{{ route('admin.asesi.show', $asesi) }}?tab=frak01"
                                                class="dbadge bg-{{ $asesi->frak01->status_badge }} text-white">
                                                {{ $asesi->frak01->status_label }}
                                            </a>
                                            @else
                                            <span class="dbadge bg-light text-muted border">-</span>
                                            @endif
                                        </td>

                                        {{-- FR.AK.04 --}}
                                        <td class="text-center" onclick="event.stopPropagation()">
                                            @php $s = $asesi->frak04?->status; @endphp
                                            @if($s === 'submitted')
                                            <a href="{{ route('admin.asesi.show', $asesi) }}?tab=frak04"
                                                class="dbadge bg-warning text-dark">
                                                <i class="bi bi-megaphone"></i> Banding
                                            </a>
                                            @else
                                            <span class="dbadge bg-light text-muted border">-</span>
                                            @endif
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="text-center" onclick="event.stopPropagation()">
                                            <a href="{{ route('admin.asesi.show', $asesi) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer batch: tombol mulai jika ada yang belum dimulai --}}
                        @if($countWaiting > 0 && $countWaiting < $total)
                        <div class="px-3 py-2 bg-light border-top d-flex align-items-center gap-2" style="font-size:.82rem;">
                            <i class="bi bi-info-circle text-muted"></i>
                            <span class="text-muted">{{ $countWaiting }} asesi belum dimulai.</span>
                            <button class="btn btn-sm btn-primary ms-auto"
                                onclick="confirmStartBatch('{{ $batchId }}', {{ $countWaiting }})">
                                <i class="bi bi-play-circle me-1"></i>Mulai yang Belum
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
                </div>{{-- #batch-list --}}
                @endif
            </div>

            {{-- ════════════════════════════════
                 TAB MANDIRI
            ════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-mandiri">
                @if($mandiri->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-person d-block fs-1 mb-2 opacity-25"></i>
                    <h6>Tidak ada asesi mandiri</h6>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:.85rem;">
                        <thead class="table-light" style="font-size:.75rem; text-transform:uppercase; color:#64748b;">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Nama</th>
                                <th>Skema</th>
                                <th>Status</th>
                                <th class="text-center">APL-01</th>
                                <th class="text-center">APL-02</th>
                                <th class="text-center">AK.01</th>
                                <th class="text-center">AK.04</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mandiri as $i => $asesi)
                            @php
                                $hasUrgent = $asesi->aplsatu?->status === 'submitted'
                                    || $asesi->apldua?->status === 'submitted'
                                    || $asesi->frak04?->status === 'submitted';
                            @endphp
                            <tr class="asesi-row {{ $hasUrgent ? 'table-warning' : '' }}"
                                onclick="window.location='{{ route('admin.asesi.show', $asesi) }}'">
                                <td class="ps-3 text-muted">{{ $i+1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $asesi->full_name }}</div>
                                    <div class="text-muted small">{{ $asesi->user->email ?? '-' }}</div>
                                    @if($hasUrgent)
                                    <span style="font-size:.65rem;" class="text-warning fw-bold">
                                        <i class="bi bi-exclamation-circle"></i> Perlu tindakan
                                    </span>
                                    @endif
                                </td>
                                <td class="small">{{ $asesi->skema?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $asesi->status_badge }}">{{ $asesi->status_label }}</span>
                                    @if($asesi->status === 'data_completed')
                                    <br>
                                    <button class="btn btn-xs btn-primary mt-1" style="font-size:.72rem;padding:2px 8px;"
                                        onclick="event.stopPropagation(); confirmStartSingle({{ $asesi->id }}, '{{ addslashes($asesi->full_name) }}')">
                                        <i class="bi bi-play-circle me-1"></i>Mulai
                                    </button>
                                    @endif
                                </td>
                                <td class="text-center" onclick="event.stopPropagation()">
                                    @if($asesi->aplsatu)
                                    <a href="{{ route('admin.asesi.show', $asesi) }}?tab=apl01"
                                        class="dbadge bg-{{ $asesi->aplsatu->status_badge }} text-white">
                                        {{ $asesi->aplsatu->status_label }}
                                    </a>
                                    @else
                                    <span class="dbadge bg-light text-muted border">-</span>
                                    @endif
                                </td>
                                <td class="text-center" onclick="event.stopPropagation()">
                                    @if($asesi->apldua)
                                    <a href="{{ route('admin.asesi.show', $asesi) }}?tab=apl02"
                                        class="dbadge bg-{{ $asesi->apldua->status_badge }} text-white">
                                        {{ $asesi->apldua->status_label }}
                                    </a>
                                    @else
                                    <span class="dbadge bg-light text-muted border">-</span>
                                    @endif
                                </td>
                                <td class="text-center" onclick="event.stopPropagation()">
                                    @if($asesi->frak01)
                                    <a href="{{ route('admin.asesi.show', $asesi) }}?tab=frak01"
                                        class="dbadge bg-{{ $asesi->frak01->status_badge }} text-white">
                                        {{ $asesi->frak01->status_label }}
                                    </a>
                                    @else
                                    <span class="dbadge bg-light text-muted border">-</span>
                                    @endif
                                </td>
                                <td class="text-center" onclick="event.stopPropagation()">
                                    @if($asesi->frak04?->status === 'submitted')
                                    <a href="{{ route('admin.asesi.show', $asesi) }}?tab=frak04"
                                        class="dbadge bg-warning text-dark">
                                        <i class="bi bi-megaphone"></i> Banding
                                    </a>
                                    @else
                                    <span class="dbadge bg-light text-muted border">-</span>
                                    @endif
                                </td>
                                <td class="text-center" onclick="event.stopPropagation()">
                                    <a href="{{ route('admin.asesi.show', $asesi) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- Hidden form untuk start single --}}
<form id="form-start-single" method="POST" style="display:none;">
    @csrf
</form>

@endsection

@push('scripts')
<script>
// ── Toggle batch accordion ──────────────────────────────────
function toggleBatch(headerEl) {
    const body   = headerEl.closest('.batch-card').querySelector('.batch-body');
    const chev   = headerEl.querySelector('.chevron');
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : 'block';
    chev.classList.toggle('open', !isOpen);
}

// ── Auto-buka batch yang perlu tindakan ────────────────────
document.querySelectorAll('.batch-card').forEach(card => {
    const hasWarn = card.querySelector('.table-warning');
    const hasUrgentBadge = card.querySelector('.badge.bg-warning');
    if (hasWarn || hasUrgentBadge) {
        const h = card.querySelector('.batch-header');
        if (h) toggleBatch(h);
    }
});

// ── Filter & search ────────────────────────────────────────
function applyBatchFilter() {
    const search = document.getElementById('batch-search')?.value?.toLowerCase() ?? '';
    const status = document.getElementById('batch-status-filter')?.value ?? '';

    document.querySelectorAll('.batch-item').forEach(card => {
        const okSearch = !search || card.dataset.batchText.includes(search);
        const okStatus = !status || card.dataset.batchStatus === status;
        card.style.display = (okSearch && okStatus) ? '' : 'none';
    });
}

document.getElementById('batch-search')
    ?.addEventListener('input', applyBatchFilter);
document.getElementById('batch-status-filter')
    ?.addEventListener('change', applyBatchFilter);

// ── Start batch ────────────────────────────────────────────
function confirmStartBatch(batchId, count) {
    Swal.fire({
        title: 'Mulai Pra-Asesmen Batch?',
        html: `Memulai pra-asesmen untuk <strong>${count} peserta</strong> dalam batch <code>${batchId}</code>.<br>
               <small class="text-muted">Asesi akan dapat mengisi APL-01, APL-02, dan FR.AK.01.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-play-circle me-1"></i> Mulai',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
    }).then(r => {
        if (!r.isConfirmed) return;
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = '{{ route("admin.praasesmen.batch.process") }}';
        f.innerHTML = `@csrf <input type="hidden" name="batch_id" value="${batchId}">`;
        document.body.appendChild(f);
        f.submit();
    });
}

// ── Start single (mandiri) ─────────────────────────────────
function confirmStartSingle(asesmenId, nama) {
    Swal.fire({
        title: 'Mulai Asesmen?',
        html: `Memulai pra-asesmen untuk <strong>${nama}</strong>.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-play-circle me-1"></i> Mulai',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
    }).then(r => {
        if (!r.isConfirmed) return;
        const f = document.getElementById('form-start-single');
        f.action = `{{ url('admin/praasesmen') }}/${asesmenId}`;
        f.submit();
    });
}
</script>

<style>
@keyframes pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(245,158,11,.4); }
    50%      { box-shadow: 0 0 0 6px rgba(245,158,11,0); }
}
</style>
@endpush