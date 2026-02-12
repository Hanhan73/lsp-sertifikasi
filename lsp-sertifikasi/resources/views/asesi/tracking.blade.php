@extends('layouts.app')

@section('title', 'Tracking Status')
@section('page-title', 'Tracking Status Sertifikasi')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Current Status -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Status Saat Ini</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>No. Registrasi:</h6>
                        <p class="lead">#{{ $asesmen->id }}</p>

                        <h6>Status:</h6>
                        <p>
                            <span class="badge bg-{{ $asesmen->status_badge }} badge-lg">
                                {{ $asesmen->status_label }}
                            </span>
                        </p>

                        <h6>Langkah Berikutnya:</h6>
                        <p class="text-muted">{{ $asesmen->next_action }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>TUK:</h6>
                        <p>{{ $asesmen->tuk->name ?? '-' }}</p>

                        <h6>Skema Sertifikasi:</h6>
                        <p>{{ $asesmen->skema->name ?? '-' }}</p>

                        <h6>Jenis Pendaftaran:</h6>
                        <p>
                            @if($asesmen->is_collective)
                            <span class="badge bg-primary">Kolektif</span>
                            @else
                            <span class="badge bg-success">Mandiri</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline Proses</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <!-- 1. Registration -->
                    <div class="timeline-item completed">
                        <div class="timeline-marker">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Pendaftaran</h6>
                            <p class="text-muted mb-1">
                                {{ $asesmen->registration_date->format('d F Y, H:i') }}
                            </p>
                            @if($asesmen->is_collective && $asesmen->registrar)
                            <small class="text-muted">Didaftarkan oleh: {{ $asesmen->registrar->name }}</small>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Data Completion -->
                    <div
                        class="timeline-item {{ $asesmen->status === 'data_completed' ? 'active' : (in_array($asesmen->status, ['verified', 'paid', 'scheduled', 'pre_assessment_completed', 'assessed', 'certified']) ? 'completed' : '') }}">
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

                    <!-- 3. TUK Verification (Collective Only) -->
                    @if($asesmen->is_collective)
                    <div
                        class="timeline-item {{ $asesmen->tuk_verified_at ? 'completed' : ($asesmen->status === 'data_completed' ? 'active' : '') }}">
                        <div class="timeline-marker">
                            <i class="bi {{ $asesmen->tuk_verified_at ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Verifikasi TUK</h6>
                            @if($asesmen->tuk_verified_at)
                            <p class="text-muted mb-1">{{ $asesmen->tuk_verified_at->format('d F Y, H:i') }}</p>
                            @if($asesmen->tukVerifier)
                            <small class="text-muted">Oleh: {{ $asesmen->tukVerifier->name }}</small>
                            @endif
                            @else
                            <p class="text-muted mb-0">Menunggu verifikasi TUK</p>
                            @endif
                        </div>
                    </div>

                    <!-- 4. Admin Fee Setup (Collective Only) -->
                    <div
                        class="timeline-item {{ $asesmen->admin_verified_at ? 'completed' : ($asesmen->tuk_verified_at && !$asesmen->admin_verified_at ? 'active' : '') }}">
                        <div class="timeline-marker">
                            <i class="bi {{ $asesmen->admin_verified_at ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Penetapan Biaya</h6>
                            @if($asesmen->admin_verified_at)
                            <p class="text-muted mb-1">{{ $asesmen->admin_verified_at->format('d F Y, H:i') }}</p>
                            @if($asesmen->fee_amount)
                            <p class="mb-1">
                                <strong>Biaya:</strong> Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}
                            </p>
                            @if($asesmen->payment_phases === 'two_phase')
                            <small class="text-muted">
                                Fase 1: Rp {{ number_format($asesmen->phase_1_amount ?? 0, 0, ',', '.') }} |
                                Fase 2: Rp {{ number_format($asesmen->phase_2_amount ?? 0, 0, ',', '.') }}
                            </small>
                            @endif
                            @endif
                            @else
                            <p class="text-muted mb-0">Menunggu penetapan biaya dari Admin LSP</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- 5. Payment -->
                    <div
                        class="timeline-item {{ $asesmen->status === 'verified' ? 'active' : ($asesmen->status === 'paid' || in_array($asesmen->status, ['scheduled', 'pre_assessment_completed', 'assessed', 'certified']) ? 'completed' : '') }}">
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
                            @if($asesmen->payment)
                            <p class="text-muted mb-1">
                                {{ $asesmen->payment->verified_at ? $asesmen->payment->verified_at->format('d F Y, H:i') : 'Menunggu verifikasi' }}
                            </p>
                            <p class="mb-1">
                                <strong>Jumlah:</strong> Rp {{ number_format($asesmen->payment->amount, 0, ',', '.') }}
                            </p>
                            <p class="mb-0">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $asesmen->payment->status_badge }}">
                                    {{ $asesmen->payment->status_label }}
                                </span>
                            </p>
                            @if($asesmen->is_collective && $asesmen->payment_phases === 'two_phase')
                            <div class="mt-2">
                                @php
                                $phase1Paid = $asesmen->payments()->where('payment_phase', 'phase_1')->where('status',
                                'verified')->exists();
                                $phase2Paid = $asesmen->payments()->where('payment_phase', 'phase_2')->where('status',
                                'verified')->exists();
                                @endphp
                                <small>
                                    Fase 1:
                                    @if($phase1Paid)
                                    <i class="bi bi-check-circle text-success"></i>
                                    @else
                                    <i class="bi bi-circle text-muted"></i>
                                    @endif
                                    |
                                    Fase 2:
                                    @if($phase2Paid)
                                    <i class="bi bi-check-circle text-success"></i>
                                    @else
                                    <i class="bi bi-circle text-muted"></i>
                                    @endif
                                </small>
                            </div>
                            @endif
                            @else
                            <p class="text-muted mb-0">
                                @if($asesmen->is_collective)
                                Menunggu pembayaran oleh TUK
                                @else
                                Belum melakukan pembayaran
                                @endif
                            </p>
                            @endif
                        </div>
                    </div>

                    <!-- 6. Scheduled -->
                    <div
                        class="timeline-item {{ $asesmen->status === 'scheduled' ? 'active' : ($asesmen->status === 'pre_assessment_completed' || in_array($asesmen->status, ['assessed', 'certified']) ? 'completed' : '') }}">
                        <div class="timeline-marker">
                            <i
                                class="bi {{ in_array($asesmen->status, ['pre_assessment_completed', 'assessed', 'certified']) ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Penjadwalan Asesmen</h6>
                            @if($asesmen->schedule)
                            <p class="text-muted mb-1">{{ $asesmen->schedule->assessment_date->format('d F Y') }}</p>
                            <p class="mb-1">
                                <strong>Waktu:</strong> {{ $asesmen->schedule->start_time }} -
                                {{ $asesmen->schedule->end_time }}
                            </p>
                            @if($asesmen->schedule->location)
                            <p class="mb-0">
                                <strong>Lokasi:</strong> {{ $asesmen->schedule->location }}
                            </p>
                            @endif
                            @else
                            <p class="text-muted mb-0">Belum dijadwalkan</p>
                            @endif
                        </div>
                    </div>

                    <!-- 7. Pre-Assessment -->
                    <div
                        class="timeline-item {{ $asesmen->status === 'pre_assessment_completed' ? 'active' : (in_array($asesmen->status, ['assessed', 'certified']) ? 'completed' : '') }}">
                        <div class="timeline-marker">
                            <i
                                class="bi {{ in_array($asesmen->status, ['assessed', 'certified']) ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Pra-Asesmen</h6>
                            <p class="text-muted mb-0">
                                {{ $asesmen->status === 'pre_assessment_completed' || in_array($asesmen->status, ['assessed', 'certified']) ? 'Selesai' : 'Belum dilakukan' }}
                            </p>
                        </div>
                    </div>

                    <!-- 8. Assessment -->
                    <div
                        class="timeline-item {{ $asesmen->status === 'assessed' ? 'active' : ($asesmen->status === 'certified' ? 'completed' : '') }}">
                        <div class="timeline-marker">
                            <i class="bi {{ $asesmen->status === 'certified' ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Asesmen</h6>
                            @if($asesmen->assessed_at)
                            <p class="text-muted mb-1">{{ $asesmen->assessed_at->format('d F Y') }}</p>
                            @if($asesmen->result)
                            <p class="mb-1">
                                <strong>Hasil:</strong>
                                <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">
                                    {{ strtoupper($asesmen->result) }}
                                </span>
                            </p>
                            @endif
                            @if($asesmen->assessor)
                            <small class="text-muted">Asesor: {{ $asesmen->assessor->name }}</small>
                            @endif
                            @else
                            <p class="text-muted mb-0">Belum dilakukan</p>
                            @endif
                        </div>
                    </div>

                    <!-- 9. Certificate -->
                    <div class="timeline-item {{ $asesmen->status === 'certified' ? 'completed' : '' }}">
                        <div class="timeline-marker">
                            <i class="bi {{ $asesmen->status === 'certified' ? 'bi-check-circle' : 'bi-circle' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Sertifikat</h6>
                            @if($asesmen->certificate)
                            <p class="text-muted mb-1">{{ $asesmen->certificate->issue_date->format('d F Y') }}</p>
                            <p class="mb-0">
                                <strong>No. Sertifikat:</strong> {{ $asesmen->certificate->certificate_number }}
                            </p>
                            @else
                            <p class="text-muted mb-0">Belum terbit</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Info (Collective) -->
        @if($batchInfo)
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Informasi Batch Kolektif</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Batch ID:</strong></p>
                        <p class="text-muted">{{ $batchInfo['batch_id'] }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Total Peserta:</strong></p>
                        <p class="text-muted">{{ $batchInfo['members']->count() }} orang</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Skema Pembayaran:</strong></p>
                        <p>
                            @if($batchInfo['payment_phases'] === 'single')
                            <span class="badge bg-success">1 Fase (Full Payment)</span>
                            @else
                            <span class="badge bg-primary">2 Fase (Split Payment)</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Status Pembayaran:</strong></p>
                        <p>
                            @if($batchInfo['payment_status'] === 'paid' || $batchInfo['payment_status'] ===
                            'fully_paid')
                            <span class="badge bg-success">Lunas</span>
                            @elseif($batchInfo['payment_status'] === 'phase_1_paid')
                            <span class="badge bg-warning">Fase 1 Lunas</span>
                            @else
                            <span class="badge bg-secondary">Belum Bayar</span>
                            @endif
                        </p>
                    </div>
                </div>

                <h6 class="mb-2">Daftar Peserta Batch:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batchInfo['members'] as $index => $member)
                            <tr class="{{ $member->id === $asesmen->id ? 'table-primary' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $member->full_name ?? $member->user->name }}
                                    @if($member->id === $asesmen->id)
                                    <span class="badge bg-primary badge-sm">Anda</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $member->status_badge }}">
                                        {{ $member->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Info -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Cepat</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">No. Registrasi</small>
                    <strong>#{{ $asesmen->id }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Nama Lengkap</small>
                    <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Email</small>
                    <strong>{{ $asesmen->email ?? $asesmen->user->email }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">TUK</small>
                    <strong>{{ $asesmen->tuk->name ?? '-' }}</strong>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block">Skema</small>
                    <strong>{{ $asesmen->skema->name ?? '-' }}</strong>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning"></i> Actions</h6>
            </div>
            <div class="card-body">
                @if($asesmen->status === 'registered')
                <a href="{{ route('asesi.complete-data') }}" class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="bi bi-pencil"></i> Lengkapi Data
                </a>
                @endif

                @if($asesmen->status === 'verified' && !$asesmen->is_collective)
                <a href="{{ route('asesi.payment') }}" class="btn btn-success btn-sm w-100 mb-2">
                    <i class="bi bi-credit-card"></i> Bayar Sekarang
                </a>
                @endif

                @if($asesmen->status === 'scheduled')
                <a href="{{ route('asesi.pre-assessment') }}" class="btn btn-warning btn-sm w-100 mb-2">
                    <i class="bi bi-file-earmark-text"></i> Isi Pra-Asesmen
                </a>
                @endif

                @if($asesmen->status === 'certified')
                <a href="{{ route('asesi.certificate') }}" class="btn btn-success btn-sm w-100 mb-2">
                    <i class="bi bi-award"></i> Lihat Sertifikat
                </a>
                @endif

                <a href="{{ route('asesi.dashboard') }}" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
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

.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}
</style>
@endpush