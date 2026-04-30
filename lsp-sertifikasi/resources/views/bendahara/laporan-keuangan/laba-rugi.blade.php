@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')
@section('page-title', 'Laporan Laba Rugi')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@include('bendahara.laporan-keuangan._filter', ['route' => 'bendahara.laporan-keuangan.laba-rugi'])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
        <div>
            <span class="fw-bold fs-6">LAPORAN LABA RUGI (SURPLUS/DEFISIT)</span><br>
            <small class="text-muted">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }}</small>
        </div>
        <a href="{{ route('bendahara.laporan-keuangan.laba-rugi', ['tahun'=>$tahun, 'export'=>'pdf']) }}"
           class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0" style="font-size:.9rem;">
            <tbody>

            {{-- PENDAPATAN --}}
            <tr class="table-success">
                <td colspan="2" class="fw-bold ps-3">PENDAPATAN</td>
            </tr>
            <tr>
                <td class="ps-4">Pendapatan Sertifikasi Kompetensi</td>
                <td class="text-end pe-3 fw-semibold">Rp {{ number_format($summary['pendapatan'],0,',','.') }}</td>
            </tr>

            @foreach($pendapatanSkema as $s)
            <tr class="text-muted" style="font-size:.82rem;">
                <td class="ps-5"><i class="bi bi-dash me-1"></i>{{ $s->skema }}</td>
                <td class="text-end pe-3">Rp {{ number_format($s->total,0,',','.') }}</td>
            </tr>
            @endforeach

            <tr class="table-light fw-bold">
                <td class="ps-3">Total Pendapatan</td>
                <td class="text-end pe-3 text-success">Rp {{ number_format($summary['pendapatan'],0,',','.') }}</td>
            </tr>

            {{-- BEBAN --}}
            <tr><td colspan="2" class="py-1 bg-white border-0"></td></tr>
            <tr class="table-danger">
                <td colspan="2" class="fw-bold ps-3">BEBAN</td>
            </tr>
            <tr>
                <td class="ps-4">Beban Honor Asesor</td>
                <td class="text-end pe-3">Rp {{ number_format($summary['beban_honor'],0,',','.') }}</td>
            </tr>
            <tr>
                <td class="ps-4">Beban Operasional</td>
                <td class="text-end pe-3">Rp {{ number_format($summary['beban_ops'],0,',','.') }}</td>
            </tr>

            @foreach($bebanOpsDetail as $b)
            <tr class="text-muted" style="font-size:.82rem;">
                <td class="ps-5"><i class="bi bi-dash me-1"></i>{{ $b->uraian }} ({{ $b->nama_penerima }})</td>
                <td class="text-end pe-3">Rp {{ number_format($b->total,0,',','.') }}</td>
            </tr>
            @endforeach

            <tr class="table-light fw-bold">
                <td class="ps-3">Total Beban</td>
                <td class="text-end pe-3 text-danger">
                    Rp {{ number_format($summary['beban_honor'] + $summary['beban_ops'],0,',','.') }}
                </td>
            </tr>

            {{-- SURPLUS --}}
            <tr><td colspan="2" class="py-1 bg-white border-0"></td></tr>
            <tr class="{{ $summary['surplus'] >= 0 ? 'table-success' : 'table-danger' }} fw-bold fs-6">
                <td class="ps-3">{{ $summary['surplus'] >= 0 ? 'SURPLUS' : 'DEFISIT' }} TAHUN BERJALAN</td>
                <td class="text-end pe-3 {{ $summary['surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format(abs($summary['surplus']),0,',','.') }}
                </td>
            </tr>

            @if($summary['distribusi'] > 0)
            <tr>
                <td class="ps-4 text-muted">Distribusi ke Yayasan</td>
                <td class="text-end pe-3 text-danger">
                    (Rp {{ number_format($summary['distribusi'],0,',','.') }})
                </td>
            </tr>
            <tr class="fw-bold">
                <td class="ps-3">Surplus Setelah Distribusi</td>
                <td class="text-end pe-3 {{ ($summary['surplus'] - $summary['distribusi']) >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($summary['surplus'] - $summary['distribusi'],0,',','.') }}
                </td>
            </tr>
            @endif

            </tbody>
        </table>
    </div>
</div>

@endsection