@extends('layouts.app')
@section('title', 'Pembayaran Kolektif')
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

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Invoice Pembayaran Kolektif</h5>
        <span class="badge bg-primary">{{ $invoices->count() }} Invoice</span>
    </div>
    <div class="card-body">

        {{-- Info rekening --}}
        <div class="alert alert-info d-flex gap-3 align-items-start mb-4">
            <i class="bi bi-info-circle-fill fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Cara Pembayaran</strong><br>
                Transfer ke Rekening Bank BSI No. <strong>1619161919</strong> a.n. LSP-KAP (KCP Bandung UPI).<br>
                Setelah transfer, upload bukti bayar di halaman detail invoice. Bendahara LSP akan memverifikasi.<br>
                <small class="text-muted">Kontak Admin: Anggriawan Oktobisono — 085867219139</small>
            </div>
        </div>

        @if($invoices->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size:3.5rem;color:#ccc;"></i>
            <h5 class="mt-3 text-muted">Belum ada invoice yang diterbitkan</h5>
            <p class="text-muted small">Bendahara LSP akan menerbitkan invoice setelah batch kolektif Anda terdaftar.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Invoice</th>
                        <th class="text-center">Batch</th>
                        <th class="text-center">Asesi</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-end">Terbayar</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-center">Status</th>
                        <th>Tgl Invoice</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    @php
                        $lunas   = $inv->collectivePayments->where('status','verified')->sum('amount');
                        $sisa    = $inv->total_amount - $lunas;
                        $pending = $inv->collectivePayments->where('status','pending')->whereNotNull('proof_path')->count();
                    @endphp
                    <tr>
                        <td><strong class="small">{{ $inv->invoice_number }}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ count($inv->batch_ids) }} batch</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $inv->total_asesi }} orang</span>
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="text-end text-success">
                            Rp {{ number_format($lunas, 0, ',', '.') }}
                        </td>
                        <td class="text-end {{ $sisa > 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($sisa, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $inv->status_badge }}">{{ $inv->status_label }}</span>
                            @if($pending > 0)
                            <br><span class="badge bg-warning text-dark small mt-1">{{ $pending }} menunggu verifikasi</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $inv->issued_at->translatedFormat('d M Y') }}</small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('tuk.invoice-kolektif.show', $inv) }}"
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