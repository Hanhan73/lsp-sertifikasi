@extends('layouts.app')
@section('title', 'Buku Besar')
@section('page-title', 'Buku Besar')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

{{-- Filter --}}
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
            <select name="akun_id" class="form-select form-select-sm" style="width:280px" onchange="this.form.submit()">
                <option value="">— Pilih Akun —</option>
                @foreach($akunList->groupBy('tipe') as $tipe => $group)
                <optgroup label="{{ \App\Models\ChartOfAccount::tipeList()[$tipe] ?? $tipe }}">
                    @foreach($group as $akun)
                    <option value="{{ $akun->id }}" {{ $selectedAkun?->id == $akun->id ? 'selected' : '' }}>
                        {{ $akun->kode }} — {{ $akun->nama }}
                    </option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('bendahara.laporan-keuangan.buku-besar.export', ['tahun'=>$tahun,'akun_id'=>$selectedAkun?->id,'format'=>'pdf']) }}"
                class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="{{ route('bendahara.laporan-keuangan.buku-besar.export', ['tahun'=>$tahun,'akun_id'=>$selectedAkun?->id,'format'=>'excel']) }}"
                class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>

            <a href="{{ route('bendahara.laporan-keuangan.index', ['tahun' => $tahun]) }}"
                class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </form>
    </div>
</div>

@if($selectedAkun)

{{-- Info akun --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3 d-flex align-items-center gap-3 flex-wrap">
        <div>
            <code class="fs-5 text-primary fw-bold">{{ $selectedAkun->kode }}</code>
            <span class="fw-semibold ms-2 fs-6">{{ $selectedAkun->nama }}</span>
        </div>
        <span class="badge bg-{{ $selectedAkun->tipe_badge }} ms-1">{{ $selectedAkun->tipe_label }}</span>
        @if($selectedAkun->sub_tipe)
        <span class="badge bg-light text-dark border">{{ $selectedAkun->sub_tipe_label }}</span>
        @endif
        @if($selectedAkun->keterangan)
        <span class="text-muted small">{{ $selectedAkun->keterangan }}</span>
        @endif
        <div class="ms-auto text-muted small">
            Saldo normal:
            <strong>{{ in_array($selectedAkun->tipe, ['aset','beban']) ? 'Debit' : 'Kredit' }}</strong>
        </div>
    </div>
</div>

@php
$totalDebit = $entries->sum('debit');
$totalKredit = $entries->sum('kredit');
$saldoAkhir = $entries->last()['saldo'] ?? 0;
@endphp

{{-- Summary --}}
<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Total Debit</div>
            <div class="fw-bold text-success">Rp {{ number_format($totalDebit,0,',','.') }}</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Total Kredit</div>
            <div class="fw-bold text-danger">Rp {{ number_format($totalKredit,0,',','.') }}</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Saldo Akhir</div>
            <div class="fw-bold {{ $saldoAkhir >= 0 ? 'text-dark' : 'text-danger' }}">
                Rp {{ number_format($saldoAkhir,0,',','.') }}
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
        <div>
            <span class="fw-bold">BUKU BESAR — {{ $selectedAkun->kode }} {{ $selectedAkun->nama }}</span><br>
            <small class="text-muted">Periode 1 Januari {{ $tahun }} — 31 Desember {{ $tahun }}</small>
        </div>
        <span class="badge bg-secondary">{{ $entries->count() }} transaksi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width:120px">No. Jurnal</th>
                        <th style="width:100px">Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-end" style="width:150px">Debit (Rp)</th>
                        <th class="text-end" style="width:150px">Kredit (Rp)</th>
                        <th class="text-end" style="width:150px">Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $e)
                    <tr>
                        <td><code class="small">{{ $e['nomor'] }}</code></td>
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
                            Tidak ada transaksi untuk akun ini di tahun {{ $tahun }}.
                            @if($selectedAkun->is_system === false)
                            <br><small>Akun ini belum memiliki mapping transaksi otomatis.</small>
                            @endif
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
                        <td class="text-end fw-bold">{{ number_format($saldoAkhir,0,',','.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@else

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-book" style="font-size:3rem"></i><br>
        Pilih akun dari dropdown di atas untuk melihat buku besar.
    </div>
</div>

@endif

@endsection