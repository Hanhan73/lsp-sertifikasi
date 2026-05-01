@extends('layouts.app')
@section('title', 'Distribusi ke Yayasan')
@section('page-title', 'Distribusi ke Yayasan')
@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@section('content')

@include('direktur.keuangan._filter', ['route' => 'direktur.keuangan.distribusi'])

<div class="row g-3">

    {{-- Info distribusi --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-send-arrow-down text-danger me-2"></i>
                Distribusi Tahun {{ $tahun }}
            </div>
            <div class="card-body">
                <div class="alert alert-info small mb-3">
                    Surplus tahun {{ $tahun }} (dari jurnal):
                    <strong class="{{ $summary['surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($summary['surplus'],0,',','.') }}
                    </strong>
                </div>

                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="text-muted">Total Distribusi</td>
                        <td class="fw-bold text-end">Rp {{ number_format($balance->distribusi_yayasan,0,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Hutang Distribusi</td>
                        <td class="fw-bold text-end text-danger">Rp {{ number_format($balance->hutang_distribusi,0,',','.') }}</td>
                    </tr>
                    @if($balance->tanggal_distribusi)
                    <tr>
                        <td class="text-muted">Tanggal Distribusi</td>
                        <td class="fw-bold text-end">{{ $balance->tanggal_distribusi->translatedFormat('d F Y') }}</td>
                    </tr>
                    @endif
                    @if($balance->catatan_distribusi)
                    <tr>
                        <td class="text-muted" colspan="2">{{ $balance->catatan_distribusi }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Info jurnal balik --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-arrow-counterclockwise text-warning me-2"></i>
                Jurnal Balik Akhir Tahun
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Jurnal balik dilakukan di awal tahun berikutnya untuk memindahkan saldo distribusi
                    ke akun <strong>Hutang Distribusi Yayasan</strong>.
                </p>

                <div class="card bg-light border-0 mb-3 p-3" style="font-size:.85rem;">
                    <div><strong>Jurnal Distribusi:</strong></div>
                    <div class="ms-3 text-success">
                        Dr. Surplus Tahun Berjalan &nbsp;&nbsp;
                        Rp {{ number_format($balance->distribusi_yayasan,0,',','.') }}
                    </div>
                    <div class="ms-5 text-danger">
                        Cr. Hutang Distribusi Yayasan &nbsp;&nbsp;
                        Rp {{ number_format($balance->distribusi_yayasan,0,',','.') }}
                    </div>
                </div>

                @if($balance->jurnal_balik_done)
                <div class="alert alert-success small mb-0">
                    <i class="bi bi-check-circle me-1"></i>
                    Jurnal balik tahun {{ $tahun }} sudah dilakukan.
                </div>
                @else
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Jurnal balik tahun {{ $tahun }} belum dilakukan.
                    Bendahara perlu melakukan ini di awal tahun {{ $tahun + 1 }}.
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection