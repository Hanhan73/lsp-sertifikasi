@extends('layouts.app')

@section('title', 'Status Pembayaran')
@section('page-title', 'Status Pembayaran')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body text-center py-5">
                @if($asesmen->payment && $asesmen->payment->status === 'verified')
                <!-- SUCCESS -->
                <i class="bi bi-check-circle-fill" style="font-size: 5rem; color: #28a745;"></i>
                <h3 class="mt-4 text-success">Pembayaran Berhasil!</h3>
                <p class="text-muted">Pembayaran Anda telah diverifikasi</p>

                <div class="alert alert-success mt-4">
                    <h6 class="mb-2"><strong>Detail Pembayaran:</strong></h6>
                    <div class="row text-start">
                        <div class="col-md-6">
                            <small><strong>Jumlah:</strong></small><br>
                            <p>Rp {{ number_format($asesmen->payment->amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="col-md-6">
                            <small><strong>Tanggal:</strong></small><br>
                            <p>{{ $asesmen->payment->verified_at->format('d F Y H:i') }}</p>
                        </div>
                        @if($asesmen->payment->transaction_id)
                        <div class="col-12">
                            <small><strong>Transaction ID:</strong></small><br>
                            <p class="text-muted">{{ $asesmen->payment->transaction_id }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="{{ route('asesi.payment.invoice') }}" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf"></i> Download Invoice (PDF)
                    </a>
                    <a href="{{ route('asesi.dashboard') }}" class="btn btn-success">
                        <i class="bi bi-house"></i> Ke Dashboard
                    </a>
                </div>

                @elseif($asesmen->payment && $asesmen->payment->status === 'pending')
                <!-- PENDING -->
                <i class="bi bi-hourglass-split" style="font-size: 5rem; color: #ffc107;"></i>
                <h3 class="mt-4 text-warning">Pembayaran Menunggu</h3>
                <p class="text-muted">Transaksi sedang diproses. Mohon tunggu beberapa saat.</p>

                <div class="alert alert-warning mt-4">
                    <strong>Status:</strong> Pending<br>
                    <small>Sistem akan otomatis memperbarui status pembayaran</small>
                </div>

                <button id="check-payment-btn" class="btn btn-warning mt-3">
                    <i class="bi bi-arrow-clockwise"></i> Cek Status Sekarang
                </button>

                @else
                <!-- NOT PAID -->
                <i class="bi bi-x-circle" style="font-size: 5rem; color: #dc3545;"></i>
                <h3 class="mt-4 text-danger">Belum Ada Pembayaran</h3>
                <p class="text-muted">Anda belum melakukan pembayaran</p>

                @if($asesmen->status === 'verified' && !$asesmen->is_collective)
                <a href="{{ route('asesi.payment') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-credit-card"></i> Lakukan Pembayaran
                </a>
                @endif
                @endif
            </div>
        </div>

        <!-- Payment History (for 2 phase) -->
        @if($asesmen->is_collective && $asesmen->payment_phases === 'two_phase')
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <strong>Fase 1 (50%)</strong>
                            </div>
                            <div class="card-body">
                                @php
                                $phase1 = $asesmen->payments()->where('payment_phase', 'phase_1')->first();
                                @endphp
                                @if($phase1 && $phase1->status === 'verified')
                                <p class="mb-1">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <strong>Lunas</strong>
                                </p>
                                <p class="mb-1">
                                    <small>Jumlah:</small><br>
                                    Rp {{ number_format($phase1->amount, 0, ',', '.') }}
                                </p>
                                <p class="mb-0">
                                    <small>Tanggal:</small><br>
                                    {{ $phase1->verified_at->format('d M Y H:i') }}
                                </p>
                                @else
                                <p class="text-muted mb-0">Belum dibayar</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <strong>Fase 2 (50%)</strong>
                            </div>
                            <div class="card-body">
                                @php
                                $phase2 = $asesmen->payments()->where('payment_phase', 'phase_2')->first();
                                @endphp
                                @if($phase2 && $phase2->status === 'verified')
                                <p class="mb-1">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <strong>Lunas</strong>
                                </p>
                                <p class="mb-1">
                                    <small>Jumlah:</small><br>
                                    Rp {{ number_format($phase2->amount, 0, ',', '.') }}
                                </p>
                                <p class="mb-0">
                                    <small>Tanggal:</small><br>
                                    {{ $phase2->verified_at->format('d M Y H:i') }}
                                </p>
                                @else
                                <p class="text-muted mb-0">Belum dibayar</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#check-payment-btn').click(function() {
        $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Mengecek...');

        $.ajax({
            url: '{{ route("payment.check-status", $asesmen) }}',
            method: 'GET',
            success: function(response) {
                if (response.status === 'verified') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil!',
                        text: 'Pembayaran Anda telah diverifikasi',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Status: ' + response.status,
                        text: 'Pembayaran masih dalam proses',
                        confirmButtonText: 'OK'
                    });
                    $('#check-payment-btn').prop('disabled', false).html(
                        '<i class="bi bi-arrow-clockwise"></i> Cek Status Sekarang');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengecek status pembayaran',
                    confirmButtonText: 'OK'
                });
                $('#check-payment-btn').prop('disabled', false).html(
                    '<i class="bi bi-arrow-clockwise"></i> Cek Status Sekarang');
            }
        });
    });
});
</script>
@endpush