@extends('layouts.app')

@section('title', 'Rekap Asesi')
@section('page-title', 'Laporan Rekap Asesi')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $total = $rekap['total'];
    $query = array_filter($filters, fn($v) => $v !== null && $v !== '');
@endphp

{{-- ── Filter ─────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.rekap-asesi.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Tgl Asesmen Dari</label>
                    <input type="date" name="dari" class="form-control" value="{{ $filters['dari'] }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="sampai" class="form-control" value="{{ $filters['sampai'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Skema</label>
                    <select name="skema_id" class="form-select">
                        <option value="">Semua Skema</option>
                        @foreach($skemas as $s)
                        <option value="{{ $s->id }}" @selected($filters['skema_id'] === $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">TUK</label>
                    <select name="tuk_id" class="form-select">
                        <option value="">Semua TUK</option>
                        @foreach($tuks as $t)
                        <option value="{{ $t->id }}" @selected($filters['tuk_id'] === $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Tampilkan</button>
                    <a href="{{ route('admin.rekap-asesi.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
        @if($errors->any())
        <div class="alert alert-danger mt-3 mb-0 py-2">{{ $errors->first() }}</div>
        @endif
        <small class="text-muted d-block mt-2">
            Periode berdasarkan <strong>tanggal asesmen</strong>. Jika periode diisi, asesi yang belum dijadwalkan tidak ikut dihitung.
        </small>
    </div>
</div>

{{-- ── Ringkasan ──────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total Daftar', $total['daftar'], 'primary', 'people'],
        ['Kompeten', $total['kompeten'], 'success', 'check-circle'],
        ['Belum Kompeten', $total['bk'], 'danger', 'x-circle'],
        ['Belum Ada Hasil', $total['belum'], 'warning', 'hourglass-split'],
        ['Asesor Ditugaskan', $total['asesor'], 'info', 'person-badge'],
    ] as [$lbl, $val, $color, $icon])
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-{{ $icon }} fs-2 text-{{ $color }}"></i>
                <div>
                    <div class="small text-muted">{{ $lbl }}</div>
                    <div class="fs-4 fw-bold">{{ number_format($val, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Tabel rekap (tab) ──────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="bi bi-table"></i> Rekap Asesi — <span class="text-muted fw-normal">{{ $periodeText }}</span></h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rekap-asesi.export', $query + ['format' => 'excel']) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="{{ route('admin.rekap-asesi.export', $query + ['format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" role="tablist">
            @foreach($tabs as $key => $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                    data-bs-target="#tab-{{ $key }}" type="button" role="tab">
                    {{ $tab['title'] }}
                    <span class="badge bg-secondary ms-1">{{ count($rekap['rows'][$key]) }}</span>
                </button>
            </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($tabs as $key => $tab)
            @php $isAsesor = $key === 'asesor'; @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $key }}" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle rekap-table w-100">
                        <thead class="table-dark">
                            <tr>
                                <th>{{ $tab['label'] }}</th>
                                <th class="text-center">{{ $isAsesor ? 'Jumlah Asesi Ditangani' : 'Daftar' }}</th>
                                <th class="text-center">Kompeten</th>
                                <th class="text-center">Belum Kompeten</th>
                                <th class="text-center">Belum Ada Hasil</th>
                                @unless($isAsesor)
                                <th class="text-center">Jumlah Asesor Ditugaskan</th>
                                @endunless
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekap['rows'][$key] as $r)
                            <tr>
                                <td data-order="{{ $r['order'] }}">{{ $r['label'] }}</td>
                                <td class="text-center fw-semibold">{{ $r['daftar'] }}</td>
                                <td class="text-center text-success">{{ $r['kompeten'] }}</td>
                                <td class="text-center text-danger">{{ $r['bk'] }}</td>
                                <td class="text-center text-warning-emphasis">{{ $r['belum'] }}</td>
                                @unless($isAsesor)
                                <td class="text-center">{{ $r['asesor'] }}</td>
                                @endunless
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-center">{{ $total['daftar'] }}</td>
                                <td class="text-center">{{ $total['kompeten'] }}</td>
                                <td class="text-center">{{ $total['bk'] }}</td>
                                <td class="text-center">{{ $total['belum'] }}</td>
                                @unless($isAsesor)
                                <td class="text-center" title="Jumlah asesor berbeda secara keseluruhan">{{ $total['asesor'] }}*</td>
                                @endunless
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @unless($isAsesor)
                <small class="text-muted">* Total jumlah asesor = asesor berbeda secara keseluruhan, bukan penjumlahan kolom.</small>
                @endunless
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('.rekap-table').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        paging: false,
        info: false,
        order: [[0, 'asc']],
    });
    // Sesuaikan lebar kolom saat tab yang tersembunyi ditampilkan
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });
});
</script>
@endpush
