@extends('layouts.app')
@section('title', 'Laporan Arus Kas')
@section('page-title', 'Laporan Arus Kas')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@include('bendahara.laporan-keuangan._filter', ['route' => 'bendahara.laporan-keuangan.arus-kas'])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
        <div>
            <span class="fw-bold fs-6">LAPORAN ARUS KAS</span><br>
            <small class="text-muted">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }}</small>
        </div>
        <a href="{{ route('bendahara.laporan-keuangan.arus-kas', ['tahun'=>$tahun, 'export'=>'pdf']) }}"
           class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0" style="font-size:.9rem;">
            <tbody>

            {{-- Aktivitas Operasi --}}
            <tr class="table-primary">
                <td colspan="2" class="fw-bold ps-3">AKTIVITAS OPERASI</td>
            </tr>

            <tr class="table-success">
                <td class="ps-4 fw-semibold">Penerimaan</td>
                <td></td>
            </tr>
            <tr>
                <td class="ps-5">Penerimaan Pembayaran Sertifikasi</td>
                <td class="text-end pe-3 text-success fw-semibold">+ Rp {{ number_format($penerimaanSertifikasi,0,',','.') }}</td>
            </tr>

            <tr class="table-danger">
                <td class="ps-4 fw-semibold">Pengeluaran</td>
                <td></td>
            </tr>
            <tr>
                <td class="ps-5">Pembayaran Honor Asesor</td>
                <td class="text-end pe-3 text-danger">− Rp {{ number_format($pembayaranHonor,0,',','.') }}</td>
            </tr>
            <tr>
                <td class="ps-5">Pembayaran Biaya Operasional</td>
                <td class="text-end pe-3 text-danger">− Rp {{ number_format($pembayaranOps,0,',','.') }}</td>
            </tr>
            @if($pembayaranDistr > 0)
            <tr>
                <td class="ps-5">Distribusi ke Yayasan</td>
                <td class="text-end pe-3 text-danger">− Rp {{ number_format($pembayaranDistr,0,',','.') }}</td>
            </tr>
            @endif

            <tr class="table-light fw-bold">
                <td class="ps-3">Arus Kas Bersih dari Aktivitas Operasi</td>
                <td class="text-end pe-3 {{ $kasOperasi >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $kasOperasi >= 0 ? '+' : '−' }} Rp {{ number_format(abs($kasOperasi),0,',','.') }}
                </td>
            </tr>

            {{-- Saldo --}}
            <tr><td colspan="2" class="py-1 bg-white border-0"></td></tr>
            <tr class="table-secondary">
                <td class="ps-3 fw-bold">SALDO KAS DAN BANK</td>
                <td></td>
            </tr>
            <tr>
                <td class="ps-4">Saldo Awal (Kas + Bank tahun {{ $tahun - 1 }})</td>
                <td class="text-end pe-3">Rp {{ number_format($kasAwal,0,',','.') }}</td>
            </tr>
            <tr>
                <td class="ps-4">Kenaikan / (Penurunan) Kas</td>
                <td class="text-end pe-3 {{ $kasOperasi >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $kasOperasi >= 0 ? '+' : '' }} Rp {{ number_format($kasOperasi,0,',','.') }}
                </td>
            </tr>
            <tr class="fw-bold fs-6 {{ $kasAkhir >= 0 ? 'table-success' : 'table-danger' }}">
                <td class="ps-3">Saldo Akhir Kas dan Bank per 31 Desember {{ $tahun }}</td>
                <td class="text-end pe-3 {{ $kasAkhir >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($kasAkhir,0,',','.') }}
                </td>
            </tr>

            </tbody>
        </table>
    </div>
</div>

@endsection