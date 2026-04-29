@extends('layouts.app')
@section('title', 'Batch Kolektif — ' . $tuk->name)
@section('page-title', 'Pembayaran Kolektif')

@section('sidebar')
@include('bendahara.partials.sidebar')
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
        <li class="breadcrumb-item"><a href="{{ route('bendahara.payments.kolektif') }}">Kolektif</a></li>
        <li class="breadcrumb-item active">{{ $tuk->name }}</li>
    </ol>
</nav>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold">{{ $tuk->name }}</h6>
            <small class="text-muted">{{ $tuk->email ?? '-' }} &nbsp;|&nbsp; {{ $tuk->phone ?? '-' }}</small>
        </div>
        <span class="badge bg-secondary fs-6">{{ $batches->count() }} Batch</span>
    </div>
</div>

{{-- ══ SECTION A: Pilih batch untuk invoice baru ══════════════════════════ --}}
@php $bisaDibuat = $batches->filter(fn($b) => !$b->invoice); @endphp

<form action="{{ route('bendahara.payments.kolektif.invoice.bulk', $tuk) }}" method="POST" id="formBulk">
@csrf
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0"><i class="bi bi-collection"></i> Pilih Batch untuk Invoice Baru</h6>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small" id="selectedCount">0 batch dipilih</span>
            @if($bisaDibuat->count() > 0)
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSelectAll">
                <i class="bi bi-check-all"></i> Pilih Semua
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="btnBulkInvoice" disabled>
                <i class="bi bi-file-earmark-plus"></i> Buat Invoice
            </button>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px" class="text-center">
                            @if($bisaDibuat->count() > 0)
                            <input type="checkbox" id="checkAll" class="form-check-input">
                            @endif
                        </th>
                        <th>Skema</th>
                        <th class="text-center">Asesi</th>
                        <th>Status Invoice</th>
                        <th>Tgl Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $b)
                    @php $bisaInvoice = !$b->invoice; @endphp
                    <tr>
                        <td class="text-center">
                            @if($bisaInvoice)
                            <input type="checkbox" name="batch_ids[]"
                                   value="{{ $b->collective_batch_id }}"
                                   class="form-check-input batch-check"
                                   data-skema="{{ $b->skema_names->implode(', ') }}"
                                   data-jumlah="{{ $b->jumlah_asesi }}">
                            @else
                            <i class="bi bi-check-circle-fill text-success" title="Sudah ada invoice"></i>
                            @endif
                        </td>
                        <td>
                            @foreach($b->skema_names as $sn)
                            <span class="badge bg-light text-dark border small">{{ Str::limit($sn, 30) }}</span><br>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $b->jumlah_asesi }}</span>
                        </td>
                        <td>
                            @if($b->invoice)
                            <span class="badge bg-{{ $b->invoice->status_badge }}">{{ $b->invoice->status_label }}</span>
                            <small class="text-muted d-block">{{ $b->invoice->invoice_number }}</small>
                            @else
                            <span class="badge bg-secondary">Belum ada invoice</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ \Carbon\Carbon::parse($b->tanggal_daftar)->translatedFormat('d M Y') }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</form>

{{-- ══ SECTION B: Daftar invoice yang sudah ada ══════════════════════════ --}}
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-receipt"></i> Invoice yang Sudah Dibuat</h6>
    </div>
    <div class="card-body p-0">
        @if($invoices->isEmpty())
        <p class="text-muted text-center py-4 mb-0">Belum ada invoice untuk TUK ini.</p>
        @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Invoice</th>
                        <th class="text-center">Batch</th>
                        <th class="text-center">Asesi</th>
                        <th>Skema</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Angsuran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    @php
                        $lunas   = $inv->collectivePayments->where('status','verified')->sum('amount');
                        $sisa    = $inv->total_amount - $lunas;
                        $pending = $inv->collectivePayments->where('status','pending')->whereNotNull('proof_path')->count();
                        $ver     = $inv->collectivePayments->where('status','verified')->count();
                        $total   = $inv->collectivePayments->count();
                    @endphp
                    <tr>
                        <td><strong class="small">{{ $inv->invoice_number }}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $inv->batch_count }} batch</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $inv->total_asesi }} orang</span>
                        </td>
                        <td>
                            @foreach($inv->skema_names->take(3) as $sn)
                            <span class="badge bg-light text-dark border small">{{ Str::limit($sn, 25) }}</span><br>
                            @endforeach
                            @if($inv->skema_names->count() > 3)
                            <small class="text-muted">+{{ $inv->skema_names->count() - 3 }} lainnya</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong>Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</strong>
                            @if($sisa > 0 && $sisa < $inv->total_amount)
                            <br><small class="text-danger">Sisa Rp {{ number_format($sisa, 0, ',', '.') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $inv->status_badge }}">{{ $inv->status_label }}</span>
                        </td>
                        <td class="text-center">
                            <small>{{ $ver }}/{{ $total }} lunas</small>
                            @if($pending > 0)
                            <br><span class="badge bg-warning text-dark small">{{ $pending }} cek</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('bendahara.payments.kolektif.detail', $inv) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Kelola
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Modal konfirmasi --}}
<div class="modal fade" id="modalBulkConfirm" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-file-earmark-plus"></i> Konfirmasi Buat Invoice</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    Batch-batch yang dipilih akan digabung dalam <strong>1 invoice</strong>.
                    Harga diambil dari data skema dan bisa diedit setelah invoice dibuat (selama Draft).
                </div>
                <div id="bulkSummary"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnConfirmBulk">
                    <i class="bi bi-check-lg"></i> Ya, Buat Invoice
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const checkAll      = document.getElementById('checkAll');
const btnBulk       = document.getElementById('btnBulkInvoice');
const selectedCount = document.getElementById('selectedCount');
const formBulk      = document.getElementById('formBulk');

function updateState() {
    const checked = document.querySelectorAll('.batch-check:checked');
    const all     = document.querySelectorAll('.batch-check');
    if (btnBulk) btnBulk.disabled = checked.length === 0;
    if (selectedCount) selectedCount.textContent = checked.length + ' batch dipilih';
    if (checkAll) {
        checkAll.checked       = checked.length === all.length && all.length > 0;
        checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }
}

document.querySelectorAll('.batch-check').forEach(cb => cb.addEventListener('change', updateState));

if (checkAll) {
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.batch-check').forEach(cb => cb.checked = this.checked);
        updateState();
    });
}

const btnSelectAll = document.getElementById('btnSelectAll');
if (btnSelectAll) {
    btnSelectAll.addEventListener('click', function () {
        const all = document.querySelectorAll('.batch-check');
        const anyUnchecked = [...all].some(cb => !cb.checked);
        all.forEach(cb => cb.checked = anyUnchecked);
        updateState();
    });
}

if (btnBulk) {
    btnBulk.addEventListener('click', function () {
        const checked = document.querySelectorAll('.batch-check:checked');
        let html = '<ul class="list-group">';
        checked.forEach(cb => {
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                <span>${cb.dataset.skema}</span>
                <span class="badge bg-primary">${cb.dataset.jumlah} asesi</span>
            </li>`;
        });
        html += '</ul>';
        document.getElementById('bulkSummary').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalBulkConfirm')).show();
    });
}

document.getElementById('btnConfirmBulk').addEventListener('click', function () {
    formBulk.submit();
});
</script>
@endpush