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
    $selisih = $totalAset - $totalKewEkuitas;
    $kasManualKosong = $balance->kas == 0 && $mutasiBank > 0;
    $bankBelumDiinput = $saldoAwalBank == 0 && $mutasiBank > 0;
    $surplusNegatif = $surplus < 0;
    $piutangBelumLunas = $piutangAsesi > 0;
    $utangHonorAda = $utangHonor > 0;
@endphp

@if(abs($selisih) > 0)
<div class="alert alert-danger mb-3">
    <div class="fw-bold mb-2">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Neraca belum balance — Selisih: <span class="text-warning">Rp {{ number_format(abs($selisih),0,',','.') }}</span>
    </div>
    <table class="table table-sm table-borderless mb-2" style="font-size:.85rem;">
        <tr>
            <td class="text-muted" style="width:220px">Total Aset</td>
            <td class="fw-semibold">Rp {{ number_format($totalAset,0,',','.') }}</td>
        </tr>
        <tr>
            <td class="text-muted">Total Kewajiban + Ekuitas</td>
            <td class="fw-semibold">Rp {{ number_format($totalKewEkuitas,0,',','.') }}</td>
        </tr>
        <tr>
            <td class="text-muted">Selisih</td>
            <td class="fw-semibold text-danger">Rp {{ number_format(abs($selisih),0,',','.') }}
                ({{ $totalAset > $totalKewEkuitas ? 'Aset lebih besar' : 'Kew+Ekuitas lebih besar' }})
            </td>
        </tr>
    </table>

    @if($bankBelumDiinput || $kasManualKosong || $surplusNegatif)
    <strong class="small">Kemungkinan penyebab:</strong>
    <ul class="mb-0 mt-1 small">
        @if($bankBelumDiinput)
        <li>
            <strong>Saldo awal bank belum diinput.</strong>
            Mutasi bank dari jurnal = <strong class="text-warning">Rp {{ number_format($mutasiBank,0,',','.') }}</strong>,
            tapi saldo awal = 0. Saldo bank di neraca hanya Rp {{ number_format($bank,0,',','.') }}.
            <a href="{{ route('bendahara.laporan-keuangan.edit-saldo', ['tahun'=>$tahun]) }}"
               class="alert-link">→ Isi saldo awal bank</a>
        </li>
        @endif
        @if($kasManualKosong)
        <li>
            <strong>Kas = 0</strong> padahal ada transaksi masuk ke bank.
            Pastikan saldo kas sudah diinput dengan benar, atau memang seluruh penerimaan langsung ke rekening bank.
        </li>
        @endif
        @if($surplusNegatif)
        <li>
            <strong>Surplus negatif</strong> — beban melebihi pendapatan.
            Surplus: <span class="text-danger">Rp {{ number_format($surplus,0,',','.') }}</span>.
            Periksa data honor asesor dan biaya operasional.
        </li>
        @endif
    </ul>
    @endif

    @if($utangHonorAda || $piutangBelumLunas)
    <div class="mt-2 small text-muted border-top pt-2">
        <strong>Catatan (bukan penyebab tidak balance):</strong>
        <ul class="mb-0 mt-1">
            @if($utangHonorAda)
            <li>Utang honor asesor <strong>Rp {{ number_format($utangHonor,0,',','.') }}</strong> belum dilunasi — ini normal dan sudah masuk sisi kewajiban.</li>
            @endif
            @if($piutangBelumLunas)
            <li>Piutang asesi <strong>Rp {{ number_format($piutangAsesi,0,',','.') }}</strong> belum diterima — sudah masuk sisi aset.</li>
            @endif
        </ul>
    </div>
    @endif
</div>

@else
<div class="alert alert-success small mb-3">
    <i class="bi bi-check-circle-fill me-1"></i>
    <strong>Neraca balance.</strong>
    Total Aset = Total Kewajiban + Ekuitas = <strong>Rp {{ number_format($totalAset,0,',','.') }}</strong>
    @if($utangHonorAda)
    <span class="text-muted ms-2">| Utang honor Rp {{ number_format($utangHonor,0,',','.') }} masih outstanding.</span>
    @endif
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
                <td class="neraca-val">{{ number_format($utangHonor,0,',','.') }}</td>  {{-- ← ganti --}}
            </tr>

            {{-- Row 2: Bank (otomatis dari jurnal) --}}
            <tr>
                <td class="neraca-label">
                    Bank
                    <small class="d-block text-muted" style="font-size:.75rem;">
                        (awal Rp {{ number_format($saldoAwalBank,0,',','.') }}
                        + mutasi Rp {{ number_format($mutasiBank,0,',','.') }})
                    </small>
                </td>
                <td class="neraca-val">{{ number_format($bank,0,',','.') }}</td>
                <td class="neraca-label">Utang Operasional</td>
                <td class="neraca-val">{{ number_format($balance->utang_operasional,0,',','.') }}</td>
            </tr>

            {{-- Row 3 --}}
            <tr>
                <td class="neraca-label">Piutang Asesi</td>
                <td class="neraca-val">{{ number_format($piutangAsesi,0,',','.') }}</td>
                <td class="neraca-label fw-semibold text-warning" style="background:#fef9e7">Saldo Dana</td>
                <td class="neraca-val" style="background:#fef9e7">{{ number_format($balance->saldo_dana,0,',','.') }}</td>
            </tr>

            {{-- Row 4 --}}
            <tr>
                <td class="neraca-label">Perlengkapan</td>
                <td class="neraca-val">{{ number_format($balance->perlengkapan,0,',','.') }}</td>
                <td class="neraca-label fw-semibold text-warning" style="background:#fef9e7">
                    Surplus Tahun Berjalan
                    @if($summary['distribusi'] > 0)
                    <br><small class="text-muted fw-normal" style="font-size:.78rem;">
                        (setelah distribusi Rp {{ number_format($summary['distribusi'],0,',','.') }})
                    </small>
                    @endif
                </td>
                <td class="neraca-val {{ $surplus >= 0 ? '' : 'text-danger' }}" style="background:#fef9e7">
                    {{ number_format($surplus - $summary['distribusi'],0,',','.') }}  {{-- ← ganti --}}
                </td>
            </tr>

            {{-- Total --}}
            <tr>
                <td class="neraca-total">Total Aset</td>
                <td class="neraca-total-val">{{ number_format($totalAset,0,',','.') }}</td>  {{-- ← ganti --}}
                <td class="neraca-total">Total Kewajiban + Ekuitas</td>
                <td class="neraca-total-val">{{ number_format($totalKewEkuitas,0,',','.') }}</td>  {{-- ← ganti --}}
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