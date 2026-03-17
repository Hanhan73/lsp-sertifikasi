@extends('layouts.app')

@section('title', 'Penetapan Biaya Batch')
@section('page-title', 'Penetapan Biaya Batch - ' . $batchId)

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <!-- Left: Batch Info & Members -->
    <div class="col-lg-8">
        <!-- Batch Info -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Batch</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150"><strong>Batch ID:</strong></td>
                                <td>{{ $batchId }}</td>
                            </tr>
                            <tr>
                                <td><strong>TUK:</strong></td>
                                <td>{{ $firstBatch->tuk->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Skema:</strong></td>
                                <td>{{ $firstBatch->skema->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Biaya Skema:</strong></td>
                                <td>Rp {{ number_format($firstBatch->skema->fee, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="180"><strong>Total Peserta:</strong></td>
                                <td>{{ $asesmens->count() }} orang</td>
                            </tr>
                            <tr>
                                <td><strong>Dengan Pelatihan:</strong></td>
                                <td>{{ $trainingCount }} orang</td>
                            </tr>
                            <tr>
                                <td><strong>Tanpa Pelatihan:</strong></td>
                                <td>{{ $noTrainingCount }} orang</td>
                            </tr>
                            <tr>
                                <td><strong>Metode Pembayaran:</strong></td>
                                <td>
                                    @if($paymentPhases === 'single')
                                    <span class="badge bg-success">1 Fase</span>
                                    @else
                                    <span class="badge bg-primary">2 Fase</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Members Table -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Daftar Peserta</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Pelatihan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesmens as $index => $asesmen)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $asesmen->full_name ?? $asesmen->user->name }}</td>
                                <td>{{ $asesmen->email }}</td>
                                <td>
                                    @if($asesmen->training_flag)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-mortarboard-fill"></i> Ya
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Fee Setup Form -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Penetapan Biaya</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.verifications.batch.process') }}" method="POST" id="fee-form">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batchId }}">

                    <!-- Mode Perhitungan -->
                    <div class="mb-3">
                        <label class="form-label">Mode Perhitungan <span class="text-danger">*</span></label>
                        <select class="form-select" name="calculation_mode" id="calculation-mode" required>
                            <option value="">-- Pilih --</option>
                            <option value="per_person">Per Orang</option>
                            <option value="total">Total Keseluruhan</option>
                        </select>
                        <small class="text-muted" id="mode-help"></small>
                    </div>

                    <!-- Biaya Asesmen -->
                    <div class="mb-3" id="asesmen-fee-group" style="display: none;">
                        <label class="form-label" id="asesmen-label">Biaya Asesmen <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control rupiah" name="asesmen_fee" id="asesmen-fee" placeholder="0">
                        </div>
                        <small class="text-muted" id="asesmen-help"></small>
                    </div>

                    <!-- Biaya Pelatihan (hanya muncul jika ada yang ikut pelatihan) -->
                    @if($trainingCount > 0)
                    <div class="mb-3" id="training-fee-group" style="display: none;">
                        <label class="form-label">Biaya Pelatihan per Orang <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control rupiah" name="training_fee" id="training-fee" value="1.500.000">
                        </div>
                        <small class="text-success">
                            <i class="bi bi-mortarboard-fill"></i> {{ $trainingCount }} peserta ikut pelatihan
                        </small>
                    </div>
                    @endif

                    <!-- Preview Total -->
                    <div class="card bg-light mb-3" id="preview-card" style="display: none;">
                        <div class="card-body">
                            <h6 class="mb-2">Preview:</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td>Total Batch:</td>
                                    <td class="text-end"><strong id="total-display">Rp 0</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Fase 1 & 2 (hanya untuk 2 fase) -->
                    @if($paymentPhases === 'two_phase')
                    <div id="phase-section" style="display: none;">
                        <h6 class="mb-3"><i class="bi bi-cash-coin"></i> Pembagian Fase</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Nominal Fase 1 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control rupiah" name="phase_1_amount" id="phase-1-amount" placeholder="0">
                            </div>
                            <small class="text-muted">Masukkan nominal Fase 1 per orang</small>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="mb-2">Breakdown per Orang:</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td>Fase 1:</td>
                                        <td class="text-end"><strong class="text-primary" id="phase1-display">Rp 0</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Fase 2:</td>
                                        <td class="text-end"><strong class="text-success" id="phase2-display">Rp 0</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="bi bi-cash-coin"></i> Tetapkan Biaya
                        </button>
                        <a href="{{ route('admin.verifications') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const totalPeserta = {{ $asesmens->count() }};
    const trainingCount = {{ $trainingCount }};
    const noTrainingCount = {{ $noTrainingCount }};
    const paymentPhases = '{{ $paymentPhases }}';

    // Mode change
    $('#calculation-mode').on('change', function() {
        const mode = $(this).val();
        
        if (!mode) {
            $('#asesmen-fee-group, #training-fee-group, #preview-card, #phase-section').hide();
            return;
        }

        $('#asesmen-fee-group').show();
        
        @if($trainingCount > 0)
        $('#training-fee-group').show();
        @endif

        if (mode === 'per_person') {
            $('#asesmen-label').text('Biaya Asesmen per Orang');
            $('#asesmen-help').text('Biaya asesmen untuk setiap peserta (belum termasuk pelatihan)');
            $('#mode-help').text('Setiap peserta bayar asesmen yang sama, pelatihan dihitung terpisah');
        } else {
            $('#asesmen-label').text('Total Biaya Asesmen Keseluruhan');
            $('#asesmen-help').text(`Total untuk ${totalPeserta} peserta (belum termasuk pelatihan)`);
            $('#mode-help').text('Total biaya asesmen dibagi rata, pelatihan dihitung terpisah');
        }

        calculate();
    });

    // Calculate on input
    $('#asesmen-fee, #training-fee, #phase-1-amount').on('input', function() {
        calculate();
    });

    function calculate() {
        const mode = $('#calculation-mode').val();
        if (!mode) return;

        const asesmenFee = parseFloat($('#asesmen-fee').val().replace(/\./g, '')) || 0;
        const trainingFee = parseFloat($('#training-fee').val().replace(/\./g, '')) || 1500000;

        let feePerPerson = 0;
        let totalBatch = 0;

        // Calculate fee per person
        if (mode === 'per_person') {
            feePerPerson = asesmenFee;
        } else { // total
            feePerPerson = asesmenFee / totalPeserta;
        }

        // Calculate total batch
        totalBatch = (feePerPerson * noTrainingCount) + ((feePerPerson + trainingFee) * trainingCount);

        $('#total-display').text('Rp ' + totalBatch.toLocaleString('id-ID'));
        $('#preview-card').show();

        // Calculate phases if two_phase
        if (paymentPhases === 'two_phase' && feePerPerson > 0) {
            $('#phase-section').show();
            
            const phase1Input = parseFloat($('#phase-1-amount').val().replace(/\./g, '')) || (feePerPerson / 2);
            const phase2Amount = feePerPerson - phase1Input;

            if (phase1Input > feePerPerson || phase1Input < 0 || phase2Amount < 0) {
                $('#phase-1-amount').addClass('is-invalid');
                $('#phase1-display').html('<span class="text-danger">Invalid</span>');
                $('#phase2-display').html('<span class="text-danger">Invalid</span>');
            } else {
                $('#phase-1-amount').removeClass('is-invalid');
                $('#phase1-display').text('Rp ' + phase1Input.toLocaleString('id-ID'));
                $('#phase2-display').text('Rp ' + phase2Amount.toLocaleString('id-ID'));
            }
        }
    }

    // Format rupiah
    $('.rupiah').on('input', function() {
        let val = $(this).val().replace(/\D/g, '');
        if (val) {
            val = parseInt(val).toLocaleString('id-ID');
        }
        $(this).val(val);
    });

    // Clean before submit
    $('#fee-form').on('submit', function() {
        $('.rupiah').each(function() {
            $(this).val($(this).val().replace(/\./g, ''));
        });
    });
});
</script>
@endpush