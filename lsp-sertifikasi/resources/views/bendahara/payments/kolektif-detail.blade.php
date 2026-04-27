@extends('layouts.app')

@section('title', 'Detail Batch Kolektif')
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

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('bendahara.payments.kolektif') }}">Kolektif</a>
        </li>
        <li class="breadcrumb-item active">{{ $tuk->name }}</li>
    </ol>
</nav>

{{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">TUK</div>
                <div class="fw-bold">{{ $tuk->name }}</div>
                <div class="small text-muted">{{ $tuk->email ?? '-' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Asesi</div>
                <div class="fw-bold fs-4">{{ $asesmens->count() }} orang</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Tagihan</div>
                <div class="fw-bold fs-5 text-primary">
                    {{ $invoice ? 'Rp ' . number_format($invoice->total_amount, 0, ',', '.') : '-' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Status Invoice</div>
                @if($invoice)
                    <span class="badge bg-{{ $invoice->status_badge }} fs-6">{{ $invoice->status_label }}</span>
                @else
                    <span class="badge bg-secondary fs-6">Belum Ada Invoice</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     SECTION A — Form Buat / Edit Invoice
══════════════════════════════════════════════════════════════════════ --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-text"></i>
            {{ $invoice ? 'Invoice #' . $invoice->invoice_number : 'Buat Invoice Baru' }}
        </h6>
        @if($invoice)
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('bendahara.payments.kolektif.invoice.pdf', $invoice) }}"
               target="_blank" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-pdf"></i> Download Invoice
            </a>
            @if($invoice->status === 'draft')
            <form action="{{ route('bendahara.payments.kolektif.invoice.send', $invoice) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary"
                        onclick="return confirm('Kirim invoice ke TUK?')">
                    <i class="bi bi-send"></i> Kirim ke TUK
                </button>
            </form>
            @endif
        </div>
        @endif
    </div>
    <div class="card-body">

        {{-- ── View-only (invoice sudah sent/paid) ───────────────────────── --}}
        @if($invoice && $invoice->status !== 'draft')

        <div class="row mb-3">
            <div class="col-md-6">
                <small class="text-muted">Kepada:</small>
                <div class="fw-bold">{{ $invoice->recipient_name }}</div>
                <small class="text-muted">{{ $invoice->recipient_address }}</small>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Tanggal Invoice:</small>
                <div>{{ $invoice->issued_at->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
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
                        <td class="text-end fw-bold text-primary fs-6">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($invoice->notes)
        <div class="alert alert-light border mb-0">
            <small><strong>Catatan:</strong> {{ $invoice->notes }}</small>
        </div>
        @endif

        {{-- ── Form buat / edit draft ──────────────────────────────────── --}}
        @else

        @php
            $formAction = $invoice
                ? route('bendahara.payments.kolektif.invoice.update', $invoice)
                : route('bendahara.payments.kolektif.invoice.store');
            $formMethod = $invoice ? 'PUT' : 'POST';
        @endphp

        <form action="{{ $formAction }}" method="POST" id="invoiceForm">
            @csrf
            @if($invoice) @method('PUT') @endif
            <input type="hidden" name="batch_id" value="{{ $batchId }}">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Kepada (Nama Lembaga / Pimpinan) <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="recipient_name" class="form-control @error('recipient_name') is-invalid @enderror"
                           value="{{ old('recipient_name', $invoice->recipient_name ?? $tuk->name) }}" required>
                    @error('recipient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea name="recipient_address" class="form-control" rows="2">{{ old('recipient_address', $invoice->recipient_address ?? ($tuk->address ?? '')) }}</textarea>
                </div>
            </div>

            {{-- Tabel items per skema --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Rincian Biaya per Skema</label>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:40px">No</th>
                                <th>Nama Skema</th>
                                <th style="width:120px" class="text-center">Jumlah Asesi</th>
                                <th style="width:200px">Harga Satuan (Rp)</th>
                                <th style="width:160px" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($skemaGroups as $i => $item)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $i }}][skema_id]"   value="{{ $item['skema_id'] }}">
                                    <input type="hidden" name="items[{{ $i }}][skema_name]" value="{{ $item['skema_name'] }}">
                                    <input type="hidden" name="items[{{ $i }}][jumlah]"     value="{{ $item['jumlah'] }}">
                                    <strong>{{ $item['skema_name'] }}</strong>
                                </td>
                                <td class="text-center">{{ $item['jumlah'] }} orang</td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="items[{{ $i }}][harga_satuan]"
                                               class="form-control harga-satuan"
                                               data-jumlah="{{ $item['jumlah'] }}"
                                               data-index="{{ $i }}"
                                               value="{{ old('items.'.$i.'.harga_satuan', $item['harga_satuan']) }}"
                                               min="0" step="1000" required>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <strong id="subtotal-{{ $i }}">
                                        Rp {{ number_format(($item['subtotal'] ?? 0), 0, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="4" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold text-primary" id="grandTotal">
                                    Rp {{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Catatan (opsional)</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $invoice->notes ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i>
                {{ $invoice ? 'Simpan Perubahan' : 'Buat Invoice' }}
            </button>
        </form>

        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     SECTION B — Angsuran (hanya tampil jika invoice sudah ada)
══════════════════════════════════════════════════════════════════════ --}}
@if($invoice)
@php
    $totalLunas  = $collectivePayments->where('status','verified')->sum('amount');
    $sisaTagihan = $invoice->total_amount - $totalLunas;
    $jumlahAng   = $collectivePayments->count();
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0"><i class="bi bi-list-check"></i> Riwayat Angsuran</h6>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <small class="text-muted">
                Terbayar: <strong class="text-success">Rp {{ number_format($totalLunas, 0, ',', '.') }}</strong>
                &nbsp;|&nbsp;
                Sisa: <strong class="{{ $sisaTagihan > 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                </strong>
            </small>
            @if($jumlahAng < 3 && $sisaTagihan > 0)
            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalAngsuran">
                <i class="bi bi-plus-circle"></i> Tambah Angsuran
            </button>
            @endif
        </div>
    </div>

    {{-- Progress bar --}}
    @if($invoice->total_amount > 0)
    @php $pct = min(100, round($totalLunas / $invoice->total_amount * 100)); @endphp
    <div class="px-3 pt-3">
        <div class="progress" style="height:8px;">
            <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
        </div>
        <div class="text-end small text-muted mt-1 mb-0">{{ $pct }}% lunas</div>
    </div>
    @endif

    <div class="card-body">
        @if($collectivePayments->isEmpty())
        <p class="text-muted text-center py-3 mb-0">
            Belum ada angsuran. Klik "Tambah Angsuran" untuk menambahkan tagihan pertama.
        </p>
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
                        <th>Kwitansi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($collectivePayments as $cp)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">ke-{{ $cp->installment_number }}</span>
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($cp->amount, 0, ',', '.') }}
                        </td>
                        <td>
                            {{ $cp->due_date ? $cp->due_date->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td>
                            @if($cp->proof_path)
                                <a href="{{ route('bendahara.payments.kolektif.angsuran.bukti', $cp) }}"
                                   target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark"></i> Lihat
                                </a>
                                <div class="small text-muted mt-1">
                                    {{ $cp->proof_uploaded_at?->translatedFormat('d M Y H:i') }}
                                </div>
                            @else
                                <span class="text-muted small">Belum diupload TUK</span>
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
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if($cp->status === 'verified')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown">
                                    <i class="bi bi-file-earmark-text"></i> Kwitansi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ route('bendahara.payments.kolektif.invoice.kwitansi', [$invoice, 'payment_id' => $cp->id, 'versi' => 'kosong']) }}"
                                           target="_blank">
                                            <i class="bi bi-file-earmark"></i> Kosong (tanpa TTD)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ route('bendahara.payments.kolektif.invoice.kwitansi', [$invoice, 'payment_id' => $cp->id, 'versi' => 'berisi']) }}"
                                           target="_blank">
                                            <i class="bi bi-file-earmark-check"></i> Berisi (TTD + Stempel)
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-center">
                            @if($cp->status === 'pending' && $cp->proof_path)
                            <div class="d-flex gap-1 justify-content-center">
                                <form action="{{ route('bendahara.payments.kolektif.angsuran.verify', $cp) }}"
                                      method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="verify">
                                    <button type="submit" class="btn btn-sm btn-success"
                                            onclick="return confirm('Verifikasi angsuran ini?')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <button class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalReject{{ $cp->id }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            {{-- Modal Tolak --}}
                            <div class="modal fade" id="modalReject{{ $cp->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('bendahara.payments.kolektif.angsuran.verify', $cp) }}"
                                          method="POST" class="modal-content">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <div class="modal-header">
                                            <h6 class="modal-title">
                                                Tolak Angsuran ke-{{ $cp->installment_number }}
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label">Alasan penolakan <span class="text-danger">*</span></label>
                                            <textarea name="notes" class="form-control" rows="3" required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @elseif($cp->status === 'pending' && !$cp->proof_path)
                            <small class="text-muted">Menunggu bukti</small>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════
     SECTION C — Daftar Asesi dalam batch
══════════════════════════════════════════════════════════════════════ --}}
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-people"></i> Asesi dalam Batch</h6>
        <span class="badge bg-secondary">{{ $asesmens->count() }} orang</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Skema</th>
                        <th>Lembaga</th>
                        <th class="text-center">Status</th>
                    </tr>
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

{{-- ── Modal Tambah Angsuran ────────────────────────────────────────────── --}}
@if($invoice && $jumlahAng < 3 && $sisaTagihan > 0)
<div class="modal fade" id="modalAngsuran" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('bendahara.payments.kolektif.angsuran.store', $invoice) }}"
              method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Angsuran ke-{{ $jumlahAng + 1 }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small py-2">
                    Sisa tagihan: <strong>Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="amount" class="form-control"
                               max="{{ $sisaTagihan }}" min="1" step="1000"
                               value="{{ $sisaTagihan }}" required>
                    </div>
                    <div class="form-text">Maks. Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jatuh Tempo</label>
                    <input type="date" name="due_date" class="form-control"
                           min="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Live hitung subtotal & grand total
document.querySelectorAll('.harga-satuan').forEach(input => {
    input.addEventListener('input', recalc);
});

function recalc() {
    let grand = 0;
    document.querySelectorAll('.harga-satuan').forEach(input => {
        const idx    = input.dataset.index;
        const jumlah = parseInt(input.dataset.jumlah) || 0;
        const harga  = parseInt(input.value) || 0;
        const sub    = jumlah * harga;
        grand += sub;
        const el = document.getElementById('subtotal-' + idx);
        if (el) el.textContent = 'Rp ' + sub.toLocaleString('id-ID');
    });
    const gt = document.getElementById('grandTotal');
    if (gt) gt.textContent = 'Rp ' + grand.toLocaleString('id-ID');
}

// Init sekali waktu load
recalc();
</script>
@endpush