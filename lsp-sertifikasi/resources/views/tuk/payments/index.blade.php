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
        @if($batches->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3 text-muted">Belum Ada Batch Pembayaran</h4>
            <p class="text-muted">Batch kolektif yang sudah diverifikasi akan muncul di sini</p>
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
                        <th>Jumlah Peserta</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Total Biaya</th>
                        <th>Tanggal Daftar</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ Str::limit($batch['batch_id'], 20) }}</strong>
                        </td>
                        <td>{{ $batch['skema']->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info fs-6">{{ $batch['total_participants'] }} peserta</span>
                        </td>
                        <td>
                            @if($batch['payment_phases'] === 'single')
                            <span class="badge bg-success">
                                <i class="bi bi-cash-stack"></i> 1 Fase
                            </span>
                            @else
                            <span class="badge bg-primary">
                                <i class="bi bi-cash-coin"></i> 2 Fase
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($batch['payment_status'] === 'paid' || $batch['payment_status'] === 'fully_paid')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Lunas
                            </span>
                            @elseif($batch['payment_status'] === 'phase_1_paid')
                            <span class="badge bg-warning">
                                <i class="bi bi-hourglass-half"></i> Fase 1 Lunas
                            </span>
                            @elseif($batch['payment_status'] === 'pending')
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Belum Bayar
                            </span>
                            @endif
                        </td>
                        <td>
                            <strong class="text-success">Rp
                                {{ number_format($batch['total_amount'], 0, ',', '.') }}</strong>
                            @if($batch['payment_phases'] === 'two_phase')
                            <br>
                            <small class="text-muted">
                                F1: Rp {{ number_format($batch['phase_1_amount'], 0, ',', '.') }}
                                | F2: Rp {{ number_format($batch['phase_2_amount'], 0, ',', '.') }}
                            </small>
                            @endif
                        </td>
                        <td>
                            <small>{{ $batch['registration_date']->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div class="d-grid gap-1">
                                {{-- Tombol Utama (Bayar atau Status) --}}
                                @if($batch['payment_status'] === 'paid' || $batch['payment_status'] === 'fully_paid')
                                <button class="btn btn-sm btn-success" disabled>
                                    <i class="bi bi-check-circle-fill"></i> Sudah Lunas
                                </button>
                                @elseif($batch['can_pay_phase_1'])
                                <a href="{{ route('tuk.collective.payment', $batch['batch_id']) }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="bi bi-credit-card"></i> Bayar Fase 1
                                </a>
                                @elseif($batch['can_pay_phase_2'])
                                <a href="{{ route('tuk.collective.payment', $batch['batch_id']) }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="bi bi-credit-card"></i> Bayar Fase 2
                                </a>
                                @else
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="bi bi-clock"></i> Menunggu Verifikasi
                                </button>
                                @endif

                                {{-- Tombol Detail (Selalu Ada) --}}
                                <a href="{{ route('tuk.batch.detail', $batch['batch_id']) }}"
                                    class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i> Lihat Detail
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
@endsection

@push('scripts')
<script>
$(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush