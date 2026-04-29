@extends('layouts.app')
@section('title', 'Neraca')
@section('page-title', 'Neraca Bentuk T')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@push('styles')
<style>
.neraca-header { background:#1a5276; color:#fff; text-align:center; padding:10px; font-weight:700; }
.neraca-sub    { background:#2e86c1; color:#fff; text-align:center; padding:6px; font-size:.85rem; }
.neraca-label  { padding:8px 12px; }
.neraca-val    { padding:8px 12px; text-align:right; font-weight:600; color:#e67e22; }
.neraca-total  { background:#1a5276; color:#fff; font-weight:700; padding:10px 12px; }
.neraca-total-val { background:#1a5276; color:#fff; font-weight:700; padding:10px 12px; text-align:right; }
.divider-row td { height:8px; background:#f8f9fa; border:0; }
</style>
@endpush

@section('content')

@include('bendahara.laporan-keuangan._filter', ['route' => 'bendahara.laporan-keuangan.neraca'])

{{-- Judul --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body text-center py-3">
        <div class="fw-bold fs-5">LSP-KAP</div>
        <div class="fw-bold text-primary">NERACA BENTUK T</div>
        <div class="text-muted small">Per 31 Desember {{ $tahun }}</div>
    </div>
</div>

{{-- Cek balance --}}
@php
    $selisih = $balance->total_aset - $balance->total_kewajiban_ekuitas;
@endphp

@if(abs($selisih) > 0)
<div class="alert alert-warning small mb-3">
    <i class="bi bi-exclamation-triangle me-1"></i>
    Neraca belum seimbang. Selisih: <strong>Rp {{ number_format(abs($selisih),0,',','.') }}</strong>.
    Periksa kembali saldo manual di <a href="{{ route('bendahara.laporan-keuangan.edit-saldo', ['tahun'=>$tahun]) }}">Input Saldo</a>.
</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-end border-bottom">
        <a href="{{ route('bendahara.laporan-keuangan.neraca', ['tahun'=>$tahun, 'export'=>'pdf']) }}"
           class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0" style="font-size:.9rem;">
            <thead>
                <tr>
                    <th colspan="2" class="neraca-header" style="width:50%">ASET</th>
                    <th colspan="2" class="neraca-header" style="width:50%">KEWAJIBAN &amp; EKUITAS</th>
                </tr>
                <tr>
                    <th colspan="2" class="neraca-sub">Rp</th>
                    <th colspan="2" class="neraca-sub">Rp</th>
                </tr>
            </thead>
            <tbody>

                {{-- Row 1 --}}
                <tr>
                    <td class="neraca-label">Kas</td>
                    <td class="neraca-val">{{ number_format($balance->kas,0,',','.') }}</td>
                    <td class="neraca-label">Utang Honor Asesor</td>
                    <td class="neraca-val">{{ number_format($balance->utang_honor,0,',','.') }}</td>
                </tr>

                {{-- Row 2 --}}
                <tr>
                    <td class="neraca-label">Bank</td>
                    <td class="neraca-val">{{ number_format($balance->bank,0,',','.') }}</td>
                    <td class="neraca-label">Utang Operasional</td>
                    <td class="neraca-val">{{ number_format($balance->utang_operasional,0,',','.') }}</td>
                </tr>

                {{-- Row 3 --}}
                <tr>
                    <td class="neraca-label">Piutang Asesi</td>
                    <td class="neraca-val">{{ number_format($balance->piutang_asesi,0,',','.') }}</td>
                    <td class="neraca-label">Hutang Distribusi Yayasan</td>
                    <td class="neraca-val">{{ number_format($balance->hutang_distribusi,0,',','.') }}</td>
                </tr>

                {{-- Row 4 --}}
                <tr>
                    <td class="neraca-label">Perlengkapan</td>
                    <td class="neraca-val">{{ number_format($balance->perlengkapan,0,',','.') }}</td>
                    <td class="neraca-label fw-semibold text-warning" style="background:#fef9e7">Saldo Dana</td>
                    <td class="neraca-val" style="background:#fef9e7">{{ number_format($balance->saldo_dana,0,',','.') }}</td>
                </tr>

                {{-- Row 5 --}}
                <tr>
                    <td class="neraca-label" colspan="2"></td>
                    <td class="neraca-label fw-semibold text-warning" style="background:#fef9e7">
                        Surplus Tahun Berjalan
                        @if($balance->distribusi_yayasan > 0)
                        <br><small class="text-muted fw-normal" style="font-size:.78rem;">
                            (setelah distribusi Rp {{ number_format($balance->distribusi_yayasan,0,',','.') }})
                        </small>
                        @endif
                    </td>
                    <td class="neraca-val {{ $balance->surplus >= 0 ? '' : 'text-danger' }}" style="background:#fef9e7">
                        {{ number_format($balance->surplus - $balance->distribusi_yayasan,0,',','.') }}
                    </td>
                </tr>

                {{-- Total --}}
                <tr>
                    <td class="neraca-total">Total Aset</td>
                    <td class="neraca-total-val">{{ number_format($balance->total_aset,0,',','.') }}</td>
                    <td class="neraca-total">Total Kewajiban + Ekuitas</td>
                    <td class="neraca-total-val">{{ number_format($balance->total_kewajiban_ekuitas,0,',','.') }}</td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

<div class="text-muted small text-center">
    * Piutang Asesi dan Utang Honor dihitung otomatis dari data sistem.
    Kas, Bank, Perlengkapan, Utang Operasional, dan Saldo Dana diinput manual.
</div>

@endsection