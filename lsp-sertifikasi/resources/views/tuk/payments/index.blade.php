@extends('layouts.app')

@section('title', 'Pembayaran Kolektif')
@section('page-title', 'Pembayaran Kolektif')

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-credit-card"></i> Daftar Batch Pembayaran Kolektif
        </h5>
        <span class="badge bg-primary">{{ $batches->count() }} Batch</span>
    </div>
    <div class="card-body">

        {{-- Info pembayaran manual --}}
        <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
            <i class="bi bi-info-circle-fill fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Cara Pembayaran Kolektif</strong><br>
                Pembayaran dilakukan secara <strong>manual melalui Transfer Bank atau QRIS</strong>.
                Hubungi Admin LSP untuk mendapatkan rekening tujuan / kode QRIS, lalu kirimkan bukti transfer.
                Admin LSP akan memverifikasi dan memperbarui status di sistem.
                <br><small class="text-muted">Proses asesmen tetap dapat berjalan selama pembayaran diproses.</small>
            </div>
        </div>

        @if($batches->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3 text-muted">Belum Ada Batch Pembayaran</h4>
            <p class="text-muted">Batch kolektif yang sudah terdaftar akan muncul di sini</p>
            <a href="{{ route('tuk.collective') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle"></i> Daftarkan Batch Baru
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Batch ID</th>
                        <th>Skema</th>
                        <th>Peserta</th>
                        <th>Metode</th>
                        <th>Status Pembayaran</th>
                        <th>Total Biaya</th>
                        <th>Tgl Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td>
                            <code class="text-primary small">{{ Str::limit($batch['batch_id'], 22) }}</code>
                        </td>
                        <td>{{ $batch['skema']->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info fs-6">{{ $batch['total_participants'] }} orang</span>
                        </td>
                        <td>
                            @if($batch['payment_phases'] === 'single')
                                <span class="badge bg-success"><i class="bi bi-cash-stack"></i> 1 Fase</span>
                            @else
                                <span class="badge bg-primary"><i class="bi bi-cash-coin"></i> 2 Fase</span>
                            @endif
                        </td>
                        <td>
                            @php $status = $batch['payment_status']; @endphp
                            @if(in_array($status, ['paid', 'fully_paid']))
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Lunas
                                </span>
                            @elseif($status === 'phase_1_paid')
                                <span class="badge bg-info">
                                    <i class="bi bi-hourglass-half"></i> Fase 1 Lunas
                                </span>
                            @elseif($status === 'pending')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> Pending
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-bank"></i> Bayar Manual
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($batch['total_amount'] > 0)
                                <strong class="text-success">
                                    Rp {{ number_format($batch['total_amount'], 0, ',', '.') }}
                                </strong>
                                @if($batch['payment_phases'] === 'two_phase')
                                    <br>
                                    <small class="text-muted">
                                        F1: Rp {{ number_format($batch['phase_1_amount'], 0, ',', '.') }}
                                        &nbsp;|&nbsp;
                                        F2: Rp {{ number_format($batch['phase_2_amount'], 0, ',', '.') }}
                                    </small>
                                @endif
                            @else
                                <span class="text-muted small"><em>Menunggu Admin LSP</em></span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $batch['registration_date'] ? $batch['registration_date']->translatedFormat('d/m/Y') : '-' }}
                            </small>
                        </td>
                        <td>
                            <div class="d-grid gap-1" style="min-width: 140px;">
                                {{-- Status pembayaran manual — tidak ada tombol bayar via gateway --}}
                                @if(in_array($batch['payment_status'], ['paid', 'fully_paid']))
                                    <span class="btn btn-sm btn-success disabled">
                                        <i class="bi bi-check-circle-fill"></i> Lunas
                                    </span>
                                @else
                                    <a href="{{ route('tuk.collective.payment.index', $batch['batch_id']) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-bank"></i> Info Pembayaran
                                    </a>
                                @endif

                                <a href="{{ route('tuk.batch.detail', $batch['batch_id']) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║  MIDTRANS PAYMENT BUTTONS — DISEMBUNYIKAN SEMENTARA               ║
    ║  Tombol "Bayar Fase 1/2" via Midtrans sengaja dinonaktifkan.       ║
    ║  Aktifkan kembali jika integrasi gateway sudah siap.               ║
    ╚══════════════════════════════════════════════════════════════════════╝

    Contoh kode yang dinonaktifkan:
    @if($batch['can_pay_phase_1'])
        <a href="{{ route('tuk.collective.payment.index', $batch['batch_id']) }}"
           class="btn btn-sm btn-warning">
            <i class="bi bi-credit-card"></i> Bayar Fase 1
        </a>
    @elseif($batch['can_pay_phase_2'])
        <a href="{{ route('tuk.collective.payment.index', $batch['batch_id']) }}"
           class="btn btn-sm btn-warning">
            <i class="bi bi-credit-card"></i> Bayar Fase 2
        </a>
    @endif
--}}

@endsection