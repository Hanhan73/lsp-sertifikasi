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
    $totalLunas  = $payments->where('status','verified')->sum('amount');
    $totalPending = $payments->where('status','pending')->sum('amount');
    $sisaTagihan = $invoice->total_amount - $totalLunas;
    $jumlahAng   = $payments->count();
    $pct         = $invoice->total_amount > 0 ? min(100, round($totalLunas / $invoice->total_amount * 100)) : 0;
    $bisaTambah  = $jumlahAng < 3 && $sisaTagihan > 0 && $invoice->status !== 'draft';
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
        <div class="card border mb-3
            {{ $cp->status === 'verified' ? 'border-success' : ($cp->status === 'rejected' ? 'border-danger' : 'border-warning') }}">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    Angsuran ke-{{ $cp->installment_number }}
                    &mdash;
                    <strong>Rp {{ number_format($cp->amount, 0, ',', '.') }}</strong>
                </span>
                <span class="badge bg-{{ $cp->status_badge }}">{{ $cp->status_label }}</span>
            </div>
            <div class="card-body py-2">
                <div class="row g-2 align-items-start">
                    <div class="col-md-4">
                        @if($cp->due_date)
                        <small class="text-muted d-block">Jatuh Tempo:</small>
                        <div>{{ $cp->due_date->translatedFormat('d M Y') }}</div>
                        @endif
                        @if($cp->notes)
                        <small class="text-muted">Catatan: {{ $cp->notes }}</small>
                        @endif
                        <small class="text-muted d-block mt-1">
                            Diupload: {{ $cp->proof_uploaded_at?->translatedFormat('d M Y H:i') ?? '-' }}
                        </small>
                    </div>
                    <div class="col-md-4">
                        @if($cp->status === 'verified')
                        <div class="text-success">
                            <i class="bi bi-check-circle-fill me-1"></i> Terverifikasi<br>
                            <small>{{ $cp->verified_at?->translatedFormat('d M Y') }}</small>
                        </div>
                        @elseif($cp->status === 'rejected')
                        <div class="text-danger">
                            <i class="bi bi-x-circle-fill me-1"></i> Ditolak<br>
                            @if($cp->rejection_notes)
                            <small>Alasan: {{ $cp->rejection_notes }}</small>
                            @endif
                        </div>
                        @else
                        <div class="text-warning">
                            <i class="bi bi-clock me-1"></i> Menunggu verifikasi bendahara
                        </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        @if($cp->status === 'rejected')
                        {{-- Upload ulang kalau ditolak --}}
                        <form action="{{ route('tuk.invoice-kolektif.upload-bukti', $cp) }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label small fw-semibold mb-1 text-danger">
                                Upload Ulang Bukti Bayar
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
                        @elseif($cp->proof_path)
                        <a href="{{ Storage::url($cp->proof_path) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i> Lihat Bukti
                        </a>
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
                        <input type="number" name="amount" class="form-control"
                               max="{{ $sisaTagihan }}" min="1" step="1000"
                               value="{{ $sisaTagihan }}" required>
                    </div>
                    <div class="form-text">Maks. Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</div>
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

@endsection