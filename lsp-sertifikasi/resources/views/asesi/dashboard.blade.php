@extends('layouts.app')

@section('title', 'Dashboard Asesi')
@section('page-title', 'Dashboard')

@section('sidebar')
<a href="{{ route('asesi.dashboard') }}" class="nav-link active">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
@if($asesmen && $asesmen->status !== 'registered')
<a href="{{ route('asesi.tracking') }}" class="nav-link">
    <i class="bi bi-geo-alt"></i> Tracking Status
</a>
@endif
@if($asesmen && in_array($asesmen->status, ['scheduled', 'pre_assessment_completed']))
<a href="{{ route('asesi.pre-assessment') }}" class="nav-link">
    <i class="bi bi-file-earmark-text"></i> Pra-Asesmen
</a>
@endif
@if($asesmen && $asesmen->status === 'certified')
<a href="{{ route('asesi.certificate') }}" class="nav-link">
    <i class="bi bi-award"></i> Sertifikat
</a>
@endif
@endsection

@section('content')
<!-- Welcome -->
<div class="row mb-4">
    <div class="col">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h4>Selamat Datang, {{ auth()->user()->name }}!</h4>
                <p class="mb-0">
                    @if(!$asesmen)
                    Silakan daftar sebagai asesi untuk mengikuti program sertifikasi
                    @elseif($asesmen->is_collective)
                    Anda terdaftar dalam program sertifikasi kolektif
                    @else
                    Anda terdaftar dalam program sertifikasi mandiri
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

@if($asesmen)
<!-- Status Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Status Pendaftaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>No. Registrasi</strong></td>
                                <td>: #{{ $asesmen->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Pendaftaran</strong></td>
                                <td>:
                                    @if($asesmen->is_collective)
                                    <span class="badge bg-primary">Kolektif</span>
                                    @else
                                    <span class="badge bg-success">Mandiri</span>
                                    @endif
                                </td>
                            </tr>
                            @if($asesmen->is_collective && $asesmen->collective_batch_id)
                            <tr>
                                <td><strong>Batch ID</strong></td>
                                <td>: {{ $asesmen->collective_batch_id }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>TUK</strong></td>
                                <td>: {{ $asesmen->tuk->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Skema</strong></td>
                                <td>: {{ $asesmen->skema->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Status Saat Ini</strong></td>
                                <td>:
                                    <span class="badge bg-{{ $asesmen->status_badge }} badge-status">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Langkah Berikutnya</strong></td>
                                <td>: {{ $asesmen->next_action }}</td>
                            </tr>
                            @if($asesmen->is_collective)
                            <tr>
                                <td><strong>Skema Pembayaran</strong></td>
                                <td>:
                                    @if($asesmen->payment_phases === 'single')
                                    <span class="badge bg-success">1 Fase</span>
                                    @else
                                    <span class="badge bg-primary">2 Fase</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if($asesmen->training_flag)
                            <tr>
                                <td><strong>Pelatihan</strong></td>
                                <td>:
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-mortarboard-fill"></i> Terdaftar
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- 🔥 PAYMENT PHASE INFO for Collective 2 Fase -->
                @if($asesmen->is_collective && $asesmen->payment_phases === 'two_phase')
                <hr>
                <div class="alert alert-info">
                    <h6 class="mb-2"><i class="bi bi-cash-coin"></i> Informasi Pembayaran 2 Fase</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-2">
                                <div class="card-body py-2">
                                    <small class="text-muted">Fase 1 (50%)</small>
                                    <br>
                                    @php
                                    $phase1Paid = $asesmen->payments()->where('payment_phase',
                                    'phase_1')->where('status', 'verified')->exists();
                                    @endphp
                                    @if($phase1Paid)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Sudah Dibayar
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">Belum Dibayar</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-2">
                                <div class="card-body py-2">
                                    <small class="text-muted">Fase 2 (50%)</small>
                                    <br>
                                    @php
                                    $phase2Paid = $asesmen->payments()->where('payment_phase',
                                    'phase_2')->where('status', 'verified')->exists();
                                    @endphp
                                    @if($phase2Paid)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Sudah Dibayar
                                    </span>
                                    @elseif($phase1Paid && in_array($asesmen->status, ['assessed', 'certified']))
                                    <span class="badge bg-warning">
                                        <i class="bi bi-hourglass-half"></i> Menunggu TUK
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">Belum Waktunya</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-list-task"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                @if($asesmen->status === 'registered')
                <a href="{{ route('asesi.complete-data') }}" class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="bi bi-pencil"></i> Lengkapi Data Pribadi
                </a>
                @endif

                @if($asesmen->status === 'verified' && !$asesmen->is_collective)
                <a href="{{ route('asesi.payment') }}" class="btn btn-success btn-sm w-100 mb-2">
                    <i class="bi bi-credit-card"></i> Lakukan Pembayaran
                </a>
                @endif

                @if($asesmen->status === 'scheduled')
                <a href="{{ route('asesi.pre-assessment') }}" class="btn btn-warning btn-sm w-100 mb-2">
                    <i class="bi bi-file-earmark-text"></i> Isi Pra-Asesmen
                </a>
                @endif

                @if($asesmen->status === 'certified')
                <a href="{{ route('asesi.certificate') }}" class="btn btn-success btn-sm w-100 mb-2">
                    <i class="bi bi-download"></i> Download Sertifikat
                </a>
                @endif

                <a href="{{ route('asesi.tracking') }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-geo-alt"></i> Tracking Detail
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Timeline - UPDATED untuk skip admin verification di mandiri -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline Proses Sertifikasi</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <!-- 1. Registration -->
                    <div class="timeline-item {{ $asesmen->status === 'registered' ? 'active' : 'completed' }}">
                        <div class="timeline-marker">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Pendaftaran</h6>
                            <p class="text-muted mb-0">
                                {{ $asesmen->registration_date ? $asesmen->registration_date->format('d M Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- 2. Data Completion -->
                    <div class="timeline-item {{ $asesmen->status === 'data_completed' ? 'active' : (in_array($asesmen->status, ['verified', 'paid', 'scheduled', 'pre_assessment_completed', 'assessed', 'certified']) ? 'completed' : '') }}">
                        <div class="timeline-marker">
                            <i
                                class="bi {{ in_array($asesmen->status, ['verified', 'paid', 'scheduled', 'pre_assessment_completed', 'assessed', 'certified']) ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Kelengkapan Data</h6>
                            <p class="text-muted mb-0">
                                {{ $asesmen->status !== 'registered' ? 'Data Lengkap' : 'Belum Lengkap' }}
                            </p>
                        </div>
                    </div>

                    <!-- 🔥 3. SKIP ADMIN VERIFICATION untuk MANDIRI -->
                    @if($asesmen->is_collective)
                    <!-- TUK Verification (Collective Only) -->
                    <div class="timeline-item {{ $asesmen->tuk_verified_at ? 'completed' : ($asesmen->status === 'data_completed' ? 'active' : '') }}">
                        <div class="timeline-marker">
                            <i class="bi {{ $asesmen->tuk_verified_at ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Verifikasi TUK</h6>
                            <p class="text-muted mb-0">
                                {{ $asesmen->tuk_verified_at ? $asesmen->tuk_verified_at->format('d M Y') : 'Menunggu' }}
                            </p>
                        </div>
                    </div>

                    <!-- Admin Fee Setup (Collective Only) -->
                    <div
                        class="timeline-item {{ $asesmen->admin_verified_at ? 'completed' : ($asesmen->tuk_verified_at && !$asesmen->admin_verified_at ? 'active' : '') }}">
                        <div class="timeline-marker">
                            <i class="bi {{ $asesmen->admin_verified_at ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Penetapan Biaya (Admin LSP)</h6>
                            <p class="text-muted mb-0">
                                {{ $asesmen->admin_verified_at ? $asesmen->admin_verified_at->format('d M Y') : 'Menunggu' }}
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- 4. Payment -->
                    <div class="timeline-item {{ $asesmen->status === 'verified' ? 'active' : ($asesmen->status === 'paid' || in_array($asesmen->status, ['scheduled', 'pre_assessment_completed', 'assessed', 'certified']) ? 'completed' : '') }}">
                        <div class="timeline-marker">
                            <i
                                class="bi {{ in_array($asesmen->status, ['paid', 'scheduled', 'pre_assessment_completed', 'assessed', 'certified']) ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Pembayaran
                                @if($asesmen->is_collective && $asesmen->payment_phases === 'two_phase')
                                <span class="badge bg-primary badge-sm">2 Fase</span>
                                @endif
                            </h6>
                            <p class="text-muted mb-0">
                                @if($asesmen->payment)
                                {{ $asesmen->payment->verified_at ? $asesmen->payment->verified_at->format('d M Y') :
                                'Menunggu Verifikasi' }}
                                <a href="{{ route('asesi.payment.status') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-file-earmark-pdf"></i> Download Invoice (PDF)
                                </a>
                                @else
                                Belum Bayar
                                @endif
                            </p>
                            @if($asesmen->is_collective && $asesmen->payment_phases === 'two_phase')
                            <small class="text-muted">
                                @php
                                $phase1Paid = $asesmen->payments()->where('payment_phase',
                                'phase_1')->where('status', 'verified')->exists();
                                $phase2Paid = $asesmen->payments()->where('payment_phase',
                                'phase_2')->where('status', 'verified')->exists();
                                @endphp
                                Fase 1:
                                @if($phase1Paid)
                                <i class="bi bi-check-circle text-success"></i>
                                @else
                                <i class="bi bi-circle text-muted"></i>
                                @endif
                                | Fase 2:
                                @if($phase2Paid)
                                <i class="bi bi-check-circle text-success"></i>
                                @else
                                <i class="bi bi-circle text-muted"></i>
                                @endif
                            </small>
                            @endif
                        </div>
                    </div>

                    <!-- 5. Scheduled -->
                    <div
                        class="timeline-item {{ $asesmen->status === 'scheduled' ? 'active' : ($asesmen->status === 'pre_assessment_completed' || in_array($asesmen->status, ['assessed', 'certified']) ? 'completed' : '') }}">
                        <div class="timeline-marker">
                            <i
                                class="bi {{ in_array($asesmen->status, ['pre_assessment_completed', 'assessed', 'certified']) ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Penjadwalan Asesmen</h6>
                            <p class="text-muted mb-0">
                                @if($asesmen->schedule)
                                {{ $asesmen->schedule->assessment_date->format('d M Y') }}
                                @else
                                Belum Dijadwalkan
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- 6. Pre-Assessment -->
                    <div
                        class="timeline-item {{ $asesmen->status === 'pre_assessment_completed' ? 'active' : (in_array($asesmen->status, ['assessed', 'certified']) ? 'completed' : '') }}">
                        <div class="timeline-marker">
                            <i
                                class="bi {{ in_array($asesmen->status, ['assessed', 'certified']) ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Pra-Asesmen</h6>
                            <p class="text-muted mb-0">
                                {{ $asesmen->status === 'pre_assessment_completed' || in_array($asesmen->status,
                                ['assessed', 'certified']) ? 'Selesai' : 'Belum Dilakukan' }}
                            </p>
                        </div>
                    </div>

                    <!-- 7. Assessment -->
                    <div
                        class="timeline-item {{ $asesmen->status === 'assessed' ? 'active' : ($asesmen->status === 'certified' ? 'completed' : '') }}">
                        <div class="timeline-marker">
                            <i class="bi {{ $asesmen->status === 'certified' ? 'bi-check-circle' : 'bi-circle' }}">
                            </i>
                        </div>
                        <div class="timeline-content">
                            <h6>Asesmen</h6>
                            <p class="text-muted mb-0">
                                {{ $asesmen->assessed_at ? $asesmen->assessed_at->format('d M Y') : 'Belum Dilakukan' }}
                            </p>
                            @if($asesmen->result)
                            <span
                                class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }} badge-sm mt-1">
                                {{ strtoupper($asesmen->result) }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- 8. Certificate -->
                    <div class="timeline-item {{ $asesmen->status === 'certified' ? 'completed' : '' }}">
                        <div class="timeline-marker">
                            <i class="bi {{ $asesmen->status === 'certified' ? 'bi-check-circle' : 'bi-circle' }}">
                            </i>
                        </div>
                        <div class="timeline-content">
                            <h6>Sertifikat</h6>
                            <p class="text-muted mb-0">
                                @if($asesmen->certificate)
                                {{ $asesmen->certificate->issue_date->format('d M Y') }}
                                @else
                                Belum Terbit
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Collective Batch Info -->
@if($batchInfo)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Informasi Batch Kolektif</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Batch ID:</strong> {{ $batchInfo['batch_id'] }}</p>
                        <p><strong>Total Peserta:</strong> {{ $batchInfo['total_members'] }} orang</p>
                        <p><strong>TUK:</strong> {{ $batchInfo['tuk']->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Skema Pembayaran:</strong>
                            @if($batchInfo['payment_phases'] === 'single')
                            <span class="badge bg-success">1 Fase (Full Payment)</span>
                            @else
                            <span class="badge bg-primary">2 Fase (Split Payment)</span>
                            @endif
                        </p>
                        <p><strong>Status Pembayaran:</strong>
                            @if($batchInfo['payment_status'] === 'paid' || $batchInfo['payment_status'] === 'fully_paid')
                            <span class="badge bg-success">Lunas</span>
                            @elseif($batchInfo['payment_status'] === 'phase_1_paid')
                            <span class="badge bg-warning">Fase 1 Lunas</span>
                            @else
                            <span class="badge bg-secondary">Belum Bayar</span>
                            @endif
                        </p>
                        <p><strong>Didaftarkan Oleh:</strong> {{ $batchInfo['registered_by']->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@else
<!-- No Asesmen Yet -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-person-plus" style="font-size: 4rem; color: #0d6efd;"></i>
                <h4 class="mt-3">Mulai Pendaftaran Sertifikasi</h4>
                <p class="text-muted">Anda belum terdaftar sebagai asesi. Silakan lengkapi data pribadi untuk memulai proses sertifikasi.</p>
                <a href="{{ route('asesi.complete-data') }}" class="btn btn-primary btn-lg mt-3">
                    <i class="bi bi-pencil"></i> Daftar Sekarang
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    padding-left: 60px;
    padding-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: 10px;
    top: 0;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #6c757d;
}

.timeline-item.active .timeline-marker {
    background: #ffc107;
    border-color: #ffc107;
    color: #fff;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
    border-color: #28a745;
    color: #fff;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}

.badge-status {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}
</style>
@endpush