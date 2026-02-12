@extends('layouts.app')

@section('title', 'Pembayaran Kolektif')
@section('page-title', 'Pembayaran Kolektif - Batch #' . substr($batchId, 0, 20))

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Batch Info -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Batch Pendaftaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="180"><strong>Batch ID:</strong></td>
                                <td>{{ $batchId }}</td>
                            </tr>
                            <tr>
                                <td><strong>TUK:</strong></td>
                                <td>{{ $tuk->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Metode Pembayaran:</strong></td>
                                <td>
                                    @if($paymentPhases === 'single')
                                    <span class="badge bg-success">
                                        <i class="bi bi-cash-stack"></i> 1 Fase (Full Payment)
                                    </span>
                                    @else
                                    <span class="badge bg-primary">
                                        <i class="bi bi-cash-coin"></i> 2 Fase (Split Payment)
                                    </span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Total Peserta:</strong></td>
                                <td>{{ $asesmens->count() }} orang</td>
                            </tr>
                            <tr>
                                <td><strong>Skema:</strong></td>
                                <td>{{ $asesmens->first()->skema->name ?? '-' }}</td>
                            </tr>
                            @if($paymentPhases === 'two_phase')
                            <tr>
                                <td><strong>Fase Saat Ini:</strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $currentPhase === 'phase_1' ? 'Fase 1 (50%)' : 'Fase 2 (50%)' }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase Info for Two Phase Payment -->
        @if($paymentPhases === 'two_phase')
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pembayaran 2 Fase</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card {{ $currentPhase === 'phase_1' ? 'border-success' : 'border-secondary' }}">
                            <div class="card-body text-center">
                                <h6>
                                    <i class="bi bi-1-circle{{ $currentPhase === 'phase_1' ? '-fill text-success' : '' }}"></i>
                                    Fase 1 (50%)
                                </h6>
                                <p class="text-muted small mb-2">Setelah Admin Verifikasi</p>
                                @if($phase1Status === 'paid')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Sudah Dibayar
                                </span>
                                @elseif($currentPhase === 'phase_1')
                                <span class="badge bg-warning">
                                    <i class="bi bi-hourglass-half"></i> Perlu Dibayar
                                </span>
                                @else
                                <span class="badge bg-secondary">Belum Dibayar</span>
                                @endif
                                <p class="mt-2 mb-0">
                                    <strong>Rp {{ number_format($asesmens->first()->phase_1_amount ?? 0, 0, ',', '.') }}</strong>
                                    <small class="text-muted">/ peserta</small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card {{ $currentPhase === 'phase_2' ? 'border-success' : 'border-secondary' }}">
                            <div class="card-body text-center">
                                <h6>
                                    <i class="bi bi-2-circle{{ $currentPhase === 'phase_2' ? '-fill text-success' : '' }}"></i>
                                    Fase 2 (50%)
                                </h6>
                                <p class="text-muted small mb-2">Setelah Asesmen Selesai</p>
                                @if($phase2Status === 'paid')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Sudah Dibayar
                                </span>
                                @elseif($currentPhase === 'phase_2')
                                <span class="badge bg-warning">
                                    <i class="bi bi-hourglass-half"></i> Perlu Dibayar
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    {{ $phase1Status === 'paid' ? 'Menunggu Asesmen' : 'Belum Waktunya' }}
                                </span>
                                @endif
                                <p class="mt-2 mb-0">
                                    <strong>Rp {{ number_format($asesmens->first()->phase_2_amount ?? 0, 0, ',', '.') }}</strong>
                                    <small class="text-muted">/ peserta</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Participants List -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Daftar Peserta</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                @if($paymentPhases === 'single')
                                <th>Biaya</th>
                                @else
                                <th>Fase 1</th>
                                <th>Fase 2</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesmens as $index => $asesmen)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $asesmen->full_name ?? $asesmen->user->name }}</td>
                                <td>{{ $asesmen->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $asesmen->status_badge }} badge-status">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                                @if($paymentPhases === 'single')
                                <td>
                                    @if($asesmen->fee_amount)
                                    Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}
                                    @else
                                    <span class="text-muted">Belum ditentukan</span>
                                    @endif
                                </td>
                                @else
                                <td>
                                    @if($asesmen->phase_1_amount)
                                    <span class="badge bg-{{ $asesmen->payments()->where('payment_phase', 'phase_1')->where('status', 'verified')->exists() ? 'success' : 'secondary' }}">
                                        Rp {{ number_format($asesmen->phase_1_amount, 0, ',', '.') }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($asesmen->phase_2_amount)
                                    <span class="badge bg-{{ $asesmen->payments()->where('payment_phase', 'phase_2')->where('status', 'verified')->exists() ? 'success' : 'secondary' }}">
                                        Rp {{ number_format($asesmen->phase_2_amount, 0, ',', '.') }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        @if($canPay && $totalAmount > 0)
                        <tfoot>
                            <tr class="table-success">
                                <td colspan="{{ $paymentPhases === 'single' ? 4 : 5 }}" class="text-end">
                                    <strong>TOTAL PEMBAYARAN{{ $paymentPhases === 'two_phase' ? ' ('.strtoupper(str_replace('_', ' ', $currentPhase)).')' : '' }}:</strong>
                                </td>
                                <td><strong>Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        @if(!$canPay)
        <div class="card">
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-clock"></i>
                    <strong>Belum Dapat Melakukan Pembayaran</strong>
                    <p class="mb-0 mt-2">
                        @if($paymentPhases === 'single')
                        Semua peserta harus terverifikasi oleh Admin LSP dan biaya harus sudah ditentukan sebelum dapat melakukan pembayaran.
                        @else
                            @if($currentPhase === 'phase_1')
                            Pembayaran Fase 1 akan tersedia setelah Admin LSP memverifikasi semua peserta dan menentukan biaya.
                            @else
                            Pembayaran Fase 2 akan tersedia setelah semua peserta menyelesaikan asesmen.
                            @endif
                        @endif
                    </p>
                </div>

                <a href="{{ route('tuk.asesi') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Asesi
                </a>
            </div>
        </div>
        @elseif(isset($allPaid) && $allPaid)
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <strong>Pembayaran Berhasil!</strong>
                    <p class="mb-0 mt-2">
                        @if($paymentPhases === 'single')
                        Pembayaran kolektif untuk batch ini telah berhasil diverifikasi.
                        @else
                            @if($currentPhase === 'phase_1' && $phase1Status === 'paid')
                            Pembayaran Fase 1 telah berhasil. Fase 2 akan tersedia setelah asesmen selesai.
                            @elseif($phase2Status === 'paid')
                            Pembayaran Fase 2 telah berhasil. Semua pembayaran untuk batch ini sudah selesai.
                            @endif
                        @endif
                    </p>
                </div>
                <a href="{{ route('tuk.asesi') }}" class="btn btn-primary w-100">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Asesi
                </a>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Pembayaran Kolektif</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Pembayaran untuk <strong>{{ $asesmens->count() }} peserta</strong>
                    <br>
                    @if($paymentPhases === 'single')
                    <strong>Metode:</strong> 1 Fase (Full Payment)
                    @else
                    <strong>Metode:</strong> 2 Fase - 
                    @if($currentPhase === 'phase_1')
                    <span class="badge bg-primary">Fase 1 (50%)</span>
                    @else
                    <span class="badge bg-success">Fase 2 (50%)</span>
                    @endif
                    @endif
                </div>

                <div class="alert alert-success text-center mb-4">
                    <h4 class="mb-0">
                        <i class="bi bi-cash-coin"></i>
                        Total Pembayaran {{ $paymentPhases === 'two_phase' ? '('.ucfirst(str_replace('_', ' ', $currentPhase)).')' : '' }}
                    </h4>
                    <h2 class="mt-2 mb-0">
                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                    </h2>
                    @if($paymentPhases === 'two_phase')
                    <small class="text-muted">
                        @if($currentPhase === 'phase_1')
                        50% dari total biaya (Fase 2: Rp {{ number_format($totalAmount, 0, ',', '.') }} akan dibayar setelah asesmen)
                        @else
                        Sisa 50% dari total biaya (Fase 1 sudah dibayar)
                        @endif
                    </small>
                    @endif
                </div>

                <button id="pay-button" class="btn btn-success btn-lg w-100 mb-3">
                    <i class="bi bi-cash-coin"></i> Bayar Sekarang - Rp {{ number_format($totalAmount, 0, ',', '.') }}
                </button>

                <div class="text-center">
                    <small class="text-muted">
                        <i class="bi bi-shield-check"></i>
                        Transaksi dijamin aman dengan Midtrans Payment Gateway
                    </small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($canPay && !$allPaid)  <!-- ✅ FIXED: Ganti dari isset($allPaid) -->
<!-- Midtrans Snap JS -->
<script type="text/javascript"
    src="https://app{{ config('midtrans.is_production') ? '' : '.sandbox' }}.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
$(document).ready(function() {
    const batchId = '{{ $batchId }}';
    const phase = '{{ $currentPhase }}';

    // ✅ ADDED: Debug logging
    console.log('Payment page loaded');
    console.log('Batch ID:', batchId);
    console.log('Current Phase:', phase);
    console.log('Snap available:', typeof snap !== 'undefined');

    $('#pay-button').click(function() {
        console.log('Pay button clicked'); // ✅ ADDED
        
        $(this).prop('disabled', true);
        $(this).html('<i class="bi bi-hourglass-split"></i> Memproses...');

        $.ajax({
            url: '/tuk/collective/payment/' + batchId + '/create-token',
            method: 'POST',
            data: {
                phase: phase
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('AJAX Response:', response); // ✅ ADDED
                
                if (response.success) {
                    console.log('Snap Token:', response.snap_token); // ✅ ADDED
                    
                    // ✅ ADDED: Check if snap is available
                    if (typeof snap === 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Midtrans Snap belum dimuat. Refresh halaman dan coba lagi.',
                            confirmButtonText: 'OK'
                        });
                        resetPayButton();
                        return;
                    }
                    
                    snap.pay(response.snap_token, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result); // ✅ ADDED
                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil!',
                                text: 'Pembayaran kolektif sedang diproses.',
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '{{ route("tuk.collective.payment.finish", $batchId) }}';
                            });
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result); // ✅ ADDED
                            Swal.fire({
                                icon: 'info',
                                title: 'Pembayaran Menunggu',
                                text: 'Silakan selesaikan pembayaran Anda.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '{{ route("tuk.collective.payment.finish", $batchId) }}';
                            });
                        },
                        onError: function(result) {
                            console.log('Payment error:', result); // ✅ ADDED
                            Swal.fire({
                                icon: 'error',
                                title: 'Pembayaran Gagal',
                                text: 'Terjadi kesalahan. Silakan coba lagi.',
                                confirmButtonText: 'OK'
                            });
                            resetPayButton();
                        },
                        onClose: function() {
                            console.log('Payment popup closed'); // ✅ ADDED
                            resetPayButton();
                        }
                    });
                } else {
                    console.error('Response not success:', response.message); // ✅ ADDED
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Gagal membuat token pembayaran',
                        confirmButtonText: 'OK'
                    });
                    resetPayButton();
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr); // ✅ ADDED
                console.error('Response:', xhr.responseJSON); // ✅ ADDED
                
                let errorMsg = 'Terjadi kesalahan pada server.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    confirmButtonText: 'OK'
                });
                resetPayButton();
            }
        });
    });

    function resetPayButton() {
        $('#pay-button').prop('disabled', false);
        $('#pay-button').html(
            '<i class="bi bi-cash-coin"></i> Bayar Sekarang - Rp {{ number_format($totalAmount, 0, ",", ".") }}'
        );
    }
});
</script>
@endif
@endpush