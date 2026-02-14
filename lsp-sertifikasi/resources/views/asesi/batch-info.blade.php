@extends('layouts.app')

@section('title', 'Info Batch Kolektif')
@section('page-title', 'Informasi Batch Kolektif')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <!-- Batch Overview -->
    <div class="col-lg-12 mb-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-people-fill"></i> Batch ID: {{ $asesmen->collective_batch_id }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="180"><strong>TUK Pendaftar:</strong></td>
                                <td>{{ $asesmen->tuk->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Skema:</strong></td>
                                <td>{{ $asesmen->skema->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Didaftarkan oleh:</strong></td>
                                <td>{{ $asesmen->registrar->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Pendaftaran:</strong></td>
                                <td>{{ $asesmen->registration_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="180"><strong>Total Peserta:</strong></td>
                                <td><span class="badge bg-info">{{ $stats['total_members'] }} orang</span></td>
                            </tr>
                            <tr>
                                <td><strong>Metode Pembayaran:</strong></td>
                                <td>
                                    @if($asesmen->payment_phases === 'single')
                                        <span class="badge bg-success">1 Fase (Full Payment)</span>
                                    @else
                                        <span class="badge bg-primary">2 Fase (50% + 50%)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status Pembayaran:</strong></td>
                                <td>
                                    @php
                                        $status = $paymentInfo['status'];
                                        $badgeClass = match($status) {
                                            'paid' => 'success',
                                            'phase1_paid' => 'warning',
                                            'pending' => 'secondary',
                                            default => 'secondary'
                                        };
                                        $statusText = match($status) {
                                            'paid' => 'Lunas',
                                            'phase1_paid' => 'Fase 1 Lunas',
                                            'pending' => 'Menunggu Pembayaran',
                                            default => 'Belum Ada Pembayaran'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ $statusText }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Biaya:</strong></td>
                                <td><strong class="text-success">Rp {{ number_format($paymentInfo['total_fee'], 0, ',', '.') }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="col-lg-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-people" style="font-size: 2rem; color: #0dcaf0;"></i>
                        <h3 class="mt-2 mb-0">{{ $stats['total_members'] }}</h3>
                        <small class="text-muted">Total Peserta</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-check" style="font-size: 2rem; color: #0d6efd;"></i>
                        <h3 class="mt-2 mb-0">{{ $stats['data_completed'] }}</h3>
                        <small class="text-muted">Data Lengkap</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check" style="font-size: 2rem; color: #ffc107;"></i>
                        <h3 class="mt-2 mb-0">{{ $stats['scheduled'] }}</h3>
                        <small class="text-muted">Terjadwal</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-award" style="font-size: 2rem; color: #198754;"></i>
                        <h3 class="mt-2 mb-0">{{ $stats['certified'] }}</h3>
                        <small class="text-muted">Tersertifikasi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    @if($paymentInfo['verified_payments']->isNotEmpty())
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-credit-card"></i> Riwayat Pembayaran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Fase</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentInfo['verified_payments'] as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    @if($payment->payment_phase === 'phase1')
                                        <span class="badge bg-warning">Fase 1 (50%)</span>
                                    @elseif($payment->payment_phase === 'phase2')
                                        <span class="badge bg-success">Fase 2 (50%)</span>
                                    @else
                                        <span class="badge bg-info">Full Payment</span>
                                    @endif
                                </td>
                                <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Terverifikasi
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('tuk.collective.payment.invoice', $asesmen->collective_batch_id) }}" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-file-pdf"></i> Invoice
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Batch Members List -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Anggota Batch</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="batch-table">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>NIK</th>
                                <th>Status</th>
                                <th>Jadwal Asesmen</th>
                                <th>Sertifikat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batchMembers as $index => $member)
                            <tr class="{{ $member->id === $asesmen->id ? 'table-primary' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $member->full_name }}
                                    @if($member->id === $asesmen->id)
                                        <span class="badge bg-primary ms-1">Anda</span>
                                    @endif
                                </td>
                                <td>{{ $member->user->email ?? '-' }}</td>
                                <td>{{ $member->nik ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusInfo = match($member->status) {
                                            'registered' => ['badge' => 'secondary', 'icon' => 'person-plus', 'text' => 'Terdaftar'],
                                            'data_completed' => ['badge' => 'info', 'icon' => 'file-earmark-check', 'text' => 'Data Lengkap'],
                                            'verified' => ['badge' => 'primary', 'icon' => 'check-circle', 'text' => 'Terverifikasi'],
                                            'scheduled' => ['badge' => 'warning', 'icon' => 'calendar-check', 'text' => 'Terjadwal'],
                                            'pre_assessment_completed' => ['badge' => 'warning', 'icon' => 'clipboard-check', 'text' => 'Pra-Asesmen Selesai'],
                                            'assessed' => ['badge' => 'info', 'icon' => 'clipboard-data', 'text' => 'Dinilai'],
                                            'certified' => ['badge' => 'success', 'icon' => 'award', 'text' => 'Tersertifikasi'],
                                            default => ['badge' => 'secondary', 'icon' => 'question', 'text' => ucfirst($member->status)]
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusInfo['badge'] }}">
                                        <i class="bi bi-{{ $statusInfo['icon'] }}"></i> {{ $statusInfo['text'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($member->schedule)
                                        <small>
                                            <i class="bi bi-calendar3"></i>
                                            {{ $member->schedule->assessment_date->format('d M Y') }}<br>
                                            <i class="bi bi-clock"></i>
                                            {{ $member->schedule->start_time }} - {{ $member->schedule->end_time }}
                                        </small>
                                    @else
                                        <span class="text-muted">Belum dijadwalkan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($member->certificate)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Tersedia
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
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

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#batch-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
    });
});
</script>
@endpush