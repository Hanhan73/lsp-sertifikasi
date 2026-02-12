@extends('layouts.app')

@section('title', 'Penetapan Biaya')
@section('page-title', 'Penetapan Biaya Sertifikasi')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-check"></i> Asesi Kolektif Menunggu Penetapan Biaya</h5>
        <div>
            <span class="badge bg-warning">{{ $asesmens->count() }} Perlu Penetapan Biaya</span>
        </div>
    </div>
    <div class="card-body">
        <!-- Info about mandiri auto-verification -->
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Catatan Penting:</strong>
            <ul class="mb-0 mt-2">
                <li><strong>Asesi Mandiri:</strong> Biaya ditetapkan <strong>otomatis</strong> dari skema (tidak perlu
                    verifikasi manual)</li>
                <li><strong>Asesi Kolektif:</strong> Memerlukan penetapan biaya manual oleh Admin LSP (ditampilkan di
                    bawah)</li>
            </ul>
        </div>

        @if($asesmens->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
            <h4 class="mt-3">Semua Asesi Kolektif Sudah Ditetapkan Biaya</h4>
            <p class="text-muted">Tidak ada data asesi kolektif yang menunggu penetapan biaya saat ini.</p>
            <p class="text-muted small mt-2">
                <i class="bi bi-lightbulb"></i>
                Asesi mandiri akan otomatis terverifikasi dengan biaya dari skema yang dipilih.
            </p>
        </div>
        @else
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3" id="filterTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    Semua <span class="badge bg-secondary ms-1">{{ $asesmens->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="single-phase-tab" data-bs-toggle="tab" data-bs-target="#single-phase"
                    type="button">
                    1 Fase <span
                        class="badge bg-success ms-1">{{ $asesmens->where('payment_phases', 'single')->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="two-phase-tab" data-bs-toggle="tab" data-bs-target="#two-phase"
                    type="button">
                    2 Fase <span
                        class="badge bg-primary ms-1">{{ $asesmens->where('payment_phases', 'two_phase')->count() }}</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="filterTabsContent">
            <!-- All -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                @include('admin.verifications.table', ['data' => $asesmens])
            </div>

            <!-- Single Phase -->
            <div class="tab-pane fade" id="single-phase" role="tabpanel">
                @include('admin.verifications.table', ['data' => $asesmens->where('payment_phases', 'single')])
            </div>

            <!-- Two Phase -->
            <div class="tab-pane fade" id="two-phase" role="tabpanel">
                @include('admin.verifications.table', ['data' => $asesmens->where('payment_phases', 'two_phase')])
            </div>
        </div>

        <!-- Batch Fee Setup Section -->
        @php
        $collectiveBatches = $asesmens
        ->whereNotNull('collective_batch_id')
        ->groupBy('collective_batch_id');
        @endphp

        @if($collectiveBatches->count() > 0)
        <hr>
        <h6><i class="bi bi-layers"></i> Penetapan Biaya Batch Kolektif</h6>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Anda dapat menetapkan biaya untuk seluruh batch sekaligus dengan harga yang sama untuk semua peserta dalam
            batch.
        </div>

        <div class="accordion" id="batchAccordion">
            @foreach($collectiveBatches as $batchId => $batchAsesmens)
            @php
            $firstBatch = $batchAsesmens->first();
            $hasTraining = $batchAsesmens->where('training_flag', true)->count();
            $recommendedFee = ($firstBatch->skema->fee ?? 0) + ($hasTraining > 0 ? 1500000 : 0);
            @endphp
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#batch-{{ md5($batchId) }}">
                        <strong>{{ $batchId }}</strong>
                        <span class="badge bg-primary ms-2">{{ $batchAsesmens->count() }} peserta</span>
                        <span class="badge bg-info ms-2">{{ $firstBatch->tuk->name ?? '-' }}</span>
                        <span class="badge bg-success ms-2">
                            <i class="bi bi-check-circle"></i> Diverifikasi TUK
                        </span>
                        <span
                            class="badge bg-{{ $firstBatch->payment_phases === 'single' ? 'success' : 'primary' }} ms-2">
                            {{ $firstBatch->payment_phases === 'single' ? '1 Fase' : '2 Fase' }}
                        </span>
                        @if($hasTraining > 0)
                        <span class="badge bg-warning text-dark ms-2">
                            <i class="bi bi-mortarboard-fill"></i> {{ $hasTraining }} Ikut Pelatihan
                        </span>
                        @endif
                    </button>
                </h2>
                <div id="batch-{{ md5($batchId) }}" class="accordion-collapse collapse"
                    data-bs-parent="#batchAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Skema</th>
                                        <th>Pelatihan</th>
                                        <th>Verifikasi TUK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batchAsesmens as $asesmen)
                                    <tr>
                                        <td>{{ $asesmen->full_name ?? $asesmen->user->name }}</td>
                                        <td>{{ $asesmen->email }}</td>
                                        <td>{{ $asesmen->skema->name ?? '-' }}</td>
                                        <td>
                                            @if($asesmen->training_flag)
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-mortarboard-fill"></i> Ya
                                            </span>
                                            @else
                                            <span class="badge bg-secondary">Tidak</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check"></i>
                                                {{ $asesmen->tuk_verified_at->format('d/m/Y') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Payment Phases Info -->
                        @if($firstBatch->payment_phases === 'two_phase')
                        <div class="alert alert-primary mb-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Pembayaran 2 Fase:</strong> TUK akan membayar 50% setelah penetapan biaya, dan 50%
                            lagi setelah asesmen selesai.
                        </div>
                        @else
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Pembayaran 1 Fase:</strong> TUK akan membayar 100% sekaligus setelah penetapan
                            biaya.
                        </div>
                        @endif

                        <form action="{{ route('admin.verifications.batch') }}" method="POST" class="row g-3">
                            @csrf
                            <input type="hidden" name="batch_id" value="{{ $batchId }}">

                            <div class="col-md-6">
                                <label class="form-label">Biaya per Peserta <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control rupiah batch-fee-input" name="fee_amount"
                                        required placeholder="Contoh: 500.000" value="{{ $recommendedFee }}"
                                        data-count="{{ $batchAsesmens->count() }}"
                                        data-phases="{{ $firstBatch->payment_phases }}">
                                </div>
                                <small class="text-muted">
                                    Rekomendasi: Rp {{ number_format($recommendedFee, 0, ',', '.') }}
                                    @if($hasTraining > 0)
                                    (Termasuk {{ $hasTraining }} peserta dengan pelatihan)
                                    @endif
                                </small>
                                <br>
                                <small class="text-muted">
                                    Total untuk {{ $batchAsesmens->count() }} peserta: <span
                                        class="total-batch-fee fw-bold">Rp 0</span>
                                </small>
                                @if($firstBatch->payment_phases === 'two_phase')
                                <br>
                                <small class="text-primary">
                                    <i class="bi bi-cash-coin"></i> Fase 1: <span class="phase-1-fee fw-bold">Rp
                                        0</span> | Fase 2: <span class="phase-2-fee fw-bold">Rp 0</span>
                                </small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" name="notes" rows="3"
                                    placeholder="Catatan penetapan biaya"></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-cash-coin"></i> Tetapkan Biaya Batch ({{ $batchAsesmens->count() }}
                                    peserta)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable for each tab
    $('.data-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        order: [
            [0, 'asc']
        ],
        pageLength: 25
    });

    // Calculate batch total and phases
    $('.batch-fee-input').on('input', function() {
        const fee = parseFloat($(this).val()) || 0;
        const count = parseInt($(this).data('count'));
        const phases = $(this).data('phases');
        const accordionBody = $(this).closest('.accordion-body');
        const total = fee * count;

        accordionBody.find('.total-batch-fee').text('Rp ' + total.toLocaleString('id-ID'));

        if (phases === 'two_phase') {
            const phase1 = fee / 2;
            const phase2 = fee / 2;
            accordionBody.find('.phase-1-fee').text('Rp ' + phase1.toLocaleString('id-ID'));
            accordionBody.find('.phase-2-fee').text('Rp ' + phase2.toLocaleString('id-ID'));
        }
    });

    // Trigger on load
    $('.batch-fee-input').trigger('input');
});
</script>
@endpush