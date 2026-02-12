@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan & Statistik')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<!-- By Status -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Asesi Berdasarkan Status</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = $data['by_status']->sum('total');
                        @endphp
                        @foreach($data['by_status'] as $item)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $item->status)) }}</td>
                            <td class="text-end"><strong>{{ $item->total }}</strong></td>
                            <td class="text-end">{{ $total > 0 ? number_format(($item->total / $total) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                        <tr class="table-light">
                            <th>Total</th>
                            <th class="text-end">{{ $total }}</th>
                            <th class="text-end">100%</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- By TUK -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Asesi Berdasarkan TUK</h5>
            </div>
            <div class="card-body">
                <canvas id="tukChart"></canvas>
                <hr>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>TUK</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['by_tuk'] as $item)
                        <tr>
                            <td>{{ $item->tuk->name ?? 'Tidak ada TUK' }}</td>
                            <td class="text-end"><strong>{{ $item->total }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- By Skema -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Asesi Berdasarkan Skema</h5>
            </div>
            <div class="card-body">
                <canvas id="skemaChart"></canvas>
                <hr>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Skema</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['by_skema'] as $item)
                        <tr>
                            <td>{{ $item->skema->name ?? 'Tidak ada Skema' }}</td>
                            <td class="text-end"><strong>{{ $item->total }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Registrations -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Trend Pendaftaran (12 Bulan Terakhir)</h5>
    </div>
    <div class="card-body">
        <canvas id="monthlyChart" height="80"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($data['by_status']->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))) !!},
        datasets: [{
            data: {!! json_encode($data['by_status']->pluck('total')) !!},
            backgroundColor: [
                '#6c757d', '#0dcaf0', '#0d6efd', '#198754', 
                '#ffc107', '#20c997', '#0dcaf0', '#fd7e14'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// TUK Chart
const tukCtx = document.getElementById('tukChart').getContext('2d');
new Chart(tukCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($data['by_tuk']->map(fn($t) => $t->tuk->name ?? 'N/A')) !!},
        datasets: [{
            label: 'Jumlah Asesi',
            data: {!! json_encode($data['by_tuk']->pluck('total')) !!},
            backgroundColor: '#0d6efd'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Skema Chart
const skemaCtx = document.getElementById('skemaChart').getContext('2d');
new Chart(skemaCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($data['by_skema']->map(fn($s) => $s->skema->name ?? 'N/A')) !!},
        datasets: [{
            label: 'Jumlah Asesi',
            data: {!! json_encode($data['by_skema']->pluck('total')) !!},
            backgroundColor: '#198754'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($data['monthly_registrations']->map(fn($m) => $monthNames[$m->month - 1] . ' ' . $m->year)) !!},
        datasets: [{
            label: 'Pendaftaran',
            data: {!! json_encode($data['monthly_registrations']->pluck('total')) !!},
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush