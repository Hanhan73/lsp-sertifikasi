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
            <i class="bi bi-plus-circle"></i> Pendaftaran Kolektif
        </a>
    </div>
    <div class="card-body">
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3" id="filterTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    Semua <span class="badge bg-secondary ms-1">{{ $asesmens->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mandiri-tab" data-bs-toggle="tab" data-bs-target="#mandiri" type="button">
                    Mandiri <span class="badge bg-success ms-1">{{ $asesmens->where('is_collective',
                        false)->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="collective-tab" data-bs-toggle="tab" data-bs-target="#collective"
                    type="button">
                    Kolektif <span class="badge bg-primary ms-1">{{ $asesmens->where('is_collective',
                        true)->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pending-payment-tab" data-bs-toggle="tab" data-bs-target="#pending-payment"
                    type="button">
                    Perlu Bayar <span class="badge bg-warning ms-1" id="pending-payment-count">0</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="filterTabsContent">
            <!-- All -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                @include('tuk.asesi.table', ['data' => $asesmens])
            </div>

            <!-- Mandiri -->
            <div class="tab-pane fade" id="mandiri" role="tabpanel">
                @include('tuk.asesi.table', ['data' => $asesmens->where('is_collective', false)])
            </div>

            <!-- Collective -->
            <div class="tab-pane fade" id="collective" role="tabpanel">
                @include('tuk.asesi.table', ['data' => $asesmens->where('is_collective', true)])
            </div>

            <!-- Pending Payment -->
            <div class="tab-pane fade" id="pending-payment" role="tabpanel">
                @php
                $pendingPayments = $asesmens->filter(function($asesmen) {
                if (!$asesmen->is_collective) return false;

                // Single phase: verified but not paid
                if ($asesmen->payment_phases === 'single') {
                return $asesmen->status === 'verified' &&
                !$asesmen->payments()->where('payment_phase', 'full')->where('status',
                'verified')->exists();
                }

                // Two phase
                $phase1Paid = $asesmen->payments()->where('payment_phase', 'phase_1')->where('status',
                'verified')->exists();
                $phase2Paid = $asesmen->payments()->where('payment_phase', 'phase_2')->where('status',
                'verified')->exists();

                // Phase 1 not paid
                if ($asesmen->status === 'verified' && !$phase1Paid) {
                return true;
                }

                // Phase 2 not paid and ready
                if (in_array($asesmen->status, ['assessed', 'certified']) && $phase1Paid && !$phase2Paid) {
                return true;
                }

                return false;
                });
                @endphp
                @include('tuk.asesi.table', ['data' => $pendingPayments])
            </div>
        </div>

        <!-- 🔥 Collective Batch Summary with Invoice Download -->
        @php
        $collectiveBatches = $asesmens
            ->where('is_collective', true)
            ->groupBy('collective_batch_id');
        @endphp

        @if($collectiveBatches->count() > 0)
        <hr class="my-4">
        <h5 class="mb-3"><i class="bi bi-layers"></i> Ringkasan Batch Kolektif</h5>

        <div class="row">
            @foreach($collectiveBatches as $batchId => $batchAsesmens)
            @php
                $firstAsesmen = $batchAsesmens->first();
                $paymentStatus = $firstAsesmen->getBatchPaymentStatus();
                
                // Check if has verified payment for invoice
                $hasVerifiedPayment = $batchAsesmens->flatMap->payments->where('status', 'verified')->isNotEmpty();

                // Detect pending phase
                $pendingPhase = null;
                if ($firstAsesmen->payment_phases === 'two_phase') {
                    $phase1Paid = $batchAsesmens->every(fn($a) => $a->payments()->where('payment_phase', 'phase_1')->where('status', 'verified')->exists());
                    $phase2Paid = $batchAsesmens->every(fn($a) => $a->payments()->where('payment_phase', 'phase_2')->where('status', 'verified')->exists());
                    
                    if (!$phase1Paid && $firstAsesmen->status === 'verified') {
                        $pendingPhase = 'phase_1';
                    } elseif ($phase1Paid && !$phase2Paid && $batchAsesmens->every(fn($a) => in_array($a->status, ['assessed', 'certified']))) {
                        $pendingPhase = 'phase_2';
                    }
                } else {
                    // Single phase
                    if ($firstAsesmen->status === 'verified' && !$batchAsesmens->every(fn($a) => $a->payments()->where('payment_phase', 'full')->where('status', 'verified')->exists())) {
                        $pendingPhase = 'full';
                    }
                }
            @endphp

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 {{ $pendingPhase ? 'border-warning' : ($hasVerifiedPayment ? 'border-success' : '') }}">
                    <div class="card-header {{ $hasVerifiedPayment ? 'bg-success text-white' : 'bg-light' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-collection"></i>
                                {{ Str::limit($batchId, 25) }}
                            </h6>
                            @if($paymentStatus === 'paid' || $paymentStatus === 'fully_paid')
                            <span class="badge bg-white text-success">
                                <i class="bi bi-check-circle"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary">
                                    <i class="bi bi-people"></i> {{ $batchAsesmens->count() }} peserta
                                </span>
                                <span class="badge bg-{{ $firstAsesmen->payment_phases === 'single' ? 'success' : 'info' }}">
                                    {{ $firstAsesmen->payment_phases === 'single' ? '1 Fase' : '2 Fase' }}
                                </span>
                            </div>
                            
                            <p class="mb-2 small">
                                <i class="bi bi-book text-primary"></i> 
                                <strong>{{ $firstAsesmen->skema->name ?? '-' }}</strong>
                            </p>
                            
                            <p class="mb-2">
                                <small class="text-muted">Status Pembayaran:</small><br>
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
                            </p>
                        </div>

                        <!-- 🔥 Payment Action Buttons -->
                        @if($pendingPhase)
                            <div class="alert alert-warning py-2 mb-2">
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

                            <a href="{{ route('tuk.collective.payment', $batchId) }}" class="btn btn-warning btn-sm w-100 mb-2">
                                <i class="bi bi-cash-coin"></i>
                                Bayar {{ $pendingPhase === 'phase_2' ? 'Fase 2' : ($pendingPhase === 'phase_1' ? 'Fase 1' : 'Sekarang') }}
                            </a>
                        @endif
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('tuk.batch.detail', $batchId) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Lihat Detail Batch
                            </a>
                            
                        @if($hasVerifiedPayment)
                        <a href="{{ route('tuk.collective.payment.invoice', $batchId) }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-file-earmark-pdf"></i> Download Invoice (PDF)
                        </a>
                        @endif
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> 
                            Dibuat {{ $firstAsesmen->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTables
    $('.data-table').each(function() {
        $(this).DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            order: [
                [0, 'desc']
            ],
            pageLength: 25
        });
    });

    // Update pending payment count
    const pendingCount = $('#pending-payment .data-table tbody tr').length;
    $('#pending-payment-count').text(pendingCount);

    // Add warning badge if there are pending payments
    if (pendingCount > 0) {
        $('#pending-payment-tab').addClass('text-warning fw-bold');
    }
});
</script>
@endpush