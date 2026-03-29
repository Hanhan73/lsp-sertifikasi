@extends('layouts.app')
@section('title', 'Review Jadwal — ' . $schedule->assessment_date->format('d M Y'))
@section('page-title', 'Review Jadwal Asesmen')
@section('sidebar')
@include('direktur.partials.sidebar')
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

.status-banner {
    border-radius: 10px; padding: 14px 18px;
    display: flex; align-items: flex-start; gap: 14px;
    margin-bottom: 20px;
}
.status-banner.pending  { background: #fefce8; border: 1.5px solid #fde047; }
.status-banner.approved { background: #f0fdf4; border: 1.5px solid #86efac; }
.status-banner.rejected { background: #fef2f2; border: 1.5px solid #fca5a5; }

.checklist-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px; border-radius: 8px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    margin-bottom: 6px; font-size: .85rem;
}
.checklist-item.ok     { background: #f0fdf4; border-color: #86efac; }
.checklist-item.warn   { background: #fef9c3; border-color: #fde047; }
.checklist-item.danger { background: #fef2f2; border-color: #fca5a5; }
</style>
@endpush

@section('content')

{{-- ── Breadcrumb ─────────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('direktur.schedules.index') }}">Approval Jadwal</a></li>
        <li class="breadcrumb-item active">{{ $schedule->assessment_date->format('d M Y') }}</li>
    </ol>
</nav>

@php
    $peserta      = $schedule->asesmens;
    $pesertaCount = $peserta->count();
    $isPending    = $schedule->isPendingApproval();
    $isApproved   = $schedule->isApproved();
    $isRejected   = $schedule->isRejected();

    // Checklist untuk direktur
    $hasAsesor      = !!$schedule->asesor_id;
    $pesertaOk      = $pesertaCount > 0;
    $pesertaWarning = $pesertaCount > 50; // terlalu banyak
@endphp

{{-- ── Status Banner ──────────────────────────────────────── --}}
<div class="status-banner {{ $schedule->approval_status }}">
    @if($isPending)
    <i class="bi bi-hourglass-split text-warning" style="font-size:1.5rem;margin-top:2px;"></i>
    <div>
        <div class="fw-bold">Menunggu Persetujuan Anda</div>
        <div class="small text-muted">Periksa detail jadwal di bawah lalu setujui atau tolak dengan memberikan catatan.</div>
    </div>
    @elseif($isApproved)
    <i class="bi bi-check-circle-fill text-success" style="font-size:1.5rem;margin-top:2px;"></i>
    <div>
        <div class="fw-bold">Jadwal Telah Disetujui</div>
        <div class="small text-muted">
            Disetujui oleh {{ $schedule->approvedBy?->name ?? '-' }} pada {{ $schedule->approved_at?->format('d M Y H:i') }}.
            Nomor SK: <strong class="font-monospace">{{ $schedule->sk_number }}</strong>
        </div>
        @if($schedule->approval_notes)
        <div class="small mt-1 text-muted"><i class="bi bi-sticky me-1"></i>{{ $schedule->approval_notes }}</div>
        @endif
    </div>
    @if($schedule->hasSk())
    <div class="ms-auto">
        <a href="{{ route('direktur.schedules.sk.download', $schedule) }}" class="btn btn-sm btn-success">
            <i class="bi bi-download me-1"></i>Unduh SK
        </a>
        <form action="{{ route('direktur.schedules.sk.regenerate', $schedule) }}" method="POST" class="d-inline ms-1">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-1"></i>Re-generate
            </button>
        </form>
    </div>
    @endif
    @elseif($isRejected)
    <i class="bi bi-x-circle-fill text-danger" style="font-size:1.5rem;margin-top:2px;"></i>
    <div>
        <div class="fw-bold">Jadwal Ditolak</div>
        <div class="small text-danger mt-1">{{ $schedule->approval_notes }}</div>
        <div class="small text-muted mt-1">Ditolak pada {{ $schedule->rejected_at?->format('d M Y H:i') }}</div>
    </div>
    @endif
</div>

<div class="row g-3">

    {{-- ── KIRI: Detail Jadwal ────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Info jadwal --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="section-heading"><i class="bi bi-calendar3 me-1"></i>Info Jadwal</div>
                <div class="info-row"><span class="info-label">Tanggal</span>
                    <span class="info-value">{{ $schedule->assessment_date->translatedFormat('l, d F Y') }}</span></div>
                <div class="info-row"><span class="info-label">Waktu</span>
                    <span class="info-value">{{ $schedule->start_time }} – {{ $schedule->end_time }}</span></div>
                <div class="info-row"><span class="info-label">Lokasi</span>
                    <span class="info-value">{{ $schedule->location }}</span></div>
                <div class="info-row"><span class="info-label">Skema</span>
                    <span class="info-value">{{ $schedule->skema?->name ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">TUK</span>
                    <span class="info-value">{{ $schedule->tuk?->name ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">Total Peserta</span>
                    <span class="info-value">{{ $pesertaCount }} orang</span></div>
                <div class="info-row"><span class="info-label">Dibuat Oleh</span>
                    <span class="info-value">{{ $schedule->creator?->name ?? '-' }}</span></div>
                @if($schedule->notes)
                <div class="info-row"><span class="info-label">Catatan Admin</span>
                    <span class="info-value small">{{ $schedule->notes }}</span></div>
                @endif
            </div>
        </div>

        {{-- Asesor --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="section-heading"><i class="bi bi-person-badge me-1"></i>Asesor</div>
                @if($schedule->asesor)
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#2563eb);
                         display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;flex-shrink:0;">
                        {{ strtoupper(substr($schedule->asesor->nama, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $schedule->asesor->nama }}</div>
                        <div class="text-muted small">{{ $schedule->asesor->no_reg_met ?? 'Tanpa no. registrasi' }}</div>
                        <div class="text-muted small">{{ $schedule->asesor->email ?? '' }}</div>
                    </div>
                </div>
                @else
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-person-dash fs-3 d-block mb-2 opacity-25"></i>
                    <p class="small mb-0">Asesor belum ditugaskan.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Checklist untuk Direktur --}}
        @if($isPending)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="section-heading"><i class="bi bi-clipboard-check me-1"></i>Checklist Review</div>

                <div class="checklist-item {{ $pesertaOk ? 'ok' : 'danger' }}">
                    <i class="bi bi-{{ $pesertaOk ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }}"></i>
                    <span>Peserta: <strong>{{ $pesertaCount }} orang</strong>
                        {{ $pesertaOk ? '✓' : '— tidak ada peserta' }}
                    </span>
                </div>

                <div class="checklist-item {{ $hasAsesor ? 'ok' : 'warn' }}">
                    <i class="bi bi-{{ $hasAsesor ? 'check-circle-fill text-success' : 'exclamation-triangle-fill text-warning' }}"></i>
                    <span>Asesor: {{ $hasAsesor ? $schedule->asesor->nama : 'Belum ditugaskan (opsional)' }}</span>
                </div>

                <div class="checklist-item ok">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span>Semua peserta sudah lulus kriteria (APL-01 verified, APL-02 & FR.AK.01 submitted)</span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-grid gap-2">
            <button class="btn btn-success"
                    onclick="approveSchedule({{ $schedule->id }}, {{ $pesertaCount }}, '{{ addslashes($schedule->skema?->name ?? '') }}')">
                <i class="bi bi-check-lg me-1"></i>Setujui Jadwal
            </button>
            <button class="btn btn-outline-danger"
                    onclick="rejectSchedule({{ $schedule->id }})">
                <i class="bi bi-x-lg me-1"></i>Tolak Jadwal
            </button>
        </div>
        @endif

    </div>

    {{-- ── KANAN: Daftar Peserta ───────────────────────────── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:26px;height:26px;background:#f0fdf4;">
                    <i class="bi bi-people-fill text-success" style="font-size:.75rem;"></i>
                </div>
                <span class="fw-semibold">Daftar Peserta</span>
                <span class="badge bg-success ms-1">{{ $pesertaCount }}</span>
                <div class="ms-auto">
                    <input type="text" class="form-control form-control-sm" id="search-peserta"
                           placeholder="Cari peserta..." style="max-width:200px;">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="peserta-table">
                        <thead class="table-light" style="font-size:.78rem;">
                            <tr>
                                <th class="ps-3" width="40">#</th>
                                <th>Peserta</th>
                                <th class="text-center">APL-01</th>
                                <th class="text-center">APL-02</th>
                                <th class="text-center">FR.AK.01</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peserta as $i => $asesmen)
                            <tr data-search="{{ strtolower($asesmen->full_name . ' ' . ($asesmen->user?->email ?? '')) }}">
                                <td class="ps-3 text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:30px;height:30px;border-radius:50%;background:#e0e7ff;color:#4f46e5;
                                             font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ $asesmen->full_name }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">{{ $asesmen->user?->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($asesmen->aplsatu && $asesmen->aplsatu->status === 'verified')
                                    <span class="badge bg-success">Verified</span>
                                    @elseif($asesmen->aplsatu)
                                    <span class="badge bg-{{ $asesmen->aplsatu->status_badge }}">{{ $asesmen->aplsatu->status_label }}</span>
                                    @else
                                    <span class="text-muted" style="font-size:.75rem;">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->apldua)
                                    <span class="badge bg-{{ $asesmen->apldua->status_badge }}">{{ $asesmen->apldua->status_label }}</span>
                                    @else
                                    <span class="text-muted" style="font-size:.75rem;">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->frak01)
                                    <span class="badge bg-{{ $asesmen->frak01->status_badge }}">{{ $asesmen->frak01->status_label }}</span>
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
    </div>

</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

document.getElementById('search-peserta')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#peserta-table tbody tr').forEach(tr => {
        tr.style.display = !q || tr.dataset.search?.includes(q) ? '' : 'none';
    });
});

async function approveSchedule(id, count, skema) {
    const result = await Swal.fire({
        title: 'Setujui Jadwal?',
        html: `<div class="text-start small">
            <div class="p-3 rounded bg-light mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Skema</span><strong>${skema}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Peserta</span><strong>${count} orang</strong>
                </div>
            </div>
            <div class="mb-2 fw-semibold">Catatan (opsional):</div>
            <textarea id="approval-notes" class="form-control form-control-sm" rows="2"
                      placeholder="Catatan untuk admin..."></textarea>
            <div class="alert alert-info py-2 mt-3 mb-0 small">
                <i class="bi bi-info-circle me-1"></i>
                SK akan otomatis di-generate dan ${count} asesi statusnya berubah ke <strong>Terjadwal</strong>.
            </div>
        </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Ya, Setujui',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#16a34a',
        reverseButtons: true,
        preConfirm: () => document.getElementById('approval-notes')?.value ?? '',
    });

    if (!result.isConfirmed) return;

    const btn = document.querySelector('[onclick*="approveSchedule"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...'; }

    try {
        const res  = await fetch(`/direktur/schedules/${id}/approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ notes: result.value }),
        });
        const data = await res.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Disetujui!', text: data.message, timer: 2500, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Setujui Jadwal'; }
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}

async function rejectSchedule(id) {
    const result = await Swal.fire({
        title: 'Tolak Jadwal?',
        html: `<div class="text-start">
            <p class="text-muted small mb-2">Admin akan memperbaiki jadwal dan mengajukan ulang.</p>
            <label class="form-label small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea id="rejection-notes" class="form-control" rows="3"
                      placeholder="Contoh: Terlalu banyak peserta, asesor bentrok jadwal di tempat lain..."></textarea>
        </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-x-lg me-1"></i>Ya, Tolak',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
        preConfirm: () => {
            const val = document.getElementById('rejection-notes')?.value?.trim() ?? '';
            if (val.length < 10) {
                Swal.showValidationMessage('Alasan penolakan minimal 10 karakter.');
                return false;
            }
            return val;
        },
    });

    if (!result.isConfirmed) return;

    try {
        const res  = await fetch(`/direktur/schedules/${id}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ rejection_notes: result.value }),
        });
        const data = await res.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Ditolak', text: data.message, timer: 2000, showConfirmButton: false });
            window.location.href = '{{ route("direktur.schedules.index") }}';
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush