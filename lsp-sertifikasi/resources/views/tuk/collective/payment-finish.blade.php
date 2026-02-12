@extends('layouts.app')

@section('title', 'Status Pembayaran Kolektif')
@section('page-title', 'Status Pembayaran Kolektif')

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body text-center py-5">
                <div id="loading-status">
                    <i class="bi bi-hourglass-split" style="font-size: 4rem; color: #ffc107;"></i>
                    <h4 class="mt-4">Mengecek Status Pembayaran...</h4>
                    <p class="text-muted">Mohon tunggu sebentar, kami sedang memverifikasi pembayaran Anda</p>
                    <div class="spinner-border text-primary mt-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div id="success-status" style="display: none;">
                    <i class="bi bi-check-circle-fill" style="font-size: 5rem; color: #28a745;"></i>
                    <h3 class="mt-4 text-success">Pembayaran Kolektif Berhasil!</h3>
                    <p class="text-muted">Pembayaran untuk <span id="success-count"></span> peserta telah dikonfirmasi.</p>
                    
                    <div class="alert alert-success mt-4">
                        <strong><i class="bi bi-info-circle"></i> Informasi Batch</strong><br>
                        Batch ID: <strong>{{ $batchId }}</strong><br>
                        Total Peserta: <strong>{{ $asesmens->count() }}</strong><br>
                        Skema: <strong>{{ $asesmens->first()->skema->name ?? '-' }}</strong>
                    </div>

                    <a href="{{ route('tuk.asesi') }}" class="btn btn-success btn-lg mt-3">
                        <i class="bi bi-list-check"></i> Lihat Daftar Asesi
                    </a>
                </div>

                <div id="pending-status" style="display: none;">
                    <i class="bi bi-clock" style="font-size: 5rem; color: #ffc107;"></i>
                    <h3 class="mt-4 text-warning">Pembayaran Menunggu</h3>
                    <p class="text-muted">Transaksi sedang diproses. Jika Anda sudah melakukan pembayaran, tunggu beberapa saat.</p>
                    
                    <div class="alert alert-warning mt-4">
                        <strong>Status:</strong> <span id="pending-status-text">Pending</span><br>
                        <small>Sistem akan otomatis memperbarui status setiap 5 detik</small>
                    </div>

                    <button id="manual-check-btn" class="btn btn-warning btn-lg mt-3">
                        <i class="bi bi-arrow-clockwise"></i> Cek Status Sekarang
                    </button>
                </div>

                <div id="failed-status" style="display: none;">
                    <i class="bi bi-x-circle-fill" style="font-size: 5rem; color: #dc3545;"></i>
                    <h3 class="mt-4 text-danger">Pembayaran Gagal</h3>
                    <p class="text-muted">Transaksi dibatalkan atau gagal diproses.</p>
                    
                    <a href="{{ route('tuk.collective.payment', $batchId) }}" class="btn btn-danger btn-lg mt-3">
                        <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                    </a>
                </div>
            </div>
        </div>

        <!-- Payment Details Card -->
        <div class="card mt-4" id="payment-details-card" style="display: none;">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Detail Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Peserta</th>
                                <th>Status Payment</th>
                                <th>Status Asesmen</th>
                            </tr>
                        </thead>
                        <tbody id="payment-details-tbody">
                            @foreach($asesmens as $index => $asesmen)
                            <tr data-asesmen-id="{{ $asesmen->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $asesmen->full_name }}</td>
                                <td>
                                    <span class="badge bg-{{ $asesmen->payment && $asesmen->payment->status === 'verified' ? 'success' : 'warning' }}">
                                        {{ $asesmen->payment ? ucfirst($asesmen->payment->status) : 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $asesmen->status === 'paid' ? 'success' : 'secondary' }}">
                                        {{ $asesmen->status_label }}
                                    </span>
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

@push('scripts')
<script>
$(document).ready(function() {
    const batchId = '{{ $batchId }}';
    let checkInterval;
    let checkCount = 0;
    const maxChecks = 60; // Max 5 menit (60 * 5 detik)

    // Check payment status
    function checkPaymentStatus(manual = false) {
        if (manual) {
            $('#manual-check-btn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Mengecek...');
        }

        $.ajax({
            url: '/tuk/collective/payment/' + batchId + '/check-status',
            method: 'POST',  // Changed to POST
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Payment status check:', response);

                if (response.success) {
                    if (response.status === 'verified') {
                        // Success!
                        clearInterval(checkInterval);
                        showSuccess(response.updated_count || {{ $asesmens->count() }});
                    } else if (response.status === 'pending' || response.status === 'settlement' || response.status === 'capture') {
                        // Still pending or processing
                        showPending(response.message || response.status);
                        checkCount++;
                        
                        // Stop auto-check after max attempts
                        if (checkCount >= maxChecks) {
                            clearInterval(checkInterval);
                            $('#pending-status .alert-warning small').text('Pembayaran masih pending. Silakan hubungi admin jika ada masalah.');
                        }
                    } else {
                        // Other status
                        $('#loading-status').hide();
                        $('#pending-status').show();
                        $('#pending-status-text').text(response.message || response.status);
                    }
                } else {
                    // If not success but has message
                    if (response.message && response.message.includes('Payment belum dibuat')) {
                        showPending('Menunggu pembayaran...');
                    } else {
                        showFailed();
                    }
                }

                if (manual) {
                    $('#manual-check-btn').prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Cek Status Sekarang');
                }
            },
            error: function(xhr) {
                console.error('Error checking payment status:', xhr);
                
                if (manual) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengecek status pembayaran. Silakan coba lagi.',
                        confirmButtonText: 'OK'
                    });
                    $('#manual-check-btn').prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Cek Status Sekarang');
                }
            }
        });
    }

    function showSuccess(count) {
        $('#loading-status').hide();
        $('#pending-status').hide();
        $('#success-status').show();
        $('#success-count').text(count);
        $('#payment-details-card').show();

        // Show success alert
        Swal.fire({
            icon: 'success',
            title: 'Pembayaran Berhasil!',
            text: count + ' peserta telah dibayarkan.',
            timer: 3000,
            showConfirmButton: false
        });

        // Refresh page after 3 seconds to show updated status
        setTimeout(function() {
            window.location.href = '{{ route("tuk.asesi") }}';
        }, 3000);
    }

    function showPending(status) {
        $('#loading-status').hide();
        $('#pending-status').show();
        $('#pending-status-text').text(status);
        $('#payment-details-card').show();
    }

    function showFailed() {
        clearInterval(checkInterval);
        $('#loading-status').hide();
        $('#failed-status').show();
    }

    // Manual check button
    $('#manual-check-btn').click(function() {
        checkPaymentStatus(true);
    });

    // Initial check after 2 seconds
    setTimeout(function() {
        checkPaymentStatus();
    }, 2000);

    // Auto refresh every 5 seconds
    checkInterval = setInterval(function() {
        if ($('#pending-status').is(':visible') || $('#loading-status').is(':visible')) {
            checkPaymentStatus();
        }
    }, 5000);

    // Clear interval when leaving page
    $(window).on('beforeunload', function() {
        clearInterval(checkInterval);
    });
});
</script>
@endpush