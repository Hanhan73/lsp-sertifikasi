@extends('layouts.app')
@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan Keuangan')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

{{-- Filter tahun --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <label class="fw-semibold mb-0"><i class="bi bi-calendar3"></i> Tahun:</label>
            <form method="GET" class="mb-0">
                <select name="tahun" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
            <span class="text-muted small">Periode tahun <strong>{{ $tahun }}</strong></span>
            <a href="{{ route('bendahara.laporan-keuangan.edit-saldo', ['tahun' => $tahun]) }}"
               class="btn btn-sm btn-outline-primary ms-auto">
                <i class="bi bi-pencil-square"></i> Input Saldo Manual
            </a>
        </div>
    </div>
</div>

{{-- Summary mini --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #11998e !important;">
            <div class="text-muted small">Pendapatan</div>
            <div class="fw-bold text-success fs-5">Rp {{ number_format($summary['pendapatan'],0,',','.') }}</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #f5576c !important;">
            <div class="text-muted small">Total Beban</div>
            <div class="fw-bold text-danger fs-5">Rp {{ number_format($summary['beban_honor'] + $summary['beban_ops'],0,',','.') }}</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #f7971e !important;">
            <div class="text-muted small">Surplus / Defisit</div>
            <div class="fw-bold fs-5 {{ $summary['surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format($summary['surplus'],0,',','.') }}
            </div>
        </div>
    </div>
</div>

{{-- Menu laporan --}}
<div class="row g-3">
    @php
    $menus = [
        ['icon'=>'bi-file-earmark-bar-graph','color'=>'success','title'=>'Laporan Laba Rugi',
         'desc'=>'Pendapatan, beban, dan surplus/defisit tahun berjalan.',
         'route'=>route('bendahara.laporan-keuangan.laba-rugi', ['tahun'=>$tahun])],
        ['icon'=>'bi-layout-split','color'=>'primary','title'=>'Neraca Bentuk T',
         'desc'=>'Posisi aset, kewajiban, dan ekuitas LSP.',
         'route'=>route('bendahara.laporan-keuangan.neraca', ['tahun'=>$tahun])],
        ['icon'=>'bi-arrow-left-right','color'=>'info','title'=>'Laporan Arus Kas',
         'desc'=>'Penerimaan dan pengeluaran kas periode berjalan.',
         'route'=>route('bendahara.laporan-keuangan.arus-kas', ['tahun'=>$tahun])],
        ['icon'=>'bi-graph-up-arrow','color'=>'warning','title'=>'Perubahan Modal',
         'desc'=>'Mutasi saldo dana dan surplus ekuitas.',
         'route'=>route('bendahara.laporan-keuangan.perubahan-modal', ['tahun'=>$tahun])],
        ['icon'=>'bi-send-arrow-down','color'=>'danger','title'=>'Distribusi ke Yayasan',
         'desc'=>'Pencatatan distribusi surplus dan jurnal balik.',
         'route'=>route('bendahara.laporan-keuangan.distribusi', ['tahun'=>$tahun])],
        ['icon'=>'bi-clock-history','color'=>'secondary','title'=>'Transaksi Harian',
         'desc'=>'Jurnal harian seluruh transaksi per tanggal.',
         'route'=>route('bendahara.laporan-keuangan.transaksi-harian')],
        ['icon'=>'bi-book','color'=>'dark','title'=>'Buku Besar',
         'desc'=>'Mutasi per akun sepanjang tahun.',
         'route'=>route('bendahara.laporan-keuangan.buku-besar', ['tahun'=>$tahun])],
    ];
    @endphp

    @foreach($menus as $m)
    <div class="col-md-6 col-xl-4">
        <a href="{{ $m['route'] }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-shadow" style="transition:.2s">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                        bg-{{ $m['color'] }} bg-opacity-10"
                        style="width:48px;height:48px;">
                        <i class="bi {{ $m['icon'] }} text-{{ $m['color'] }} fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $m['title'] }}</div>
                        <div class="text-muted small">{{ $m['desc'] }}</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

@endsection