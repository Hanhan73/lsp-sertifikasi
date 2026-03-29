@extends('layouts.app')
@section('title', 'Jadwal Asesmen')
@section('page-title', 'Manajemen Jadwal Asesmen')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.section-heading {
    font-size: .72rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: #64748b; margin-bottom: 10px;
}
.info-row { display: flex; gap: 8px; padding: 5px 0; border-bottom: 1px solid #f1f5f9; }
.info-row:last-child { border-bottom: none; }
.info-label { color: #94a3b8; font-size: .8rem; min-width: 110px; flex-shrink: 0; }
.info-value  { font-weight: 600; font-size: .85rem; }

/* ── Stat cards ── */
.stat-card {
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 18px;
    background: #fff;
    transition: box-shadow .2s, border-color .2s;
}
.stat-card:hover { box-shadow: 0 3px 14px rgba(0,0,0,.07); }
.stat-num  { font-size: 1.8rem; font-weight: 800; line-height: 1; }
.stat-lbl  { font-size: .78rem; color: #64748b; margin-top: 2px; }

/* ── Ready-to-schedule asesi cards ── */
.ready-group {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 10px;
}
.ready-group-header {
    background: #f8fafc;
    padding: 8px 14px;
    border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; gap: 10px;
}
.ready-asesi-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 14px;
    border-bottom: 1px solid #f8fafc;
    font-size: .83rem;
}
.ready-asesi-row:last-child { border-bottom: none; }
.ready-asesi-row:hover { background: #f8faff; }

/* ── Schedule table rows ── */
.sched-row { transition: background .1s; cursor: pointer; }
.sched-row:hover { background: #f8faff; }

/* ── Status dot ── */
.status-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
    display: inline-block;
}

/* ── Avatar stack ── */
.avatar-stack { display: flex; }
.avatar-stack .av {
    width: 26px; height: 26px; border-radius: 50%;
    background: #e0e7ff; color: #4f46e5;
    font-size: .65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #fff;
    margin-left: -6px;
    flex-shrink: 0;
}
.avatar-stack .av:first-child { margin-left: 0; }
.avatar-stack .av-more {
    background: #f1f5f9; color: #64748b;
}
</style>
@endpush

@section('content')

{{-- ── Flash messages ── --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible shadow-sm mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible shadow-sm mb-4" role="alert">
    <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Stat cards ─────────────────────────────────────────── --}}
@php
    $totalSchedules  = $schedules->total();
    $totalReady      = $readyToSchedule->flatten()->count();
    $upcomingCount   = $schedules->getCollection()->filter(fn($s) => $s->assessment_date >= today())->count();
    $noAsesorCount   = $schedules->getCollection()->filter(fn($s) => !$s->asesor_id)->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-num text-primary">{{ $totalSchedules }}</div>
            <div class="stat-lbl">Total Jadwal</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-num text-warning">{{ $totalReady }}</div>
            <div class="stat-lbl">Menunggu Dijadwalkan</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-num text-success">{{ $upcomingCount }}</div>
            <div class="stat-lbl">Akan Datang</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-num text-danger">{{ $noAsesorCount }}</div>
            <div class="stat-lbl">Belum Ada Asesor</div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ══════════════════════════════════════════════
         KIRI — Asesi yang siap dijadwalkan
    ══════════════════════════════════════════════ --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:26px;height:26px;background:#fef9c3;">
                    <i class="bi bi-hourglass-split text-warning" style="font-size:.75rem;"></i>
                </div>
                <span class="fw-semibold">Siap Dijadwalkan</span>
                <span class="badge bg-warning text-dark ms-auto">{{ $totalReady }}</span>
            </div>
            <div class="card-body p-0" style="overflow-y:auto; max-height:520px;">

                @if($readyToSchedule->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2 opacity-50"></i>
                    <p class="small mb-0">Semua asesi sudah terjadwal.</p>
                </div>
                @else

                {{-- Group by TUK --}}
                @foreach($readyToSchedule as $tukId => $group)
                @php $tukName = $group->first()->tuk?->name ?? 'TUK Tidak Diketahui'; @endphp
                <div class="ready-group mx-3 mt-3">
                    <div class="ready-group-header">
                        <i class="bi bi-building text-secondary" style="font-size:.8rem;"></i>
                        <span class="small fw-semibold">{{ $tukName }}</span>
                        <span class="badge bg-secondary ms-auto">{{ $group->count() }}</span>
                    </div>
                    @foreach($group as $asesmen)
                    <div class="ready-asesi-row">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:28px;height:28px;background:#e0e7ff;color:#4f46e5;font-size:.65rem;font-weight:700;">
                            {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold text-truncate">{{ $asesmen->full_name }}</div>
                            <div class="text-muted" style="font-size:.72rem;">
                                {{ $asesmen->skema?->name ?? '-' }}
                                @if($asesmen->is_collective)
                                &bull; <span class="badge bg-primary" style="font-size:.6rem;">Kolektif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
                <div class="p-3">
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-warning btn-sm w-100">
                        <i class="bi bi-calendar-plus me-1"></i>Buat Jadwal Sekarang
                    </a>
                </div>

                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         KANAN — Daftar semua jadwal
    ══════════════════════════════════════════════ --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:26px;height:26px;background:#eff6ff;">
                        <i class="bi bi-calendar3 text-primary" style="font-size:.75rem;"></i>
                    </div>
                    <span class="fw-semibold">Semua Jadwal</span>
                </div>

                {{-- Search --}}
                <div class="ms-auto d-flex gap-2 flex-wrap">
                    <div class="input-group input-group-sm" style="max-width:220px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted" style="font-size:.75rem;"></i>
                        </span>
                        <input type="text" class="form-control form-control-sm border-start-0 ps-0"
                               id="search-sched" placeholder="Cari jadwal...">
                    </div>
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Buat Jadwal
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if($schedules->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
                    <p class="mb-2">Belum ada jadwal yang dibuat.</p>
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Buat Jadwal Pertama
                    </a>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="sched-table">
                        <thead class="table-light" style="font-size:.78rem;">
                            <tr>
                                <th class="ps-3">Tanggal & Lokasi</th>
                                <th>Skema / TUK</th>
                                <th class="text-center">Peserta</th>
                                <th>Asesor</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                            @php
                                $isPast     = $schedule->assessment_date < today();
                                $isToday    = $schedule->assessment_date->isToday();
                                $hasAsesor  = !!$schedule->asesor_id;
                                $peserta    = $schedule->asesmens->count();
                            @endphp
                            <tr class="sched-row"
                                data-search="{{ strtolower(($schedule->skema?->name ?? '') . ' ' . ($schedule->tuk?->name ?? '') . ' ' . ($schedule->location ?? '') . ' ' . ($schedule->asesor?->nama ?? '')) }}"
                                onclick="window.location='{{ route('admin.schedules.show', $schedule) }}'">

                                {{-- Tanggal & Lokasi --}}
                                <td class="ps-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded text-center flex-shrink-0 px-2 py-1"
                                             style="background:{{ $isToday ? '#eff6ff' : ($isPast ? '#f8fafc' : '#f0fdf4') }};
                                                    border:1px solid {{ $isToday ? '#bfdbfe' : ($isPast ? '#e2e8f0' : '#bbf7d0') }};
                                                    min-width:46px;">
                                            <div style="font-size:.65rem;color:#64748b;line-height:1;">
                                                {{ $schedule->assessment_date->format('M Y') }}
                                            </div>
                                            <div style="font-size:1.2rem;font-weight:800;line-height:1;color:{{ $isToday ? '#2563eb' : ($isPast ? '#94a3b8' : '#16a34a') }};">
                                                {{ $schedule->assessment_date->format('d') }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">
                                                {{ $schedule->assessment_date->translatedFormat('l') }}
                                                @if($isToday) <span class="badge bg-info ms-1" style="font-size:.6rem;">Hari Ini</span>@endif
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem;">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $schedule->start_time }} – {{ $schedule->end_time }}
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem;">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ Str::limit($schedule->location, 35) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Skema / TUK --}}
                                <td>
                                    <div class="fw-semibold small">{{ Str::limit($schedule->skema?->name ?? '-', 30) }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        <i class="bi bi-building me-1"></i>{{ $schedule->tuk?->name ?? '-' }}
                                    </div>
                                </td>

                                {{-- Peserta --}}
                                <td class="text-center">
                                    <div class="avatar-stack justify-content-center">
                                        @foreach($schedule->asesmens->take(3) as $a)
                                        <div class="av" title="{{ $a->full_name }}">
                                            {{ strtoupper(substr($a->full_name, 0, 1)) }}
                                        </div>
                                        @endforeach
                                        @if($peserta > 3)
                                        <div class="av av-more">+{{ $peserta - 3 }}</div>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1">{{ $peserta }} orang</div>
                                </td>

                                {{-- Asesor --}}
                                <td>
                                    @if($schedule->asesor)
                                    <div class="fw-semibold small">{{ $schedule->asesor->nama }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $schedule->asesor->no_reg_met ?? '-' }}</div>
                                    @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                        <i class="bi bi-exclamation-circle me-1"></i>Belum ada
                                    </span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="text-center">
                                    @if($isToday)
                                    <span class="badge bg-info">Berlangsung</span>
                                    @elseif($isPast)
                                    <span class="badge bg-secondary">Selesai</span>
                                    @else
                                    <span class="badge bg-success">Akan Datang</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="text-end pe-3" onclick="event.stopPropagation()">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.schedules.show', $schedule) }}"
                                           class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger" title="Hapus"
                                                onclick="deleteSchedule({{ $schedule->id }}, {{ $peserta }})">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($schedules->hasPages())
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Menampilkan {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }}
                        dari {{ $schedules->total() }} jadwal
                    </div>
                    {{ $schedules->links('pagination::bootstrap-5') }}
                </div>
                @endif

                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// Search jadwal
document.getElementById('search-sched')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#sched-table tbody tr').forEach(tr => {
        tr.style.display = !q || tr.dataset.search?.includes(q) ? '' : 'none';
    });
});

// Delete schedule
async function deleteSchedule(id, peserta) {
    const result = await Swal.fire({
        title: 'Hapus Jadwal?',
        html: `<p class="text-muted small mb-2">Jadwal ini akan dihapus dan <strong>${peserta} asesi</strong> akan dikembalikan ke status <code>asesmen_started</code>.</p>
               <div class="alert alert-warning py-2 small mb-0">
                   <i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini tidak dapat dibatalkan.
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
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const res  = await fetch(`/admin/schedules/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1800, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush