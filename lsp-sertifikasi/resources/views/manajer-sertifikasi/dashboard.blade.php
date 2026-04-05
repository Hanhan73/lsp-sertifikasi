@extends('layouts.app')
@section('title', 'Dashboard Manajer Sertifikasi')
@section('breadcrumb', 'Dashboard')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <h4 class="fw-bold mb-1">Dashboard</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
        Selamat datang, <strong>{{ auth()->user()->name }}</strong> —
        {{ now()->translatedFormat('l, d F Y') }}
    </p>
</div>

{{-- ── STAT CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:#eff6ff;">
                    <i class="bi bi-calendar-event text-primary" style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Jadwal Aktif</div>
                    <div class="fw-bold fs-4 lh-1">{{ $totalJadwal }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:#f0fdf4;">
                    <i class="bi bi-check-circle text-success" style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Distribusi Lengkap</div>
                    <div class="fw-bold fs-4 lh-1">{{ $jadwalLengkap }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:#fffbeb;">
                    <i class="bi bi-exclamation-circle text-warning" style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Perlu Soal Teori</div>
                    <div class="fw-bold fs-4 lh-1">{{ $jadwalBelumTeori }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:#f5f3ff;">
                    <i class="bi bi-journal-text" style="font-size:1.3rem;color:#7c3aed;"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Bank Soal PG</div>
                    <div class="fw-bold fs-4 lh-1">{{ $totalBankSoal }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── KIRI: Jadwal Mendatang ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div class="fw-semibold">
                    <i class="bi bi-calendar-week text-primary me-2"></i>Jadwal Mendatang
                </div>
                <a href="{{ route('manajer-sertifikasi.distribusi') }}"
                   class="btn btn-sm btn-outline-primary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @if($jadwalMendatang->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-calendar-check" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
                <p class="fw-semibold mb-0">Tidak ada jadwal mendatang</p>
            </div>
            @else
            <div class="list-group list-group-flush">
                @foreach($jadwalMendatang as $s)
                @php
                    $daysLeft = now()->startOfDay()->diffInDays($s->assessment_date->startOfDay(), false);
                @endphp
                <div class="list-group-item px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        {{-- Tanggal --}}
                        <div class="text-center flex-shrink-0 rounded-3 text-white"
                             style="min-width:50px;padding:8px 6px;
                                    background:{{ $daysLeft === 0 ? '#f59e0b' : ($daysLeft <= 3 ? '#ef4444' : '#3b82f6') }}">
                            <div class="fw-bold" style="font-size:1.3rem;line-height:1;">
                                {{ $s->assessment_date->translatedFormat('d') }}
                            </div>
                            <div style="font-size:.68rem;opacity:.85;text-transform:uppercase;">
                                {{ $s->assessment_date->translatedFormat('M') }}
                            </div>
                        </div>
                        {{-- Info --}}
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="fw-semibold small text-truncate">{{ $s->skema->name }}</div>
                            <div class="text-muted d-flex flex-wrap gap-2 mt-1" style="font-size:.78rem;">
                                <span><i class="bi bi-building me-1"></i>{{ $s->tuk->name ?? '-' }}</span>
                                <span><i class="bi bi-people me-1"></i>{{ $s->asesmens_count }} asesi</span>
                                <span><i class="bi bi-clock me-1"></i>{{ $s->start_time }}</span>
                            </div>
                        </div>
                        {{-- Status + aksi --}}
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if($daysLeft === 0)
                            <span class="badge bg-warning text-dark" style="font-size:.68rem;">Hari ini</span>
                            @elseif($daysLeft > 0)
                            <span class="badge bg-light text-muted border" style="font-size:.68rem;">
                                {{ $daysLeft }}h lagi
                            </span>
                            @endif

                            @if(!$s->distribusiSoalTeori)
                            <span class="badge bg-danger" style="font-size:.68rem;">
                                <i class="bi bi-exclamation me-1"></i>Belum ada soal
                            </span>
                            @else
                            <span class="badge bg-success" style="font-size:.68rem;">
                                <i class="bi bi-check-lg me-1"></i>Siap
                            </span>
                            @endif

                            <a href="{{ route('manajer-sertifikasi.jadwal.show', $s) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── KANAN: Bank Soal per Skema ── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div class="fw-semibold">
                    <i class="bi bi-collection text-primary me-2"></i>Bank Soal
                </div>
                <a href="{{ route('manajer-sertifikasi.bank-soal.index') }}"
                   class="btn btn-sm btn-outline-primary">
                    Kelola <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @if($bankSoalPerSkema->isEmpty())
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-journal-x" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                <p class="small mb-0">Belum ada soal teori di bank soal</p>
            </div>
            @else
            <div class="list-group list-group-flush">
                @foreach($bankSoalPerSkema as $item)
                <div class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold small text-truncate">{{ $item->skema_name }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $item->skema_code }}</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="badge rounded-pill px-3
                            {{ $item->total >= 30 ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $item->total }} soal
                        </span>
                        @if($item->total < 30)
                        <div class="text-danger mt-1" style="font-size:.7rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i>Kurang dari 30
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

@endsection