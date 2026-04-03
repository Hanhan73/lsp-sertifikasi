@extends('layouts.app')

@section('title', 'Detail Batch Kolektif')
@section('page-title', 'Detail Batch - ' . $batchId)

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="row">

    {{-- ── Kolom Kiri ──────────────────────────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Info Batch --}}
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-collection"></i> Informasi Batch</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td width="160"><strong>Batch ID</strong></td>
                                <td>: <code class="small">{{ $batchId }}</code></td>
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
                                <td><strong>Tanggal Daftar</strong></td>
                                <td>: {{ $firstAsesmen->created_at->translatedFormat('d F Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td width="160"><strong>Status Pembayaran</strong></td>
                                <td>:
                                    @if(in_array($paymentStatus, ['paid', 'fully_paid']))
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Lunas
                                        </span>
                                    @elseif($paymentStatus === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-hourglass-split"></i> Pending
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-bank"></i> Belum Bayar
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Biaya</strong></td>
                                <td>:
                                    @if($totalAmount > 0)
                                        <strong class="text-success">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted"><em>Menunggu Admin LSP</em></span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Pelatihan</strong></td>
                                <td>:
                                    @if($firstAsesmen->training_flag)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-mortarboard-fill"></i> Ya
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat Pembayaran (hanya tampil jika ada) --}}
        @if($payments->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Riwayat Pembayaran</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Tanggal</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Transaction ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td class="ps-3">
                                    {{ $payment->verified_at ? $payment->verified_at->translatedFormat('d M Y H:i') : '-' }}
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>{{ ucfirst($payment->method ?? '-') }}</td>
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

        {{-- Daftar Peserta --}}
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-people"></i> Daftar Peserta
                    <span class="badge bg-secondary ms-1">{{ $asesmens->count() }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesmens as $index => $asesmen)
                            <tr>
                                <td class="ps-3">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $asesmen->email ?? $asesmen->user->email }}</small>
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

    {{-- ── Kolom Kanan ─────────────────────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Actions --}}
        <div class="card sticky-top mb-3" style="top: 20px;">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Aksi</h5>
            </div>
            <div class="card-body d-grid gap-2">

                {{-- Info pembayaran manual --}}
                @if(!in_array($paymentStatus, ['paid', 'fully_paid']))
                <div class="alert alert-info py-2 mb-1">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Pembayaran dilakukan secara manual (TF/QRIS).
                        Hubungi Admin LSP untuk informasi rekening / kode QRIS.
                    </small>
                </div>
                <a href="{{ route('tuk.collective.payment.index', $batchId) }}"
                   class="btn btn-outline-info">
                    <i class="bi bi-bank"></i> Info Pembayaran
                </a>
                @else
                <div class="alert alert-success py-2 mb-1">
                    <small>
                        <i class="bi bi-check-circle"></i>
                        Semua pembayaran sudah terverifikasi.
                    </small>
                </div>
                @endif

                {{-- Download invoice jika sudah ada pembayaran terverifikasi --}}
                @if($hasVerifiedPayment)
                <a href="{{ route('tuk.collective.payment.invoice', $batchId) }}"
                   class="btn btn-success">
                    <i class="bi bi-file-earmark-pdf"></i> Download Invoice
                </a>
                @endif

                <a href="{{ route('tuk.asesi') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        {{-- Statistik Status --}}
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Statistik Peserta</h6>
            </div>
            <div class="card-body">
                @php
                    $statusLabels = [
                        'registered'               => ['label' => 'Terdaftar',            'badge' => 'secondary'],
                        'data_completed'            => ['label' => 'Data Lengkap',         'badge' => 'info'],
                        'verified'                  => ['label' => 'Terverifikasi',         'badge' => 'primary'],
                        'paid'                      => ['label' => 'Sudah Bayar',          'badge' => 'success'],
                        'scheduled'                 => ['label' => 'Terjadwal',            'badge' => 'warning'],
                        'pre_assessment_completed'  => ['label' => 'Pra-Asesmen Selesai', 'badge' => 'info'],
                        'assessed'                  => ['label' => 'Sudah Diases',         'badge' => 'primary'],
                        'certified'                 => ['label' => 'Tersertifikasi',        'badge' => 'success'],
                    ];
                    $statusCounts = $asesmens->groupBy('status')->map->count();
                @endphp

                @forelse($statusCounts as $status => $count)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-{{ $statusLabels[$status]['badge'] ?? 'secondary' }}">
                        {{ $statusLabels[$status]['label'] ?? $status }}
                    </span>
                    <span class="fw-semibold">{{ $count }} orang</span>
                </div>
                @empty
                <p class="text-muted small mb-0">Belum ada data.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection