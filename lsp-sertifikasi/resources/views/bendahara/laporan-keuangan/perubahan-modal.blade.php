{{-- ═══════════════════════════════════════════════════════════
     perubahan-modal.blade.php
     ═══════════════════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'Perubahan Modal')
@section('page-title', 'Laporan Perubahan Modal')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@include('bendahara.laporan-keuangan._filter', ['route' => 'bendahara.laporan-keuangan.perubahan-modal'])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
        <div>
            <span class="fw-bold fs-6">LAPORAN PERUBAHAN MODAL (EKUITAS)</span><br>
            <small class="text-muted">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }}</small>
        </div>
        <a href="{{ route('bendahara.laporan-keuangan.perubahan-modal', ['tahun'=>$tahun, 'export'=>'pdf']) }}"
           class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0" style="font-size:.9rem;">
            <tbody>
            <tr class="table-secondary">
                <td colspan="2" class="fw-bold ps-3">SALDO DANA (MODAL)</td>
            </tr>
            <tr>
                <td class="ps-4">Saldo Dana Awal (1 Januari {{ $tahun }})</td>
                <td class="text-end pe-3 fw-semibold">Rp {{ number_format($saldoAwal,0,',','.') }}</td>
            </tr>
            <tr>
                <td class="ps-4 text-success">Ditambah: Surplus Tahun {{ $tahun }}</td>
                <td class="text-end pe-3 text-success">+ Rp {{ number_format($surplus,0,',','.') }}</td>
            </tr>
            @if($distribusi > 0)
            <tr>
                <td class="ps-4 text-danger">Dikurangi: Distribusi ke Yayasan</td>
                <td class="text-end pe-3 text-danger">− Rp {{ number_format($distribusi,0,',','.') }}</td>
            </tr>
            @endif
            <tr class="fw-bold fs-6 table-warning">
                <td class="ps-3">Saldo Dana Akhir (31 Desember {{ $tahun }})</td>
                <td class="text-end pe-3 text-dark">Rp {{ number_format($saldoAkhir,0,',','.') }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection