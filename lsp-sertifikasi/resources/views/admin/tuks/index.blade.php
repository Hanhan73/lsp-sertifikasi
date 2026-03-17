@extends('layouts.app')

@section('title', 'Kelola TUK')
@section('page-title', 'Manajemen TUK (Tempat Uji Kompetensi)')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-building"></i> Daftar TUK
            <span class="badge bg-primary ms-2">{{ $tuks->count() }}</span>
        </h5>
        <a href="{{ route('admin.tuks.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah TUK Baru
        </a>
    </div>
    <div class="card-body">
        @if($tuks->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-building" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3 text-muted">Belum Ada TUK</h4>
            <p class="text-muted">Silakan tambahkan TUK pertama Anda</p>
            <a href="{{ route('admin.tuks.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle"></i> Tambah TUK
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th width="60">Logo</th>
                        <th>Kode</th>
                        <th>Nama TUK</th>
                        <th>Alamat</th>
                        <th>Manajer</th>
                        <th>Bendahara</th> <!-- ✅ NEW -->
                        <th>Staff</th>
                        <th>No. HP</th> <!-- ✅ NEW -->
                        <th>SK TUK</th> <!-- ✅ NEW -->
                        <th>Total Asesi</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tuks as $tuk)
                    <tr>
                        <td>
                            @if($tuk->logo_path)
                            <img src="{{ $tuk->logo_url }}" alt="Logo" class="img-thumbnail"
                                style="max-width: 50px; max-height: 50px; object-fit: cover;">
                            @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px; border-radius: 4px;">
                                <i class="bi bi-building text-muted"></i>
                            </div>
                            @endif
                        </td>
                        <td>
                            <strong class="text-primary">{{ $tuk->code }}</strong>
                        </td>
                        <td>
                            <strong>{{ $tuk->name }}</strong>
                            @if($tuk->email)
                            <br><small class="text-muted"><i class="bi bi-envelope"></i> {{ $tuk->email }}</small>
                            @endif
                        </td>
                        <td>
                            <small>{{ Str::limit($tuk->address, 40) }}</small>
                        </td>
                        <td>{{ $tuk->manager_name ?? '-' }}</td>
                        <td>{{ $tuk->treasurer_name ?? '-' }}</td> <!-- ✅ NEW -->
                        <td>{{ $tuk->staff_name ?? '-' }}</td>
                        <td>
                            <!-- ✅ NEW -->
                            @if($tuk->phone)
                            <small><i class="bi bi-telephone"></i> {{ $tuk->phone }}</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <!-- ✅ NEW -->
                            @if($tuk->hasSkDocument())
                            <a href="{{ $tuk->sk_document_url }}" target="_blank" class="btn btn-sm btn-success"
                                data-bs-toggle="tooltip" title="Lihat SK Penetapan">
                                <i class="bi bi-file-earmark-check-fill"></i>
                            </a>
                            @else
                            <span class="text-muted" data-bs-toggle="tooltip" title="Belum ada SK">
                                <i class="bi bi-dash-circle"></i>
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info fs-6">{{ $tuk->asesmens_count ?? 0 }}</span>
                        </td>
                        <td>
                            @if($tuk->is_active)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Aktif
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Nonaktif
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.tuks.edit', $tuk) }}" class="btn btn-sm btn-warning"
                                    data-bs-toggle="tooltip" title="Edit TUK">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-info" onclick="viewDetail({{ $tuk->id }})"
                                    data-bs-toggle="tooltip" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="confirmDelete({{ $tuk->id }}, '{{ $tuk->name }}')" data-bs-toggle="tooltip"
                                    title="Hapus TUK">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-building"></i> Detail TUK
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
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

@push('styles')
<style>
.datatable tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script>
$(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Initialize DataTable
    if (!$.fn.DataTable.isDataTable('.datatable')) {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [
                [1, 'asc']
            ],
            pageLength: 25,
            columnDefs: [{
                orderable: false,
                targets: [0, -1]
            }]
        });
    }
});

function viewDetail(id) {
    console.log('Loading TUK detail for ID:', id);

    $('#detailModal').modal('show');
    $('#detail-content').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuat data TUK...</p>
        </div>
    `);

    // ✅ FIX: Gunakan route yang benar untuk detail
    $.ajax({
        url: `/admin/tuks/${id}/detail`,
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
            console.error('Error loading TUK detail:', {
                xhr,
                status,
                error
            });

            let errorMessage = 'Terjadi kesalahan saat memuat data TUK';
            if (xhr.status === 404) {
                errorMessage = 'Data TUK tidak ditemukan';
            } else if (xhr.status === 500) {
                errorMessage = 'Kesalahan server: ' + (xhr.responseJSON?.message || error);
            }

            $('#detail-content').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Error!</strong> ${errorMessage}
                </div>
            `);
        }
    });
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus TUK?',
        html: `
            <p>Anda akan menghapus TUK:</p>
            <strong class="text-danger">${name}</strong>
            <p class="mt-2 small text-muted">Data TUK, logo, SK document, dan akun login akan dihapus permanen!</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const form = document.getElementById('delete-form');
            form.action = `/admin/tuks/${id}`;
            form.submit();
        }
    });
}
</script>
@endpush