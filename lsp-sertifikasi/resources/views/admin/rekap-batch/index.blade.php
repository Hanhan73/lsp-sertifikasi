@extends('layouts.app')

@section('title', 'Rekap Per Batch')
@section('page-title', 'Laporan Rekap Per Batch')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
@php
    $total = $data['total'];
    $query = array_filter($filters, fn($v) => $v !== null && $v !== '');
    $pct   = fn($n, $d) => $d > 0 ? number_format($n / $d * 100, 1, ',', '.') . '%' : '0%';
    $nMandiri = $data['rows']->where('is_mandiri', true)->sum('daftar');
@endphp

{{-- ── Filter ─────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.rekap-batch.index') }}" method="GET">
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
                    <a href="{{ route('admin.rekap-batch.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
        @if($errors->any())
        <div class="alert alert-danger mt-3 mb-0 py-2">{{ $errors->first() }}</div>
        @endif
        <small class="text-muted d-block mt-2">
            Periode berdasarkan <strong>tanggal asesmen</strong>. Jika periode diisi, batch yang belum dijadwalkan tidak ikut ditampilkan.
        </small>
    </div>
</div>

{{-- ── Ringkasan ──────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Jumlah Batch',     $data['n_batch'],  null,                                     'collection'],
        ['Total Asesi',      $total['daftar'],  null,                                     'people'],
        ['Asesi Kolektif',   $total['daftar'] - $nMandiri, $pct($total['daftar'] - $nMandiri, $total['daftar']), 'diagram-3'],
        ['Asesi Mandiri',    $nMandiri,         $pct($nMandiri, $total['daftar']),         'person'],
        ['Kompeten',         $total['kompeten'], $pct($total['kompeten'], $total['daftar']), 'check-circle'],
    ] as [$lbl, $val, $persen, $icon])
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-{{ $icon }} fs-2 text-secondary"></i>
                <div>
                    <div class="small text-muted">{{ $lbl }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($val, 0, ',', '.') }}</div>
                    @if($persen)
                    <div class="small text-muted">{{ $persen }} dari total asesi</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Tabel ──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="bi bi-collection"></i> Rekap Per Batch — <span class="text-muted fw-normal">{{ $periodeText }}</span></h5>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rekap-batch.export', $query + ['format' => 'excel']) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="{{ route('admin.rekap-batch.export', $query + ['format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle w-100 small" id="batch-table">
                <thead class="table-dark">
                    <tr>
                        <th>Batch</th>
                        <th>Asal Lembaga</th>
                        <th>TUK</th>
                        <th>Skema</th>
                        <th>Tanggal Asesmen</th>
                        <th class="text-center">Daftar</th>
                        <th class="text-center">Kompeten</th>
                        <th class="text-center">Belum Kompeten</th>
                        <th class="text-center">Belum Ada Hasil</th>
                        <th class="text-center">% Kompeten</th>
                        <th class="text-center">Jumlah Asesor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['rows'] as $r)
                    @php $rate = $r['daftar'] > 0 ? $r['kompeten'] / $r['daftar'] * 100 : 0; @endphp
                    <tr class="{{ $r['url'] ? 'batch-row' : '' }}" @if($r['url']) data-href="{{ $r['url'] }}" tabindex="0"
                        title="{{ $r['is_mandiri'] ? 'Buka daftar asesi mandiri di TUK ini' : 'Buka detail batch' }}" @endif>
                        <td class="text-nowrap {{ $r['is_mandiri'] ? 'fst-italic text-muted' : 'fw-semibold' }}"
                            data-order="{{ $r['is_mandiri'] ? 'zzz' : $r['label'] }}">
                            {{ $r['label'] }}
                        </td>
                        <td>
                            {{ $r['lembaga'] }}
                            @if($r['lembaga_src'] === 'asesi' && !$r['is_mandiri'])
                            <span class="badge text-bg-light border fw-normal ms-1" title="Nama lembaga di jadwal belum diisi, diambil dari isian asesi terbanyak">dari isian asesi</span>
                            @endif
                            @if($r['lembaga_lain'] > 0)
                            <span class="small text-muted ms-1" title="Ada {{ $r['lembaga_lain'] }} penulisan nama lembaga lain di batch ini">+{{ $r['lembaga_lain'] }} variasi</span>
                            @endif
                        </td>
                        <td title="{{ $r['tuk_full'] }}" data-order="{{ $r['tuk_full'] }}">{{ $r['tuk'] }}</td>
                        <td class="small" title="{{ $r['skema_full'] }}">{{ $r['skema'] }}</td>
                        <td class="small text-nowrap" data-order="{{ $r['tgl_iso'] }}">{{ $r['tanggal'] }}</td>
                        <td class="text-center fw-semibold">{{ $r['daftar'] }}</td>
                        <td class="text-center">{{ $r['kompeten'] }}</td>
                        <td class="text-center">{{ $r['bk'] }}</td>
                        <td class="text-center">{{ $r['belum'] }}</td>
                        <td class="text-center" data-order="{{ number_format($rate, 2, '.', '') }}">{{ number_format($rate, 1, ',', '.') }}%</td>
                        <td class="text-center">{{ $r['asesor'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="5">TOTAL ({{ $data['n_batch'] }} batch{{ $nMandiri ? ' + mandiri' : '' }})</td>
                        <td class="text-center">{{ $total['daftar'] }}</td>
                        <td class="text-center">{{ $total['kompeten'] }}</td>
                        <td class="text-center">{{ $total['bk'] }}</td>
                        <td class="text-center">{{ $total['belum'] }}</td>
                        <td class="text-center">{{ $pct($total['kompeten'], $total['daftar']) }}</td>
                        <td class="text-center" title="Jumlah asesor berbeda secara keseluruhan">{{ $total['asesor'] }}*</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <small class="text-muted d-block mt-1">
            <i class="bi bi-hand-index"></i> Klik baris untuk membuka detail batch.
            Asal lembaga diambil dari <strong>nama lembaga di jadwal</strong>; jika belum diisi, dari isian asesi terbanyak.
            Asesi mandiri dikelompokkan per TUK. * Total jumlah asesor = asesor berbeda secara keseluruhan.
        </small>
    </div>
</div>
@endsection

@push('styles')
<style>
.batch-row { cursor: pointer; }
#batch-table thead th { vertical-align: middle; }
#batch-table td.text-center { width: 1%; white-space: nowrap; }  /* kolom angka sesempit isinya */
.batch-row:focus-visible { outline: 2px solid #2a78d6; outline-offset: -2px; }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    $('#batch-table').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        autoWidth: false,
        pageLength: 50,
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'Semua']],
        order: [], // pertahankan urutan server: TUK, lalu batch terbaru
    });

    // Klik / Enter pada baris → halaman detail (link tidak di-intercept)
    $(document).on('click', '.batch-row', function (e) {
        if ($(e.target).closest('a, button').length) return;
        window.location = this.dataset.href;
    });
    $(document).on('keydown', '.batch-row', function (e) {
        if (e.key === 'Enter') window.location = this.dataset.href;
    });
});
</script>
@endpush
