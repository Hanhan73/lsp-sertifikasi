@extends('layouts.app')
@section('title', 'Invoice Kolektif')
@section('page-title', 'Invoice Kolektif')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center gap-2">
        <i class="bi bi-receipt text-primary"></i>
        <span class="fw-semibold">Daftar Invoice Kolektif</span>
        <span class="badge bg-secondary ms-auto">{{ $invoices->count() }}</span>
    </div>

    {{-- Filter --}}
    <div class="card-body border-bottom pb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <select name="tuk_id" class="form-select form-select-sm">
                    <option value="">Semua TUK</option>
                    @foreach($tuks as $tuk)
                    <option value="{{ $tuk->id }}" {{ request('tuk_id') == $tuk->id ? 'selected' : '' }}>
                        {{ $tuk->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="draft"  {{ request('status') === 'draft'  ? 'selected' : '' }}>Draft</option>
                    <option value="sent"   {{ request('status') === 'sent'   ? 'selected' : '' }}>Terkirim</option>
                    <option value="paid"   {{ request('status') === 'paid'   ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div class="col-sm-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                <a href="{{ route('admin.invoice-kolektif.index') }}" class="btn btn-sm btn-outline-secondary">×</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($invoices->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Belum ada invoice kolektif.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>No. Invoice</th>
                        <th>TUK</th>
                        <th class="text-center">Batch</th>
                        <th class="text-center">Asesi</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Terbayar</th>
                        <th class="text-center">Status</th>
                        <th>Tgl Invoice</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    @php
                        $lunas = $inv->collectivePayments->where('status', 'verified')->sum('amount');
                        $sisa  = $inv->total_amount - $lunas;
                        $pending = $inv->collectivePayments->where('status', 'pending')->whereNotNull('proof_path')->count();
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->tuk->name ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ count($inv->batch_ids) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $inv->total_asesi }}</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                        <td class="text-end text-success">Rp {{ number_format($lunas, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $inv->status_badge }}">{{ $inv->status_label }}</span>
                            @if($pending > 0)
                            <br><span class="badge bg-warning text-dark mt-1" style="font-size:.65rem;">
                                {{ $pending }} menunggu
                            </span>
                            @endif
                        </td>
                        <td>{{ $inv->issued_at?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.invoice-kolektif.show', $inv) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
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