@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Pembayaran Kolektif')

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('tuk.invoice-kolektif.index') }}">Invoice Kolektif</a>
        </li>
        <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
    </ol>
</nav>

{{-- Stat cards --}}
@php
    $totalLunas   = $payments->where('status','verified')->sum('amount');
    $totalPending = $payments->where('status','pending')->sum('amount');
    $sisaTagihan  = $invoice->total_amount - $totalLunas;
    $jumlahAng    = $payments->count();
    $pct          = $invoice->total_amount > 0 ? min(100, round($totalLunas / $invoice->total_amount * 100)) : 0;
    $bisaTambah   = $jumlahAng < 3 && $sisaTagihan > 0 && $invoice->status !== 'draft';
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Status Invoice</div>
                <span class="badge bg-{{ $invoice->status_badge }} fs-6">{{ $invoice->status_label }}</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Tagihan</div>
                <div class="fw-bold text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Terbayar</div>
                <div class="fw-bold text-success">Rp {{ number_format($totalLunas, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Sisa Tagihan</div>
                <div class="fw-bold {{ $sisaTagihan > 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ Rincian Invoice ══════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Rincian Invoice</h6>
        <a href="{{ route('tuk.invoice-kolektif.pdf', $invoice) }}" target="_blank"
           class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-pdf"></i> Download PDF
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <small class="text-muted">Dari:</small>
                <div class="fw-bold">LSP Kompetensi Administrasi Perkantoran</div>
                <small class="text-muted">Tanggal: {{ $invoice->issued_at->translatedFormat('d F Y') }}</small>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info small py-2 mb-0">
                    <i class="bi bi-bank me-1"></i>
                    Transfer ke <strong>BSI No. 1619161919</strong> a.n. LSP-KAP<br>
                    Kontak: <strong>085867219139</strong> (Anggriawan)
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Skema</th>
                        <th class="text-center">Jumlah Asesi</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $item['skema_name'] }}</td>
                        <td class="text-center">{{ $item['jumlah'] }} orang</td>
                        <td class="text-end">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold text-primary">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ══ Riwayat Angsuran ════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0"><i class="bi bi-list-check"></i> Riwayat Pembayaran</h6>
        @if($bisaTambah)
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAngsuran">
            <i class="bi bi-plus-circle"></i> Tambah Pembayaran
        </button>
        @elseif($invoice->status === 'draft')
        <span class="text-muted small fst-italic">Invoice belum dikirim bendahara</span>
        @endif
    </div>

    <div class="px-3 pt-3">
        <div class="progress" style="height:8px;">
            <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted mt-1">
            <span>{{ $pct }}% lunas</span>
            @if($totalPending > 0)
            <span class="text-warning">Rp {{ number_format($totalPending, 0, ',', '.') }} menunggu verifikasi</span>
            @endif
        </div>
    </div>

    <div class="card-body">
        @if($payments->isEmpty())
        <p class="text-muted text-center py-3 mb-0">
            @if($invoice->status === 'draft')
                Invoice masih dalam status Draft. Bendahara LSP akan segera mengirimkan invoice.
            @else
                Belum ada pembayaran. Klik "Tambah Pembayaran" untuk melakukan pembayaran pertama.
            @endif
        </p>
        @else
        @foreach($payments as $cp)
        @php
            $ext     = strtolower(pathinfo($cp->proof_path ?? '', PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg','jpeg','png']);
            $fileUrl = $cp->proof_path ? route('tuk.invoice-kolektif.bukti', $cp) : null;
        @endphp
        <div class="card border mb-3 {{ $cp->status === 'verified' ? 'border-success' : ($cp->status === 'rejected' ? 'border-danger' : 'border-warning') }}">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    Angsuran ke-{{ $cp->installment_number }}
                    &mdash;
                    <strong>Rp {{ number_format($cp->amount, 0, ',', '.') }}</strong>
                </span>
                <span class="badge bg-{{ $cp->status_badge }}">{{ $cp->status_label }}</span>
            </div>
            <div class="card-body py-3">
                <div class="row g-3 align-items-start">

                    {{-- Kolom 1: Info --}}
                    <div class="col-md-4">
                        @if($cp->due_date)
                        <small class="text-muted d-block">Tanggal Transfer:</small>
                        <div class="mb-1">{{ $cp->due_date->translatedFormat('d M Y') }}</div>
                        @endif
                        @if($cp->notes)
                        <small class="text-muted d-block">Catatan:</small>
                        <div class="small mb-1">{{ $cp->notes }}</div>
                        @endif
                        <small class="text-muted d-block mt-1">
                            Diupload: {{ $cp->proof_uploaded_at?->translatedFormat('d M Y H:i') ?? '-' }}
                        </small>
                    </div>

                    {{-- Kolom 2: Status verifikasi --}}
                    <div class="col-md-4">
                        @if($cp->status === 'verified')
                        <div class="text-success">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            <strong>Terverifikasi</strong><br>
                            <small>{{ $cp->verified_at?->translatedFormat('d M Y') }}</small>
                        </div>
                        @elseif($cp->status === 'rejected')
                        <div class="text-danger">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            <strong>Ditolak</strong><br>
                            @if($cp->rejection_notes)
                            <small>Alasan: {{ $cp->rejection_notes }}</small>
                            @endif
                        </div>
                        @else
                        <div class="text-warning">
                            <i class="bi bi-clock me-1"></i>
                            Menunggu verifikasi bendahara
                        </div>
                        @endif
                    </div>

                   {{-- Kolom 3: Bukti bayar --}}
                    <div class="col-md-4">
                        @if($cp->status === 'rejected')
                        <form action="{{ route('tuk.invoice-kolektif.upload-bukti', $cp) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label small fw-semibold mb-1 text-danger">
                                <i class="bi bi-arrow-clockwise me-1"></i>Upload Ulang Bukti
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="file" name="proof" class="form-control form-control-sm"
                                    accept=".jpg,.jpeg,.png,.pdf" required>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-upload"></i>
                                </button>
                            </div>
                            <small class="text-muted">JPG/PNG/PDF, maks 5MB</small>
                        </form>

                        @elseif($fileUrl)
                        @if($isImage)
                        {{-- Inline preview + klik zoom --}}
                        <div class="border rounded-3 overflow-hidden">
                            <div style="cursor:zoom-in;"
                                onclick="bukaZoomBukti('{{ $fileUrl }}', 'Bukti Angsuran ke-{{ $cp->installment_number }}', '{{ $fileUrl }}?download=1')">
                                <img src="{{ $fileUrl }}"
                                    class="w-100"
                                    style="max-height:160px;object-fit:cover;transition:opacity .2s;"
                                    onmouseover="this.style.opacity='.85'"
                                    onmouseout="this.style.opacity='1'"
                                    alt="Bukti Angsuran ke-{{ $cp->installment_number }}">
                            </div>
                            <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between"
                                style="font-size:.75rem;">
                                <span class="text-muted">
                                    <i class="bi bi-image me-1"></i>Bukti ke-{{ $cp->installment_number }}
                                </span>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-secondary py-0 px-1"
                                            style="font-size:.7rem;"
                                            onclick="bukaZoomBukti('{{ $fileUrl }}', 'Bukti Angsuran ke-{{ $cp->installment_number }}', '{{ $fileUrl }}?download=1')"
                                            title="Zoom">
                                        <i class="bi bi-zoom-in"></i>
                                    </button>
                                    <a href="{{ $fileUrl }}?download=1"
                                    class="btn btn-outline-primary py-0 px-1"
                                    style="font-size:.7rem;"
                                    title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        @else
                        {{-- PDF --}}
                        <div class="d-flex align-items-center gap-2 p-2 border rounded-3"
                            style="background:#fff5f5">
                            <i class="bi bi-file-earmark-pdf text-danger fs-3 flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">Bukti Bayar (PDF)</div>
                                <small class="text-muted">Angsuran ke-{{ $cp->installment_number }}</small>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <a href="{{ $fileUrl }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary py-0 px-2"
                                style="font-size:.75rem;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ $fileUrl }}?download=1"
                                class="btn btn-sm btn-outline-primary py-0 px-2"
                                style="font-size:.75rem;">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                        @endif

                        @else
                        <span class="text-muted small fst-italic">Belum ada bukti bayar</span>
                        @endif
                    </div>

                </div>
            </div>
        </div>
        @endforeach
        @endif
    </div>
</div>

{{-- ══ Daftar Asesi ════════════════════════════════════════════════════ --}}
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-people"></i> Asesi dalam Invoice ini</h6>
        <span class="badge bg-secondary">{{ $asesmens->count() }} orang</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th><th>Nama</th><th>Skema</th><th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesmens as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $a->full_name }}</td>
                        <td><small>{{ $a->skema->name ?? '-' }}</small></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $a->status_badge }}">{{ $a->status_label }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══ Modal Tambah Pembayaran ════════════════════════════════════════ --}}
@if($bisaTambah)
<div class="modal fade" id="modalAngsuran" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('tuk.invoice-kolektif.angsuran.store', $invoice) }}"
              method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Pembayaran ke-{{ $jumlahAng + 1 }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small py-2 mb-3">
                    Sisa tagihan:
                    <strong>Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong>
                    @if($jumlahAng > 0)
                    &nbsp;|&nbsp; Angsuran {{ $jumlahAng + 1 }} dari maks. 3
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Nominal Pembayaran <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="amount_display"
                               class="form-control"
                               value="{{ number_format($sisaTagihan, 0, ',', '.') }}"
                               placeholder="0"
                               inputmode="numeric"
                               autocomplete="off">
                        <input type="hidden" name="amount" id="amount_hidden"
                               value="{{ $sisaTagihan }}">
                    </div>
                    <div class="form-text">Maks: Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Bukti Transfer <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="proof" class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf" required>
                    <div class="form-text">JPG/PNG/PDF, maks 5MB</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Transfer</label>
                    <input type="date" name="due_date" class="form-control"
                           value="{{ date('Y-m-d') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Misal: Transfer via BSI mobile tanggal ..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload"></i> Kirim Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modal Zoom Bukti --}}
<div class="modal fade" id="modalZoomBukti" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-header border-0 pb-1 px-0">
                <span class="text-white fw-semibold" id="zoomBuktiLabel" style="font-size:.9rem;"></span>
                <button type="button" class="btn-close btn-close-white ms-auto"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="zoomBuktiImg" src="" alt="Bukti Bayar"
                     class="img-fluid rounded-3 shadow"
                     style="max-height:85vh;object-fit:contain;">
            </div>
            <div class="modal-footer border-0 justify-content-center pt-2">
                <a id="zoomBuktiDownload" href="#"
                   class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i> Unduh
                </a>
                <button type="button" class="btn btn-sm btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
#modalZoomBukti .modal-dialog { max-width: 90vw; }
#modalZoomBukti { background: rgba(0,0,0,.85); }
</style>

@endsection

@push('scripts')
<script>
// ── Format rupiah otomatis ────────────────────────────────────────────────
const amountDisplay = document.getElementById('amount_display');
const amountHidden  = document.getElementById('amount_hidden');
const maxAmount     = {{ $sisaTagihan }};

if (amountDisplay) {
    amountDisplay.addEventListener('input', function () {
        let raw = this.value.replace(/\D/g, '');
        let num = parseInt(raw) || 0;
        if (num > maxAmount) num = maxAmount;
        this.value         = num.toLocaleString('id-ID');
        amountHidden.value = num;
    });

    amountDisplay.addEventListener('focus', function () {
        let raw = this.value.replace(/\D/g, '');
        this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    });

    amountDisplay.addEventListener('blur', function () {
        let num = parseInt(amountHidden.value) || 0;
        this.value = num.toLocaleString('id-ID');
    });
}

// ── Zoom bukti bayar ─────────────────────────────────────────────────────
const modalZoom     = new bootstrap.Modal(document.getElementById('modalZoomBukti'));
const zoomImg       = document.getElementById('zoomBuktiImg');
const zoomDownload  = document.getElementById('zoomBuktiDownload');
const zoomLabel     = document.getElementById('zoomBuktiLabel');

document.querySelectorAll('.bukti-preview').forEach(function (img) {
    img.addEventListener('click', function () {
        const url      = this.dataset.url;
        const filename = this.dataset.filename;

        zoomImg.src              = url;
        zoomDownload.href        = url + '?download=1';
        zoomDownload.download    = filename;
        zoomLabel.textContent    = filename;

        modalZoom.show();
    });
});

function bukaZoomBukti(src, label, downloadUrl) {
    document.getElementById('zoomBuktiImg').src         = src;
    document.getElementById('zoomBuktiLabel').textContent = label;
    document.getElementById('zoomBuktiDownload').href   = downloadUrl;
    bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalZoomBukti')
    ).show();
}
</script>
@endpush