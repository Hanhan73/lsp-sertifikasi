{{-- ═══════════════════════════════════════════════
     transaksi-harian.blade.php
     ═══════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'Transaksi Harian')
@section('page-title', 'Transaksi Harian')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
            <label class="fw-semibold mb-0"><i class="bi bi-calendar-event"></i> Tanggal:</label>
            <input type="date" name="tanggal" class="form-control form-control-sm" style="width:160px"
                value="{{ $tanggal }}" onchange="this.form.submit()">
            <span class="text-muted small">
                {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
            </span>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('bendahara.laporan-keuangan.transaksi-harian', ['tanggal' => \Carbon\Carbon::parse($tanggal)->subDay()->toDateString()]) }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <a href="{{ route('bendahara.laporan-keuangan.transaksi-harian', ['tanggal' => today()->toDateString()]) }}"
                    class="btn btn-sm btn-outline-primary">Hari Ini</a>
                <a href="{{ route('bendahara.laporan-keuangan.transaksi-harian', ['tanggal' => \Carbon\Carbon::parse($tanggal)->addDay()->toDateString()]) }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </form>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('bendahara.laporan-keuangan.transaksi-harian.export', ['tanggal'=>$tanggal,'format'=>'pdf']) }}"
                class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
            <a href="{{ route('bendahara.laporan-keuangan.transaksi-harian.export', ['tanggal'=>$tanggal,'format'=>'excel']) }}"
                class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
        </div>
    </div>
</div>

{{-- Summary harian --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Total Debit (masuk)</div>
            <div class="fw-bold text-success fs-5">Rp {{ number_format($totalDebit,0,',','.') }}</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Total Kredit (keluar)</div>
            <div class="fw-bold text-danger fs-5">Rp {{ number_format($totalKredit,0,',','.') }}</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Saldo Hari Ini</div>
            @php $saldoHari = $totalDebit - $totalKredit; @endphp
            <div class="fw-bold fs-5 {{ $saldoHari >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format($saldoHari,0,',','.') }}
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold border-bottom">
        <i class="bi bi-journal-text me-2"></i>
        Jurnal Harian — {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
        <span class="badge bg-secondary ms-2">{{ $transaksi->count() }} transaksi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width:70px">Waktu</th>
                        <th>Keterangan</th>
                        <th style="width:160px">Akun Debit</th>
                        <th style="width:180px">Akun Kredit</th>
                        <th class="text-center" style="width:100px">Tipe</th>
                        <th class="text-end" style="width:140px">Debit (Rp)</th>
                        <th class="text-end" style="width:140px">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $t)
                    <tr>
                        <td class="text-muted">{{ $t['waktu'] }}</td>
                        <td>{{ $t['keterangan'] }}</td>
                        <td class="small text-success">{{ $t['akun_debit'] }}</td>
                        <td class="small text-danger">{{ $t['akun_kredit'] }}</td>
                        <td class="text-center">
                            @php
                            $tipeBadge = match($t['tipe']) {
                                'pemasukan'  => ['bg-success',          'Masuk'],
                                'piutang'    => ['bg-primary',           'Piutang'],
                                'honor'      => ['bg-danger',            'Honor Bayar'],
                                'beban'      => ['bg-warning text-dark', 'Beban Honor'],
                                'biaya_ops'  => ['bg-info text-dark',    'Operasional'],
                                'distribusi' => ['bg-secondary',         'Distribusi'],
                                default      => ['bg-light text-dark',   'Umum'],
                            };
                            @endphp
                            <span class="badge {{ $tipeBadge[0] }}">{{ $tipeBadge[1] }}</span>
                        </td>
                        <td class="text-end fw-semibold text-success">
                            {{ $t['debit'] > 0 ? number_format($t['debit'],0,',','.') : '-' }}
                        </td>
                        <td class="text-end fw-semibold text-danger">
                            {{ $t['kredit'] > 0 ? number_format($t['kredit'],0,',','.') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size:2rem"></i><br>
                            Tidak ada transaksi pada tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($transaksi->count())
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="5" class="fw-bold text-end">TOTAL</td>
                        <td class="text-end fw-bold text-success">{{ number_format($totalDebit,0,',','.') }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($totalKredit,0,',','.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection