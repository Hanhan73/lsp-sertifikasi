@extends('layouts.app')
@section('title', 'Biaya Operasional')
@section('page-title', 'Biaya Operasional')

@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@section('content')

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('direktur.keuangan.biaya-operasional') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label mb-1 small">Bulan</label>
                <select name="bulan" class="form-select form-select-sm">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-1 small">Tahun</label>
                <select name="tahun" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('direktur.keuangan.biaya-operasional') }}" class="btn btn-sm btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
<div class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-3">
    <span><i class="bi bi-cash-stack"></i> Total Biaya Operasional (filter aktif)</span>
    <strong>Rp {{ number_format($biayaOps->sum('total'), 0, ',', '.') }}</strong>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-table"></i> Daftar Biaya Operasional</h6>
        <div>
            <span class="badge bg-secondary">{{ $biayaOps->total() }} item</span>
            <span class="badge bg-info ms-1"><i class="bi bi-eye me-1"></i>Mode Lihat Saja</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px">No</th>
                        <th style="width:140px">Nomor</th>
                        <th style="width:110px">Tanggal</th>
                        <th>Uraian / Nama Penerima</th>
                        <th class="text-end" style="width:130px">Tarif</th>
                        <th class="text-center" style="width:70px">Jml</th>
                        <th class="text-end" style="width:140px">Total</th>
                        <th class="text-center" style="width:90px">Bukti Transaksi</th>
                        <th class="text-center" style="width:90px">Bukti Kegiatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($biayaOps as $i => $item)
                    <tr>
                        <td class="text-center">{{ $biayaOps->firstItem() + $i }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $item->nomor }}</span></td>
                        <td>{{ $item->tanggal->translatedFormat('d/m/Y') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->uraian }}</div>
                            <small class="text-muted"><i class="bi bi-person"></i> {{ $item->nama_penerima }}</small>
                        </td>
                        <td class="text-end">Rp {{ number_format($item->tarif, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item->jumlah }}</td>
                        <td class="text-end fw-semibold text-danger">Rp {{ number_format($item->total, 0, ',', '.') }}</td>

                        {{-- Bukti Transaksi --}}
                        <td class="text-center">
                            @if($item->bukti_transaksi)
                            <a href="{{ $item->bukti_transaksi_url }}" target="_blank" title="Lihat bukti transaksi">
                                <img src="{{ $item->bukti_transaksi_url }}"
                                     style="width:44px;height:44px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
                            </a>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>

                        {{-- Bukti Kegiatan --}}
                        <td class="text-center">
                            @if($item->bukti_kegiatan)
                            <a href="{{ $item->bukti_kegiatan_url }}" target="_blank" title="Lihat bukti kegiatan">
                                <img src="{{ $item->bukti_kegiatan_url }}"
                                     style="width:44px;height:44px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
                            </a>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size:2rem;"></i><br>
                            Belum ada data biaya operasional.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($biayaOps->count())
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total (halaman ini):</td>
                        <td class="text-end fw-bold text-danger">
                            Rp {{ number_format($biayaOps->sum('total'), 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @if($biayaOps->hasPages())
    <div class="card-footer">
        {{ $biayaOps->links() }}
    </div>
    @endif
</div>

@endsection