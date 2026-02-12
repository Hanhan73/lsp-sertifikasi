@extends('layouts.app')

@section('title', 'Detail Batch Kolektif')
@section('page-title', 'Detail Batch - ' . $batchId)

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="row">
    <!-- Left Column - Batch Info & Members -->
    <div class="col-lg-8">
        <!-- Batch Information -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-collection"></i> Informasi Batch</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Batch ID</strong></td>
                                <td>: {{ $batchId }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah Peserta</strong></td>
                                <td>: {{ $asesmens->count() }} orang</td>
                            </tr>
                            <tr>
                                <td><strong>Skema</strong></td>
                                <td>: {{ $firstAsesmen->skema->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Metode Bayar</strong></td>
                                <td>:
                                    @if($paymentPhases === 'single')
                                    <span class="badge bg-success">
                                        <i class="bi bi-1-circle"></i> 1 Fase (100%)
                                    </span>
                                    @else
                                    <span class="badge bg-primary">
                                        <i class="bi bi-2-circle"></i> 2 Fase (50% + 50%)
                                    </span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Tanggal Dibuat</strong></td>
                                <td>: {{ $firstAsesmen->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status Pembayaran</strong></td>
                                <td>:
                                    @if($paymentStatus === 'paid' || $paymentStatus === 'fully_paid')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Lunas
                                    </span>
                                    @elseif($paymentStatus === 'phase_1_paid')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-hourglass-split"></i> Fase 1 Lunas
                                    </span>
                                    @elseif($paymentStatus === 'pending')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-hourglass-split"></i> Pending
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">Belum Bayar</span>
                                    @endif
                                </td>
                            </tr>
                            @if($paymentPhases === 'two_phase')
                            <tr>
                                <td><strong>Fase 1 (50%)</strong></td>
                                <td>:
                                    @if($phase1Status === 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                    @else
                                    <span class="badge bg-secondary">Belum Bayar</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Fase 2 (50%)</strong></td>
                                <td>:
                                    @if($phase2Status === 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                    @else
                                    <span class="badge bg-secondary">Belum Bayar</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Total Biaya</strong></td>
                                <td>: Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        @if($payments->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Riwayat Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fase</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Transaction ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>
                                    @if($payment->payment_phase === 'full')
                                    <span class="badge bg-success">Full</span>
                                    @elseif($payment->payment_phase === 'phase_1')
                                    <span class="badge bg-primary">Fase 1</span>
                                    @else
                                    <span class="badge bg-info">Fase 2</span>
                                    @endif
                                </td>
                                <td>{{ $payment->verified_at ? $payment->verified_at->format('d M Y H:i') : '-' }}</td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td>{{ ucfirst($payment->method) }}</td>
                                <td>
                                    <span class="badge bg-{{ $payment->status_badge }}">
                                        {{ $payment->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $payment->transaction_id ?? '-' }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Members List -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Daftar Peserta ({{ $asesmens->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesmens as $index => $asesmen)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                                    @if($asesmen->training_flag)
                                    <br>
                                    <small class="badge bg-warning text-dark mt-1">
                                        <i class="bi bi-mortarboard-fill"></i> Pelatihan
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $asesmen->email ?? $asesmen->user->email }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $asesmen->status_badge }}">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('tuk.asesi.show', $asesmen) }}"
                                        class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
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

    <!-- Right Column - Actions -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Actions</h5>
            </div>
            <div class="card-body">
                @if($pendingPhase)
                <div class="alert alert-warning mb-3">
                    <small>
                        <i class="bi bi-exclamation-triangle"></i>
                        @if($pendingPhase === 'full')
                        Menunggu pembayaran penuh (100%)
                        @elseif($pendingPhase === 'phase_1')
                        Menunggu pembayaran Fase 1 (50%)
                        @else
                        Menunggu pembayaran Fase 2 (50%)
                        @endif
                    </small>
                </div>

                <a href="{{ route('tuk.collective.payment', $batchId) }}" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-cash-coin"></i>
                    Bayar
                    {{ $pendingPhase === 'phase_2' ? 'Fase 2' : ($pendingPhase === 'phase_1' ? 'Fase 1' : 'Sekarang') }}
                </a>
                @endif

                @if($hasVerifiedPayment)
                <a href="{{ route('tuk.collective.payment.invoice', $batchId) }}" class="btn btn-success w-100 mb-2">
                    <i class="bi bi-file-earmark-pdf"></i> Download Invoice
                </a>
                @endif

                <a href="{{ route('tuk.asesi') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="card mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Statistik</h6>
            </div>
            <div class="card-body">
                @php
                $statusCounts = $asesmens->groupBy('status')->map->count();
                @endphp

                <div class="mb-2">
                    <small class="text-muted">Status Peserta:</small>
                </div>

                @foreach($statusCounts as $status => $count)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-{{ $asesmens->first()->getStatusBadgeAttribute($status) }}">
                        {{ $asesmens->first()->getStatusLabelAttribute($status) }}
                    </span>
                    <span class="text-muted">{{ $count }} orang</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection