@extends('layouts.app')

@section('title', 'Semua Asesi')
@section('page-title', 'Database Asesi')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-people"></i> Semua Data Asesi
        </h5>
        <div>
            <button class="btn btn-sm btn-success" onclick="exportData()">
                <i class="bi bi-file-excel"></i> Export Excel
            </button>
            <span class="badge bg-primary ms-2">{{ $asesmens->count() }} Total</span>
        </div>
    </div>
    <div class="card-body">
        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="stat-card" style="--bg-color: #6c757d; --bg-color-end: #495057;">
                    <p class="mb-1 small">Registered</p>
                    <h5 class="mb-0">{{ $asesmens->where('status', 'registered')->count() }}</h5>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card" style="--bg-color: #0dcaf0; --bg-color-end: #0aa2c0;">
                    <p class="mb-1 small">Data Completed</p>
                    <h5 class="mb-0">{{ $asesmens->where('status', 'data_completed')->count() }}</h5>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card" style="--bg-color: #0d6efd; --bg-color-end: #0a58ca;">
                    <p class="mb-1 small">Verified</p>
                    <h5 class="mb-0">{{ $asesmens->where('status', 'verified')->count() }}</h5>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card" style="--bg-color: #198754; --bg-color-end: #146c43;">
                    <p class="mb-1 small">Paid</p>
                    <h5 class="mb-0">{{ $asesmens->where('status', 'paid')->count() }}</h5>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card" style="--bg-color: #ffc107; --bg-color-end: #cc9a06;">
                    <p class="mb-1 small">Scheduled</p>
                    <h5 class="mb-0">{{ $asesmens->where('status', 'scheduled')->count() }}</h5>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card" style="--bg-color: #20c997; --bg-color-end: #19a077;">
                    <p class="mb-1 small">Certified</p>
                    <h5 class="mb-0">{{ $asesmens->where('status', 'certified')->count() }}</h5>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label small">Filter Status</label>
                <select id="filter-status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="registered">Registered</option>
                    <option value="data_completed">Data Completed</option>
                    <option value="verified">Verified</option>
                    <option value="paid">Paid</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="pre_assessment_completed">Pra-Asesmen Selesai</option>
                    <option value="assessed">Assessed</option>
                    <option value="certified">Certified</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Filter Jenis</label>
                <select id="filter-type" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="mandiri">Mandiri</option>
                    <option value="collective">Kolektif</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Filter TUK</label>
                <select id="filter-tuk" class="form-select form-select-sm">
                    <option value="">Semua TUK</option>
                    @foreach($asesmens->pluck('tuk')->unique('id')->sortBy('name') as $tuk)
                    @if($tuk)
                    <option value="{{ $tuk->id }}">{{ $tuk->name }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Filter Skema</label>
                <select id="filter-skema" class="form-select form-select-sm">
                    <option value="">Semua Skema</option>
                    @foreach($asesmens->pluck('skema')->unique('id')->sortBy('name') as $skema)
                    @if($skema)
                    <option value="{{ $skema->id }}">{{ $skema->name }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover table-sm" id="asesi-table">
                <thead class="table-light">
                    <tr>
                        <th>No Reg</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>TUK</th>
                        <th>Skema</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Jadwal</th>
                        <th>Hasil</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesmens as $asesmen)
                    <tr data-status="{{ $asesmen->status }}"
                        data-type="{{ $asesmen->is_collective ? 'collective' : 'mandiri' }}"
                        data-tuk="{{ $asesmen->tuk_id }}" data-skema="{{ $asesmen->skema_id }}">
                        <td><strong>#{{ $asesmen->id }}</strong></td>
                        <td>
                            {{ $asesmen->full_name ?? $asesmen->user->name }}
                            @if($asesmen->is_collective)
                            <br><small class="text-muted">{{ $asesmen->collective_batch_id }}</small>
                            @endif
                        </td>
                        <td>{{ $asesmen->email }}</td>
                        <td>{{ $asesmen->tuk->name ?? '-' }}</td>
                        <td>{{ $asesmen->skema->name ?? '-' }}</td>
                        <td>
                            @if($asesmen->is_collective)
                            <span class="badge bg-primary">Kolektif</span>
                            @else
                            <span class="badge bg-success">Mandiri</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $asesmen->status_badge }}">
                                {{ $asesmen->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($asesmen->payment)
                            <span
                                class="badge bg-{{ $asesmen->payment->status === 'verified' ? 'success' : 'warning' }}">
                                {{ ucfirst($asesmen->payment->status) }}
                            </span>
                            <br><small>Rp {{ number_format($asesmen->payment->amount, 0, ',', '.') }}</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($asesmen->schedule)
                            <small>{{ $asesmen->schedule->assessment_date->format('d/m/Y') }}</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($asesmen->result)
                            <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">
                                {{ ucfirst($asesmen->result) }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $asesmen->registration_date->format('d/m/Y') }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewDetail({{ $asesmen->id }})"
                                data-bs-toggle="tooltip" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Asesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-content">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#asesi-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        order: [
            [10, 'desc']
        ], // Sort by registration date
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    // Filters
    $('#filter-status, #filter-type, #filter-tuk, #filter-skema').on('change', function() {
        const status = $('#filter-status').val();
        const type = $('#filter-type').val();
        const tuk = $('#filter-tuk').val();
        const skema = $('#filter-skema').val();

        table.rows().every(function() {
            const row = $(this.node());
            let show = true;

            if (status && row.data('status') !== status) show = false;
            if (type && row.data('type') !== type) show = false;
            if (tuk && row.data('tuk') != tuk) show = false;
            if (skema && row.data('skema') != skema) show = false;

            row.toggle(show);
        });
    });
});

function viewDetail(id) {
    $('#detailModal').modal('show');
    $('#detail-content').html('<div class="text-center"><div class="spinner-border"></div></div>');

    $.get(`/admin/asesi/${id}/detail`, function(data) {
        $('#detail-content').html(data);
    }).fail(function() {
        $('#detail-content').html('<div class="alert alert-danger">Gagal memuat data</div>');
    });
}

function exportData() {
    Swal.fire({
        title: 'Export Data',
        text: 'Fitur export akan segera tersedia',
        icon: 'info'
    });
}
</script>
@endpush