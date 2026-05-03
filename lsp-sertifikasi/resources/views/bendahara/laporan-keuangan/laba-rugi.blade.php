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

            {{-- A. PENDAPATAN ASESMEN --}}
            <tr class="table-success">
                <td colspan="2" class="fw-bold ps-3">A. PENDAPATAN SERTIFIKASI</td>
            </tr>
            @foreach($pendapatanSkema as $s)
            <tr class="text-muted" style="font-size:.82rem;">
                <td class="ps-5"><i class="bi bi-dash me-1"></i>{{ $s->skema }}</td>
                <td class="text-end pe-3">Rp {{ number_format($s->total,0,',','.') }}</td>
            </tr>
            @endforeach
            <tr class="table-light fw-bold">
                <td class="ps-3">Total Pendapatan Sertifikasi</td>
                <td class="text-end pe-3 text-success">Rp {{ number_format($summary['pendapatan_asesmen'],0,',','.') }}</td>
            </tr>

            {{-- B. BEBAN OPERASIONAL --}}
            <tr><td colspan="2" class="py-1 bg-white border-0"></td></tr>
            <tr class="table-danger">
                <td colspan="2" class="fw-bold ps-3">B. BEBAN OPERASIONAL</td>
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
                <td class="ps-5"><i class="bi bi-dash me-1"></i>{{ $b->uraian }}</td>
                <td class="text-end pe-3">Rp {{ number_format($b->total,0,',','.') }}</td>
            </tr>
            @endforeach
            <tr class="table-light fw-bold">
                <td class="ps-3">Total Beban Operasional</td>
                <td class="text-end pe-3 text-danger">
                    Rp {{ number_format($summary['beban_honor'] + $summary['beban_ops'],0,',','.') }}
                </td>
            </tr>

            {{-- C. PENDAPATAN LAIN-LAIN --}}
            <tr><td colspan="2" class="py-1 bg-white border-0"></td></tr>
            <tr class="table-info">
                <td colspan="2" class="fw-bold ps-3">C. PENDAPATAN LAIN-LAIN</td>
            </tr>
            @forelse($pendapatanLuarDetail ?? [] as $pl)
            <tr>
                <td class="ps-4">{{ $pl->nama }}</td>
                <td class="text-end pe-3 text-info">Rp {{ number_format($pl->total,0,',','.') }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="ps-4 text-muted fst-italic">Tidak ada pendapatan lain-lain.</td></tr>
            @endforelse
            <tr class="table-light fw-bold">
                <td class="ps-3">Total Pendapatan Lain-lain</td>
                <td class="text-end pe-3 text-info">Rp {{ number_format($summary['pendapatan_luar'],0,',','.') }}</td>
            </tr>

            {{-- SURPLUS OPERASIONAL --}}
            <tr><td colspan="2" class="py-1 bg-white border-0"></td></tr>
            @php $surplusKas = $summary['pendapatan_asesmen'] + $summary['pendapatan_luar'] - $summary['beban_honor'] - $summary['beban_ops']; @endphp
            <tr class="{{ $surplusKas >= 0 ? 'table-success' : 'table-danger' }} fw-bold">
                <td class="ps-3">Surplus Kas Operasional (A - B + C)</td>
                <td class="text-end pe-3 {{ $surplusKas >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($surplusKas,0,',','.') }}
                </td>
            </tr>

            {{-- E. REKONSILIASI --}}
            <tr><td colspan="2" class="py-1 bg-white border-0"></td></tr>
            <tr class="table-warning">
                <td colspan="2" class="fw-bold ps-3">D. REKONSILIASI NERACA</td>
            </tr>
            <tr>
                <td class="ps-4 text-success">Tambah: Piutang Asesi Belum Diterima</td>
                <td class="text-end pe-3 text-success">+ Rp {{ number_format($summary['pendapatan'] > 0 ? $this->saldoAkun('1-003', $tahun) : 0,0,',','.') }}</td>
            </tr>

            @if(($summary['piutang_lainnya'] ?? 0) > 0)
            <tr>
                <td class="ps-4 text-success">
                    Tambah: Piutang Lainnya Belum Diterima
                    <small class="d-block text-muted" style="font-size:.78rem">
                        @foreach($piutangLainnyaDetail ?? [] as $pr)
                            {{ $pr->nama_pihak }} ({{ $pr->uraian }}) Rp {{ number_format($pr->jumlah,0,',','.') }}@if(!$loop->last), @endif
                        @endforeach
                    </small>
                </td>
                <td class="text-end pe-3 text-success">+ Rp {{ number_format($summary['piutang_lainnya'],0,',','.') }}</td>
            </tr>
            @endif

            <tr>
                <td class="ps-4 text-danger">Kurang: Utang Honor Asesor Belum Dibayar</td>
                <td class="text-end pe-3 text-danger">− Rp {{ number_format($summary['beban_honor'],0,',','.') }}</td>
            </tr>

            @if($summary['distribusi'] > 0)
            <tr>
                <td class="ps-4 text-danger">Kurang: Distribusi ke PT</td>
                <td class="text-end pe-3 text-danger">− Rp {{ number_format($summary['distribusi'],0,',','.') }}</td>
            </tr>
            @endif

            <tr class="fw-bold fs-6 table-dark">
                <td class="ps-3">LABA / (RUGI) BERSIH TAHUN {{ $tahun }}</td>
                <td class="text-end pe-3 {{ $summary['surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($summary['surplus'],0,',','.') }}
                </td>
            </tr>

            </tbody>
        </table>
    </div>
</div>

@endsection