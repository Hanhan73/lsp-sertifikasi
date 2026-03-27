@extends('layouts.app')

@section('title', 'Pembayaran Kolektif')
@section('page-title', 'Pembayaran Kolektif - Batch #' . substr($batchId, 0, 20))

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- ── Info Batch ────────────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Batch Pendaftaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="180"><strong>Batch ID:</strong></td>
                                <td><code>{{ $batchId }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>TUK:</strong></td>
                                <td>{{ $tuk->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Metode Pembayaran:</strong></td>
                                <td>
                                    @if($paymentPhases === 'single')
                                        <span class="badge bg-success"><i class="bi bi-cash-stack"></i> 1 Fase</span>
                                    @else
                                        <span class="badge bg-primary"><i class="bi bi-cash-coin"></i> 2 Fase</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td width="150"><strong>Total Peserta:</strong></td>
                                <td>{{ $asesmens->count() }} orang</td>
                            </tr>
                            <tr>
                                <td><strong>Skema:</strong></td>
                                <td>{{ $asesmens->first()->skema->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Biaya:</strong></td>
                                <td>
                                    @if($totalAmount > 0)
                                        <strong class="text-success">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted"><em>Menunggu penetapan Admin LSP</em></span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Info Pembayaran Manual ────────────────────────────────────── --}}
        @if(!$allPaid)
        <div class="card mb-4 border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-bank"></i> Cara Pembayaran
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>Pembayaran Kolektif via Transfer Bank / QRIS</strong><br>
                    Pembayaran untuk batch ini dilakukan secara manual oleh TUK. Setelah transfer, Admin LSP akan memverifikasi pembayaran.
                </div>

                {{-- Informasi rekening / QRIS akan dikonfigurasi oleh Admin --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-bank2" style="font-size: 2.5rem; color: #0d6efd;"></i>
                                <h6 class="mt-3">Transfer Bank</h6>
                                <p class="text-muted small mb-0">
                                    Hubungi Admin LSP untuk mendapatkan nomor rekening tujuan.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-qr-code" style="font-size: 2.5rem; color: #198754;"></i>
                                <h6 class="mt-3">QRIS</h6>
                                <p class="text-muted small mb-0">
                                    Hubungi Admin LSP untuk mendapatkan kode QRIS pembayaran.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-secondary mt-3 mb-0">
                    <i class="bi bi-telephone"></i>
                    <strong>Langkah pembayaran:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Hubungi Admin LSP untuk konfirmasi total tagihan batch ini</li>
                        <li>Lakukan transfer atau scan QRIS sesuai nominal yang diberikan Admin</li>
                        <li>Kirimkan bukti transfer ke Admin LSP</li>
                        <li>Admin LSP akan memverifikasi dan mengupdate status pembayaran</li>
                    </ol>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Status Phase (two_phase) ─────────────────────────────────── --}}
        @if($paymentPhases === 'two_phase')
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-layers"></i> Status Pembayaran Dua Fase</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card {{ $phase1Status === 'paid' ? 'border-success' : 'border-secondary' }}">
                            <div class="card-body text-center">
                                <h6><i class="bi bi-1-circle{{ $phase1Status === 'paid' ? '-fill text-success' : '' }}"></i> Fase 1</h6>
                                <p class="text-muted small mb-2">Sebelum Asesmen</p>
                                @if($phase1Status === 'paid')
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Sudah Dibayar</span>
                                @else
                                    <span class="badge bg-secondary">Menunggu Pembayaran</span>
                                @endif
                                @if($asesmens->first()->phase_1_amount)
                                    <p class="mt-2 mb-0">
                                        <strong>Rp {{ number_format($asesmens->first()->phase_1_amount, 0, ',', '.') }}</strong>
                                        <small class="text-muted">/ peserta</small>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card {{ $phase2Status === 'paid' ? 'border-success' : 'border-secondary' }}">
                            <div class="card-body text-center">
                                <h6><i class="bi bi-2-circle{{ $phase2Status === 'paid' ? '-fill text-success' : '' }}"></i> Fase 2</h6>
                                <p class="text-muted small mb-2">Setelah Asesmen Selesai</p>
                                @if($phase2Status === 'paid')
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Sudah Dibayar</span>
                                @elseif($phase1Status === 'paid')
                                    <span class="badge bg-warning">Menunggu Pembayaran</span>
                                @else
                                    <span class="badge bg-secondary">Menunggu Fase 1</span>
                                @endif
                                @if($asesmens->first()->phase_2_amount)
                                    <p class="mt-2 mb-0">
                                        <strong>Rp {{ number_format($asesmens->first()->phase_2_amount, 0, ',', '.') }}</strong>
                                        <small class="text-muted">/ peserta</small>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Daftar Peserta ───────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Daftar Peserta ({{ $asesmens->count() }} orang)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status Asesmen</th>
                                @if($paymentPhases === 'single')
                                    <th>Biaya</th>
                                @else
                                    <th>Fase 1</th>
                                    <th>Fase 2</th>
                                    <th>Total</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesmens as $index => $asesmen)
                            <tr>
                                <td class="ps-3">{{ $index + 1 }}</td>
                                <td>{{ $asesmen->full_name ?? $asesmen->user->name }}</td>
                                <td>{{ $asesmen->email ?? $asesmen->user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $asesmen->status_badge }}">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                                @if($paymentPhases === 'single')
                                    <td>
                                        @if($asesmen->fee_amount)
                                            <strong>Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted small">Belum ditentukan</span>
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
                                    <td>
                                        @if($asesmen->fee_amount)
                                            <strong>Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        @if($totalAmount > 0)
                        <tfoot class="table-success">
                            <tr>
                                <td colspan="{{ $paymentPhases === 'single' ? 4 : 6 }}" class="text-end pe-3">
                                    <strong>TOTAL:</strong>
                                </td>
                                <td><strong>Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Status Pembayaran / Sudah Lunas ──────────────────────────── --}}
        @if($allPaid)
        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-success mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Semua Pembayaran Sudah Terverifikasi!</strong><br>
                    Proses asesmen dapat dilanjutkan.
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('tuk.batch.detail', $batchId) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> Lihat Detail Batch
                    </a>
                    <a href="{{ route('tuk.asesi') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Asesi
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-clock-history"></i>
                    <strong>Menunggu Verifikasi Pembayaran</strong><br>
                    Setelah Anda melakukan transfer, Admin LSP akan memverifikasi dan memperbarui status pembayaran secara manual.
                    Proses asesmen tetap dapat berjalan normal selama menunggu konfirmasi pembayaran.
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('tuk.batch.detail', $batchId) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> Lihat Detail Batch
                    </a>
                    <a href="{{ route('tuk.schedules.index') }}" class="btn btn-outline-warning">
                        <i class="bi bi-calendar-check"></i> Atur Jadwal Asesmen
                    </a>
                    <a href="{{ route('tuk.asesi') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Asesi
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{--
            ╔══════════════════════════════════════════════════════════════╗
            ║  MIDTRANS — DISEMBUNYIKAN SEMENTARA                        ║
            ║  Kode di bawah ini tetap ada, hanya dinonaktifkan.         ║
            ║  Aktifkan kembali dengan menghapus komentar jika diperlukan ║
            ╚══════════════════════════════════════════════════════════════╝

        @if($canPay && !$allPaid)
        <script type="text/javascript"
            src="https://app{{ config('midtrans.is_production') ? '' : '.sandbox' }}.midtrans.com/snap/snap.js"
            data-client-key="{{ config('midtrans.client_key') }}">
        </script>
        <script>
        $('#pay-button').click(function() {
            $.ajax({
                url: '/tuk/collective/payment/{{ $batchId }}/create-token',
                method: 'POST',
                data: { phase: '{{ $currentPhase }}' },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        snap.pay(response.snap_token, {
                            onSuccess: function(result) {
                                window.location.href = '{{ route("tuk.collective.payment.finish", $batchId) }}';
                            },
                            onPending: function(result) {
                                window.location.href = '{{ route("tuk.collective.payment.finish", $batchId) }}';
                            },
                            onError: function(result) { alert('Pembayaran gagal'); }
                        });
                    }
                }
            });
        });
        </script>
        @endif

        --}}

    </div>
</div>
@endsection