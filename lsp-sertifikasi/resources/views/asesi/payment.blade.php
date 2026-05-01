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
@php
    $buktiUrl = $payment->proof_path
        ? route('payment.download-bukti', $payment)
        : null;
    $buktiExt = $payment->proof_path
        ? strtolower(pathinfo($payment->proof_path, PATHINFO_EXTENSION))
        : null;
    $buktiIsImage = in_array($buktiExt, ['jpg','jpeg','png']);
@endphp
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

    {{-- Preview bukti --}}
    @if($buktiUrl)
    <div class="mt-3">
        @if($buktiIsImage)
        <div class="border rounded-3 overflow-hidden" style="max-width:340px;">
            <div style="cursor:zoom-in;"
                 onclick="bukaZoomBukti('{{ $buktiUrl }}', 'Bukti Transfer')">
                <img src="{{ $buktiUrl }}"
                     class="w-100"
                     style="max-height:180px;object-fit:cover;transition:opacity .2s;"
                     onmouseover="this.style.opacity='.85'"
                     onmouseout="this.style.opacity='1'"
                     alt="Bukti Transfer">
            </div>
            <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between"
                 style="font-size:.75rem;">
                <span class="text-muted"><i class="bi bi-receipt me-1"></i>Bukti Transfer</span>
                <div class="d-flex gap-1">
                    <button class="btn btn-outline-secondary py-0 px-1" style="font-size:.7rem;"
                            onclick="bukaZoomBukti('{{ $buktiUrl }}', 'Bukti Transfer')"
                            title="Perbesar">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <a href="{{ $buktiUrl }}?download=1"
                       class="btn btn-outline-primary py-0 px-1" style="font-size:.7rem;"
                       title="Download">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="d-flex align-items-center gap-2 p-2 border rounded-3" style="background:#fff5f5;max-width:280px;">
            <i class="bi bi-file-earmark-pdf text-danger fs-3 flex-shrink-0"></i>
            <div>
                <div class="small fw-semibold">Bukti Transfer (PDF)</div>
                <a href="{{ $buktiUrl }}?download=1" class="btn btn-sm btn-outline-primary py-0 mt-1" style="font-size:.75rem;">
                    <i class="bi bi-download me-1"></i>Download
                </a>
            </div>
        </div>
        @endif
    </div>
    @endif

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

    {{-- Preview bukti yang ditolak --}}
    @if($buktiUrl)
    <div class="mb-3">
        @if($buktiIsImage)
        <div class="border rounded-3 overflow-hidden" style="max-width:340px;">
            <div style="cursor:zoom-in;"
                 onclick="bukaZoomBukti('{{ $buktiUrl }}', 'Bukti Transfer (Ditolak)')">
                <img src="{{ $buktiUrl }}"
                     class="w-100"
                     style="max-height:180px;object-fit:cover;opacity:.7;transition:opacity .2s;"
                     onmouseover="this.style.opacity='1'"
                     onmouseout="this.style.opacity='.7'"
                     alt="Bukti Transfer">
            </div>
            <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between"
                 style="font-size:.75rem;">
                <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Bukti Ditolak</span>
                <button class="btn btn-outline-secondary py-0 px-1" style="font-size:.7rem;"
                        onclick="bukaZoomBukti('{{ $buktiUrl }}', 'Bukti Transfer (Ditolak)')"
                        title="Perbesar">
                    <i class="bi bi-zoom-in"></i>
                </button>
            </div>
        </div>
        @endif
    </div>
    @endif

    @else
    {{-- Pending --}}
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-hourglass-split text-warning fs-3"></i>
        <div>
            <div class="fw-bold">Menunggu Verifikasi Bendahara</div>
            <div class="small text-muted">Diupload {{ $payment->created_at->translatedFormat('d F Y, H:i') }}</div>
        </div>
    </div>

    {{-- Preview bukti pending --}}
    @if($buktiUrl)
    <div class="mt-3">
        @if($buktiIsImage)
        <div class="border rounded-3 overflow-hidden" style="max-width:340px;">
            <div style="cursor:zoom-in;"
                 onclick="bukaZoomBukti('{{ $buktiUrl }}', 'Bukti Transfer')">
                <img src="{{ $buktiUrl }}"
                     class="w-100"
                     style="max-height:180px;object-fit:cover;transition:opacity .2s;"
                     onmouseover="this.style.opacity='.85'"
                     onmouseout="this.style.opacity='1'"
                     alt="Bukti Transfer">
            </div>
            <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between"
                 style="font-size:.75rem;">
                <span class="text-muted"><i class="bi bi-receipt me-1"></i>Bukti Transfer</span>
                <div class="d-flex gap-1">
                    <button class="btn btn-outline-secondary py-0 px-1" style="font-size:.7rem;"
                            onclick="bukaZoomBukti('{{ $buktiUrl }}', 'Bukti Transfer')"
                            title="Perbesar">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <a href="{{ $buktiUrl }}?download=1"
                       class="btn btn-outline-primary py-0 px-1" style="font-size:.7rem;"
                       title="Download">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="d-flex align-items-center gap-2 p-2 border rounded-3" style="background:#fff5f5;max-width:280px;">
            <i class="bi bi-file-earmark-pdf text-danger fs-3 flex-shrink-0"></i>
            <div>
                <div class="small fw-semibold">Bukti Transfer (PDF)</div>
                <a href="{{ $buktiUrl }}?download=1" class="btn btn-sm btn-outline-primary py-0 mt-1" style="font-size:.75rem;">
                    <i class="bi bi-download me-1"></i>Download
                </a>
            </div>
        </div>
        @endif
    </div>
    @endif

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

{{-- Modal Zoom Bukti --}}
<div class="modal fade" id="modalZoomBuktiPayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-header border-0 pb-1 px-0">
                <span class="text-white fw-semibold" id="zoomBuktiPaymentLabel" style="font-size:.9rem;"></span>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="zoomBuktiPaymentImg" src="" alt="Bukti Transfer"
                     class="img-fluid rounded-3 shadow"
                     style="max-height:85vh;object-fit:contain;">
            </div>
            <div class="modal-footer border-0 justify-content-center pt-2">
                <a id="zoomBuktiPaymentDownload" href="#"
                   class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i>Unduh
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
#modalZoomBuktiPayment .modal-dialog { max-width:90vw; }
#modalZoomBuktiPayment { background:rgba(0,0,0,.85); }
</style>

<script>
function bukaZoomBukti(src, label) {
    document.getElementById('zoomBuktiPaymentImg').src        = src;
    document.getElementById('zoomBuktiPaymentLabel').textContent = label;
    document.getElementById('zoomBuktiPaymentDownload').href  = src + '?download=1';
    bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalZoomBuktiPayment')
    ).show();
}

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