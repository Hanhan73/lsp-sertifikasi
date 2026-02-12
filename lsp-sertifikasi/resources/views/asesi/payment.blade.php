@extends('layouts.app')

@section('title', 'Pembayaran')
@section('page-title', 'Pembayaran Sertifikasi')

@section('sidebar')
<a href="{{ route('asesi.dashboard') }}" class="nav-link">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>
<a href="{{ route('asesi.payment') }}" class="nav-link active">
    <i class="bi bi-credit-card"></i> Pembayaran
</a>
<a href="{{ route('asesi.tracking') }}" class="nav-link">
    <i class="bi bi-list-check"></i> Tracking Status
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Payment Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>No. Registrasi</strong></td>
                                <td>: #{{ $asesmen->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama</strong></td>
                                <td>: {{ $asesmen->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $asesmen->email }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Skema</strong></td>
                                <td>: {{ $asesmen->skema->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>TUK</strong></td>
                                <td>: {{ $asesmen->tuk->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>: <span
                                        class="badge bg-{{ $asesmen->status_badge }}">{{ $asesmen->status_label }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-success text-center">
                            <h4 class="mb-0">
                                <i class="bi bi-cash-coin"></i>
                                Total Biaya Sertifikasi
                            </h4>
                            <h2 class="mt-2 mb-0">
                                Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Already Verified -->
        @if($asesmen->payment && $asesmen->payment->status === 'verified')
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Pembayaran Berhasil Terverifikasi!</strong>
                    <p class="mb-0 mt-2">Pembayaran Anda telah berhasil dan terverifikasi otomatis oleh sistem.</p>
                    @if($asesmen->payment->transaction_id)
                    <small class="d-block mt-2">
                        <strong>Transaction ID:</strong> {{ $asesmen->payment->transaction_id }}<br>
                        <strong>Verified at:</strong> {{ $asesmen->payment->verified_at->format('d/m/Y H:i') }}
                    </small>
                    @endif
                </div>
                <a href="{{ route('asesi.dashboard') }}" class="btn btn-primary w-100">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
        @elseif($asesmen->payment && $asesmen->payment->status === 'pending')
        <!-- Payment Pending -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-clock"></i>
                    <strong>Pembayaran Sedang Diproses</strong>
                    <p class="mb-0 mt-2">Anda memiliki transaksi pembayaran yang sedang menunggu konfirmasi. Silakan
                        selesaikan pembayaran atau cek status terbaru.</p>
                    @if($asesmen->payment->order_id)
                    <small class="d-block mt-2">
                        <strong>Order ID:</strong> {{ $asesmen->payment->order_id }}
                    </small>
                    @endif
                </div>
                <button id="check-status-btn" class="btn btn-info w-100 mb-2">
                    <i class="bi bi-arrow-clockwise"></i> Cek Status Pembayaran
                </button>
                
                @if(!config('midtrans.is_production'))
                <div class="alert alert-info mt-3">
                    <strong>Mode Testing:</strong> Gunakan tombol di bawah untuk simulasi pembayaran berhasil
                </div>
                <button id="test-verify-btn" class="btn btn-warning w-100">
                    <i class="bi bi-lightning-fill"></i> Test: Verifikasi Otomatis (Dev Only)
                </button>
                @endif
            </div>
        </div>
        @else
        <!-- Payment Button -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Metode Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Kami menggunakan <strong>Midtrans</strong> sebagai payment gateway yang aman dan terpercaya.
                    <br><br>
                    <strong>Metode pembayaran yang tersedia:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Transfer Bank (BCA, Mandiri, BNI, BRI, Permata, dll)</li>
                        <li>Virtual Account</li>
                        <li>QRIS</li>
                        <li>GoPay, ShopeePay, OVO</li>
                        <li>Kartu Kredit/Debit</li>
                        <li>Alfamart, Indomaret</li>
                    </ul>
                </div>

                <button id="pay-button" class="btn btn-success btn-lg w-100">
                    <i class="bi bi-cash-coin"></i> Bayar Sekarang - Rp
                    {{ number_format($asesmen->fee_amount, 0, ',', '.') }}
                </button>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-shield-check"></i>
                        Transaksi dijamin aman dengan teknologi enkripsi
                    </small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<!-- Midtrans Snap JS -->
<script type="text/javascript"
    src="https://app{{ config('midtrans.is_production') ? '' : '.sandbox' }}.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
$(document).ready(function() {
    const asesmenId = {{ $asesmen->id }};

    // Pay Button Click
    $('#pay-button').click(function() {
        $(this).prop('disabled', true);
        $(this).html('<i class="bi bi-hourglass-split"></i> Memproses...');

        // Get Snap Token from server
        $.ajax({
            url: '/payment/create-snap-token/' + asesmenId,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Open Midtrans Snap
                    snap.pay(response.snap_token, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result);
                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil!',
                                text: 'Pembayaran Anda sedang diverifikasi otomatis. Halaman akan di-refresh.',
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result);
                            Swal.fire({
                                icon: 'info',
                                title: 'Pembayaran Menunggu',
                                text: 'Silakan selesaikan pembayaran Anda. Status akan diperbarui otomatis setelah pembayaran berhasil.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        onError: function(result) {
                            console.log('Payment error:', result);
                            Swal.fire({
                                icon: 'error',
                                title: 'Pembayaran Gagal',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                                confirmButtonText: 'OK'
                            });
                            $('#pay-button').prop('disabled', false);
                            $('#pay-button').html(
                                '<i class="bi bi-cash-coin"></i> Bayar Sekarang - Rp {{ number_format($asesmen->fee_amount, 0, ",", ".") }}'
                            );
                        },
                        onClose: function() {
                            console.log('Payment popup closed');
                            $('#pay-button').prop('disabled', false);
                            $('#pay-button').html(
                                '<i class="bi bi-cash-coin"></i> Bayar Sekarang - Rp {{ number_format($asesmen->fee_amount, 0, ",", ".") }}'
                            );
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Gagal membuat token pembayaran',
                        confirmButtonText: 'OK'
                    });
                    $('#pay-button').prop('disabled', false);
                    $('#pay-button').html(
                        '<i class="bi bi-cash-coin"></i> Bayar Sekarang - Rp {{ number_format($asesmen->fee_amount, 0, ",", ".") }}'
                    );
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan pada server. Silakan coba lagi.',
                    confirmButtonText: 'OK'
                });
                $('#pay-button').prop('disabled', false);
                $('#pay-button').html(
                    '<i class="bi bi-cash-coin"></i> Bayar Sekarang - Rp {{ number_format($asesmen->fee_amount, 0, ",", ".") }}'
                );
            }
        });
    });

    // Check Status Button
    $('#check-status-btn').click(function() {
        const $btn = $(this);
        $btn.html('<i class="bi bi-hourglass-split"></i> Mengecek...');
        $btn.prop('disabled', true);

        $.ajax({
            url: '/payment/check-status/' + asesmenId,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    if (response.status === 'verified' || response.asesmen_status === 'paid') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            html: 'Pembayaran Anda telah terverifikasi otomatis.<br><small>Transaction ID: ' + (response.payment_details.transaction_id || '-') + '</small>',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else if (response.status === 'pending') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Status: Menunggu Pembayaran',
                            text: 'Pembayaran masih dalam proses. Silakan selesaikan pembayaran Anda.',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Status: ' + response.status,
                            text: 'Status pembayaran: ' + response.status,
                            confirmButtonText: 'OK'
                        });
                    }
                }
                $btn.html('<i class="bi bi-arrow-clockwise"></i> Cek Status Pembayaran');
                $btn.prop('disabled', false);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengecek status',
                    confirmButtonText: 'OK'
                });
                $btn.html('<i class="bi bi-arrow-clockwise"></i> Cek Status Pembayaran');
                $btn.prop('disabled', false);
            }
        });
    });

    // Test Verify Button (Dev Only)
    $('#test-verify-btn').click(function() {
        const $btn = $(this);
        
        Swal.fire({
            title: 'Simulasi Verifikasi Otomatis?',
            text: 'Ini akan mensimulasikan pembayaran berhasil dan verifikasi otomatis oleh sistem.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simulasi!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.html('<i class="bi bi-hourglass-split"></i> Memverifikasi...');
                $btn.prop('disabled', true);

                $.ajax({
                    url: '/payment/test-verify/' + asesmenId,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Verifikasi Berhasil!',
                                html: '<strong>TEST MODE:</strong> Pembayaran telah terverifikasi otomatis.<br>Status: ' + response.status,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Gagal verifikasi test',
                            confirmButtonText: 'OK'
                        });
                        $btn.html('<i class="bi bi-lightning-fill"></i> Test: Verifikasi Otomatis (Dev Only)');
                        $btn.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Auto check status every 10 seconds if pending
    @if($asesmen->payment && $asesmen->payment->status === 'pending')
    let checkInterval = setInterval(function() {
        $.ajax({
            url: '/payment/check-status/' + asesmenId,
            method: 'GET',
            success: function(response) {
                if (response.success && (response.status === 'verified' || response.asesmen_status === 'paid')) {
                    clearInterval(checkInterval);
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil!',
                        text: 'Pembayaran telah terverifikasi otomatis.',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            }
        });
    }, 10000); // Check every 10 seconds

    // Stop checking after 5 minutes
    setTimeout(function() {
        clearInterval(checkInterval);
    }, 300000);
    @endif
});
</script>
@endpush