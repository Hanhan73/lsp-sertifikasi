@extends('layouts.app')
@section('title', 'Rekap Pendapatan')
@section('page-title', 'Rekap Pendapatan & Keuangan')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@push('styles')
<style>
.summary-card {
    border-radius: 16px;
    padding: 1.5rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.summary-card .icon-bg {
    position: absolute; right: -10px; top: -10px;
    font-size: 5rem; opacity: .15;
}
.summary-card h3 { font-size: 1.6rem; font-weight: 700; margin: 0; }
.summary-card p  { font-size: .8rem; opacity: .85; margin: 0; }
.summary-card small { font-size: .75rem; opacity: .7; }
.card-pemasukan { background: linear-gradient(135deg, #11998e, #38ef7d); }
.card-honor     { background: linear-gradient(135deg, #f093fb, #f5576c); }
.card-biayaops  { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.card-saldo     { background: linear-gradient(135deg, #f7971e, #ffd200); color: #333 !important; }
.card-saldo h3, .card-saldo p, .card-saldo small { color: #333 !important; }
.progress-thin  { height: 6px; border-radius: 10px; }
</style>
@endpush

@section('content')

{{-- ── Filter + Export ──────────────────────────────────────────────────── --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <label class="fw-semibold mb-0"><i class="bi bi-calendar3"></i> Tahun:</label>
            <form method="GET" class="d-flex align-items-center gap-2 mb-0">
                <select name="tahun" class="form-select form-select-sm" style="width:120px"
                        onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
            <span class="text-muted small">Menampilkan data tahun <strong>{{ $tahun }}</strong></span>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('bendahara.rekap-pendapatan.export', ['tahun' => $tahun, 'format' => 'pdf']) }}"
                   class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </a>
                <a href="{{ route('bendahara.rekap-pendapatan.export', ['tahun' => $tahun, 'format' => 'excel']) }}"
                   class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Summary Cards ────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="summary-card card-pemasukan">
            <i class="bi bi-arrow-down-circle icon-bg"></i>
            <p>Total Pemasukan {{ $tahun }}</p>
            <h3>Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h3>
            <small>{{ $breakdownJenis->total_transaksi ?? 0 }} payment individu terverifikasi</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="summary-card card-honor">
            <i class="bi bi-person-badge icon-bg"></i>
            <p>Total Honor Asesor</p>
            <h3>Rp {{ number_format($totalHonor, 0, ',', '.') }}</h3>
            <small>Pengeluaran honor asesor</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="summary-card card-biayaops">
            <i class="bi bi-cash-stack icon-bg"></i>
            <p>Total Biaya Operasional</p>
            <h3>Rp {{ number_format($totalBiayaOps, 0, ',', '.') }}</h3>
            <small>Pengeluaran operasional</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="summary-card card-saldo">
            <i class="bi bi-wallet2 icon-bg"></i>
            <p>Saldo Bersih</p>
            <h3>Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h3>
            <small>Pemasukan − Honor − Operasional</small>
        </div>
    </div>
</div>

{{-- ── 2 Chart Sejajar ─────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Komposisi Pengeluaran --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-pie-chart text-success me-2"></i>
                Komposisi Pengeluaran
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="position:relative;height:240px;width:240px;">
                    <canvas id="chartPie"></canvas>
                </div>
                <div class="mt-3 w-100">
                    <div class="d-flex justify-content-between small mb-1">
                        <span><span class="badge bg-danger me-1">&nbsp;</span>Honor Asesor</span>
                        <strong>{{ $totalPemasukan > 0 ? number_format($totalHonor/$totalPemasukan*100,1) : 0 }}%</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span><span class="badge bg-info me-1">&nbsp;</span>Biaya Operasional</span>
                        <strong>{{ $totalPemasukan > 0 ? number_format($totalBiayaOps/$totalPemasukan*100,1) : 0 }}%</strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span><span class="badge bg-warning me-1">&nbsp;</span>Saldo Bersih</span>
                        <strong>{{ $totalPemasukan > 0 ? number_format(max(0,$totalSaldo)/$totalPemasukan*100,1) : 0 }}%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Trend Pemasukan vs Pengeluaran --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-bar-chart text-warning me-2"></i>
                Trend Pemasukan vs Pengeluaran
            </div>
            <div class="card-body">
                <div style="position:relative;height:320px;">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Tabel 12 Bulan + Rincian Collapse ──────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold border-bottom">
        <i class="bi bi-table text-secondary me-2"></i>
        Rincian Per Bulan {{ $tahun }}
        <small class="text-muted fw-normal ms-2">— Klik baris bulan untuk melihat detail transaksi</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle" style="font-size:.875rem;">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width:40px"></th>
                        <th class="text-start" style="width:100px">Bulan</th>
                        <th class="text-success">Pemasukan</th>
                        <th class="text-danger">Honor Asesor</th>
                        <th class="text-info">Biaya Ops</th>
                        <th>Total Keluar</th>
                        <th>Saldo Bersih</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($bulanLabels as $i => $bln)
                @php
                    $m       = $i + 1;
                    $in      = $dataPemasukan[$i];
                    $hon     = $dataHonor[$i];
                    $ops     = $dataBiayaOps[$i];
                    $sal     = $dataSaldo[$i];
                    $ada     = $in > 0 || $hon > 0 || $ops > 0;
                    $rincian = $rincianPerBulan[$m] ?? collect();
                @endphp

                {{-- Row bulan --}}
                <tr @if($ada) onclick="toggleBulan({{ $m }})" style="cursor:pointer" @endif
                    class="{{ !$ada ? 'text-muted' : '' }}">
                    <td class="text-center">
                        @if($ada)
                        <i class="bi bi-chevron-right text-secondary"
                           id="chevron-{{ $m }}"
                           style="transition:transform .2s;font-size:.8rem;"></i>
                        @endif
                    </td>
                    <td class="fw-semibold">{{ $bln }}</td>
                    <td class="text-end text-success">
                        {{ $in > 0 ? 'Rp '.number_format($in,0,',','.') : '-' }}
                    </td>
                    <td class="text-end text-danger">
                        {{ $hon > 0 ? 'Rp '.number_format($hon,0,',','.') : '-' }}
                    </td>
                    <td class="text-end text-info">
                        {{ $ops > 0 ? 'Rp '.number_format($ops,0,',','.') : '-' }}
                    </td>
                    <td class="text-end">
                        {{ ($hon+$ops) > 0 ? 'Rp '.number_format($hon+$ops,0,',','.') : '-' }}
                    </td>
                    <td class="text-end fw-bold {{ $sal >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($sal,0,',','.') }}
                    </td>
                </tr>

                {{-- Row rincian collapse --}}
                @if($ada)
                <tr id="detail-{{ $m }}" style="display:none;">
                    <td colspan="7" class="p-0 bg-light">
                        <div class="px-4 py-3">
                            <table class="table table-sm table-bordered mb-0 bg-white"
                                   style="font-size:.82rem;">
                                <thead class="table-secondary">
                                    <tr>
                                        <th style="width:90px">Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Sub Info</th>
                                        <th class="text-center" style="width:110px">Tipe</th>
                                        <th class="text-end" style="width:150px">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($rincian as $r)
                                <tr>
                                    <td>{{ $r['tanggal'] }}</td>
                                    <td>{{ $r['keterangan'] }}</td>
                                    <td class="text-muted small">{{ $r['sub'] }}</td>
                                    <td class="text-center">
                                        @if($r['tipe'] === 'pemasukan')
                                            <span class="badge bg-success">Pemasukan</span>
                                        @elseif($r['tipe'] === 'honor')
                                            <span class="badge bg-danger">Honor</span>
                                        @else
                                            <span class="badge bg-info text-dark">Biaya Ops</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold
                                        {{ $r['tipe'] === 'pemasukan' ? 'text-success' : 'text-danger' }}">
                                        {{ $r['tipe'] === 'pemasukan' ? '+' : '−' }}
                                        Rp {{ number_format($r['jumlah'],0,',','.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-2">
                                        Tidak ada transaksi
                                    </td>
                                </tr>
                                @endforelse
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Saldo Bulan Ini:</td>
                                        <td class="text-end fw-bold {{ $sal >= 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($sal,0,',','.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </td>
                </tr>
                @endif

                @endforeach
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="2" class="fw-bold">TOTAL</td>
                        <td class="text-end text-success fw-bold">Rp {{ number_format($totalPemasukan,0,',','.') }}</td>
                        <td class="text-end text-danger fw-bold">Rp {{ number_format($totalHonor,0,',','.') }}</td>
                        <td class="text-end fw-bold" style="color:#00f2fe">Rp {{ number_format($totalBiayaOps,0,',','.') }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($totalHonor+$totalBiayaOps,0,',','.') }}</td>
                        <td class="text-end fw-bold {{ $totalSaldo >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($totalSaldo,0,',','.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ── Breakdown TUK + Skema ────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-building text-warning me-2"></i> Per TUK
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th>TUK</th>
                            <th class="text-center">Trx</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($breakdownTuk as $row)
                        <tr>
                            <td>{{ $row->tuk_name }}</td>
                            <td class="text-center">{{ $row->jumlah }}</td>
                            <td class="text-end fw-semibold text-success">
                                Rp {{ number_format($row->total,0,',','.') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-file-earmark-text text-info me-2"></i> Per Skema
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Skema</th>
                            <th class="text-center">Trx</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($breakdownSkema as $row)
                        <tr>
                            <td>{{ $row->skema_name }}</td>
                            <td class="text-center">{{ $row->jumlah }}</td>
                            <td class="text-end fw-semibold text-success">
                                Rp {{ number_format($row->total,0,',','.') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Transaksi Terbaru ────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
        <span class="fw-semibold">
            <i class="bi bi-clock-history text-secondary me-2"></i>
            50 Transaksi Terverifikasi Terbaru
        </span>
        <span class="badge bg-secondary">{{ $transaksiTerbaru->count() }} item</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle" style="font-size:.82rem;">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Asesi</th>
                        <th>TUK</th>
                        <th>Skema</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiTerbaru as $p)
                    <tr>
                        <td>{{ $p->verified_at?->translatedFormat('d/m/Y') ?? '-' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $p->asesmen->full_name ?? '-' }}</div>
                            <small class="text-muted">{{ $p->asesmen->user->email ?? '-' }}</small>
                        </td>
                        <td>{{ $p->asesmen->tuk->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary" style="font-size:.7rem;">
                                {{ Str::limit($p->asesmen->skema->name ?? '-', 25) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($p->asesmen->is_collective)
                            <span class="badge bg-info">Kolektif</span>
                            @else
                            <span class="badge bg-success">Mandiri</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-success">
                            Rp {{ number_format($p->amount,0,',','.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const bulanLabels   = @json($bulanLabels);
const dataPemasukan = @json($dataPemasukan);
const dataHonor     = @json($dataHonor);
const dataBiayaOps  = @json($dataBiayaOps);
const dataSaldo     = @json($dataSaldo);

// ── Chart 1: Doughnut Komposisi Pengeluaran ───────────────────────────────
new Chart(document.getElementById('chartPie'), {
    type: 'doughnut',
    data: {
        labels: ['Honor Asesor', 'Biaya Ops', 'Saldo Bersih'],
        datasets: [{
            data: [{{ $totalHonor }}, {{ $totalBiayaOps }}, {{ max(0, $totalSaldo) }}],
            backgroundColor: ['#f5576c', '#4facfe', '#ffd200'],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});

// ── Chart 2: Bar Trend Pemasukan vs Pengeluaran ───────────────────────────
new Chart(document.getElementById('chartTrend'), {
    type: 'bar',
    data: {
        labels: bulanLabels,
        datasets: [
            {
                label: 'Pemasukan',
                data: dataPemasukan,
                backgroundColor: 'rgba(17,153,142,.75)',
                borderRadius: 5,
            },
            {
                label: 'Pengeluaran',
                data: dataPemasukan.map((v, i) => dataHonor[i] + dataBiayaOps[i]),
                backgroundColor: 'rgba(245,87,108,.65)',
                borderRadius: 5,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                }
            }
        },
        scales: {
            y: {
                ticks: {
                    callback: v => 'Rp ' + (v / 1000000).toFixed(1) + 'jt'
                }
            }
        }
    }
});

// ── Toggle rincian bulan ──────────────────────────────────────────────────
function toggleBulan(m) {
    const row     = document.getElementById('detail-' + m);
    const chevron = document.getElementById('chevron-' + m);
    const open    = row.style.display !== 'none';
    row.style.display       = open ? 'none' : 'table-row';
    chevron.style.transform = open ? '' : 'rotate(90deg)';
}
</script>
@endpush