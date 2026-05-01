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
        <div class="d-flex gap-2 flex-wrap mt-3">
            <a href="{{ route('asesi.payment.invoice') }}" class="btn btn-outline-primary btn-sm">
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
            <i class="bi bi-bank me-2 text-primary"></i>Informasi Rekening Pembayaran
        </div>
        <div class="card-body">
            <div class="border rounded-3 p-4 bg-light">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:48px;height:48px;background:#1a5276;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-bank2 text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-6">Bank Syariah Indonesia (BSI)</div>
                        <div class="text-muted small">KCP Bandung UPI</div>
                    </div>
                </div>

                <table class="table table-borderless table-sm mb-3">
                    <tr>
                        <td class="text-muted fw-semibold" style="width:140px;">No. Rekening</td>
                        <td>
                            <span class="fw-bold text-primary fs-5">1619161919</span>
                            <button class="btn btn-sm btn-outline-primary ms-2 py-0 px-2"
                                    style="font-size:.8rem;"
                                    onclick="copyNoRek()">
                                <i class="bi bi-copy me-1"></i>Salin
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Atas Nama</td>
                        <td class="fw-bold">LSP-KAP (LSP Kompetensi Administrasi Perkantoran)</td>
                    </tr>
                </table>

                <div class="alert alert-warning mb-0 py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <small>
                        Pastikan nominal sesuai tagihan
                        <strong>Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</strong>.
                        Cantumkan <strong>nama dan No. Reg (#{{ $asesmen->id }})</strong>
                        pada keterangan/berita transfer.
                    </small>
                </div>
            </div>

            {{-- Kontak Admin --}}
            <div class="mt-3 p-3 border rounded-3 d-flex align-items-center gap-3">
                <i class="bi bi-whatsapp text-success fs-4 flex-shrink-0"></i>
                <div>
                    <div class="fw-semibold small">Ada pertanyaan? Hubungi Admin LSP</div>
                    <div class="text-muted small">
                        Anggriawan Oktobisono &nbsp;·&nbsp;
                        <a href="https://wa.me/6285867219139" target="_blank" class="text-success fw-semibold">
                            0858-6721-9139
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form upload bukti --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-upload me-2 text-primary"></i>Upload Bukti Transfer
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('asesi.payment.upload-bukti') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="method" value="transfer">

                <div class="mb-3">
                    <label class="form-label">Bukti Transfer <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('proof') is-invalid @enderror"
                        name="proof" accept="image/jpeg,image/png,application/pdf" required>
                    <div class="form-text">
                        Screenshot/foto struk atau notifikasi transfer. Format: JPG, PNG, atau PDF. Maks 5 MB.
                    </div>
                    @error('proof')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Catatan (opsional)</label>
                    <input type="text" class="form-control" name="notes"
                        placeholder="Misal: Transfer BSI mobile, tgl 01 Mei 2026"
                        value="{{ old('notes') }}" maxlength="500">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Kirim Bukti Transfer
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
    navigator.clipboard.writeText('1619161919').then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Tersalin!',
            text: 'No. rekening BSI berhasil disalin.',
            timer: 1500,
            showConfirmButton: false
        });
    });
}
</script>
@endpush