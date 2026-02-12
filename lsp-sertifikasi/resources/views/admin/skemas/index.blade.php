@extends('layouts.app')

@section('title', 'Kelola Skema')
@section('page-title', 'Manajemen Skema Sertifikasi')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-file-earmark-text"></i> Daftar Skema Sertifikasi
        </h5>
        <a href="{{ route('admin.skemas.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Skema Baru
        </a>
    </div>
    <div class="card-body">
        @if($skemas->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3 text-muted">Belum Ada Skema</h4>
            <p class="text-muted">Silakan tambahkan skema sertifikasi pertama Anda</p>
            <a href="{{ route('admin.skemas.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle"></i> Tambah Skema
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Skema</th>
                        <th>Deskripsi</th>
                        <th>Biaya</th>
                        <th>Durasi (Hari)</th>
                        <th>Total Asesi</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($skemas as $skema)
                    <tr>
                        <td><strong>{{ $skema->code }}</strong></td>
                        <td>{{ $skema->name }}</td>
                        <td>{{ Str::limit($skema->description ?? '-', 50) }}</td>
                        <td><strong class="text-success">Rp {{ number_format($skema->fee, 0, ',', '.') }}</strong></td>
                        <td>
                            <span class="badge bg-info">{{ $skema->duration_days }} hari</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $skema->asesmens_count ?? 0 }}</span>
                        </td>
                        <td>
                            @if($skema->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.skemas.edit', $skema) }}" class="btn btn-sm btn-warning"
                                    data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="confirmDelete({{ $skema->id }})" data-bs-toggle="tooltip" title="Hapus">
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
@endsection

@push('scripts')
<script>
$(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Skema?',
        text: 'Data skema akan dihapus permanen! Pastikan tidak ada asesi yang menggunakan skema ini.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/skemas/${id}`;
            form.submit();
        }
    });
}
</script>
@endpush