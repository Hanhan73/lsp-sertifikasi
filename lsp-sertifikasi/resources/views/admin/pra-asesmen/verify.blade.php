@extends('layouts.app')

@section('title', 'Penetapan Biaya')
@section('page-title', 'Penetapan Biaya - ' . ($asesmen->full_name ?? $asesmen->user->name))

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <!-- Left Column - Asesi Data -->
    <div class="col-lg-8">
        <!-- TUK Verification Status -->
        <div class="card mb-3 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Status Verifikasi TUK</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1">
                            <strong>Diverifikasi oleh:</strong>
                            {{ $asesmen->tukVerifier->name ?? '-' }} ({{ $asesmen->tuk->name ?? '-' }})
                        </p>
                        <p class="mb-1">
                            <strong>Tanggal Verifikasi:</strong>
                            {{ $asesmen->tuk_verified_at->format('d F Y H:i') }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        @if($asesmen->tuk_verification_notes)
                        <p class="mb-1"><strong>Catatan TUK:</strong></p>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-chat-left-quote"></i> {{ $asesmen->tuk_verification_notes }}
                        </div>
                        @else
                        <p class="text-muted mb-0">Tidak ada catatan dari TUK</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Info -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pendaftaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>No. Registrasi</strong></td>
                                <td>: #{{ $asesmen->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Daftar</strong></td>
                                <td>: {{ $asesmen->registration_date->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Type</strong></td>
                                <td>: <span class="badge bg-primary">Kolektif</span></td>
                            </tr>
                            <tr>
                                <td><strong>Batch ID</strong></td>
                                <td>: {{ $asesmen->collective_batch_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Metode Bayar</strong></td>
                                <td>:
                                    @if($asesmen->payment_phases === 'single')
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
                            <tr>
                                <td><strong>Didaftarkan Oleh</strong></td>
                                <td>: {{ $asesmen->registrar->name ?? '-' }} ({{ $asesmen->tuk->name ?? '-' }})</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>TUK</strong></td>
                                <td>: {{ $asesmen->tuk->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Skema</strong></td>
                                <td>: {{ $asesmen->skema->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Biaya Skema</strong></td>
                                <td>: Rp {{ number_format($asesmen->skema->fee ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pelatihan</strong></td>
                                <td>:
                                    @if($asesmen->training_flag)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-mortarboard-fill"></i> Ya (+Rp 1.500.000)
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Pilihan</strong></td>
                                <td>: {{ $asesmen->preferred_date ? $asesmen->preferred_date->format('d F Y') : '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Data -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Data Pribadi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Nama Lengkap</strong></td>
                                <td>: {{ $asesmen->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>NIK</strong></td>
                                <td>: {{ $asesmen->nik }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tempat Lahir</strong></td>
                                <td>: {{ $asesmen->birth_place }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Lahir</strong></td>
                                <td>: {{ $asesmen->birth_date->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin</strong></td>
                                <td>: {{ $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $asesmen->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Telepon</strong></td>
                                <td>: {{ $asesmen->phone }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Alamat</strong></td>
                                <td>: {{ $asesmen->address }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pendidikan</strong></td>
                                <td>: {{ $asesmen->education }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pekerjaan</strong></td>
                                <td>: {{ $asesmen->occupation }}</td>
                            </tr>
                            <tr>
                                <td><strong>Sumber Anggaran</strong></td>
                                <td>: {{ $asesmen->budget_source }}</td>
                            </tr>
                            <tr>
                                <td><strong>Asal Lembaga</strong></td>
                                <td>: {{ $asesmen->institution }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark"></i> Dokumen (Sudah Diverifikasi TUK)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h6>Pas Foto</h6>
                        @if($asesmen->photo_path)
                        <img src="{{ asset('storage/' . $asesmen->photo_path) }}" alt="Foto" class="img-thumbnail mb-2"
                            style="max-height: 200px;">
                        <br>
                        <a href="{{ asset('storage/' . $asesmen->photo_path) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Lihat
                        </a>
                        @else
                        <p class="text-muted">-</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-center">
                        <h6>KTP</h6>
                        @if($asesmen->ktp_path)
                        <iframe src="{{ asset('storage/' . $asesmen->ktp_path) }}"
                            style="width: 100%; height: 200px; border: 1px solid #ddd;" class="mb-2"></iframe>
                        <br>
                        <a href="{{ asset('storage/' . $asesmen->ktp_path) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                        @else
                        <p class="text-muted">-</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-center">
                        <h6>Ijazah/Transkrip</h6>
                        @if($asesmen->document_path)
                        <iframe src="{{ asset('storage/' . $asesmen->document_path) }}"
                            style="width: 100%; height: 200px; border: 1px solid #ddd;" class="mb-2"></iframe>
                        <br>
                        <a href="{{ asset('storage/' . $asesmen->document_path) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                        @else
                        <p class="text-muted">-</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Fee Setup Form -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Penetapan Biaya</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <strong>Data Sudah Diverifikasi TUK</strong>
                    <p class="mb-0 mt-2 small">
                        TUK telah memverifikasi kelengkapan dan keabsahan data asesi ini. Silakan tetapkan biaya
                        sertifikasi.
                    </p>
                </div>

                <!-- Payment Phases Info -->
                @if($asesmen->payment_phases === 'two_phase')
                <div class="alert alert-primary">
                    <i class="bi bi-info-circle"></i>
                    <strong>Pembayaran 2 Fase</strong>
                    <p class="mb-0 mt-2 small">
                        TUK akan membayar dalam 2 tahap dengan nominal yang Anda tentukan.
                    </p>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Pembayaran 1 Fase</strong>
                    <p class="mb-0 mt-2 small">
                        TUK akan membayar <strong>100% sekaligus</strong> setelah penetapan biaya ini.
                    </p>
                </div>
                @endif

                <form action="{{ route('admin.praasesmen.process', $asesmen) }}" method="POST">
                    @csrf

                    @php
                        // ✅ CALCULATE DEFAULT FEE (termasuk pelatihan)
                        $baseFee = $asesmen->skema->fee ?? 0;
                        $trainingFee = $asesmen->training_flag ? 1500000 : 0;
                        $defaultFee = $baseFee + $trainingFee;
                    @endphp

                    <div class="mb-3">
                        <label class="form-label">Biaya Skema (Referensi)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control"
                                value="{{ number_format($baseFee, 0, ',', '.') }}" readonly>
                        </div>
                        <small class="text-muted">Harga default dari skema {{ $asesmen->skema->name ?? '-' }}</small>
                    </div>

                    @if($asesmen->training_flag)
                    <div class="mb-3">
                        <label class="form-label">Biaya Pelatihan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" value="{{ number_format(1500000, 0, ',', '.') }}"
                                readonly>
                        </div>
                        <small class="text-success">
                            <i class="bi bi-mortarboard-fill"></i> Peserta ikut pelatihan
                        </small>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Total Rekomendasi</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control"
                                value="{{ number_format($defaultFee, 0, ',', '.') }}"
                                readonly>
                        </div>
                        <small class="text-muted">
                            @if($asesmen->training_flag)
                            Skema + Pelatihan
                            @else
                            Biaya Skema
                            @endif
                        </small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Biaya yang Ditetapkan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control rupiah @error('fee_amount') is-invalid @enderror"
                                name="fee_amount" id="fee_amount" required
                                value="{{ old('fee_amount', number_format($defaultFee, 0, ',', '.')) }}"
                                placeholder="Masukkan biaya">
                        </div>
                        <small class="text-muted">Anda dapat menyesuaikan biaya sesuai kebijakan LSP</small>
                        @error('fee_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($asesmen->payment_phases === 'two_phase')
                    <div class="mb-3">
                        <label class="form-label">Nominal Fase 1 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" 
                                class="form-control rupiah" 
                                name="phase_1_amount" 
                                id="phase_1_amount"
                                value="{{ old('phase_1_amount', number_format($defaultFee / 2, 0, ',', '.')) }}"
                                placeholder="0">
                        </div>
                        <small class="text-muted">Masukkan nominal Fase 1 (sisanya otomatis jadi Fase 2)</small>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="mb-2">Breakdown Pembayaran:</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td>Fase 1:</td>
                                    <td class="text-end"><strong class="text-primary" id="phase_1_display">Rp 0</strong></td>
                                </tr>
                                <tr>
                                    <td>Fase 2:</td>
                                    <td class="text-end"><strong class="text-success" id="phase_2_display">Rp 0</strong></td>
                                </tr>
                                <tr class="table-light">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-end"><strong id="total_display">Rp 0</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Catatan Penetapan Biaya (Opsional)</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Catatan untuk penetapan biaya ini">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                Saya telah memeriksa verifikasi TUK dan menetapkan biaya dengan tepat
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-lg text-dark">
                            <i class="bi bi-cash-coin"></i> Tetapkan Biaya
                        </button>
                        <a href="{{ route('admin.praasesmen.index') }}" class="btn btn-secondary">
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
    const paymentPhases = '{{ $asesmen->payment_phases }}';

    function updatePhaseBreakdown() {
        // Get values without formatting
        const totalFeeRaw = $('#fee_amount').val().replace(/\./g, '');
        const phase1AmountRaw = $('#phase_1_amount').val().replace(/\./g, '');
        
        const totalFee = parseFloat(totalFeeRaw) || 0;
        const phase1Amount = parseFloat(phase1AmountRaw) || (totalFee / 2);
        const phase2Amount = totalFee - phase1Amount;

        if (paymentPhases === 'two_phase') {
            // Validation
            if (phase1Amount > totalFee) {
                $('#phase_1_amount').addClass('is-invalid');
                $('#phase_1_display').html('<span class="text-danger">Tidak valid!</span>');
                $('#phase_2_display').html('<span class="text-danger">-</span>');
                $('#total_display').html('<span class="text-danger">-</span>');
                return;
            } else if (phase1Amount < 0 || phase2Amount < 0) {
                $('#phase_1_amount').addClass('is-invalid');
                $('#phase_1_display').html('<span class="text-danger">Tidak valid!</span>');
                $('#phase_2_display').html('<span class="text-danger">-</span>');
                $('#total_display').html('<span class="text-danger">-</span>');
                return;
            } else {
                $('#phase_1_amount').removeClass('is-invalid');
            }

            $('#phase_1_display').text('Rp ' + Math.round(phase1Amount).toLocaleString('id-ID'));
            $('#phase_2_display').text('Rp ' + Math.round(phase2Amount).toLocaleString('id-ID'));
            $('#total_display').text('Rp ' + Math.round(totalFee).toLocaleString('id-ID'));
        }
    }

    // Format rupiah on input
    $('.rupiah').on('input', function() {
        let val = $(this).val().replace(/\D/g, '');
        const cursorPos = this.selectionStart;
        const oldLength = $(this).val().length;
        
        if (val) {
            val = parseInt(val).toLocaleString('id-ID');
        }
        $(this).val(val);
        
        // Restore cursor position
        const newLength = val.length;
        const newPos = cursorPos + (newLength - oldLength);
        this.setSelectionRange(newPos, newPos);
        
        // Update breakdown
        updatePhaseBreakdown();
    });
    
    // Initial update
    updatePhaseBreakdown();
    
    // Before form submit, clean the values
    $('form').on('submit', function() {
        $('#fee_amount').val($('#fee_amount').val().replace(/\./g, ''));
        if ($('#phase_1_amount').length) {
            $('#phase_1_amount').val($('#phase_1_amount').val().replace(/\./g, ''));
        }
    });
});
</script>
@endpush