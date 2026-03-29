@extends('layouts.app')
@section('title', 'Dashboard Direktur — Approval Jadwal')
@section('page-title', 'Persetujuan Jadwal Asesmen')
@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@push('styles')
<style>
.stat-card {
    border-radius: 12px; padding: 18px 20px;
    border: 1.5px solid #e2e8f0; background: #fff;
    transition: box-shadow .2s, border-color .2s;
}
.stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
.stat-num  { font-size: 2rem; font-weight: 800; line-height: 1; }
.stat-lbl  { font-size: .8rem; color: #64748b; margin-top: 4px; }

.sched-card {
    border: 1.5px solid #e2e8f0; border-radius: 12px;
    background: #fff; overflow: hidden;
    transition: box-shadow .2s, border-color .2s;
    margin-bottom: 14px;
}
.sched-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); border-color: #bfdbfe; }
.sched-card.rejected { border-color: #fca5a5; }
.sched-card.approved { border-color: #86efac; }

.sched-card-header {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 14px 16px; border-bottom: 1px solid #f1f5f9;
}

.date-chip {
    border-radius: 8px; padding: 6px 10px; min-width: 54px;
    text-align: center; flex-shrink: 0;
    background: #eff6ff; border: 1px solid #bfdbfe;
}
.date-chip .day   { font-size: 1.4rem; font-weight: 800; line-height: 1; color: #2563eb; }
.date-chip .month { font-size: .65rem; font-weight: 600; color: #64748b; text-transform: uppercase; }

.rejection-note {
    background: #fef2f2; border: 1px solid #fca5a5;
    border-radius: 8px; padding: 10px 14px;
    font-size: .83rem; color: #991b1b;
    margin: 10px 16px 0;
}

.nav-tabs .nav-link { font-size: .88rem; font-weight: 500; color: #64748b; border: none; border-bottom: 2px solid transparent; padding: .5rem 1rem; }
.nav-tabs .nav-link.active { color: #2563eb; border-bottom-color: #2563eb; background: none; }
.nav-tabs { border-bottom: 1.5px solid #e2e8f0; }

.empty-approval {
    padding: 48px 20px; text-align: center; color: #94a3b8;
}
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

{{-- ── Stat Cards ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="stat-card">
            <div class="stat-num text-warning">{{ $stats['pending'] }}</div>
            <div class="stat-lbl">Menunggu Persetujuan</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-card">
            <div class="stat-num text-success">{{ $stats['approved'] }}</div>
            <div class="stat-lbl">Disetujui</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-card">
            <div class="stat-num text-danger">{{ $stats['rejected'] }}</div>
            <div class="stat-lbl">Ditolak</div>
        </div>
    </div>
</div>

{{-- ── Tabs ────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body pb-0">
        <ul class="nav nav-tabs" id="tabJadwal">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}"
                   href="{{ route('direktur.schedules.index', ['tab' => 'pending']) }}">
                    Menunggu Persetujuan
                    @if($stats['pending'] > 0)
                    <span class="badge bg-warning text-dark ms-1">{{ $stats['pending'] }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'approved' ? 'active' : '' }}"
                   href="{{ route('direktur.schedules.index', ['tab' => 'approved']) }}">
                    Disetujui
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'rejected' ? 'active' : '' }}"
                   href="{{ route('direktur.schedules.index', ['tab' => 'rejected']) }}">
                    Ditolak
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body pt-4">

        {{-- ── TAB: Pending ── --}}
        @if($tab === 'pending')
        @if($pendingSchedules->isEmpty())
        <div class="empty-approval">
            <i class="bi bi-check2-circle" style="font-size:3rem;opacity:.2;display:block;margin-bottom:12px;"></i>
            <p class="mb-0 fw-semibold">Tidak ada jadwal yang menunggu persetujuan.</p>
            <p class="small">Semua jadwal sudah diproses.</p>
        </div>
        @else
        @foreach($pendingSchedules as $schedule)
        @include('direktur.schedules._card', ['schedule' => $schedule, 'showActions' => true])
        @endforeach
        {{ $pendingSchedules->links('pagination::bootstrap-5') }}
        @endif
        @endif

        {{-- ── TAB: Approved ── --}}
        @if($tab === 'approved')
        @if($approvedSchedules->isEmpty())
        <div class="empty-approval">
            <i class="bi bi-calendar-x" style="font-size:3rem;opacity:.2;display:block;margin-bottom:12px;"></i>
            <p class="mb-0">Belum ada jadwal yang disetujui.</p>
        </div>
        @else
        @foreach($approvedSchedules as $schedule)
        @include('direktur.schedules._card', ['schedule' => $schedule, 'showActions' => false])
        @endforeach
        {{ $approvedSchedules->links('pagination::bootstrap-5') }}
        @endif
        @endif

        {{-- ── TAB: Rejected ── --}}
        @if($tab === 'rejected')
        @if($rejectedSchedules->isEmpty())
        <div class="empty-approval">
            <i class="bi bi-calendar-x" style="font-size:3rem;opacity:.2;display:block;margin-bottom:12px;"></i>
            <p class="mb-0">Tidak ada jadwal yang ditolak.</p>
        </div>
        @else
        @foreach($rejectedSchedules as $schedule)
        @include('direktur.schedules._card', ['schedule' => $schedule, 'showActions' => false])
        @endforeach
        {{ $rejectedSchedules->links('pagination::bootstrap-5') }}
        @endif
        @endif

    </div>
</div>

@endsection