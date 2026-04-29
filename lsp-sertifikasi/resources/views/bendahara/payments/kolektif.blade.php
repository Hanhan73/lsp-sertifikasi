@extends('layouts.app')
@section('title', 'Pembayaran Kolektif')
@section('page-title', 'Pembayaran Kolektif')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Daftar TUK — Pembayaran Kolektif</h5>
        <span class="badge bg-primary">{{ $tuks->count() }} TUK</span>
    </div>
    <div class="card-body">

        @if($tuks->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size:3.5rem;color:#ccc;"></i>
            <h5 class="mt-3 text-muted">Belum ada batch kolektif</h5>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>TUK</th>
                        <th class="text-center">Batch</th>
                        <th class="text-center">Total Asesi</th>
                        <th class="text-center">Invoice Pending</th>
                        <th class="text-center">Bukti Menunggu</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tuks as $row)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $row->tuk->name ?? '-' }}</div>
                            <small class="text-muted">{{ $row->tuk->email ?? '' }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill">{{ $row->jumlah_batch }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $row->jumlah_asesi }}</span>
                        </td>
                        <td class="text-center">
                            @if($row->pending_invoice > 0)
                            <span class="badge bg-warning text-dark">{{ $row->pending_invoice }} draft</span>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row->pending_angsuran > 0)
                            <span class="badge bg-danger">{{ $row->pending_angsuran }} menunggu</span>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('bendahara.payments.kolektif.tuk', $row->tuk_id) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Lihat Batch
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