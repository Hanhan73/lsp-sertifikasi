@extends('layouts.app')
@section('title', 'Detail Invoice')
@section('page-title', 'Pembayaran Kolektif')

@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@section('content')

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('direktur.keuangan.pembayaran-kolektif') }}">Kolektif</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('direktur.keuangan.pembayaran-kolektif.tuk', $tuk) }}">{{ $tuk->name }}</a>
        </li>
        <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
    </ol>
</nav>

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">TUK</div>
                <div class="fw-bold">{{ $tuk->name }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Batch / Asesi</div>
                <div class="fw-bold">{{ count($invoice->batch_ids) }} batch &mdash; {{ $asesmens->count() }} orang</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Tagihan</div>
                <div class="fw-bold fs-5 text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Status</div>
                <span class="badge bg-{{ $invoice->status_badge }} fs-6">{{ $invoice->status_label }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ══ SECTION A: Invoice ══════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-text"></i> Invoice #{{ $invoice->invoice_number }}
        </h6>
        <a href="{{ route('direktur.keuangan.download.invoice-kolektif', $invoice) }}"
           target="_blank" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-pdf"></i> Download Invoice PDF
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <small class="text-muted">Kepada:</small>
                <div class="fw-bold">{{ $invoice->recipient_name }}</div>
                <small class="text-muted">{{ $invoice->recipient_address }}</small>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Tanggal:</small>
                <div>{{ $invoice->issued_at?->translatedFormat('d F Y') ?? '-' }}</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th><th>Nama Skema</th>
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
                        <td class="text-end fw-bold text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ══ SECTION B: Angsuran ════════════════════════════════════════════════ --}}
@php
    $totalLunas  = $collectivePayments->where('status','verified')->sum('amount');
    $sisaTagihan = $invoice->total_amount - $totalLunas;
    $pct         = $invoice->total_amount > 0 ? min(100, round($totalLunas / $invoice->total_amount * 100)) : 0;
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0"><i class="bi bi-list-check"></i> Riwayat Angsuran</h6>
        <small class="text-muted">
            Terbayar: <strong class="text-success">Rp {{ number_format($totalLunas, 0, ',', '.') }}</strong>
            &nbsp;|&nbsp;
            Sisa: <strong class="{{ $sisaTagihan > 0 ? 'text-danger' : 'text-success' }}">
                Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
            </strong>
        </small>
    </div>

    <div class="px-3 pt-3">
        <div class="progress" style="height:8px;">
            <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
        </div>
        <div class="text-end small text-muted mt-1">{{ $pct }}% lunas</div>
    </div>

    <div class="card-body">
        @if($collectivePayments->isEmpty())
        <p class="text-muted text-center py-3 mb-0">Belum ada pembayaran dari TUK.</p>
        @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Angsuran</th>
                        <th class="text-end">Nominal</th>
                        <th>Jatuh Tempo</th>
                        <th>Bukti Bayar</th>
                        <th class="text-center">Status</th>
                        <th>Verifikasi</th>
                        <th>Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($collectivePayments as $cp)
                    @php
                        $cpExt     = $cp->proof_path ? strtolower(pathinfo($cp->proof_path, PATHINFO_EXTENSION)) : null;
                        $cpImage   = in_array($cpExt, ['jpg','jpeg','png']);
                        $cpUrl     = $cp->proof_path
                            ? route('direktur.keuangan.download.bukti-angsuran', $cp)
                            : null;
                    @endphp
                    <tr>
                        <td><span class="badge bg-secondary">ke-{{ $cp->installment_number }}</span></td>
                        <td class="text-end fw-bold">Rp {{ number_format($cp->amount, 0, ',', '.') }}</td>
                        <td>{{ $cp->due_date ? $cp->due_date->translatedFormat('d M Y') : '-' }}</td>
                        <td style="min-width:160px;">
                            @if($cpUrl && $cpImage)
                            <div class="border rounded-3 overflow-hidden" style="max-width:160px;">
                                <div style="cursor:zoom-in;"
                                    onclick="bukaZoomBukti('{{ $cpUrl }}', 'Bukti Angsuran ke-{{ $cp->installment_number }}', '{{ $cpUrl }}?download=1')">
                                    <img src="{{ $cpUrl }}"
                                        class="w-100"
                                        style="max-height:100px;object-fit:cover;transition:opacity .2s;"
                                        onmouseover="this.style.opacity='.85'"
                                        onmouseout="this.style.opacity='1'">
                                </div>
                                <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between"
                                    style="font-size:.72rem;">
                                    <span class="text-muted">{{ $cp->proof_uploaded_at?->translatedFormat('d M H:i') }}</span>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-outline-secondary py-0 px-1" style="font-size:.68rem;"
                                                onclick="bukaZoomBukti('{{ $cpUrl }}', 'Bukti Angsuran ke-{{ $cp->installment_number }}', '{{ $cpUrl }}?download=1')"
                                                title="Zoom"><i class="bi bi-zoom-in"></i></button>
                                        <a href="{{ $cpUrl }}?download=1"
                                           class="btn btn-outline-primary py-0 px-1" style="font-size:.68rem;">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @elseif($cpUrl)
                            <div class="d-flex align-items-center gap-2 p-2 border rounded-3" style="background:#fff5f5;max-width:160px;">
                                <i class="bi bi-file-earmark-pdf text-danger fs-4 flex-shrink-0"></i>
                                <div>
                                    <div class="small fw-semibold">Bukti PDF</div>
                                    <a href="{{ $cpUrl }}?download=1" class="btn btn-outline-primary py-0 px-1 mt-1" style="font-size:.7rem;">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                            @else
                            <span class="text-muted small">Belum ada bukti</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $cp->status_badge }}">{{ $cp->status_label }}</span>
                            @if($cp->status === 'rejected' && $cp->rejection_notes)
                            <div class="small text-danger mt-1">{{ $cp->rejection_notes }}</div>
                            @endif
                        </td>
                        <td>
                            @if($cp->status === 'verified')
                            <small class="text-muted">
                                {{ $cp->verifier?->name }}<br>
                                {{ $cp->verified_at?->translatedFormat('d M Y') }}
                            </small>
                            @else -
                            @endif
                        </td>
                        <td>
                            @if($cp->status === 'verified')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" target="_blank"
                                           href="{{ route('direktur.keuangan.download.kwitansi-kolektif', [$invoice, 'payment_id' => $cp->id, 'versi' => 'berisi']) }}">
                                            <i class="bi bi-file-earmark-check me-1"></i>Kwitansi (TTD)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" target="_blank"
                                           href="{{ route('direktur.keuangan.download.kwitansi-kolektif', [$invoice, 'payment_id' => $cp->id, 'versi' => 'kosong']) }}">
                                            <i class="bi bi-file-earmark me-1"></i>Kwitansi (kosong)
                                        </a>
                                    </li>
                                    @if($cpUrl)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ $cpUrl }}?download=1">
                                            <i class="bi bi-image me-1"></i>Bukti Transfer
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            @else - @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ══ SECTION C: Daftar Asesi ════════════════════════════════════════════ --}}
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-people"></i> Asesi dalam Invoice ini</h6>
        <span class="badge bg-secondary">{{ $asesmens->count() }} orang</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th>No</th><th>Nama</th><th>Skema</th><th>Lembaga</th><th class="text-center">Status</th></tr>
                </thead>
                <tbody>
                    @foreach($asesmens as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $a->full_name }}</td>
                        <td><small>{{ $a->skema->name ?? '-' }}</small></td>
                        <td><small>{{ $a->institution ?? '-' }}</small></td>
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

{{-- Modal Zoom --}}
<div class="modal fade" id="modalZoomBukti" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-header border-0 pb-1 px-0">
                <span class="text-white fw-semibold" id="zoomBuktiLabel" style="font-size:.9rem;"></span>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="zoomBuktiImg" src="" class="img-fluid rounded-3 shadow" style="max-height:85vh;object-fit:contain;">
            </div>
            <div class="modal-footer border-0 justify-content-center pt-2">
                <a id="zoomBuktiDownload" href="#" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i> Unduh
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<style>
#modalZoomBukti .modal-dialog { max-width:90vw; }
#modalZoomBukti { background:rgba(0,0,0,.85); }
</style>

@endsection

@push('scripts')
<script>
function bukaZoomBukti(src, label, downloadUrl) {
    document.getElementById('zoomBuktiImg').src           = src;
    document.getElementById('zoomBuktiLabel').textContent = label;
    document.getElementById('zoomBuktiDownload').href     = downloadUrl;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalZoomBukti')).show();
}
</script>
@endpush