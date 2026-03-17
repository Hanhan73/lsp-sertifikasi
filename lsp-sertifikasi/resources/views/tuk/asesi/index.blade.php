@extends('layouts.app')

@section('title', 'Daftar Asesi')
@section('page-title', 'Daftar Asesi TUK')

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Daftar Asesi</h5>
        <a href="{{ route('tuk.collective') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Pendaftaran Kolektif Baru
        </a>
    </div>
    <div class="card-body">
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3" id="filterTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    <i class="bi bi-list"></i> Semua
                    <span class="badge bg-secondary ms-1">{{ $asesmens->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mandiri-tab" data-bs-toggle="tab" data-bs-target="#mandiri" type="button">
                    <i class="bi bi-person"></i> Mandiri
                    <span class="badge bg-success ms-1">{{ $asesmens->where('is_collective', false)->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="collective-tab" data-bs-toggle="tab" data-bs-target="#collective"
                    type="button">
                    <i class="bi bi-people-fill"></i> Kolektif (Batch)
                    <span class="badge bg-primary ms-1">{{ $collectiveBatches->count() }}</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="filterTabsContent">
            <!-- All Asesi -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                @include('tuk.asesi.table-all', ['data' => $asesmens])
            </div>

            <!-- Mandiri Only -->
            <div class="tab-pane fade" id="mandiri" role="tabpanel">
                @include('tuk.asesi.table-mandiri', ['data' => $asesmens->where('is_collective', false)])
            </div>

            <!-- Collective Batches Only -->
            <div class="tab-pane fade" id="collective" role="tabpanel">
                @if($collectiveBatches->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">Belum Ada Batch Kolektif</h4>
                    <p class="text-muted">Silakan daftarkan batch kolektif pertama Anda</p>
                    <a href="{{ route('tuk.collective') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> Daftarkan Batch Baru
                    </a>
                </div>
                @else
                <div class="row">
                    @foreach($collectiveBatches as $batchId => $batchData)
                    @php
                    $batch = $batchData['members'];
                    $firstAsesmen = $batch->first();
                    $paymentStatus = $firstAsesmen->getBatchPaymentStatus();
                    @endphp

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 border-primary">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bi bi-collection"></i> Batch Kolektif
                                    </h6>
                                    @if($paymentStatus === 'paid' || $paymentStatus === 'fully_paid')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Lunas
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Batch ID -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block">Batch ID:</small>
                                    <code class="fs-6">{{ Str::limit($batchId, 30) }}</code>
                                </div>

                                <!-- Stats -->
                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <h4 class="mb-0 text-primary">{{ $batch->count() }}</h4>
                                            <small class="text-muted">Peserta</small>
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <div class="p-2 bg-light rounded">
                                            <h6 class="mb-0">{{ $batchData['skema']->name ?? '-' }}</h6>
                                            <small class="text-muted">Skema Sertifikasi</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info -->
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="120"><i class="bi bi-cash"></i> Metode:</td>
                                        <td>
                                            @if($batchData['payment_timing'] === 'single')
                                            <span class="badge bg-success">1 Fase</span>
                                            @else
                                            <span class="badge bg-info">2 Fase</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-calendar"></i> Didaftar:</td>
                                        <td>{{ $firstAsesmen->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-credit-card"></i> Status:</td>
                                        <td>
                                            @if($paymentStatus === 'paid' || $paymentStatus === 'fully_paid')
                                            <span class="badge bg-success">Lunas</span>
                                            @elseif($paymentStatus === 'phase_1_paid')
                                            <span class="badge bg-warning">Fase 1 Lunas</span>
                                            @elseif($paymentStatus === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @else
                                            <span class="badge bg-secondary">Belum Bayar</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>

                                <!-- Actions -->
                                <div class="d-grid gap-2 mt-3">
                                    <a href="{{ route('tuk.batch.detail', $batchId) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye"></i> Lihat Detail Batch ({{ $batch->count() }} Peserta)
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    Update terakhir {{ $firstAsesmen->updated_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTables untuk setiap tab
    $('.data-table').each(function() {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                responsive: true
            });
        }
    });
});
</script>
@endpush