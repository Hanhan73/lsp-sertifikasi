@extends('layouts.app')
@section('title', 'Buku Besar')
@section('page-title', 'Buku Besar')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
            <label class="fw-semibold mb-0"><i class="bi bi-calendar3"></i> Tahun:</label>
            <select name="tahun" class="form-select form-select-sm" style="width:110px" onchange="this.form.submit()">
                @foreach($tahunList as $t)
                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <label class="fw-semibold mb-0 ms-2"><i class="bi bi-book"></i> Akun:</label>
            <select name="akun" class="form-select form-select-sm" style="width:240px" onchange="this.form.submit()">
                @foreach($akunList as $key => $label)
                <option value="{{ $key }}" {{ $akun == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ route('bendahara.laporan-keuangan.index', ['tahun' => $tahun]) }}"
               class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </form>
    </div>
</div>

@php
    $totalDebit  = $entries->sum('debit');
    $totalKredit = $entries->sum('kredit');

    // Hitung saldo berjalan
    $saldoBerjalan = 0;
    $entriesWithSaldo = $entries->map(function($e) use (&$saldoBerjalan) {
        $saldoBerjalan += $e['debit'] - $e['kredit'];
        $e['saldo'] = $saldoBerjalan;
        return $e;
    });
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
        <div>
            <span class="fw-bold fs-6">BUKU BESAR — {{ $akunList[$akun] ?? $akun }}</span><br>
            <small class="text-muted">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }}</small>
        </div>
        <span class="badge bg-secondary">{{ $entries->count() }} transaksi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width:100px">Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-end" style="width:150px">Debit (Rp)</th>
                        <th class="text-end" style="width:150px">Kredit (Rp)</th>
                        <th class="text-end" style="width:150px">Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($entriesWithSaldo as $e)
                <tr>
                    <td>{{ $e['tanggal'] }}</td>
                    <td>{{ $e['keterangan'] }}</td>
                    <td class="text-end text-success">
                        {{ $e['debit'] > 0 ? number_format($e['debit'],0,',','.') : '-' }}
                    </td>
                    <td class="text-end text-danger">
                        {{ $e['kredit'] > 0 ? number_format($e['kredit'],0,',','.') : '-' }}
                    </td>
                    <td class="text-end fw-semibold {{ $e['saldo'] >= 0 ? 'text-dark' : 'text-danger' }}">
                        {{ number_format($e['saldo'],0,',','.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size:2rem"></i><br>
                        Tidak ada transaksi untuk akun ini.
                    </td>
                </tr>
                @endforelse
                </tbody>
                @if($entries->count())
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="2" class="fw-bold text-end">TOTAL</td>
                        <td class="text-end fw-bold text-success">{{ number_format($totalDebit,0,',','.') }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($totalKredit,0,',','.') }}</td>
                        <td class="text-end fw-bold">
                            {{ number_format($totalDebit - $totalKredit,0,',','.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection