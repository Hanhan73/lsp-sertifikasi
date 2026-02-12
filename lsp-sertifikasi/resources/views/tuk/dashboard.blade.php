@extends('layouts.app')

@section('title', 'Dashboard TUK')
@section('page-title', 'Dashboard TUK - ' . $tuk->name)

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<!-- Pending Payment Alert -->
@if($stats['pending_payment'] > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Perhatian!</strong> Anda memiliki <strong>{{ $stats['pending_payment'] }}</strong> batch kolektif yang
    menunggu pembayaran.
    <a href="{{ route('tuk.asesi') }}#collective-batches" class="alert-link">Lihat detail</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Success Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill"></i>
    {{ session('success') }}
    @if(session('batch_id'))
    <br><small><strong>Batch ID:</strong> {{ session('batch_id') }}</small>
    @if(session('registered_count'))
    <br><small><strong>Jumlah Peserta:</strong> {{ session('registered_count') }} orang</small>
    @endif
    @endif
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #667eea; --bg-color-end: #764ba2;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Total Asesi</p>
                    <h3>{{ $stats['total_asesi'] }}</h3>
                </div>
                <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #f093fb; --bg-color-end: #f5576c;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Perlu Dijadwalkan</p>
                    <h3>{{ $stats['pending_schedule'] }}</h3>
                </div>
                <i class="bi bi-calendar-plus" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #4facfe; --bg-color-end: #00f2fe;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Sudah Terjadwal</p>
                    <h3>{{ $stats['scheduled'] }}</h3>
                </div>
                <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #43e97b; --bg-color-end: #38f9d7;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Selesai</p>
                    <h3>{{ $stats['completed'] }}</h3>
                </div>
                <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Pending Payments Card -->
@if($stats['pending_payment'] > 0)
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="bi bi-credit-card"></i> Pembayaran Kolektif Menunggu
            <span class="badge bg-dark ms-2">{{ $stats['pending_payment'] }}</span>
        </h5>
    </div>
    <div class="card-body">
        <p class="mb-3">
            <i class="bi bi-info-circle"></i>
            Anda memiliki batch pendaftaran kolektif yang sudah siap untuk dibayar. Silakan lakukan pembayaran agar
            proses sertifikasi dapat dilanjutkan.
        </p>
        <a href="{{ route('tuk.asesi') }}#collective-batches" class="btn btn-warning">
            <i class="bi bi-cash-coin"></i> Lihat & Bayar Sekarang
        </a>
    </div>
</div>
@endif

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card hover-shadow">
            <div class="card-body text-center">
                <i class="bi bi-person-plus-fill text-primary" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Pendaftaran Kolektif</h5>
                <p class="text-muted">Daftarkan beberapa asesi sekaligus</p>
                <a href="{{ route('tuk.collective') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-right"></i> Daftar Sekarang
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card hover-shadow">
            <div class="card-body text-center">
                <i class="bi bi-calendar-event-fill text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Penjadwalan</h5>
                <p class="text-muted">Atur jadwal asesmen untuk asesi</p>
                <a href="{{ route('tuk.schedules') }}" class="btn btn-success">
                    <i class="bi bi-arrow-right"></i> Kelola Jadwal
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card hover-shadow">
            <div class="card-body text-center">
                <i class="bi bi-people-fill text-info" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Daftar Asesi</h5>
                <p class="text-muted">Lihat semua asesi Anda</p>
                <a href="{{ route('tuk.asesi') }}" class="btn btn-info">
                    <i class="bi bi-arrow-right"></i> Lihat Semua
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Asesi -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Asesi Terbaru</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No Reg</th>
                        <th>Nama</th>
                        <th>Skema</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_asesmens as $asesmen)
                    <tr>
                        <td><strong>#{{ $asesmen->id }}</strong></td>
                        <td>
                            {{ $asesmen->full_name ?? $asesmen->user->name }}
                            @if($asesmen->is_collective)
                            <br><small class="text-muted">
                                <i class="bi bi-people"></i> {{ $asesmen->collective_batch_id }}
                            </small>
                            @endif
                        </td>
                        <td>{{ $asesmen->skema->name ?? '-' }}</td>
                        <td>
                            @if($asesmen->is_collective)
                            <span class="badge bg-primary">Kolektif</span>
                            @else
                            <span class="badge bg-secondary">Mandiri</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $asesmen->status_badge }} badge-status">
                                {{ $asesmen->status_label }}
                            </span>
                        </td>
                        <td>{{ $asesmen->registration_date->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('tuk.asesi.show', $asesmen) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2">Belum ada asesi terdaftar</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}
</style>
@endpush