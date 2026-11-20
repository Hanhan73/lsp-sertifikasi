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
    $pct   = fn($n) => $total['daftar'] > 0 ? number_format($n / $total['daftar'] * 100, 1, ',', '.') . '%' : '0%';
    $chartNotes = [
        'skema'  => 'Diurutkan dari jumlah pendaftar terbanyak.',
        'tuk'    => 'Diurutkan dari jumlah pendaftar terbanyak.',
        'bulan'  => 'Berdasarkan bulan tanggal asesmen.',
        'asesor' => 'Menampilkan 15 asesor dengan asesi terbanyak. Daftar lengkap ada di tabel.',
    ];
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
        ['Total Daftar',      $total['daftar'],   null,                  'people',          'secondary'],
        ['Kompeten',          $total['kompeten'], $pct($total['kompeten']), 'check-circle', 'k'],
        ['Belum Kompeten',    $total['bk'],       $pct($total['bk']),       'x-circle',     'bk'],
        ['Belum Ada Hasil',   $total['belum'],    $pct($total['belum']),    'hourglass-split', 'belum'],
        ['Asesor Ditugaskan', $total['asesor'],   null,                  'person-badge',    'secondary'],
    ] as [$lbl, $val, $persen, $icon, $tone])
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi bi-{{ $icon }} fs-2 rekap-ico-{{ $tone }}"></i>
                <div>
                    <div class="small text-muted">{{ $lbl }}</div>
                    <div class="fs-4 fw-bold lh-sm">{{ number_format($val, 0, ',', '.') }}</div>
                    @if($persen)
                    <div class="small text-muted">{{ $persen }} dari daftar</div>
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
        <h5 class="mb-0"><i class="bi bi-table"></i> Rekap Asesi — <span class="text-muted fw-normal">{{ $periodeText }}</span></h5>
        <div class="d-flex gap-2 align-items-center">
            <span class="small text-muted">Unduh semua bagian:</span>
            <a href="{{ route('admin.rekap-asesi.export', $query + ['format' => 'excel']) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <a href="{{ route('admin.rekap-asesi.export', $query + ['format' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs mb-3 rekap-tabs" role="tablist">
            @foreach($tabs as $key => $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                    data-bs-target="#tab-{{ $key }}" data-chart="{{ $key }}" type="button" role="tab">
                    {{ $tab['title'] }}
                    <span class="badge bg-secondary ms-1">{{ count($rekap['rows'][$key]) }}</span>
                </button>
            </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($tabs as $key => $tab)
            @php
                $isAsesor = $key === 'asesor';
                $rows     = $rekap['rows'][$key];
                $kosong   = collect($rows)->firstWhere('kosong', true);
                $nChart   = $key === 'bulan' ? 0 : min($isAsesor ? 15 : 999, collect($rows)->reject(fn($r) => $isAsesor && $r['kosong'])->count());
                $height   = $key === 'bulan' ? 340 : max(200, $nChart * 30 + 90);
            @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $key }}" role="tabpanel">

                {{-- Toolbar bagian --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Mode grafik">
                        <button type="button" class="btn btn-outline-secondary active" data-mode="jumlah" data-target="{{ $key }}">Jumlah</button>
                        <button type="button" class="btn btn-outline-secondary" data-mode="persen" data-target="{{ $key }}">Persentase</button>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="small text-muted">Unduh {{ strtolower($tab['title']) }}:</span>
                        <a href="{{ route('admin.rekap-asesi.export', $query + ['format' => 'excel', 'bagian' => $key]) }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </a>
                        <a href="{{ route('admin.rekap-asesi.export', $query + ['format' => 'pdf', 'bagian' => $key]) }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                    </div>
                </div>

                {{-- Grafik --}}
                @if(count($rows) > 0)
                <div class="rekap-chart-box mb-1" style="height: {{ $height }}px;">
                    <canvas id="chart-{{ $key }}" role="img"
                        aria-label="Grafik rekap {{ strtolower($tab['title']) }}: Kompeten, Belum Kompeten, dan Belum Ada Hasil. Angka lengkap ada di tabel di bawah."></canvas>
                </div>
                <p class="small text-muted mb-3">
                    {{ $chartNotes[$key] }}
                    @if($key === 'bulan' && $kosong)
                    <strong>{{ number_format($kosong['daftar'], 0, ',', '.') }}</strong> asesi belum dijadwalkan dan tidak ditampilkan di grafik.
                    @elseif($isAsesor && $kosong)
                    <strong>{{ number_format($kosong['daftar'], 0, ',', '.') }}</strong> asesi belum punya asesor dan tidak ditampilkan di grafik.
                    @endif
                </p>
                @endif

                {{-- Tabel --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle rekap-table w-100">
                        <thead class="table-dark">
                            <tr>
                                <th>{{ $tab['label'] }}</th>
                                <th class="text-center">{{ $isAsesor ? 'Jumlah Asesi Ditangani' : 'Daftar' }}</th>
                                <th class="text-center">Kompeten</th>
                                <th class="text-center">Belum Kompeten</th>
                                <th class="text-center">Belum Ada Hasil</th>
                                <th class="text-center">% Kompeten</th>
                                @unless($isAsesor)
                                <th class="text-center">Jumlah Asesor Ditugaskan</th>
                                @endunless
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $r)
                            @php $rate = $r['daftar'] > 0 ? $r['kompeten'] / $r['daftar'] * 100 : 0; @endphp
                            <tr class="rekap-row" data-bagian="{{ $key }}" data-key="{{ $r['key'] }}" data-label="{{ $r['label'] }}"
                                tabindex="0" title="Klik untuk melihat daftar batch">
                                <td data-order="{{ $r['order'] }}">
                                    {{ $r['label'] }}
                                    <i class="bi bi-chevron-right text-muted small float-end mt-1" aria-hidden="true"></i>
                                </td>
                                <td class="text-center fw-semibold">{{ $r['daftar'] }}</td>
                                <td class="text-center">{{ $r['kompeten'] }}</td>
                                <td class="text-center">{{ $r['bk'] }}</td>
                                <td class="text-center">{{ $r['belum'] }}</td>
                                <td class="text-center" data-order="{{ number_format($rate, 2, '.', '') }}">{{ number_format($rate, 1, ',', '.') }}%</td>
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
                                <td class="text-center">{{ $pct($total['kompeten']) }}</td>
                                @unless($isAsesor)
                                <td class="text-center" title="Jumlah asesor berbeda secara keseluruhan">{{ $total['asesor'] }}*</td>
                                @endunless
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <small class="text-muted d-block">
                    <i class="bi bi-hand-index"></i> Klik salah satu baris untuk melihat daftar batch-nya.
                    @unless($isAsesor)
                    &nbsp;* Total jumlah asesor = asesor berbeda secara keseluruhan, bukan penjumlahan kolom.
                    @endunless
                </small>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Modal detail batch ─────────────────────────────────────────────── --}}
<div class="modal fade" id="batchModal" tabindex="-1" aria-labelledby="batchModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="batchModalTitle">Daftar Batch</h5>
                    <small class="text-muted" id="batchModalSub"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0">
                <div id="batchModalBody" class="p-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Palet kategorikal tervalidasi (CVD-safe): biru / oranye / aqua */
.rekap-ico-k      { color: #2a78d6; }
.rekap-ico-bk     { color: #eb6834; }
.rekap-ico-belum  { color: #1baf7a; }
.rekap-ico-secondary { color: #52514e; }
.rekap-chart-box  { position: relative; width: 100%; }
/* Layout punya aturan global `.nav-link .badge {position:absolute}` untuk sidebar */
.rekap-tabs .nav-link .badge { position: static; margin-left: .4rem; }
.rekap-row { cursor: pointer; }
.rekap-row:focus-visible { outline: 2px solid #2a78d6; outline-offset: -2px; }
#batchModal thead th { position: sticky; top: 0; z-index: 1; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
$(function () {
    // ── Tabel ──────────────────────────────────────────────────────────
    $('.rekap-table').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        paging: false,
        info: false,
        order: [[0, 'asc']],
    });

    // ── Grafik ─────────────────────────────────────────────────────────
    const ROWS = @json($rekap['rows']);
    const SERIES = [
        { key: 'kompeten', label: 'Kompeten',        color: '#2a78d6' },
        { key: 'bk',       label: 'Belum Kompeten',  color: '#eb6834' },
        { key: 'belum',    label: 'Belum Ada Hasil', color: '#1baf7a' },
    ];
    const INK = { primary: '#0b0b0b', secondary: '#52514e', muted: '#898781', grid: '#e1e0d9', axis: '#c3c2b7' };
    const fmt = n => n.toLocaleString('id-ID');
    const pct = (n, d) => d > 0 ? (n / d * 100) : 0;
    const shorten = (s, n = 40) => s.length > n ? s.slice(0, n - 1) + '…' : s;

    function prepare(key) {
        let rows = ROWS[key] || [];
        if (key === 'bulan') {
            return rows.filter(r => !r.kosong); // urut waktu, tanpa baris yang belum punya jadwal
        }
        rows = [...rows].sort((a, b) => b.daftar - a.daftar);
        if (key === 'asesor') {
            rows = rows.filter(r => !r.kosong).slice(0, 15);
        }
        return rows;
    }

    const charts = {};
    const modes  = {};

    function datasets(rows, mode) {
        return SERIES.map(s => ({
            label: s.label,
            data: rows.map(r => mode === 'persen' ? pct(r[s.key], r.daftar) : r[s.key]),
            backgroundColor: s.color,
            hoverBackgroundColor: s.color,
            borderColor: '#ffffff',  // 2px surface gap antar segmen
            borderWidth: 2,
            borderSkipped: false,
            barPercentage: 0.8,
            categoryPercentage: 0.85,
            maxBarThickness: 34,
        }));
    }

    function build(key) {
        const el = document.getElementById('chart-' + key);
        if (!el || charts[key]) return;
        const rows = prepare(key);
        const horizontal = key !== 'bulan';
        const mode = modes[key] || 'jumlah';
        const valueAxis = horizontal ? 'x' : 'y';
        const catAxis   = horizontal ? 'y' : 'x';

        charts[key] = new Chart(el, {
            type: 'bar',
            data: { labels: rows.map(r => r.label), datasets: datasets(rows, mode) },
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 250 },
                interaction: { mode: 'index', intersect: false, axis: catAxis },
                scales: {
                    [valueAxis]: {
                        stacked: true,
                        beginAtZero: true,
                        max: mode === 'persen' ? 100 : undefined,
                        grid: { color: INK.grid, drawTicks: false },
                        border: { display: false },
                        ticks: {
                            color: INK.muted, padding: 6, precision: 0,
                            callback: v => mode === 'persen' ? v + '%' : fmt(v),
                        },
                    },
                    [catAxis]: {
                        stacked: true,
                        grid: { display: false },
                        border: { color: INK.axis },
                        ticks: {
                            color: INK.secondary, autoSkip: false,
                            callback: function (v) { return shorten(this.getLabelForValue(v), horizontal ? 40 : 20); },
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: 'top', align: 'start',
                        labels: { color: INK.secondary, usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 10, boxHeight: 10, padding: 16 },
                    },
                    tooltip: {
                        backgroundColor: '#ffffff', borderColor: 'rgba(11,11,11,0.10)', borderWidth: 1,
                        titleColor: INK.primary, bodyColor: INK.secondary, footerColor: INK.primary,
                        padding: 10, boxPadding: 4, usePointStyle: true,
                        callbacks: {
                            title: items => rows[items[0].dataIndex].label,
                            label: ctx => {
                                const r = rows[ctx.dataIndex], s = SERIES[ctx.datasetIndex];
                                return ` ${s.label}: ${fmt(r[s.key])} (${pct(r[s.key], r.daftar).toFixed(1).replace('.', ',')}%)`;
                            },
                            footer: items => {
                                const r = rows[items[0].dataIndex];
                                return (key === 'asesor' ? 'Asesi ditangani: ' : 'Daftar: ') + fmt(r.daftar)
                                    + (r.asesor !== null ? '  ·  Asesor: ' + fmt(r.asesor) : '');
                            },
                        },
                    },
                },
            },
        });
    }

    // Toggle Jumlah / Persentase per bagian
    $('[data-mode]').on('click', function () {
        const key = $(this).data('target');
        modes[key] = $(this).data('mode');
        $(`[data-target="${key}"]`).removeClass('active');
        $(this).addClass('active');
        if (charts[key]) { charts[key].destroy(); delete charts[key]; }
        build(key);
    });

    // Grafik dibuat saat tab pertama kali tampil (agar ukuran canvas benar)
    build($('.nav-link.active[data-chart]').data('chart'));
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        build($(this).data('chart'));
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // ── Detail batch per baris ────────────────────────────────────────
    const DETAIL_URL = @json(route('admin.rekap-asesi.detail'));
    const FILTERS    = @json($query);
    const PERIODE    = @json($periodeText);
    const TAB_LABEL  = { skema: 'Skema', tuk: 'TUK', bulan: 'Bulan', asesor: 'Asesor' };
    const modalEl    = document.getElementById('batchModal');
    const modal      = new bootstrap.Modal(modalEl);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let detailReq = 0;

    function openDetail(tr) {
        const bagian = tr.dataset.bagian, key = tr.dataset.key, label = tr.dataset.label;
        const reqId  = ++detailReq;
        $('#batchModalTitle').text('Daftar Batch — ' + label);
        $('#batchModalSub').text(TAB_LABEL[bagian] + ' · Periode: ' + PERIODE);
        $('#batchModalBody').html('<div class="text-center text-muted py-5"><div class="spinner-border spinner-border-sm me-2"></div>Memuat…</div>');
        modal.show();

        const params = new URLSearchParams({ ...FILTERS, bagian, key });
        fetch(DETAIL_URL + '?' + params, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(data => { if (reqId === detailReq) renderDetail(bagian, data); })
            .catch(err => {
                if (reqId !== detailReq) return;
                $('#batchModalBody').html('<div class="alert alert-danger m-0">Gagal memuat detail (' + esc(err.message) + '). Coba lagi.</div>');
            });
    }

    function renderDetail(bagian, data) {
        const list = data.batches || [];
        if (!list.length) {
            $('#batchModalBody').html('<div class="text-center text-muted py-5">Tidak ada data.</div>');
            return;
        }
        // Kolom yang sama dengan baris yang diklik tidak perlu diulang
        const showTuk = bagian !== 'tuk', showSkema = bagian !== 'skema';
        const nBatch  = list.filter(b => b.batch_id).length;
        const t = data.total;

        let html = `<p class="small text-muted mb-2">${nBatch} batch`
            + (list.some(b => !b.batch_id) ? ' + asesi mandiri (tanpa batch)' : '')
            + ` · ${fmt(t.daftar)} asesi</p>
            <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light"><tr>
                <th>Batch</th>${showTuk ? '<th>TUK</th>' : ''}${showSkema ? '<th>Skema</th>' : ''}
                <th>Tanggal Asesmen</th>
                <th class="text-center">Daftar</th><th class="text-center">Kompeten</th>
                <th class="text-center">Belum Kompeten</th><th class="text-center">Belum Ada Hasil</th>
                <th class="text-center">% Kompeten</th><th></th>
            </tr></thead><tbody>`;

        list.forEach(b => {
            const rate = b.daftar ? (b.kompeten / b.daftar * 100).toFixed(1).replace('.', ',') + '%' : '0%';
            html += `<tr>
                <td class="fw-semibold text-nowrap">${b.batch_id ? esc(b.label) : '<span class="fst-italic text-muted">' + esc(b.label) + '</span>'}</td>
                ${showTuk ? `<td class="small" title="${esc(b.tuk_full)}">${esc(b.tuk)}</td>` : ''}
                ${showSkema ? `<td class="small" title="${esc(b.skema_full)}">${esc(b.skema)}</td>` : ''}
                <td class="small text-nowrap">${esc(b.tanggal)}</td>
                <td class="text-center fw-semibold">${fmt(b.daftar)}</td>
                <td class="text-center">${fmt(b.kompeten)}</td>
                <td class="text-center">${fmt(b.bk)}</td>
                <td class="text-center">${fmt(b.belum)}</td>
                <td class="text-center">${rate}</td>
                <td class="text-end">${b.url
                    ? `<a href="${esc(b.url)}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary text-nowrap">Lihat <i class="bi bi-box-arrow-up-right"></i></a>`
                    : ''}</td>
            </tr>`;
        });

        const span = 1 + (showTuk ? 1 : 0) + (showSkema ? 1 : 0) + 1;
        html += `</tbody><tfoot class="table-light fw-bold"><tr>
            <td colspan="${span}">TOTAL</td>
            <td class="text-center">${fmt(t.daftar)}</td><td class="text-center">${fmt(t.kompeten)}</td>
            <td class="text-center">${fmt(t.bk)}</td><td class="text-center">${fmt(t.belum)}</td>
            <td class="text-center">${t.daftar ? (t.kompeten / t.daftar * 100).toFixed(1).replace('.', ',') + '%' : '0%'}</td><td></td>
        </tr></tfoot></table>`;

        $('#batchModalBody').html(html);
    }

    $(document).on('click', '.rekap-row', function () { openDetail(this); });
    $(document).on('keydown', '.rekap-row', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDetail(this); }
    });
});
</script>
@endpush
