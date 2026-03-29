@extends('layouts.app')
@section('title', 'Dashboard Asesi')
@section('page-title', 'Dashboard')
@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')

@if(session('verified'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-shield-check me-2"></i>
    <strong>Email berhasil diverifikasi!</strong> Anda sekarang bisa mengakses semua fitur.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Welcome banner --}}
<div class="card border-0 bg-primary text-white mb-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Selamat datang, {{ auth()->user()->name }}!</h5>
                <p class="mb-0 opacity-75 small">
                    @if(!$asesmen) Silakan lengkapi data untuk memulai sertifikasi.
                    @elseif($asesmen->is_collective) Pendaftaran Kolektif
                    @else Pendaftaran Mandiri
                    @endif
                </p>
            </div>
            @if($asesmen)
            <span class="badge bg-white text-primary fs-6 px-3 py-2">
                {{ $asesmen->status_label }}
            </span>
            @endif
        </div>
    </div>
</div>

@if($asesmen)
<div class="row g-4">

    {{-- Kiri: Info + Timeline --}}
    <div class="col-lg-8">

        {{-- Info Pendaftaran --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-info-circle me-2 text-primary"></i>Status Pendaftaran
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" width="130">No. Registrasi</td>
                                <td>: <strong>#{{ $asesmen->id }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis</td>
                                <td>:
                                    @if($asesmen->is_collective)
                                    <span class="badge bg-primary">Kolektif</span>
                                    @else
                                    <span class="badge bg-success">Mandiri</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">TUK</td>
                                <td>: {{ $asesmen->tuk->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Skema</td>
                                <td>: {{ $asesmen->skema->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" width="130">Status</td>
                                <td>:
                                    <span class="badge bg-{{ $asesmen->status_badge }}">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Langkah Berikutnya</td>
                                <td>: <span class="text-primary small">{{ $asesmen->next_action }}</span></td>
                            </tr>
                            @if($asesmen->training_flag)
                            <tr>
                                <td class="text-muted">Pelatihan</td>
                                <td>: <span class="badge bg-warning text-dark">Terdaftar</span></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card border-0 shadow-sm">
            <div
                class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Timeline Proses</span>
                <a href="{{ route('asesi.tracking') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>Detail
                </a>
            </div>
            <div class="card-body">
                <div class="timeline">

                    @php
                    $statusOrder = ['registered', 'data_completed', 'asesmen_started', 'scheduled',
                    'pre_assessment_completed', 'assessed', 'certified'];
                    $currentIndex = array_search($asesmen->status, $statusOrder) ?: 0;

                    $steps = [
                    [
                    'label' => 'Pendaftaran',
                    'status' => 'registered',
                    'detail' => $asesmen->registration_date->format('d M Y'),
                    'icon' => 'bi-person-plus',
                    ],
                    [
                    'label' => 'Kelengkapan Data',
                    'status' => 'data_completed',
                    'detail' => $asesmen->status !== 'registered' ? 'Data telah dilengkapi' : 'Belum dilengkapi',
                    'icon' => 'bi-clipboard-check',
                    ],
                    [
                    'label' => 'Admin Mulai Asesmen',
                    'status' => 'asesmen_started',
                    'detail' => $asesmen->admin_started_at
                    ? $asesmen->admin_started_at->format('d M Y')
                    : 'Menunggu Admin LSP',
                    'icon' => 'bi-play-circle',
                    ],
                    [
                    'label' => 'Pengisian Dokumen',
                    'status' => 'asesmen_started', // aktif saat asesmen_started
                    'detail' => $asesmen->status === 'asesmen_started'
                    ? 'Sedang diisi'
                    : ($currentIndex > 2 ? 'Selesai' : 'Menunggu'),
                    'icon' => 'bi-file-earmark-text',
                    'sub' => true, // sub-step, tidak ada di statusOrder
                    ],
                    [
                    'label' => 'Penjadwalan',
                    'status' => 'scheduled',
                    'detail' => $asesmen->schedule
                    ? $asesmen->schedule->assessment_date->format('d M Y')
                    : 'Belum dijadwalkan',
                    'icon' => 'bi-calendar-event',
                    ],
                    [
                    'label' => 'Asesmen',
                    'status' => 'assessed',
                    'detail' => $asesmen->assessed_at
                    ? $asesmen->assessed_at->format('d M Y')
                    : 'Belum dilakukan',
                    'icon' => 'bi-person-check',
                    ],
                    [
                    'label' => 'Sertifikat',
                    'status' => 'certified',
                    'detail' => $asesmen->certificate
                    ? $asesmen->certificate->issue_date->format('d M Y')
                    : 'Belum terbit',
                    'icon' => 'bi-award',
                    ],
                    ];
                    @endphp

                    @foreach($steps as $step)
                    @php
                    $stepIndex = array_search($step['status'], $statusOrder);
                    $isCurrent = $asesmen->status === $step['status'];
                    $isCompleted = $stepIndex !== false && $currentIndex > $stepIndex;
                    $isSub = $step['sub'] ?? false;
                    @endphp
                    <div
                        class="timeline-item {{ $isCompleted ? 'completed' : ($isCurrent ? 'active' : '') }} {{ $isSub ? 'timeline-sub' : '' }}">
                        <div class="timeline-marker">
                            @if($isCompleted)
                            <i class="bi bi-check-circle-fill"></i>
                            @elseif($isCurrent)
                            <i class="{{ $step['icon'] }}"></i>
                            @else
                            <i class="bi bi-circle"></i>
                            @endif
                        </div>
                        <div class="timeline-content">
                            <div class="fw-semibold small">{{ $step['label'] }}</div>
                            <div class="text-muted" style="font-size:.8rem;">{{ $step['detail'] }}</div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>

    {{-- Kanan: Quick Actions + Jadwal + Batch --}}
    <div class="col-lg-4">

        {{-- Quick Actions --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-lightning-fill me-2 text-warning"></i>Aksi Cepat
            </div>
            <div class="card-body d-grid gap-2">

                @if($asesmen->status === 'registered')
                <a href="{{ route('asesi.complete-data') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Lengkapi Data Pribadi
                </a>
                @endif

                @if($asesmen->status === 'asesmen_started')
                <a href="{{ route('asesi.apl01') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-file-earmark-text me-1"></i>Isi APL-01
                </a>
                <a href="{{ route('asesi.apldua') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-file-earmark-check me-1"></i>Isi APL-02
                </a>
                @endif

                @if($asesmen->status === 'scheduled')
                <a href="{{ route('asesi.schedule') }}" class="btn btn-warning btn-sm text-dark">
                    <i class="bi bi-calendar-event me-1"></i>Lihat Jadwal & Dokumen
                </a>
                @endif

                @if($asesmen->status === 'certified')
                <a href="{{ route('asesi.certificate') }}" class="btn btn-success btn-sm">
                    <i class="bi bi-award me-1"></i>Download Sertifikat
                </a>
                @endif

                <a href="{{ route('asesi.tracking') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-clock-history me-1"></i>Lihat Timeline Lengkap
                </a>
            </div>
        </div>

        {{-- Jadwal (jika sudah ada) --}}
        @if($asesmen->schedule)
        <div class="card border-0 shadow-sm border-start border-4 border-warning mb-3">
            <div class="card-header bg-warning bg-opacity-10 fw-semibold border-bottom">
                <i class="bi bi-calendar-event me-2 text-warning"></i>Jadwal Asesmen
            </div>
            <div class="card-body">
                <div class="text-center mb-2">
                    <div class="display-6 fw-bold text-primary">
                        {{ $asesmen->schedule->assessment_date->format('d') }}
                    </div>
                    <div class="text-muted">{{ $asesmen->schedule->assessment_date->format('F Y') }}</div>
                </div>
                <hr class="my-2">
                <div class="small">
                    <div class="d-flex gap-2 mb-1">
                        <i class="bi bi-clock text-muted"></i>
                        {{ $asesmen->schedule->start_time }} – {{ $asesmen->schedule->end_time }}
                    </div>
                    @if($asesmen->schedule->location)
                    <div class="d-flex gap-2">
                        <i class="bi bi-geo-alt text-muted"></i>
                        {{ $asesmen->schedule->location }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Info Batch (kolektif) --}}
        @if($batchInfo)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-people me-2 text-info"></i>Info Batch
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-3">
                    <tr>
                        <td class="text-muted small">Batch ID</td>
                        <td class="small"><code>{{ $batchInfo['batch_id'] }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted small">Total Peserta</td>
                        <td class="small">{{ $batchInfo['total_members'] }} orang</td>
                    </tr>
                    <tr>
                        <td class="text-muted small">TUK</td>
                        <td class="small">{{ $batchInfo['tuk']->name ?? '-' }}</td>
                    </tr>
                </table>
                <a href="{{ route('asesi.batch-info') }}" class="btn btn-sm btn-outline-info w-100">
                    <i class="bi bi-eye me-1"></i>Lihat Detail Batch
                </a>
            </div>
        </div>
        @endif

    </div>
</div>

@else
{{-- Belum ada asesmen --}}
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-person-plus text-primary" style="font-size:4rem;opacity:.4;"></i>
        <h5 class="mt-3">Mulai Pendaftaran Sertifikasi</h5>
        <p class="text-muted">Anda belum terdaftar. Lengkapi data pribadi untuk memulai proses sertifikasi.</p>
        <a href="{{ route('asesi.complete-data') }}" class="btn btn-primary mt-2">
            <i class="bi bi-pencil me-1"></i>Daftar Sekarang
        </a>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 10px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-left: 52px;
    padding-bottom: 24px;
}

.timeline-sub {
    padding-left: 70px;
}

.timeline-sub .timeline-marker {
    left: 28px;
    width: 18px;
    height: 18px;
    font-size: .7rem;
}

.timeline-marker {
    position: absolute;
    left: 8px;
    top: 0;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .75rem;
    color: #adb5bd;
}

.timeline-item.active .timeline-marker {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.timeline-item.completed .timeline-marker {
    background: #198754;
    border-color: #198754;
    color: #fff;
}

.timeline-content .fw-semibold {
    margin-bottom: 2px;
}
</style>
@endpush