@extends('layouts.app')
@section('title', 'Pembayaran Sertifikasi')
@section('page-title', 'Pembayaran Sertifikasi')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

    {{-- Info Tagihan --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Tagihan Pembayaran</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td class="text-muted" width="130">Nama</td><td>: <strong>{{ $asesmen->full_name }}</strong></td></tr>
                        <tr><td class="text-muted">No. Registrasi</td><td>: #{{ $asesmen->id }}</td></tr>
                        <tr><td class="text-muted">Skema</td><td>: {{ $asesmen->skema->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">TUK</td><td>: {{ $asesmen->tuk->name ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <div class="text-muted small mb-1">Total yang harus dibayar</div>
                        <div class="fw-bold text-success" style="font-size:2rem;">
                            Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}
                        </div>
                        @if($asesmen->training_flag)
                        <div class="text-muted small">(termasuk biaya pelatihan Rp 1.500.000)</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status jika sudah pernah upload --}}
    @if($payment)
    <div class="card border-0 shadow-sm mb-4"
        style="border-left: 4px solid {{ $payment->status === 'verified' ? '#22c55e' : ($payment->status === 'rejected' ? '#ef4444' : '#f59e0b') }} !important;">
        <div class="card-body">
            
        @if($payment->status === 'verified')
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-check-circle-fill text-success fs-3"></i>
            <div>
                <div class="fw-bold text-success">Pembayaran Terverifikasi</div>
                <div class="small text-muted">{{ $payment->verified_at?->translatedFormat('d F Y, H:i') }}</div>
            </div>
        </div>
        
        {{-- Tombol aksi setelah verified --}}
        <div class="d-flex gap-2 flex-wrap mt-3">
            <a href="{{ route('asesi.payment.invoice') }}"
            class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Invoice
            </a>
            <a href="{{ route('asesi.dashboard') }}" class="btn btn-success btn-sm">
                <i class="bi bi-house me-1"></i>Kembali ke Dashboard
            </a>
        </div>

            @elseif($payment->status === 'rejected')
            <div class="d-flex align-items-start gap-3 mb-3">
                <i class="bi bi-x-circle-fill text-danger fs-3 flex-shrink-0 mt-1"></i>
                <div>
                    <div class="fw-bold text-danger">Bukti Pembayaran Ditolak</div>
                    <div class="bg-light border border-danger rounded p-2 mt-2 small">
                        <strong>Alasan:</strong> {{ $payment->rejection_notes }}
                    </div>
                    <div class="small text-muted mt-1">Silakan upload ulang bukti yang sesuai.</div>
                </div>
            </div>

            @else
            {{-- pending --}}
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-hourglass-split text-warning fs-3"></i>
                <div>
                    <div class="fw-bold">Menunggu Verifikasi Bendahara</div>
                    <div class="small text-muted">Diupload {{ $payment->created_at->translatedFormat('d F Y, H:i') }}</div>
                </div>
            </div>
            <a href="{{ route('payment.download-bukti', $payment) }}" class="btn btn-sm btn-outline-secondary mt-3">
                <i class="bi bi-download me-1"></i>Lihat Bukti yang Diupload
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Form upload — tampil jika belum ada atau ditolak --}}
    @if(!$payment || $payment->status === 'rejected')

    {{-- Info rekening --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-bank me-2 text-primary"></i>Informasi Rekening / QRIS LSP-KAP
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="fw-semibold mb-2"><i class="bi bi-bank2 text-primary me-2"></i>Transfer Bank</div>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" width="90">Bank</td><td>: <strong>BCA</strong></td></tr>
                            <tr><td class="text-muted">No. Rek</td><td>: <strong class="text-primary fs-5">1234567890</strong></td></tr>
                            <tr><td class="text-muted">A/N</td><td>: LSP Kompetensi AP</td></tr>
                        </table>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyNoRek()">
                            <i class="bi bi-copy me-1"></i>Salin No. Rekening
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100 text-center">
                        <div class="fw-semibold mb-2"><i class="bi bi-qr-code text-success me-2"></i>QRIS</div>
                        <div class="bg-light rounded p-3 d-inline-block">
                            <i class="bi bi-qr-code-scan" style="font-size:4rem;color:#6c757d;"></i>
                        </div>
                        <div class="small text-muted mt-2">Scan dengan e-wallet / m-banking apapun</div>
                    </div>
                </div>
            </div>
            <div class="alert alert-warning mt-3 mb-0 py-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <small>Pastikan nominal sesuai tagihan. Cantumkan <strong>nama dan No. Reg (#{{ $asesmen->id }})</strong> pada keterangan transfer.</small>
            </div>
        </div>
    </div>

    {{-- Form upload bukti --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-upload me-2 text-primary"></i>Upload Bukti Pembayaran
        </div>
        <div class="card-body">
            {{-- ACTION tanpa parameter --}}
            <form method="POST" action="{{ route('asesi.payment.upload-bukti') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="method" id="method-transfer"
                                value="transfer" {{ old('method','transfer') === 'transfer' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="method-transfer">
                                <i class="bi bi-bank2 me-1"></i>Transfer Bank
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="method" id="method-qris"
                                value="qris" {{ old('method') === 'qris' ? 'checked' : '' }}>
                            <label class="form-check-label" for="method-qris">
                                <i class="bi bi-qr-code me-1"></i>QRIS
                            </label>
                        </div>
                    </div>
                    @error('method')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Bukti Pembayaran <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('proof') is-invalid @enderror"
                        name="proof" accept="image/jpeg,image/png,application/pdf" required>
                    <div class="form-text">Screenshot/foto struk atau notifikasi. Format: JPG, PNG, PDF. Maks 5 MB.</div>
                    @error('proof')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Catatan (opsional)</label>
                    <input type="text" class="form-control" name="notes"
                        placeholder="Misal: transfer BCA mobile, tgl 06 April 2026"
                        value="{{ old('notes') }}" maxlength="500">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Upload & Kirim ke Bendahara
                    </button>
                    <a href="{{ route('asesi.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    @endif

</div>
</div>
@endsection

@push('scripts')
<script>
function copyNoRek() {
    navigator.clipboard.writeText('1234567890').then(() => {
        Swal.fire({ icon: 'success', title: 'Tersalin!', text: 'No. rekening berhasil disalin.', timer: 1500, showConfirmButton: false });
    });
}
</script>
@endpush