@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Invoice Kolektif')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.invoice-kolektif.index') }}">Invoice Kolektif</a>
        </li>
        <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
    </ol>
</nav>

@php
    $totalLunas  = $payments->where('status', 'verified')->sum('amount');
    $totalPending = $payments->where('status', 'pending')->sum('amount');
    $sisaTagihan = $invoice->total_amount - $totalLunas;
    $jumlahAng   = $payments->count();
    $pct         = $invoice->total_amount > 0 ? min(100, round($totalLunas / $invoice->total_amount * 100)) : 0;
    $bisaTambah  = $jumlahAng < 3 && $sisaTagihan > 0 && $invoice->status !== 'draft';
@endphp

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">TUK</div>
                <div class="fw-semibold small">{{ $invoice->tuk->name ?? '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Tagihan</div>
                <div class="fw-bold text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">Terbayar</div>
                <div class="fw-bold text-success">Rp {{ number_format($totalLunas, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">Sisa</div>
                <div class="fw-bold {{ $sisaTagihan > 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Rincian Invoice --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-1"></i>Rincian Invoice</h6>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-{{ $invoice->status_badge }}">{{ $invoice->status_label }}</span>
            <a href="{{ route('admin.invoice-kolektif.pdf', $invoice) }}" target="_blank"
               class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="text-muted small">Penerima</div>
                <div class="fw-semibold">{{ $invoice->recipient_name }}</div>
                <div class="text-muted small">{{ $invoice->issued_at?->translatedFormat('d F Y') }}</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 small">
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

{{-- Riwayat Pembayaran --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-list-check me-1"></i>Riwayat Pembayaran</h6>
        @if($bisaTambah)
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAngsuran">
            <i class="bi bi-plus-circle me-1"></i>Tambah Pembayaran
        </button>
        @endif
    </div>

    <div class="px-3 pt-3">
        <div class="progress" style="height:8px;">
            <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted mt-1 mb-2">
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
                Invoice masih draft.
            @else
                Belum ada pembayaran.
            @endif
        </p>
        @else
        @foreach($payments as $cp)
        @php
            $ext     = strtolower(pathinfo($cp->proof_path ?? '', PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg','jpeg','png']);
            $fileUrl = $cp->proof_path ? route('admin.invoice-kolektif.bukti', $cp) : null;
        @endphp
        <div class="card border mb-3 {{ $cp->status === 'verified' ? 'border-success' : ($cp->status === 'rejected' ? 'border-danger' : 'border-warning') }}">
            <div class="card-header py-2 d-flex justify-content-between align-items-center small">
                <span class="fw-semibold">
                    Angsuran ke-{{ $cp->installment_number }} —
                    <strong>Rp {{ number_format($cp->amount, 0, ',', '.') }}</strong>
                </span>
                <span class="badge bg-{{ $cp->status_badge }}">{{ $cp->status_label }}</span>
            </div>
            <div class="card-body py-3">
                <div class="row g-3 align-items-start">

                    {{-- Info --}}
                    <div class="col-md-4 small">
                        @if($cp->due_date)
                        <div class="text-muted">Tanggal Transfer:</div>
                        <div>{{ $cp->due_date->translatedFormat('d M Y') }}</div>
                        @endif
                        @if($cp->notes)
                        <div class="text-muted mt-1">Catatan:</div>
                        <div>{{ $cp->notes }}</div>
                        @endif
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 small">
                        @if($cp->status === 'verified')
                        <div class="text-success">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Terverifikasi — {{ $cp->verified_at?->translatedFormat('d M Y') }}
                        </div>
                        @elseif($cp->status === 'rejected')
                        <div class="text-danger">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            Ditolak@if($cp->rejection_notes): {{ $cp->rejection_notes }}
                        </div>
                        @else
                        <div class="text-warning">
                            <i class="bi bi-clock me-1"></i>Menunggu verifikasi bendahara
                        </div>
                        @endif
                    </div>

                    {{-- Bukti / Upload --}}
                    <div class="col-md-4">
                        @if($cp->status === 'rejected' || !$cp->proof_path)
                        <form action="{{ route('admin.invoice-kolektif.upload-bukti', $cp) }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label small fw-semibold mb-1 {{ $cp->status === 'rejected' ? 'text-danger' : '' }}">
                                {{ $cp->status === 'rejected' ? 'Upload Ulang Bukti' : 'Upload Bukti' }}
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="file" name="proof" class="form-control"
                                       accept=".jpg,.jpeg,.png,.pdf" required>
                                <button type="submit" class="btn btn-{{ $cp->status === 'rejected' ? 'danger' : 'primary' }} btn-sm">
                                    <i class="bi bi-upload"></i>
                                </button>
                            </div>
                            <small class="text-muted">JPG/PNG/PDF, maks 5MB</small>
                        </form>

                        @elseif($fileUrl)
                            @if($isImage)
                            <div class="border rounded-3 overflow-hidden">
                                <div style="cursor:zoom-in;"
                                     onclick="bukaZoom('{{ $fileUrl }}', 'Bukti Angsuran ke-{{ $cp->installment_number }}', '{{ $fileUrl }}?download=1')">
                                    <img src="{{ $fileUrl }}" class="w-100"
                                         style="max-height:140px;object-fit:cover;"
                                         alt="Bukti ke-{{ $cp->installment_number }}">
                                </div>
                                <div class="px-2 py-1 bg-light d-flex justify-content-between align-items-center"
                                     style="font-size:.75rem;">
                                    <span class="text-muted"><i class="bi bi-image me-1"></i>Bukti ke-{{ $cp->installment_number }}</span>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-outline-secondary py-0 px-1" style="font-size:.7rem;"
                                                onclick="bukaZoom('{{ $fileUrl }}', 'Angsuran ke-{{ $cp->installment_number }}', '{{ $fileUrl }}?download=1')">
                                            <i class="bi bi-zoom-in"></i>
                                        </button>
                                        <a href="{{ $fileUrl }}?download=1" class="btn btn-outline-primary py-0 px-1" style="font-size:.7rem;">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                                <i class="bi bi-file-earmark-pdf text-danger fs-3"></i>
                                <div class="flex-grow-1 small">
                                    <div class="fw-semibold">Bukti PDF</div>
                                    <div class="text-muted">Angsuran ke-{{ $cp->installment_number }}</div>
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.75rem;">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ $fileUrl }}?download=1" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.75rem;">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @endif
    </div>
</div>

{{-- Daftar Asesi --}}
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-people me-1"></i>Asesi dalam Invoice</h6>
        <span class="badge bg-secondary">{{ $asesmens->count() }} orang</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Skema</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesmens as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $a->full_name }}</td>
                        <td>{{ $a->skema->name ?? '-' }}</td>
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

{{-- Modal Tambah Pembayaran --}}
@if($bisaTambah)
<div class="modal fade" id="modalAngsuran" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.invoice-kolektif.upload-bukti', ['payment' => 'NEW_' . $invoice->id]) }}"
              method="POST" enctype="multipart/form-data" class="modal-content"
              id="formAngsuran">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">Tambah Pembayaran ke-{{ $jumlahAng + 1 }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small py-2 mb-3">
                    Sisa tagihan: <strong>Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong>
                </div>

                {{-- Pilih angsuran yang mau diupload --}}
                @php $angsuranBelumUpload = $payments->filter(fn($p) => !$p->proof_path || $p->status === 'rejected'); @endphp
                @if($angsuranBelumUpload->isNotEmpty())
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Upload Bukti untuk Angsuran</label>
                    @foreach($angsuranBelumUpload as $ang)
                    <div class="border rounded p-2 mb-2">
                        <div class="small fw-semibold mb-2">
                            Angsuran ke-{{ $ang->installment_number }} —
                            Rp {{ number_format($ang->amount, 0, ',', '.') }}
                            @if($ang->status === 'rejected')
                            <span class="badge bg-danger ms-1">Ditolak</span>
                            @endif
                        </div>
                        <form action="{{ route('admin.invoice-kolektif.upload-bukti', $ang) }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="file" name="proof" class="form-control"
                                       accept=".jpg,.jpeg,.png,.pdf" required>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload me-1"></i>Upload
                                </button>
                            </div>
                            <small class="text-muted">JPG/PNG/PDF, maks 5MB</small>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted small">Semua angsuran sudah ada buktinya.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modal Zoom --}}
<div class="modal fade" id="modalZoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-header border-0 pb-1 px-0">
                <span class="text-white fw-semibold" id="zoomLabel" style="font-size:.9rem;"></span>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="zoomImg" src="" class="img-fluid rounded-3 shadow"
                     style="max-height:85vh;object-fit:contain;" alt="">
            </div>
            <div class="modal-footer border-0 justify-content-center pt-2">
                <a id="zoomDownload" href="#" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i>Download
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<style>#modalZoom .modal-dialog{max-width:90vw;}#modalZoom{background:rgba(0,0,0,.85);}</style>

@endsection

@push('scripts')
<script>
function bukaZoom(src, label, downloadUrl) {
    document.getElementById('zoomImg').src          = src;
    document.getElementById('zoomLabel').textContent = label;
    document.getElementById('zoomDownload').href    = downloadUrl;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalZoom')).show();
}
</script>
@endpush