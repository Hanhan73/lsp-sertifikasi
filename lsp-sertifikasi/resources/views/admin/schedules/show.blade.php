@extends('layouts.app')
@section('title', 'Detail Jadwal — ' . $schedule->assessment_date->format('d M Y'))
@section('page-title', 'Detail Jadwal Asesmen')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.section-heading {
    font-size: .72rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: #64748b; margin-bottom: 10px;
}
.info-row { display: flex; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f1f5f9; }
.info-row:last-child { border-bottom: none; }
.info-label { color: #94a3b8; font-size: .82rem; min-width: 130px; flex-shrink: 0; }
.info-value  { font-weight: 600; font-size: .88rem; }

/* ── Timeline date badge ── */
.date-badge {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    width: 64px; height: 72px; border-radius: 10px; flex-shrink: 0;
    border: 2px solid #bfdbfe; background: #eff6ff; text-align: center;
}
.date-badge.past    { border-color: #e2e8f0; background: #f8fafc; }
.date-badge.today   { border-color: #38bdf8; background: #f0f9ff; }
.date-badge.future  { border-color: #bbf7d0; background: #f0fdf4; }
.date-badge .day    { font-size: 1.7rem; font-weight: 900; line-height: 1; }
.date-badge .month  { font-size: .65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
.date-badge .year   { font-size: .62rem; color: #94a3b8; }

/* ── Asesi table status row ── */
.doc-dot {
    width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0;
}

/* ── Asesor card ── */
.asesor-card {
    border: 1.5px solid #e2e8f0; border-radius: 10px;
    padding: 14px 16px; background: #fff;
    transition: border-color .2s;
}
.asesor-card.has-asesor { border-color: #bfdbfe; background: #f8fbff; }
.asesor-avatar-lg {
    width: 52px; height: 52px; border-radius: 50%;
    object-fit: cover; border: 2px solid #e0e7ff;
    flex-shrink: 0;
}
.asesor-avatar-placeholder-lg {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #2563eb);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.1rem; font-weight: 700;
    flex-shrink: 0;
}

/* ── Document status mini-badges ── */
.doc-badges { display: flex; gap: 4px; flex-wrap: wrap; }
.doc-badge  {
    font-size: .65rem; padding: 1px 7px;
    border-radius: 20px; font-weight: 600;
    border: 1px solid transparent;
}
</style>
@endpush

@section('content')

{{-- ── Flash ──────────────────────────────────────────────── --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible shadow-sm mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Status Approval Banner ─────────────────────────── --}}
@if($schedule->isPendingApproval())
<div class="alert alert-warning d-flex align-items-center gap-3 shadow-sm mb-4">
    <i class="bi bi-hourglass-split fs-4 flex-shrink-0"></i>
    <div>
        <div class="fw-semibold">Jadwal Menunggu Persetujuan Direktur</div>
        <div class="small">Jadwal ini sudah dibuat dan sedang dalam antrian review Direktur. Status asesi belum berubah ke "Terjadwal" sampai Direktur menyetujui.</div>
    </div>
</div>
@elseif($schedule->isRejected())
<div class="alert alert-danger d-flex align-items-start gap-3 shadow-sm mb-4">
    <i class="bi bi-x-circle-fill fs-4 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        <div class="fw-semibold">Jadwal Ditolak oleh Direktur</div>
        <div class="mt-1"><strong>Alasan:</strong> {{ $schedule->approval_notes }}</div>
        <div class="small text-muted mt-1">Ditolak pada {{ $schedule->rejected_at?->format('d M Y H:i') }}</div>
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
            &nbsp;&bull;&nbsp;
            Disetujui pada {{ $schedule->approved_at?->format('d M Y H:i') }}
        </div>
    </div>
    @if($schedule->hasSk())
    <a href="{{ route('direktur.schedules.sk.download', $schedule) }}" class="btn btn-sm btn-success ms-auto">
        <i class="bi bi-download me-1"></i>Unduh SK
    </a>
    @endif
</div>
@endif

{{-- ── Breadcrumb ──────────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Jadwal Asesmen</a></li>
        <li class="breadcrumb-item active">{{ $schedule->assessment_date->format('d M Y') }}</li>
    </ol>
</nav>

@php
    $isPast    = $schedule->assessment_date->isPast() && !$schedule->assessment_date->isToday();
    $isToday   = $schedule->assessment_date->isToday();
    $dateClass = $isToday ? 'today' : ($isPast ? 'past' : 'future');
    $peserta   = $schedule->asesmens;
    $pesertaCount = $peserta->count();
@endphp

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — Header Info
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Jadwal utama --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex gap-3 align-items-start">
                <div class="date-badge {{ $dateClass }}">
                    <div class="day" style="color:{{ $isToday ? '#0284c7' : ($isPast ? '#94a3b8' : '#16a34a') }}">
                        {{ $schedule->assessment_date->format('d') }}
                    </div>
                    <div class="month" style="color:{{ $isToday ? '#0284c7' : ($isPast ? '#94a3b8' : '#16a34a') }}">
                        {{ $schedule->assessment_date->format('M') }}
                    </div>
                    <div class="year">{{ $schedule->assessment_date->format('Y') }}</div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1">
                        {{ $schedule->assessment_date->translatedFormat('l, d F Y') }}
                        @if($isToday)
                        <span class="badge bg-info ms-1 fs-6">Hari Ini</span>
                        @elseif($isPast)
                        <span class="badge bg-secondary ms-1 fs-6">Selesai</span>
                        @else
                        <span class="badge bg-success ms-1 fs-6">Akan Datang</span>
                        @endif
                    </h5>
                    <div class="text-muted small mb-2">
                        <i class="bi bi-clock me-1"></i>
                        {{ $schedule->start_time }} – {{ $schedule->end_time }}
                        &nbsp;&bull;&nbsp;
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ $schedule->location }}
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.schedules.edit', $schedule) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="deleteSchedule({{ $schedule->id }}, {{ $pesertaCount }})">
                            <i class="bi bi-trash3 me-1"></i>Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info skema & TUK --}}
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
                    <span class="info-value small">{{ $schedule->created_at->format('d M Y H:i') }}</span>
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
                    <button class="btn btn-link btn-sm p-0 text-primary"
                            style="font-size:.72rem;text-transform:none;letter-spacing:0;"
                            onclick="openAsesorModal()">
                        <i class="bi bi-pencil me-1"></i>{{ $schedule->asesor ? 'Ganti' : 'Tugaskan' }}
                    </button>
                </div>

                @if($schedule->asesor)
                <div class="asesor-card has-asesor">
                    <div class="d-flex gap-3 align-items-center">
                        @if($schedule->asesor->foto_url)
                        <img src="{{ $schedule->asesor->foto_url }}" class="asesor-avatar-lg" alt="">
                        @else
                        <div class="asesor-avatar-placeholder-lg">
                            {{ strtoupper(substr($schedule->asesor->nama, 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <div class="fw-bold">{{ $schedule->asesor->nama }}</div>
                            <div class="text-muted small">{{ $schedule->asesor->no_reg_met ?? 'Tanpa no. reg' }}</div>
                            <div class="text-muted small">{{ $schedule->asesor->email ?? '-' }}</div>
                            <button type="button" class="btn btn-danger btn-sm mt-2 py-0 px-2"
                                    style="font-size:.72rem;"
                                    onclick="unassignAsesor({{ $schedule->id }})">
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
     ROW 2 — Daftar Peserta
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center"
             style="width:26px;height:26px;background:#f0fdf4;">
            <i class="bi bi-people-fill text-success" style="font-size:.75rem;"></i>
        </div>
        <span class="fw-semibold">Daftar Peserta Asesmen</span>
        <span class="badge bg-success ms-1">{{ $pesertaCount }}</span>
        <div class="ms-auto">
            <input type="text" class="form-control form-control-sm" id="search-peserta"
                   placeholder="Cari peserta..." style="max-width:200px;">
        </div>
    </div>

    <div class="card-body p-0">
        @if($peserta->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
            <p class="small">Belum ada peserta dalam jadwal ini.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="peserta-table">
                <thead class="table-light" style="font-size:.78rem;">
                    <tr>
                        <th class="ps-3" width="40">#</th>
                        <th>Asesi</th>
                        <th>Status Asesmen</th>
                        <th class="text-center">APL-01</th>
                        <th class="text-center">APL-02</th>
                        <th class="text-center">FR.AK.01</th>
                        <th class="text-center">Hasil</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($peserta as $i => $asesmen)
                    <tr data-search="{{ strtolower($asesmen->full_name . ' ' . ($asesmen->user?->email ?? '')) }}">
                        <td class="ps-3 text-muted small">{{ $i + 1 }}</td>

                        {{-- Asesi --}}
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

                        {{-- Status asesmen --}}
                        <td>
                            <span class="badge bg-{{ $asesmen->status_badge }}">{{ $asesmen->status_label }}</span>
                        </td>

                        {{-- APL-01 --}}
                        <td class="text-center">
                            @php $a1 = $asesmen->aplsatu; @endphp
                            @if($a1)
                            <span class="badge bg-{{ $a1->status_badge }}">{{ $a1->status_label }}</span>
                            @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                            @endif
                        </td>

                        {{-- APL-02 --}}
                        <td class="text-center">
                            @php $a2 = $asesmen->apldua; @endphp
                            @if($a2)
                            <span class="badge bg-{{ $a2->status_badge }}">{{ $a2->status_label }}</span>
                            @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                            @endif
                        </td>

                        {{-- FR.AK.01 --}}
                        <td class="text-center">
                            @php $ak1 = $asesmen->frak01; @endphp
                            @if($ak1)
                            <span class="badge bg-{{ $ak1->status_badge }}">{{ $ak1->status_label }}</span>
                            @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                            @endif
                        </td>

                        {{-- Hasil --}}
                        <td class="text-center">
                            @if($asesmen->result)
                            <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">
                                {{ ucfirst($asesmen->result) }}
                            </span>
                            @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.asesi.show', $asesmen) }}"
                               class="btn btn-sm btn-outline-primary" title="Lihat Detail Asesi">
                                <i class="bi bi-person-lines-fill"></i>
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

{{-- ══════════════════════════════════════════════════════════
     MODAL: Assign Asesor
══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAsesor" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Tugaskan Asesor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0"
                           id="modal-search-asesor" placeholder="Cari asesor...">
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
                <button type="button" class="btn btn-primary" id="btn-assign-asesor" disabled
                        onclick="submitAssignAsesor()">
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
let selectedAsesorId = null;

// ── Search peserta ──────────────────────────────────────────
document.getElementById('search-peserta')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#peserta-table tbody tr').forEach(tr => {
        tr.style.display = !q || tr.dataset.search?.includes(q) ? '' : 'none';
    });
});

// ── Open asesor modal + load list ──────────────────────────
async function openAsesorModal() {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAsesor'));
    modal.show();

    try {
        const res  = await fetch(`/admin/schedules/${SCHEDULE_ID}/available-asesors`, {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();

        if (!data.success) throw new Error(data.message);

        renderAsesorList(data.asesors);
    } catch (e) {
        document.getElementById('asesor-list-modal').innerHTML =
            `<div class="alert alert-danger">Gagal memuat daftar asesor: ${e.message}</div>`;
    }
}

function renderAsesorList(asesors) {
    const container = document.getElementById('asesor-list-modal');

    if (!asesors.length) {
        container.innerHTML = `<div class="text-center py-4 text-muted">
            <i class="bi bi-person-x fs-2 d-block mb-2 opacity-25"></i>
            <p class="small">Tidak ada asesor tersedia untuk jadwal ini.</p>
        </div>`;
        return;
    }

    container.innerHTML = asesors.map(a => `
        <label class="asesor-opt d-flex align-items-center gap-3 p-3 rounded border mb-2"
               style="cursor:pointer; transition:all .15s;"
               data-id="${a.id}"
               data-search="${(a.nama + ' ' + (a.no_reg_met ?? '')).toLowerCase()}"
               onmouseover="this.style.background='#f0f7ff';this.style.borderColor='#93c5fd';"
               onmouseout="if(!this.classList.contains('selected')){this.style.background='';this.style.borderColor='';}"
               onclick="selectAsesor(${a.id}, this)">
            <div style="width:40px;height:40px;border-radius:50%;flex-shrink:0;
                        background:linear-gradient(135deg,#4f46e5,#2563eb);
                        display:flex;align-items:center;justify-content:center;
                        color:#fff;font-weight:700;font-size:.85rem;">
                ${a.nama.charAt(0).toUpperCase()}
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold small">${a.nama}</div>
                <div class="text-muted" style="font-size:.75rem;">
                    ${a.no_reg_met ?? 'Tanpa no. registrasi'}
                    ${a.email ? '&bull; ' + a.email : ''}
                </div>
            </div>
            <i class="bi bi-circle text-muted" id="asesor-icon-${a.id}"></i>
        </label>
    `).join('');
}

function selectAsesor(id, el) {
    document.querySelectorAll('.asesor-opt').forEach(o => {
        o.classList.remove('selected');
        o.style.background = '';
        o.style.borderColor = '';
        const ico = o.querySelector('[id^="asesor-icon-"]');
        if (ico) { ico.className = 'bi bi-circle text-muted'; }
    });
    el.classList.add('selected');
    el.style.background = '#eff6ff';
    el.style.borderColor = '#2563eb';
    const ico = document.getElementById(`asesor-icon-${id}`);
    if (ico) ico.className = 'bi bi-check-circle-fill text-primary';
    selectedAsesorId = id;
    document.getElementById('btn-assign-asesor').disabled = false;
}

// Search in modal
document.getElementById('modal-search-asesor')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.asesor-opt').forEach(opt => {
        opt.style.display = !q || opt.dataset.search?.includes(q) ? '' : 'none';
    });
});

async function submitAssignAsesor() {
    if (!selectedAsesorId) return;

    const btn = document.getElementById('btn-assign-asesor');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menugaskan...';

    try {
        const res  = await fetch(`/admin/schedules/${SCHEDULE_ID}/assign-asesor`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ asesor_id: selectedAsesorId }),
        });
        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalAsesor'))?.hide();
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1600, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-check me-1"></i>Tugaskan';
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-person-check me-1"></i>Tugaskan';
    }
}

// ── Unassign asesor ─────────────────────────────────────────
async function unassignAsesor(scheduleId) {
    const result = await Swal.fire({
        title: 'Lepas Asesor?',
        text: 'Asesor akan dilepas dari jadwal ini.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lepas',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
    });
    if (!result.isConfirmed) return;

    try {
        const res  = await fetch(`/admin/schedules/${scheduleId}/unassign-asesor`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1400, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}

// ── Delete schedule ─────────────────────────────────────────
async function deleteSchedule(id, peserta) {
    const result = await Swal.fire({
        title: 'Hapus Jadwal?',
        html: `<p class="text-muted small mb-2">${peserta} asesi akan dikembalikan ke status <code>asesmen_started</code>.</p>
               <div class="alert alert-warning py-2 small mb-0">
                   <i class="bi bi-exclamation-triangle me-1"></i>Tindakan tidak dapat dibatalkan.
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
    });
    if (!result.isConfirmed) return;

    try {
        const res  = await fetch(`/admin/schedules/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1600, showConfirmButton: false });
            window.location.href = '{{ route("admin.schedules.index") }}';
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush