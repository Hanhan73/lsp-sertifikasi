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
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="80">Logo</th>
                        <th>Kode</th>
                        <th>Nama TUK</th>
                        <th>Alamat</th>
                        <th>Manager</th>
                        <th>Staff</th>
                        <th>Total Asesi</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tuks as $tuk)
                    <tr>
                        <td>
                            @if($tuk->logo_path)
                            <img src="{{ $tuk->logo_url }}" alt="Logo" class="img-thumbnail"
                                style="max-width: 60px; max-height: 60px;">
                            @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px; border-radius: 4px;">
                                <i class="bi bi-building text-muted"></i>
                            </div>
                            @endif
                        </td>
                        <td><strong>{{ $tuk->code }}</strong></td>
                        <td>{{ $tuk->name }}</td>
                        <td>{{ Str::limit($tuk->address, 50) }}</td>
                        <td>{{ $tuk->manager_name ?? '-' }}</td>
                        <td>{{ $tuk->staff_name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $tuk->asesmens_count ?? 0 }}</span>
                        </td>
                        <td>
                            @if($tuk->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.tuks.edit', $tuk) }}" class="btn btn-sm btn-warning"
                                    data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-info" onclick="viewDetail({{ $tuk->id }})"
                                    data-bs-toggle="tooltip" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="confirmDelete({{ $tuk->id }})" data-bs-toggle="tooltip" title="Hapus">
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
            <div class="modal-header">
                <h5 class="modal-title">Detail TUK</h5>
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
$(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});

function viewDetail(id) {
    $('#detailModal').modal('show');
    $('#detail-content').html('<div class="text-center"><div class="spinner-border"></div></div>');

    $.get(`/admin/tuks/${id}/detail`, function(data) {
        $('#detail-content').html(data);
    }).fail(function() {
        $('#detail-content').html('<div class="alert alert-danger">Gagal memuat data</div>');
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus TUK?',
        text: 'Data TUK dan semua data terkait akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/tuks/${id}`;
            form.submit();
        }
    });
}
</script>
@endpush