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
    $kolektif = $total['daftar'] - $total['mandiri'];
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
        ['Jumlah Batch',   $total['n_batch'],  null,                                    'collection'],
        ['Total Asesi',    $total['daftar'],   null,                                    'people'],
        ['Asesi Kolektif', $kolektif,          $pct($kolektif, $total['daftar']),         'diagram-3'],
        ['Asesi Mandiri',  $total['mandiri'],  $pct($total['mandiri'], $total['daftar']), 'person'],
        ['Kompeten',       $total['kompeten'], $pct($total['kompeten'], $total['daftar']), 'check-circle'],
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

{{-- ── Rekap per bagian (tab) ─────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="bi bi-collection"></i> Rekap Per Batch — <span class="text-muted fw-normal">{{ $periodeText }}</span></h5>
        <div class="d-flex gap-2 align-items-center">
            <span class="small text-muted">Unduh semua bagian:</span>
            <a href="{{ route('admin.rekap-batch.export', $query + ['format' => 'excel']) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <a href="{{ route('admin.rekap-batch.export', $query + ['format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs mb-3 rekap-tabs" role="tablist">
            @foreach($tabs as $key => $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                    data-bs-target="#tab-{{ $key }}" type="button" role="tab">
                    {{ $tab['title'] }}
                    <span class="badge bg-secondary ms-1">{{ count($data['tabs'][$key]) }}</span>
                </button>
            </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($tabs as $key => $tab)
            @php $cols = \App\Http\Controllers\Admin\RekapBatchController::columnsFor($key); @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $key }}" role="tabpanel">

                {{-- Toolbar bagian --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div class="d-flex gap-2 align-items-center">
                        <input type="search" class="form-control form-control-sm batch-search" data-tab="{{ $key }}"
                            placeholder="Cari batch, lembaga, TUK, asesor…" style="width: 280px;" aria-label="Cari di {{ strtolower($tab['title']) }}">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle-all="{{ $key }}" data-open="1">
                            <i class="bi bi-arrows-collapse"></i> <span>Tutup semua</span>
                        </button>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="small text-muted">Unduh {{ strtolower($tab['title']) }}:</span>
                        <a href="{{ route('admin.rekap-batch.export', $query + ['format' => 'excel', 'bagian' => $key]) }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </a>
                        <a href="{{ route('admin.rekap-batch.export', $query + ['format' => 'pdf', 'bagian' => $key]) }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle w-100 small batch-table" id="table-{{ $key }}">
                        <thead class="table-dark">
                            <tr>
                                @foreach($cols as $label)
                                <th>{{ $label }}</th>
                                @endforeach
                                <th class="text-center">Daftar</th>
                                <th class="text-center">Kompeten</th>
                                <th class="text-center">Belum Kompeten</th>
                                <th class="text-center">Belum Ada Hasil</th>
                                <th class="text-center">% Kompeten</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['tabs'][$key] as $gi => $g)
                            @php $gid = $key . '-' . $gi; @endphp
                            <tr class="group-row" data-group="{{ $gid }}" tabindex="0" aria-expanded="true">
                                <td colspan="{{ count($cols) }}">
                                    <i class="bi bi-chevron-down group-chevron me-1" aria-hidden="true"></i>
                                    <strong>{{ $g['label'] }}</strong>
                                    <span class="text-muted ms-2">
                                        {{ $g['n_batch'] ? $g['n_batch'] . ' batch' : 'hanya asesi mandiri' }}
                                        @if($g['asesor'] !== null) · {{ $g['asesor'] }} asesor @endif
                                    </span>
                                </td>
                                <td class="text-center fw-bold">{{ $g['daftar'] }}</td>
                                <td class="text-center fw-bold">{{ $g['kompeten'] }}</td>
                                <td class="text-center fw-bold">{{ $g['bk'] }}</td>
                                <td class="text-center fw-bold">{{ $g['belum'] }}</td>
                                <td class="text-center fw-bold">{{ $pct($g['kompeten'], $g['daftar']) }}</td>
                            </tr>
                            @foreach($g['batches'] as $b)
                            <tr class="batch-row {{ $b['url'] ? 'is-link' : '' }}" data-group="{{ $gid }}"
                                @if($b['url']) data-href="{{ $b['url'] }}" tabindex="0"
                                title="{{ $b['is_mandiri'] ? 'Buka daftar asesi mandiri di TUK ini' : 'Buka detail batch' }}" @endif>
                                @foreach(array_keys($cols) as $c)
                                @switch($c)
                                    @case('batch')
                                    <td class="text-nowrap ps-4 {{ $b['is_mandiri'] ? 'fst-italic text-muted' : 'fw-semibold' }}">{{ $b['label'] }}</td>
                                    @break
                                    @case('lembaga')
                                    <td>
                                        {{ $b['lembaga'] }}
                                        @if($b['lembaga_src'] === 'asesi' && !$b['is_mandiri'])
                                        <span class="badge text-bg-light border fw-normal" title="Nama lembaga di jadwal belum diisi, diambil dari isian asesi terbanyak">dari isian asesi</span>
                                        @endif
                                        @if($b['lembaga_lain'] > 0)
                                        <span class="text-muted" title="Ada {{ $b['lembaga_lain'] }} penulisan nama lembaga lain di batch ini">+{{ $b['lembaga_lain'] }} variasi</span>
                                        @endif
                                    </td>
                                    @break
                                    @case('tuk')
                                    <td title="{{ $b['tuk_full'] }}">{{ $b['tuk'] }}</td>
                                    @break
                                    @case('skema')
                                    <td title="{{ $b['skema_full'] }}">{{ $b['skema'] }}</td>
                                    @break
                                    @case('asesor')
                                    <td title="{{ $b['asesor_full'] }}">{{ $b['asesor'] }}</td>
                                    @break
                                    @case('tanggal')
                                    <td class="text-nowrap">{{ $b['tanggal'] }}</td>
                                    @break
                                @endswitch
                                @endforeach
                                <td class="text-center">{{ $b['daftar'] }}</td>
                                <td class="text-center">{{ $b['kompeten'] }}</td>
                                <td class="text-center">{{ $b['bk'] }}</td>
                                <td class="text-center">{{ $b['belum'] }}</td>
                                <td class="text-center">{{ $pct($b['kompeten'], $b['daftar']) }}</td>
                            </tr>
                            @endforeach
                            @empty
                            <tr><td colspan="{{ count($cols) + 5 }}" class="text-center text-muted py-4">Tidak ada data</td></tr>
                            @endforelse
                            <tr class="no-result d-none">
                                <td colspan="{{ count($cols) + 5 }}" class="text-center text-muted py-4">Tidak ada batch yang cocok dengan pencarian.</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="{{ count($cols) }}">
                                    TOTAL — {{ $total['n_batch'] }} batch{{ $total['mandiri'] ? ' + asesi mandiri' : '' }}
                                    @if($key !== 'asesor') · <span title="Jumlah asesor berbeda secara keseluruhan">{{ $total['asesor'] }} asesor</span> @endif
                                </td>
                                <td class="text-center">{{ $total['daftar'] }}</td>
                                <td class="text-center">{{ $total['kompeten'] }}</td>
                                <td class="text-center">{{ $total['bk'] }}</td>
                                <td class="text-center">{{ $total['belum'] }}</td>
                                <td class="text-center">{{ $pct($total['kompeten'], $total['daftar']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <small class="text-muted d-block">
                    <i class="bi bi-hand-index"></i> Klik judul kelompok untuk membuka/menutup, klik baris batch untuk membuka detailnya.
                    Angka kelompok sama dengan halaman Rekap Asesi. Batch yang punya beberapa jadwal bisa muncul di lebih dari satu kelompok dengan jumlah bagiannya saja.
                    Asal lembaga diambil dari nama lembaga di jadwal; jika belum diisi, dari isian asesi terbanyak.
                </small>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Layout punya aturan global `.nav-link .badge {position:absolute}` untuk sidebar */
.rekap-tabs .nav-link .badge { position: static; margin-left: .4rem; }
.batch-table thead th { vertical-align: middle; }
.batch-table td.text-center { width: 1%; white-space: nowrap; }
.batch-table tr.group-row td { background: #eef3f9; cursor: pointer; }
.batch-table tr.group-row:hover td { background: #e2eaf4; }
.batch-table tr.batch-row.is-link { cursor: pointer; }
.batch-table tr.batch-row:hover td { background: #f8f9fa; }
.batch-table tr:focus-visible { outline: 2px solid #2a78d6; outline-offset: -2px; }
.batch-table .group-chevron { display: inline-block; transition: transform .15s; }
.batch-table tr.group-row[aria-expanded="false"] .group-chevron { transform: rotate(-90deg); }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    function setGroup($g, open) {
        $g.attr('aria-expanded', open ? 'true' : 'false');
        $(`.batch-row[data-group="${$g.data('group')}"]`).toggleClass('d-none', !open);
    }

    // Buka/tutup kelompok
    $(document).on('click', '.group-row', function () {
        setGroup($(this), $(this).attr('aria-expanded') !== 'true');
    });
    $(document).on('keydown', '.group-row', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $(this).trigger('click'); }
    });

    // Tutup / buka semua kelompok di satu tab
    $('[data-toggle-all]').on('click', function () {
        const open = $(this).data('open') !== 1;
        $(`#table-${$(this).data('toggle-all')} .group-row`).each((_, g) => setGroup($(g), open));
        $(this).data('open', open ? 1 : 0)
            .html(open ? '<i class="bi bi-arrows-collapse"></i> <span>Tutup semua</span>'
                       : '<i class="bi bi-arrows-expand"></i> <span>Buka semua</span>');
    });

    // Klik / Enter pada baris batch → halaman detail
    $(document).on('click', '.batch-row.is-link', function () { window.location = this.dataset.href; });
    $(document).on('keydown', '.batch-row.is-link', function (e) {
        if (e.key === 'Enter') window.location = this.dataset.href;
    });

    // Pencarian per tab: tampilkan batch yang cocok beserta judul kelompoknya
    $('.batch-search').on('input', function () {
        const q = this.value.trim().toLowerCase();
        const $table = $(`#table-${$(this).data('tab')}`);
        let any = false;
        $table.find('.group-row').each(function () {
            const $g = $(this);
            const $rows = $table.find(`.batch-row[data-group="${$g.data('group')}"]`);
            if (!q) {
                $g.removeClass('d-none');
                setGroup($g, true);
                any = true;
                return;
            }
            const groupHit = $g.text().toLowerCase().includes(q);
            let hits = 0;
            $rows.each(function () {
                const hit = groupHit || this.textContent.toLowerCase().includes(q);
                $(this).toggleClass('d-none', !hit);
                if (hit) hits++;
            });
            $g.toggleClass('d-none', hits === 0).attr('aria-expanded', 'true');
            if (hits) any = true;
        });
        $table.find('.no-result').toggleClass('d-none', any);
    });
});
</script>
@endpush
