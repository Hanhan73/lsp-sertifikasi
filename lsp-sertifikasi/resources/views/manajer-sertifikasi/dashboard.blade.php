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
        Selamat datang, <strong>{{ auth()->user()->name }}</strong>
    </p>
</div>

{{-- ── STAT CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#2563eb,#1e40af)">
            <p>Total Jadwal Aktif</p>
            <h3>{{ $totalJadwal }}</h3>
            <i class="bi bi-calendar-event stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#059669,#065f46)">
            <p>Distribusi Lengkap</p>
            <h3>{{ $jadwalLengkap }}</h3>
            <i class="bi bi-check-circle stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#d97706,#92400e)">
            <p>Belum Ada Soal Teori</p>
            <h3>{{ $jadwalBelumTeori }}</h3>
            <i class="bi bi-exclamation-circle stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#7c3aed,#4c1d95)">
            <p>Total Bank Soal PG</p>
            <h3>{{ $totalBankSoal }}</h3>
            <i class="bi bi-journal-text stat-icon"></i>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── KIRI: Jadwal Mendatang ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <div class="fw-semibold">
                    <i class="bi bi-clock-history text-warning me-2"></i>Jadwal Mendatang
                </div>
                <a href="{{ route('manajer-sertifikasi.distribusi') }}"
                   class="btn btn-sm btn-outline-primary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @if($jadwalMendatang->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-calendar-check" style="font-size:2rem;opacity:.3;"></i>
                <p class="mt-2 mb-0 small">Tidak ada jadwal mendatang</p>
            </div>
            @else
            <div class="list-group list-group-flush">
                @foreach($jadwalMendatang as $s)
                <div class="list-group-item px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        {{-- Tanggal --}}
                        <div class="text-center flex-shrink-0"
                             style="min-width:46px;background:#f1f5f9;border-radius:8px;padding:6px;">
                            <div class="fw-bold text-primary" style="font-size:1.2rem;line-height:1;">
                                {{ $s->assessment_date->format('d') }}
                            </div>
                            <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;">
                                {{ $s->assessment_date->format('M') }}
                            </div>
                        </div>
                        {{-- Info --}}
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $s->skema->name }}</div>
                            <div class="text-muted" style="font-size:.78rem;">
                                <i class="bi bi-building me-1"></i>{{ $s->tuk->name ?? '-' }}
                                &nbsp;·&nbsp;
                                <i class="bi bi-people me-1"></i>{{ $s->asesmens_count }} asesi
                            </div>
                        </div>
                        {{-- Status distribusi --}}
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if(!$s->distribusiSoalTeori)
                            <span class="badge bg-warning text-dark" style="font-size:.68rem;">
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
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <div class="fw-semibold">
                    <i class="bi bi-bar-chart text-primary me-2"></i>Bank Soal per Skema
                </div>
                <a href="{{ route('manajer-sertifikasi.bank-soal.index') }}"
                   class="btn btn-sm btn-outline-primary">
                    Kelola <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @if($bankSoalPerSkema->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-journal-x" style="font-size:2rem;opacity:.3;"></i>
                <p class="mt-2 mb-0 small">Belum ada soal teori di bank soal</p>
            </div>
            @else
            <div class="list-group list-group-flush">
                @foreach($bankSoalPerSkema as $item)
                <div class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $item->skema_name }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $item->skema_code }}</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="badge bg-primary rounded-pill px-3">{{ $item->total }} soal</span>
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

@push('scripts')
<script>
</script>
@endpush