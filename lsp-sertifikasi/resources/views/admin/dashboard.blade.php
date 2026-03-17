@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Admin LSP')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #667eea; --bg-color-end: #764ba2;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Total Asesi</p>
                    <h3>{{ $stats['total_asesi'] }}</h3>
                </div>
                <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #f093fb; --bg-color-end: #f5576c;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Total TUK</p>
                    <h3>{{ $stats['total_tuk'] }}</h3>
                </div>
                <i class="bi bi-building" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #4facfe; --bg-color-end: #00f2fe;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Perlu Penetapan Biaya</p>
                    <h3>{{ $stats['pending_verification'] }}</h3>
                </div>
                <i class="bi bi-cash-coin" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #43e97b; --bg-color-end: #38f9d7;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Tersertifikasi</p>
                    <h3>{{ $stats['certified'] }}</h3>
                </div>
                <i class="bi bi-award" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Batch Info (if exists) -->
@if($batchInfo)
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-layers"></i> Batch Kolektif Terbaru
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Batch ID</strong></td>
                        <td>: {{ $batchInfo['batch_id'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Peserta</strong></td>
                        <td>: {{ $batchInfo['total_members'] }} orang</td>
                    </tr>
                    <tr>
                        <td><strong>TUK</strong></td>
                        <td>: {{ $batchInfo['tuk']->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Didaftarkan Oleh</strong></td>
                        <td>: {{ $batchInfo['registered_by']->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status Pembayaran</strong></td>
                        <td>:
                            @if($batchInfo['payment_status'] === 'paid')
                            <span class="badge bg-success">Sudah Bayar</span>
                            @elseif($batchInfo['payment_status'] === 'pending')
                            <span class="badge bg-warning">Pending</span>
                            @else
                            <span class="badge bg-secondary">Belum Bayar</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Asesmens -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Asesi Terbaru</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="recent-asesmens-table">
                <thead>
                    <tr>
                        <th>No Registrasi</th>
                        <th>Nama</th>
                        <th>TUK</th>
                        <th>Skema</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asesmens as $asesmen)
                    <tr>
                        <td><strong>#{{ $asesmen->id }}</strong></td>
                        <td>
                            {{ $asesmen->full_name ?? $asesmen->user->name }}
                            @if($asesmen->is_collective)
                            <br><small class="text-muted"><i class="bi bi-layers"></i>
                                {{ $asesmen->collective_batch_id }}</small>
                            @endif
                        </td>
                        <td>{{ $asesmen->tuk->name ?? '-' }}</td>
                        <td>{{ $asesmen->skema->name ?? '-' }}</td>
                        <td>
                            @if($asesmen->is_collective)
                            <span class="badge bg-primary"><i class="bi bi-people"></i> Kolektif</span>
                            @else
                            <span class="badge bg-success"><i class="bi bi-person"></i> Mandiri</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $asesmen->status_badge }} badge-status">
                                {{ $asesmen->status_label }}
                            </span>
                        </td>
                        <td>{{ $asesmen->registration_date->format('d/m/Y') }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewDetail({{ $asesmen->id }})"
                                data-bs-toggle="tooltip" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">
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
                    <i class="bi bi-info-circle"></i> Detail Asesi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-content">
                <!-- Content will be loaded here -->
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

    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#recent-asesmens-table')) {
        $('#recent-asesmens-table').DataTable().destroy();
    }

    $('#recent-asesmens-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        order: [
            [6, 'desc']
        ], // Sort by date column
        pageLength: 10,
        responsive: true,
        columnDefs: [{
                orderable: false,
                targets: 7
            } // Disable sorting on action column
        ]
    });
});

// ✅ PERBAIKAN: Function untuk view detail
function viewDetail(asesmenId) {
    console.log('View detail for asesmen:', asesmenId);

    // Show modal immediately
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
        url: `/admin/asesmens/${asesmenId}/detail`,
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

// Alternative function (jika ada yang masih menggunakan nama lama)
function showAsesmenDetail(asesmenId) {
    viewDetail(asesmenId);
}
</script>
@endpush