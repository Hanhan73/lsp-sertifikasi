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
                    @foreach($asesmens->pluck('tuk')->unique('id')->filter()->sortBy('name') as $tuk)
                    <option value="{{ $tuk->id }}">{{ $tuk->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Filter Skema</label>
                <select id="filter-skema" class="form-select form-select-sm">
                    <option value="">Semua Skema</option>
                    @foreach($asesmens->pluck('skema')->unique('id')->filter()->sortBy('name') as $skema)
                    <option value="{{ $skema->id }}">{{ $skema->name }}</option>
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
                    @forelse($asesmens as $asesmen)
                    <tr data-status="{{ $asesmen->status }}"
                        data-type="{{ $asesmen->is_collective ? 'collective' : 'mandiri' }}"
                        data-tuk="{{ $asesmen->tuk_id }}" data-skema="{{ $asesmen->skema_id }}">
                        <td><strong>#{{ $asesmen->id }}</strong></td>
                        <td>
                            {{ $asesmen->full_name ?? $asesmen->user->name ?? '-' }}
                            @if($asesmen->is_collective)
                            <br><small class="text-muted"><i class="bi bi-layers"></i>
                                {{ $asesmen->collective_batch_id }}</small>
                            @endif
                        </td>
                        <td><small>{{ $asesmen->email ?? $asesmen->user->email ?? '-' }}</small></td>
                        <td>{{ $asesmen->tuk->name ?? '-' }}</td>
                        <td><small>{{ $asesmen->skema->name ?? '-' }}</small></td>
                        <td>
                            @if($asesmen->is_collective)
                            <span class="badge bg-primary"><i class="bi bi-people"></i> Kolektif</span>
                            @else
                            <span class="badge bg-success"><i class="bi bi-person"></i> Mandiri</span>
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
                        <td><small>{{ $asesmen->registration_date->format('d/m/Y') }}</small></td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewDetail({{ $asesmen->id }})"
                                data-bs-toggle="tooltip" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">Belum ada data asesi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge"></i> Detail Asesi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-content">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Destroy existing DataTable if exists
    if ($.fn.DataTable.isDataTable('#asesi-table')) {
        $('#asesi-table').DataTable().destroy();
    }

    // Initialize DataTable
    const table = $('#asesi-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        order: [
            [10, 'desc']
        ], // Sort by registration date
        pageLength: 25,
        responsive: true,
        columnDefs: [{
                orderable: false,
                targets: 11
            } // Disable sorting on action column
        ]
    });

    // Custom filter function
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            const status = $('#filter-status').val();
            const type = $('#filter-type').val();
            const tuk = $('#filter-tuk').val();
            const skema = $('#filter-skema').val();

            const row = table.row(dataIndex).node();
            const rowStatus = $(row).data('status');
            const rowType = $(row).data('type');
            const rowTuk = $(row).data('tuk');
            const rowSkema = $(row).data('skema');

            if (status && rowStatus !== status) return false;
            if (type && rowType !== type) return false;
            if (tuk && rowTuk != tuk) return false;
            if (skema && rowSkema != skema) return false;

            return true;
        }
    );

    // Filters - trigger redraw
    $('#filter-status, #filter-type, #filter-tuk, #filter-skema').on('change', function() {
        table.draw();
    });
});

// View detail function
function viewDetail(id) {
    console.log('View detail for asesi:', id);

    // Show modal
    $('#detailModal').modal('show');

    // Show loading
    $('#detail-content').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuat data asesi...</p>
        </div>
    `);

    // Fetch detail via AJAX
    $.ajax({
        url: `/admin/asesi/${id}/detail`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Success response:', response);
            if (response.success) {
                $('#detail-content').html(response.html);
            } else {
                $('#detail-content').html(`
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        ${response.message || 'Gagal memuat data'}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading detail:', {
                xhr,
                status,
                error
            });

            let errorMessage = 'Terjadi kesalahan saat memuat data';
            if (xhr.status === 404) {
                errorMessage = 'Data asesi tidak ditemukan';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses untuk melihat data ini';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            $('#detail-content').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i>
                    <strong>Error!</strong> ${errorMessage}
                </div>
            `);
        }
    });
}

// Export data function
function exportData() {
    Swal.fire({
        title: 'Export Data',
        text: 'Fitur export akan segera tersedia',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}
</script>
@endpush