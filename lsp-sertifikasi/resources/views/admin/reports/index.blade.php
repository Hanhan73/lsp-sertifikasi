@extends('layouts.app')

@section('title', 'Laporan & Rekap')
@section('page-title', 'Laporan & Rekap Sistem')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-funnel"></i> Filter Laporan
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.reports') }}" method="GET" id="filter-form">
            <div class="row">
                <!-- Report Type -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">Jenis Laporan <span class="text-danger">*</span></label>
                    <select class="form-select" name="report_type" id="report-type" required>
                        <option value="asesmen" {{ $reportType === 'asesmen' ? 'selected' : '' }}>
                            Rekap Asesmen
                        </option>
                        <option value="payment" {{ $reportType === 'payment' ? 'selected' : '' }}>
                            Rekap Pembayaran
                        </option>
                        <option value="financial" {{ $reportType === 'financial' ? 'selected' : '' }}>
                            Laporan Keuangan
                        </option>
                        <option value="tuk" {{ $reportType === 'tuk' ? 'selected' : '' }}>
                            Rekap per TUK
                        </option>
                        <option value="skema" {{ $reportType === 'skema' ? 'selected' : '' }}>
                            Rekap per Skema
                        </option>
                    </select>
                </div>

                <!-- Start Date -->
                <div class="col-md-2 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>

                <!-- End Date -->
                <div class="col-md-2 mb-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>

                <!-- TUK Filter -->
                <div class="col-md-2 mb-3 filter-tuk">
                    <label class="form-label">TUK</label>
                    <select class="form-select" name="tuk_id">
                        <option value="">Semua TUK</option>
                        @foreach($tuks as $tuk)
                        <option value="{{ $tuk->id }}" {{ $tukId == $tuk->id ? 'selected' : '' }}>
                            {{ $tuk->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Skema Filter -->
                <div class="col-md-2 mb-3 filter-skema">
                    <label class="form-label">Skema</label>
                    <select class="form-select" name="skema_id">
                        <option value="">Semua Skema</option>
                        @foreach($skemas as $skema)
                        <option value="{{ $skema->id }}" {{ $skemaId == $skema->id ? 'selected' : '' }}>
                            {{ $skema->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter (for asesmen) -->
                <div class="col-md-2 mb-3 filter-status">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="registered" {{ $status === 'registered' ? 'selected' : '' }}>Terdaftar</option>
                        <option value="data_completed" {{ $status === 'data_completed' ? 'selected' : '' }}>Data Lengkap
                        </option>
                        <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                        <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Sudah Bayar</option>
                        <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                        <option value="assessed" {{ $status === 'assessed' ? 'selected' : '' }}>Sudah Diases</option>
                        <option value="certified" {{ $status === 'certified' ? 'selected' : '' }}>Tersertifikasi
                        </option>
                    </select>
                </div>

                <!-- Jenis Registrasi Filter -->
                <div class="col-md-2 mb-3 filter-jenis">
                    <label class="form-label">Jenis Pendaftaran</label>
                    <select class="form-select" name="jenis_registrasi">
                        <option value="">Semua</option>
                        <option value="mandiri" {{ $jenisRegistrasi === 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                        <option value="kolektif" {{ $jenisRegistrasi === 'kolektif' ? 'selected' : '' }}>Kolektif
                        </option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div class="col-md-2 mb-3 filter-payment-status">
                    <label class="form-label">Status Pembayaran</label>
                    <select class="form-select" name="payment_status">
                        <option value="">Semua</option>
                        <option value="pending" {{ $paymentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ $paymentStatus === 'verified' ? 'selected' : '' }}>Terverifikasi
                        </option>
                        <option value="rejected" {{ $paymentStatus === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Tampilkan
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Card -->
@if(isset($summary) && !empty($summary))
<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Ringkasan</h5>
        <button type="button" class="btn btn-success btn-sm" onclick="exportReport()">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </button>
    </div>
    <div class="card-body">
        @if($reportType === 'asesmen')
        <div class="row">
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3>{{ $summary['total_asesi'] }}</h3>
                    <p class="text-muted mb-0">Total Asesi</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3 class="text-success">{{ $summary['mandiri'] }}</h3>
                    <p class="text-muted mb-0">Mandiri</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3 class="text-primary">{{ $summary['kolektif'] }}</h3>
                    <p class="text-muted mb-0">Kolektif</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3 class="text-warning">{{ $summary['with_training'] }}</h3>
                    <p class="text-muted mb-0">Ikut Pelatihan</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3 class="text-info">{{ $summary['certified'] }}</h3>
                    <p class="text-muted mb-0">Tersertifikasi</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3 class="text-success">Rp {{ number_format($summary['total_fee'], 0, ',', '.') }}</h3>
                    <p class="text-muted mb-0">Total Biaya</p>
                </div>
            </div>
        </div>
        @elseif($reportType === 'payment')
        <div class="row">
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3>{{ $summary['total_transaksi'] }}</h3>
                    <p class="text-muted mb-0">Total Transaksi</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3 class="text-warning">{{ $summary['pending'] }}</h3>
                    <p class="text-muted mb-0">Pending</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3 class="text-success">{{ $summary['verified'] }}</h3>
                    <p class="text-muted mb-0">Terverifikasi</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-mini">
                    <h3 class="text-danger">{{ $summary['rejected'] }}</h3>
                    <p class="text-muted mb-0">Ditolak</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-mini">
                    <h3 class="text-success">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</h3>
                    <p class="text-muted mb-0">Total Terverifikasi</p>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="alert alert-info mb-0">
                    <small>
                        <i class="bi bi-robot"></i> <strong>Auto Verified:</strong> {{ $summary['auto_verified'] }}
                        transaksi
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-warning mb-0">
                    <small>
                        <i class="bi bi-person-check"></i> <strong>Manual Verified:</strong>
                        {{ $summary['manual_verified'] }} transaksi
                    </small>
                </div>
            </div>
        </div>
        @elseif($reportType === 'financial')
        <div class="row">
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3 class="text-success">Rp {{ number_format($summary['total_pemasukan'], 0, ',', '.') }}</h3>
                    <p class="text-muted mb-0">Total Pemasukan</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3>{{ $summary['total_transaksi'] }}</h3>
                    <p class="text-muted mb-0">Total Transaksi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3 class="text-success">Rp {{ number_format($summary['mandiri_income'], 0, ',', '.') }}</h3>
                    <p class="text-muted mb-0">Mandiri</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3 class="text-primary">Rp {{ number_format($summary['kolektif_income'], 0, ',', '.') }}</h3>
                    <p class="text-muted mb-0">Kolektif</p>
                </div>
            </div>
        </div>

        <!-- Breakdown by TUK -->
        <hr>
        <h6><i class="bi bi-building"></i> Breakdown per TUK</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>TUK</th>
                        <th class="text-end">Jumlah Transaksi</th>
                        <th class="text-end">Total Pemasukan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['by_tuk'] as $item)
                    <tr>
                        <td>{{ $item['tuk_name'] }}</td>
                        <td class="text-end">{{ $item['count'] }}</td>
                        <td class="text-end"><strong>Rp {{ number_format($item['total'], 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Breakdown by Skema -->
        <hr>
        <h6><i class="bi bi-file-earmark-text"></i> Breakdown per Skema</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Skema</th>
                        <th class="text-end">Jumlah Transaksi</th>
                        <th class="text-end">Total Pemasukan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['by_skema'] as $item)
                    <tr>
                        <td>{{ $item['skema_name'] }}</td>
                        <td class="text-end">{{ $item['count'] }}</td>
                        <td class="text-end"><strong>Rp {{ number_format($item['total'], 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($reportType === 'tuk')
        <div class="row">
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3>{{ $summary['total_tuk'] }}</h3>
                    <p class="text-muted mb-0">Total TUK</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3 class="text-success">{{ $summary['active_tuk'] }}</h3>
                    <p class="text-muted mb-0">TUK Aktif</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3>{{ $summary['total_asesi'] }}</h3>
                    <p class="text-muted mb-0">Total Asesi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3 class="text-info">{{ $summary['total_certified'] }}</h3>
                    <p class="text-muted mb-0">Tersertifikasi</p>
                </div>
            </div>
        </div>
        @elseif($reportType === 'skema')
        <div class="row">
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3>{{ $summary['total_skema'] }}</h3>
                    <p class="text-muted mb-0">Total Skema</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3 class="text-success">{{ $summary['active_skema'] }}</h3>
                    <p class="text-muted mb-0">Skema Aktif</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3>{{ $summary['total_asesi'] }}</h3>
                    <p class="text-muted mb-0">Total Asesi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-mini">
                    <h3 class="text-info">{{ $summary['total_certified'] }}</h3>
                    <p class="text-muted mb-0">Tersertifikasi</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

<!-- Data Table -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-table"></i>
            Data {{ ucfirst($reportType) }}
            @if(isset($data))
            <span class="badge bg-secondary ms-2">{{ is_countable($data) ? count($data) : $data->count() }}
                record</span>
            @endif
        </h5>
    </div>
    <div class="card-body">
        @if(isset($data) && (is_countable($data) ? count($data) : $data->count()) > 0)
        <div class="table-responsive">
            @if($reportType === 'asesmen')
            @include('admin.reports.tables.asesmen', ['asesmens' => $data])
            @elseif($reportType === 'payment')
            @include('admin.reports.tables.payment', ['payments' => $data])
            @elseif($reportType === 'financial')
            @include('admin.reports.tables.financial', ['payments' => $data])
            @elseif($reportType === 'tuk')
            @include('admin.reports.tables.tuk', ['tuks' => $data])
            @elseif($reportType === 'skema')
            @include('admin.reports.tables.skema', ['skemas' => $data])
            @endif
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
            <h5 class="mt-3 text-muted">Tidak ada data</h5>
            <p class="text-muted">Coba ubah filter untuk melihat data</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.stat-mini {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.stat-mini h3 {
    margin-bottom: 5px;
    font-weight: 700;
}

.stat-mini p {
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update visible filters based on report type
    updateFilters();

    $('#report-type').on('change', function() {
        updateFilters();
    });

    // Initialize DataTables if applicable
    if ($('.data-table').length > 0) {
        $('.data-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            pageLength: 50,
            order: [
                [0, 'desc']
            ]
        });
    }
});

function updateFilters() {
    const reportType = $('#report-type').val();

    // Hide all optional filters first
    $('.filter-status, .filter-jenis, .filter-payment-status').hide();

    // Show relevant filters based on report type
    switch (reportType) {
        case 'asesmen':
            $('.filter-status, .filter-jenis').show();
            break;
        case 'payment':
            $('.filter-payment-status').show();
            break;
        case 'financial':
            // No additional filters
            break;
        case 'tuk':
            $('.filter-tuk, .filter-skema').hide();
            break;
        case 'skema':
            $('.filter-tuk, .filter-skema').hide();
            break;
    }
}

function resetFilter() {
    window.location.href = '{{ route("admin.reports") }}';
}

function exportReport() {
    // Get current filter values
    const form = $('#filter-form');
    const action = '{{ route("admin.reports.export") }}';

    // Create a temporary form to submit as POST
    const exportForm = $('<form>', {
        'method': 'POST',
        'action': action
    });

    // Add CSRF token
    exportForm.append($('<input>', {
        'type': 'hidden',
        'name': '_token',
        'value': '{{ csrf_token() }}'
    }));

    // Add all form inputs
    form.find('input, select').each(function() {
        if ($(this).val()) {
            exportForm.append($('<input>', {
                'type': 'hidden',
                'name': $(this).attr('name'),
                'value': $(this).val()
            }));
        }
    });

    // Submit form
    exportForm.appendTo('body').submit().remove();
}
</script>
@endpush