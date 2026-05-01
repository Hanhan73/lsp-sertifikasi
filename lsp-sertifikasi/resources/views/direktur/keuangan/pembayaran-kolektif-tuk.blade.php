@extends('layouts.app')
@section('title', 'Batch Kolektif — ' . $tuk->name)
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
        <li class="breadcrumb-item active">{{ $tuk->name }}</li>
    </ol>
</nav>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 fw-bold">{{ $tuk->name }}</h6>
            <small class="text-muted">{{ $tuk->email ?? '-' }} &nbsp;|&nbsp; {{ $tuk->phone ?? '-' }}</small>
        </div>
        <div>
            <span class="badge bg-secondary fs-6">{{ $batches->count() }} Batch</span>
            <span class="badge bg-info ms-2"><i class="bi bi-eye me-1"></i>Mode Lihat Saja</span>
        </div>
    </div>
</div>

{{-- ══ SECTION: Daftar Batch ══════════════════════════ --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-collection"></i> Daftar Batch</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Batch ID</th>
                        <th>Skema</th>
                        <th class="text-center">Asesi</th>
                        <th>Status Invoice</th>
                        <th>Tgl Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $b)
                    <tr>
                        <td><code class="small">{{ $b->collective_batch_id }}</code></td>
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

{{-- ══ SECTION: Daftar invoice yang sudah ada ══════════════════════════ --}}
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
                            <a href="{{ route('direktur.keuangan.pembayaran-kolektif.detail', $inv) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detail
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

@endsection