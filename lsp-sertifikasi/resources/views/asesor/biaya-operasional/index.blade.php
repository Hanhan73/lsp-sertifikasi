@extends('layouts.app')
@section('title', 'Riwayat Biaya Operasional')
@section('page-title', 'Riwayat Biaya Operasional')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

<div class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-3">
    <span><i class="bi bi-cash-stack"></i> Total biaya atas nama Anda</span>
    <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-table"></i> Daftar Biaya Operasional</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px">No</th>
                        <th style="width:130px">Nomor</th>
                        <th style="width:110px">Tanggal</th>
                        <th>Uraian</th>
                        <th class="text-end" style="width:130px">Tarif</th>
                        <th class="text-center" style="width:60px">Jml</th>
                        <th class="text-end" style="width:140px">Total</th>
                        <th class="text-center" style="width:90px">Bukti Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($biayaList as $i => $item)
                    <tr>
                        <td class="text-center">{{ $biayaList->firstItem() + $i }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $item->nomor }}</span></td>
                        <td>{{ $item->tanggal->translatedFormat('d/m/Y') }}</td>
                        <td>
                            {{ $item->uraian }}
                            @if($item->keterangan)
                            <div class="text-muted small">{{ $item->keterangan }}</div>
                            @endif
                        </td>
                        <td class="text-end">Rp {{ number_format($item->tarif, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item->jumlah }}</td>
                        <td class="text-end fw-semibold text-success">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($item->bukti_transaksi)
                            <a href="{{ $item->bukti_transaksi_url }}" target="_blank">
                                <img src="{{ $item->bukti_transaksi_url }}"
                                     style="width:44px;height:44px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
                            </a>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size:2rem;"></i><br>
                            Belum ada data biaya operasional atas nama Anda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($biayaList->hasPages())
    <div class="card-footer">{{ $biayaList->links() }}</div>
    @endif
</div>

@endsection